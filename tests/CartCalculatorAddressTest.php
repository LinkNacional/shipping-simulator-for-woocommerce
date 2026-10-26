<?php
/**
 * Testes do preenchimento de endereço do calculador NATIVO do carrinho
 * (`Calculadora_Frete::fill_cart_address_from_postcode`).
 *
 * Regressão do chamado "Nenhuma opção de entrega foi encontrada para XXXXX-XXX
 * para qualquer CEP": como país/estado/cidade são ocultados no calculador do
 * carrinho, o endereço vem do CEP — e a UF precisa ser preenchida mesmo quando a
 * consulta externa de CEP falha (fallback pelas faixas de CEP).
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Calculadora_Api;

class CartCalculatorAddressTest extends TestCase {

	/**
	 * Simula o POST do calculador do carrinho (só o CEP é enviado).
	 *
	 * @param string               $postcode CEP informado.
	 * @param array<string, mixed> $overrides Campos extras do endereço.
	 * @return array<string, mixed>
	 */
	private function calculate_address( string $postcode, array $overrides = [] ): array {
		return apply_filters(
			'woocommerce_cart_calculate_shipping_address',
			array_merge(
				[
					'country'  => '',
					'state'    => '',
					'postcode' => $postcode,
					'city'     => '',
				],
				$overrides
			)
		);
	}

	protected function setUp(): void {
		parent::setUp();

		// O `WC()->customer` é um singleton de processo: garante loja BR e nenhum
		// aviso pendente herdado de outro teste.
		wc_clear_notices();

		if ( function_exists( 'WC' ) && WC()->customer ) {
			WC()->customer->set_shipping_country( 'BR' );
			WC()->customer->set_billing_country( 'BR' );
		}
	}

	public function test_filter_is_registered() {
		$this->assertNotFalse( has_filter( 'woocommerce_cart_calculate_shipping_address' ) );
	}

	public function test_fills_state_from_postcode_range_when_lookup_fails() {
		// Nenhuma fonte de CEP responde (API fora do ar / hospedagem sem saída HTTP).
		$this->mock_http( [] );

		$address = $this->calculate_address( '01310-100' );

		$this->assertSame( 'BR', $address['country'] );
		$this->assertSame( 'SP', $address['state'] );
		$this->assertSame( '01310-100', $address['postcode'] );
		$this->assertSame( '', $address['city'] );
		// A UF resolveu: o cliente não é incomodado com aviso de CEP inválido.
		$this->assertSame( [], wc_get_notices( 'error' ) );
	}

	public function test_fills_address_from_cep_lookup() {
		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		$address = $this->calculate_address( '60165-081' );

		$this->assertSame( 'BR', $address['country'] );
		$this->assertSame( 'CE', $address['state'] );
		$this->assertSame( 'Fortaleza', $address['city'] );
	}

	public function test_state_overrides_value_sent_by_the_form() {
		$this->mock_http( [] );

		// Estado/cidade vêm de outra fonte (ex.: sessão antiga): o CEP manda.
		$address = $this->calculate_address(
			'90010-000',
			[
				'state' => 'SP',
				'city'  => 'São Paulo',
			]
		);

		$this->assertSame( 'RS', $address['state'] );
	}

	public function test_unknown_postcode_adds_error_notice() {
		$this->mock_http( [] );

		// 00000000 não pertence a nenhuma faixa de CEP brasileira.
		$address = $this->calculate_address( '00000000' );

		$this->assertSame( '', $address['state'] );

		$notices = wc_get_notices( 'error' );
		$this->assertCount( 1, $notices );
		$this->assertStringContainsString( 'Não foi possível identificar o CEP', $notices[0]['notice'] );
	}

	public function test_state_from_postcode_maps_brazilian_ranges() {
		$this->assertSame( 'SP', Calculadora_Api::state_from_postcode( '01310100' ) );
		$this->assertSame( 'SP', Calculadora_Api::state_from_postcode( '01310-100' ) );
		$this->assertSame( 'RJ', Calculadora_Api::state_from_postcode( '22041080' ) );
		$this->assertSame( 'CE', Calculadora_Api::state_from_postcode( '60165081' ) );
		$this->assertSame( 'RS', Calculadora_Api::state_from_postcode( '90010000' ) );
		$this->assertSame( '', Calculadora_Api::state_from_postcode( '00000000' ) );
		$this->assertSame( '', Calculadora_Api::state_from_postcode( '123' ) );
	}

	/**
	 * Estados cuja faixa de CEP fica DENTRO da faixa de outro (RR dentro de AM,
	 * Entorno do DF em GO dentro de DF). As UFs abaixo foram conferidas no ViaCEP.
	 *
	 * Regressão: com as faixas sobrepostas herdadas do woo-better, Boa Vista saía
	 * como AM e Luziânia/Valparaíso/Águas Lindas saíam como DF — o que manda o
	 * cliente para a zona de frete errada (ou para nenhuma).
	 */
	public function test_state_from_postcode_handles_nested_ranges() {
		$casos = [
			// Entorno do DF (GO) dentro da faixa de DF.
			'72800100' => 'GO', // Luziânia
			'72870010' => 'GO', // Valparaíso de Goiás
			'72910010' => 'GO', // Águas Lindas de Goiás
			'73700000' => 'GO', // Padre Bernardo
			'73330100' => 'DF', // Brasília
			// RR dentro da faixa de AM.
			'69301000' => 'RR', // Boa Vista
			'69700000' => 'AM', // Barcelos
		];

		foreach ( $casos as $cep => $uf ) {
			$this->assertSame( $uf, Calculadora_Api::state_from_postcode( $cep ), "CEP {$cep}" );
		}
	}

	/**
	 * Trava estrutural: nenhum CEP pode casar com duas faixas diferentes, senão a
	 * UF passa a depender da ORDEM da tabela (foi assim que RR e GO foram
	 * engolidos por AM e DF).
	 */
	public function test_state_ranges_do_not_overlap() {
		$ranges = Calculadora_Api::get_cep_state_ranges();
		$total  = count( $ranges );

		for ( $i = 0; $i < $total; $i++ ) {
			for ( $j = $i + 1; $j < $total; $j++ ) {
				$a = $ranges[ $i ];
				$b = $ranges[ $j ];

				$sobrepoe = (int) $a['min'] <= (int) $b['max'] && (int) $b['min'] <= (int) $a['max'];

				$this->assertFalse(
					$sobrepoe,
					sprintf(
						'Faixas sobrepostas: %s (%s-%s) e %s (%s-%s)',
						$a['name'],
						$a['min'],
						$a['max'],
						$b['name'],
						$b['min'],
						$b['max']
					)
				);
			}
		}
	}

	public function test_lookup_cep_is_cached_between_requests() {
		// Habilita o cache persistente (o TestCase o desliga para manter os testes
		// de HTTP determinísticos), reproduzindo duas requisições diferentes.
		remove_all_filters( 'wc_shipping_simulator_cep_cache_ttl' );

		$this->mock_http( [
			'brasilapi.com.br' => [ 'code' => 200, 'body' => $this->brasilapi_body() ],
		] );

		Calculadora_Api::lookup_cep( '60165081' );

		// Cache da requisição zerado: a segunda consulta vem do transient.
		$this->reset_cep_cache();

		$info = Calculadora_Api::lookup_cep( '60165081' );

		$this->assertTrue( $info['status'] );
		$this->assertSame( 1, $this->http_request_count() );
	}

	public function test_lookup_cep_caches_failures_briefly() {
		remove_all_filters( 'wc_shipping_simulator_cep_cache_error_ttl' );

		$this->mock_http( [] );

		Calculadora_Api::lookup_cep( '60165081' );
		$this->reset_cep_cache();
		$info = Calculadora_Api::lookup_cep( '60165081' );

		$this->assertFalse( $info['status'] );
		// BrasilAPI + ViaCEP (2 chamadas) e nada mais na segunda consulta.
		$this->assertSame( 2, $this->http_request_count() );
	}
}
