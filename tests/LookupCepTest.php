<?php
/**
 * Testes de `Calculadora_Api::lookup_cep()`.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Calculadora_Api;

class LookupCepTest extends TestCase {

	public function test_returns_normalized_data_from_brasilapi() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$info = Calculadora_Api::lookup_cep( '60165081' );

		$this->assertTrue( $info['status'] );
		$this->assertSame( 'Fortaleza', $info['city'] );
		$this->assertSame( 'CE', $info['state_sigla'] );
		$this->assertSame( 'Ceará', $info['state'] );
		$this->assertSame( 'Avenida da Abolição', $info['address'] );
		$this->assertSame( 'Meireles', $info['neighborhood'] );
	}

	public function test_accepts_masked_postcode() {
		$this->mock_http( [
			'brasilapi.com.br/api/cep/v2/60165081' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$info = Calculadora_Api::lookup_cep( '60165-081' );

		$this->assertTrue( $info['status'] );
		$this->assertSame( 'Fortaleza', $info['city'] );
	}

	public function test_falls_back_to_viacep_when_brasilapi_fails() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 500, 'body' => '{}' ],
			'viacep.com.br'    => [ 'code' => 200, 'body' => $this->viacep_body() ],
		] );

		$info = Calculadora_Api::lookup_cep( '60165081' );

		$this->assertTrue( $info['status'] );
		$this->assertSame( 'Fortaleza', $info['city'] );
		$this->assertSame( 'CE', $info['state_sigla'] );
		$this->assertSame( 'Avenida da Abolição', $info['address'] );
		// Confirma que as duas fontes foram consultadas.
		$this->assertSame( 2, $this->http_request_count() );
	}

	public function test_invalid_format_does_not_hit_network() {
		$this->mock_http( [] );

		$info = Calculadora_Api::lookup_cep( '123' );

		$this->assertFalse( $info['status'] );
		$this->assertSame( 0, $this->http_request_count() );
	}

	public function test_result_is_cached_within_request() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$a = Calculadora_Api::lookup_cep( '60165081' );
		$b = Calculadora_Api::lookup_cep( '60165-081' ); // mesmo CEP, com máscara

		$this->assertSame( $a, $b );
		$this->assertSame( 1, $this->http_request_count() );
	}

	public function test_not_found_returns_error() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 404, 'body' => wp_json_encode( [ 'errors' => [ [ 'message' => 'not found' ] ] ] ) ],
			'viacep.com.br'    => [ 'code' => 200, 'body' => '{"erro":true}' ],
		] );

		$info = Calculadora_Api::lookup_cep( '99999999' );

		$this->assertFalse( $info['status'] );
	}
}
