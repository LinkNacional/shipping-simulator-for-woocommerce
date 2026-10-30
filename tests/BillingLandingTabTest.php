<?php
/**
 * Regressão: o link final do fluxo de compra abre a aba do recurso PRO.
 *
 * Antes, "Ir para configurações" levava para a raiz da tela e o nav sempre
 * abria no primeiro bloco ("Geral"). Quem acabou de comprar o PRO caía numa aba
 * genérica e precisava caçar onde ficava o recurso que pagou.
 *
 * Agora `Billing::settings_url()` leva `?lkn_open=<bloco>` e o template do
 * layout marca ESSE bloco como `active` no markup. O nav é controlado por JS (as
 * abas não têm `href` de âncora), então o parâmetro é o único caminho — `#bloco`
 * não faria nada. A escolha é server-side de propósito: clicar no item durante o
 * evento `load` dependia da hora em que o handler das abas ficava pronto e
 * piscava a primeira aba antes de trocar.
 *
 * Duas guardas que este teste também cobre:
 *
 *  - o bloco precisa existir na tela (sem o PRO instalado ele não está lá);
 *  - o bloco não pode estar bloqueado (com o PRO sem licença a aba mostra o
 *    banner "recurso exclusivo"; cair nela seria pior que cair na primeira).
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Admin\Calculadora_Settings;
use Shipping_Simulator\Billing\Billing;

class BillingLandingTabTest extends TestCase {

	/**
	 * Renderiza a tela inteira, opcionalmente com PRO liberado.
	 *
	 * @param array $query    Conteúdo de `$_GET`.
	 * @param bool  $unlocked Simular o portão aberto (licença em vigor)?
	 * @return string Markup.
	 */
	private function render ( $query, $unlocked ) {
		$original = $_GET;
		$_GET     = $query;

		add_filter( 'wc_shipping_simulator_is_pro_unlocked', $unlocked ? '__return_true' : '__return_false' );

		try {
			$page = new Calculadora_Settings();

			ob_start();
			$page->output();
			$html = ob_get_clean();
		} finally {
			$_GET = $original;
			remove_filter( 'wc_shipping_simulator_is_pro_unlocked', $unlocked ? '__return_true' : '__return_false' );
		}

		return is_string( $html ) ? $html : '';
	}

	/**
	 * O id do bloco que nasce `active` na tela ('' quando nenhum).
	 *
	 * O markup é `<div class="admin-layout-block active" id="block-<id>">`, então
	 * o `class` vem ANTES do `id`.
	 *
	 * @param string $html Markup da tela.
	 * @return string
	 */
	private function opened_block ( $html ) {
		if ( ! preg_match_all( '/<div class="admin-layout-block[^"]*" id="block-([a-z0-9_-]+)"/', $html, $todos, PREG_SET_ORDER ) ) {
			return '';
		}

		foreach ( $todos as $bloco ) {
			if ( str_contains( $bloco[0], 'admin-layout-block active' ) ) {
				return $bloco[1];
			}
		}

		return '';
	}

	/**
	 * O link final aponta para o bloco do recurso PRO.
	 */
	public function test_settings_url_leva_o_bloco_do_recurso_pro () {
		$url = Billing::settings_url();

		$this->assertStringContainsString( 'page=wc-settings', $url );
		$this->assertStringContainsString( 'tab=' . Calculadora_Settings::TAB_ID, $url );
		$this->assertStringContainsString( 'lkn_open=' . Billing::LANDING_BLOCK, $url );
		$this->assertSame( 'pro_popup_cep', Billing::LANDING_BLOCK, 'Pop-up de CEP é o recurso PRO anunciado no card.' );
	}

	/**
	 * O filtro troca o destino sem mexer no resto (e vazio desliga).
	 */
	public function test_filtro_permite_trocar_ou_desligar_o_destino () {
		$troca = static function () {
			return 'carrinho';
		};

		add_filter( 'wc_shipping_simulator_landing_block', $troca );

		try {
			$this->assertStringContainsString( 'lkn_open=carrinho', Billing::settings_url() );
		} finally {
			remove_filter( 'wc_shipping_simulator_landing_block', $troca );
		}

		$vazio = static function () {
			return '';
		};

		add_filter( 'wc_shipping_simulator_landing_block', $vazio );

		try {
			$this->assertStringNotContainsString( 'lkn_open', Billing::settings_url() );
		} finally {
			remove_filter( 'wc_shipping_simulator_landing_block', $vazio );
		}
	}

	/**
	 * Com a licença em vigor, a tela abre no bloco pedido.
	 */
	public function test_com_licenca_abre_a_aba_pedida () {
		$html = $this->render( array( 'lkn_open' => 'pro_popup_cep' ), true );

		$this->assertSame(
			'pro_popup_cep',
			$this->opened_block( $html ),
			'A tela deveria mandar abrir a aba do recurso PRO.'
		);
		$this->assertStringContainsString( 'id="nav-pro_popup_cep"', $html );
		$this->assertStringContainsString( 'id="block-pro_popup_cep"', $html );
	}

	/**
	 * Sem o parâmetro, nada muda: abre na primeira aba (nenhum script).
	 */
	public function test_sem_parametro_abre_na_primeira_aba () {
		$html = $this->render( array(), true );

		$this->assertSame( 'geral', $this->opened_block( $html ), 'Sem `lkn_open`, abre na primeira aba.' );
		$this->assertStringContainsString( 'id="nav-geral" class="admin-layout-title-link active"', $html );
	}

	/**
	 * Bloqueado por licença, o pedido é IGNORADO (não cai numa aba vazia).
	 */
	public function test_sem_licenca_ignora_a_aba_pedida () {
		$html = $this->render( array( 'lkn_open' => 'pro_popup_cep' ), false );

		$this->assertSame(
			'geral',
			$this->opened_block( $html ),
			'Aba de recurso bloqueado não pode ser o destino: o usuário cairia num banner de venda.'
		);
	}

	/**
	 * Bloco inexistente (PRO não instalado) e lixo também são ignorados.
	 */
	public function test_bloco_inexistente_ou_invalido_e_ignorado () {
		foreach ( array( 'nao_existe', '<script>alert(1)</script>', 'geral' ) as $valor ) {
			$html = $this->render( array( 'lkn_open' => $valor ), true );

			$this->assertSame(
				'geral',
				$this->opened_block( $html ),
				'Valor "' . $valor . '" não pode virar destino de aba (cai na primeira).'
			);
		}
	}
}
