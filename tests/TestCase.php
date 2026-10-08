<?php
/**
 * Base dos testes integrados do plugin.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use WP_UnitTestCase;
use Shipping_Simulator\Calculadora_Api;

abstract class TestCase extends WP_UnitTestCase {

	/**
	 * URLs capturadas pelo mock de HTTP, na ordem das chamadas.
	 *
	 * @var array<int, string>
	 */
	protected $http_requests = [];

	/**
	 * Mapa "substring da URL" => resposta do mock.
	 *
	 * @var array<string, array{code:int, body:string}>
	 */
	private $http_map = [];

	protected function setUp(): void {
		parent::setUp();
		$this->reset_cep_cache();
		$this->http_requests = [];
		$this->http_map      = [];
	}

	protected function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		parent::tearDown();
	}

	/**
	 * Limpa o cache estático de `lookup_cep()` entre os testes.
	 */
	protected function reset_cep_cache(): void {
		if ( ! property_exists( Calculadora_Api::class, 'cep_lookup_cache' ) ) {
			return;
		}

		$ref = new \ReflectionProperty( Calculadora_Api::class, 'cep_lookup_cache' );
		$ref->setAccessible( true );
		$ref->setValue( null, [] );
	}

	/**
	 * Instala um mock de `wp_remote_get()`.
	 *
	 * @param array<string, array{code:int, body:string}> $map Substring da URL => resposta.
	 */
	protected function mock_http( array $map ): void {
		$this->http_map = $map;

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				$this->http_requests[] = $url;

				foreach ( $this->http_map as $needle => $response ) {
					if ( false !== strpos( $url, $needle ) ) {
						return [
							'headers'  => [],
							'body'     => $response['body'],
							'response' => [ 'code' => $response['code'], 'message' => 'OK' ],
							'cookies'  => [],
							'filename' => null,
						];
					}
				}

				return new \WP_Error( 'no_mock', 'Sem mock para ' . $url );
			},
			10,
			3
		);
	}

	protected function http_request_count(): int {
		return count( $this->http_requests );
	}

	/**
	 * Corpo de resposta no formato da BrasilAPI (`/api/cep/v2/`).
	 */
	protected function brasilapi_body( array $overrides = [] ): string {
		return wp_json_encode( array_merge(
			[
				'cep'          => '60165081',
				'state'        => 'CE',
				'city'         => 'Fortaleza',
				'neighborhood' => 'Meireles',
				'street'       => 'Avenida da Abolição',
				'service'      => 'open-cep',
			],
			$overrides
		) );
	}

	/**
	 * Corpo de resposta no formato do ViaCEP (`/ws/{cep}/json/`).
	 */
	protected function viacep_body( array $overrides = [] ): string {
		return wp_json_encode( array_merge(
			[
				'cep'        => '60165-081',
				'logradouro' => 'Avenida da Abolição',
				'bairro'     => 'Meireles',
				'localidade' => 'Fortaleza',
				'uf'         => 'CE',
				'estado'     => 'Ceará',
			],
			$overrides
		) );
	}
}
