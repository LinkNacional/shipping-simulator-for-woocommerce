<?php
/**
 * Testes de `Integration\Autofill_Brazilian_Addresses` — preenchimento do
 * endereço do pacote a partir do CEP.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Integration\Autofill_Brazilian_Addresses;

class AutofillBrazilianAddressesTest extends TestCase {

	private function make_package( string $postcode, string $country = 'BR' ): array {
		return [
			'destination' => [
				'country'   => $country,
				'state'     => '',
				'postcode'  => $postcode,
				'city'      => '',
				'address_1' => '',
			],
		];
	}

	public function test_fills_destination_from_lookup_cep() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$instance = new Autofill_Brazilian_Addresses();
		$package  = $instance->fill_package_destination( $this->make_package( '60165081' ) );
		$dest     = $package['destination'];

		$this->assertSame( 'Fortaleza', $dest['city'] );
		$this->assertSame( 'CE', $dest['state'] );
		$this->assertSame( 'Avenida da Abolição', $dest['address_1'] );
		$this->assertStringContainsString( 'Meireles', $dest['address'] );
		$this->assertStringContainsString( 'Fortaleza', $dest['address'] );
	}

	public function test_falls_back_to_opencep_when_lookup_fails() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 500, 'body' => '{}' ],
			'viacep.com.br'    => [ 'code' => 200, 'body' => '{"erro":true}' ],
			'opencep.com'      => [ 'code' => 200, 'body' => wp_json_encode( [
				'cep'        => '60165-081',
				'logradouro' => 'Avenida da Abolição',
				'bairro'     => 'Meireles',
				'localidade' => 'Fortaleza',
				'uf'         => 'CE',
				'estado'     => 'Ceará',
			] ) ],
		] );

		$instance = new Autofill_Brazilian_Addresses();
		$package  = $instance->fill_package_destination( $this->make_package( '60165081' ) );
		$dest     = $package['destination'];

		$this->assertSame( 'Fortaleza', $dest['city'] );
		$this->assertSame( 'CE', $dest['state'] );
		$this->assertSame( 'Avenida da Abolição', $dest['address_1'] );
	}

	public function test_ignores_non_brazil_destination() {
		$this->mock_http( [] );

		$instance = new Autofill_Brazilian_Addresses();
		$package  = $instance->fill_package_destination( $this->make_package( '10001', 'US' ) );

		$this->assertSame( '10001', $package['destination']['postcode'] );
		$this->assertSame( '', $package['destination']['city'] );
		$this->assertSame( 0, $this->http_request_count() );
	}
}
