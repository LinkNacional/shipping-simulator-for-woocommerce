<?php
/**
 * Testes da integração `Integration\Brazil` — montagem do destino do pacote e
 * sincronização do endereço do cliente (correção do chamado "produto não pode
 * ser entregue na região informada").
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Shipping_Package;
use Shipping_Simulator\Integration\Brazil;

class BrazilIntegrationTest extends TestCase {

	private function make_brazil(): Brazil {
		return new Brazil();
	}

	private function destination_of( Brazil $brazil, array $posted ): array {
		$package = new Shipping_Package();
		$package = $brazil->update_package( $package, $posted );

		return $package->get_package()['destination'];
	}

	public function test_update_package_builds_full_destination_from_brasilapi() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$dest = $this->destination_of( $this->make_brazil(), [
			'country'  => 'BR',
			'postcode' => '60165081',
		] );

		$this->assertSame( 'BR', $dest['country'] );
		$this->assertSame( 'CE', $dest['state'] );
		$this->assertSame( 'Fortaleza', $dest['city'] );
		$this->assertSame( 'Avenida da Abolição', $dest['address_1'] );
		$this->assertStringContainsString( 'Avenida da Abolição', $dest['address'] );
		$this->assertStringContainsString( 'Fortaleza', $dest['address'] );
	}

	public function test_update_package_falls_back_to_state_range_when_cep_lookup_fails() {
		// Nenhuma fonte de CEP responde: cai na faixa de CEP do state.
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 500, 'body' => '{}' ],
			'viacep.com.br'    => [ 'code' => 200, 'body' => '{"erro":true}' ],
		] );

		$dest = $this->destination_of( $this->make_brazil(), [
			'country'  => 'BR',
			'postcode' => '60165081',
		] );

		$this->assertSame( 'BR', $dest['country'] );
		$this->assertSame( 'CE', $dest['state'] );
		$this->assertSame( '', $dest['city'] );
	}

	public function test_update_package_ignores_non_brazil_destination() {
		$this->mock_http( [] );

		$dest = $this->destination_of( $this->make_brazil(), [
			'country'  => 'US',
			'postcode' => '10001',
		] );

		$this->assertSame( '', $dest['country'] );
		$this->assertSame( '', $dest['state'] );
		$this->assertSame( 0, $this->http_request_count() );
	}

	public function test_sync_customer_shipping_address_sets_full_address() {
		$package = [
			'destination' => [
				'country'   => 'BR',
				'state'     => 'CE',
				'postcode'  => '60165081',
				'city'      => 'Fortaleza',
				'address_1' => 'Avenida da Abolição',
			],
		];

		$this->make_brazil()->sync_customer_shipping_address( $package );

		$customer = WC()->customer;
		$this->assertSame( 'BR', $customer->get_shipping_country() );
		$this->assertSame( 'CE', $customer->get_shipping_state() );
		$this->assertSame( '60165081', $customer->get_shipping_postcode() );
		$this->assertSame( 'Fortaleza', $customer->get_shipping_city() );
		$this->assertSame( 'Avenida da Abolição', $customer->get_shipping_address_1() );
		$this->assertTrue( $customer->has_full_shipping_address() );
	}

	public function test_sync_customer_shipping_address_skips_empty_destination() {
		WC()->customer->set_shipping_country( 'US' );
		WC()->customer->set_shipping_city( 'New York' );

		$package = [ 'destination' => [ 'country' => '' ] ];

		$this->make_brazil()->sync_customer_shipping_address( $package );

		// Não mexe no cliente quando o destino não tem país.
		$this->assertSame( 'US', WC()->customer->get_shipping_country() );
		$this->assertSame( 'New York', WC()->customer->get_shipping_city() );
		$this->assertSame( 0, $this->http_request_count() );
	}
}
