<?php
/**
 * Regressão: as telas de configuração NÃO podem fingir "desabilitado" com o
 * atributo `disabled`.
 *
 * Bug (herdado do woo-better 5.2.0): com o "Frete grátis por valor mínimo" em
 * "Desabilitar", os campos filhos — "Somente frete grátis", "Evitar duplicidade
 * de frete grátis", "Valores na barra de progresso" e a "Base do frete grátis" —
 * recebiam `disabled`. Input `disabled` não vai no POST, então o
 * `WC_Admin_Settings::save_fields()` mantinha o valor ANTIGO da option: a tela
 * mostrava "Não"/cinza e a calculadora continuava com o recurso ativo.
 *
 * Regra (AGENTS.md § 4-B): o "desabilitado" é apenas visual — classe
 * `.wc-shipping-simulator-fake-disabled` + bloqueio de clique/teclado em captura
 * (`window.wcShippingSimulatorFakeDisabled`) — e o input segue habilitado para o
 * valor ir no POST.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use WP_UnitTestCase;
use Shipping_Simulator\Admin\Calculadora_Settings;

class AdminFakeDisabledTest extends WP_UnitTestCase {

	private const HELPER = 'Admin/js/WcShippingSimulatorAdminFakeDisabled.js';

	/**
	 * Fontes de admin que mexem em estado de dependência das configurações.
	 *
	 * @var string[]
	 */
	private const ADMIN_SOURCES = [
		'Admin/js/WcShippingSimulatorAdminSettings.js',
		'Admin/js/WcShippingSimulatorAdminLayout.js',
	];

	private function path( string $relative ): string {
		return dirname( __DIR__ ) . '/' . $relative;
	}

	private function read( string $relative ): string {
		$path = $this->path( $relative );

		$this->assertFileExists( $path, "Arquivo não encontrado: {$relative}" );

		return (string) file_get_contents( $path );
	}

	public function test_helper_exists_and_blocks_interaction_without_the_disabled_attribute() {
		$src = $this->read( self::HELPER );

		$this->assertStringContainsString(
			'window.wcShippingSimulatorFakeDisabled',
			$src,
			'O helper precisa expor `window.wcShippingSimulatorFakeDisabled`.'
		);

		$this->assertMatchesRegularExpression(
			"/addEventListener\(\s*'click',[^)]*true\s*\)/",
			$src,
			'O bloqueio de clique precisa ser em fase de CAPTURA (terceiro argumento `true`): ' .
			'`preventDefault` no `<label>` também impede a ativação do radio.'
		);

		$this->assertStringContainsString(
			'keydown',
			$src,
			'Teclado (setas/espaço) também precisa ser bloqueado — senão o valor muda via teclado.'
		);

		$this->assertStringContainsString(
			"removeAttribute('disabled')",
			$src,
			'O helper deve REMOVER um `disabled` residual, nunca aplicar.'
		);

		$this->assertStringContainsString(
			'aria-disabled',
			$src,
			'O estado precisa ser exposto via `aria-disabled` (acessibilidade).'
		);
	}

	public function test_css_class_used_by_the_helper_is_styled() {
		$css = $this->read( 'Admin/css/WcShippingSimulatorAdminSettings.css' );

		$this->assertMatchesRegularExpression(
			'/\.wc-shipping-simulator-fake-disabled\s*\{/',
			$css,
			'O `AdminSettings.css` precisa estilizar `.wc-shipping-simulator-fake-disabled`.'
		);

		$this->assertStringContainsString( 'cursor: not-allowed', $css );
	}

	public function test_helper_is_enqueued_before_the_settings_screen() {
		$php = $this->read( 'classes/Admin/Calculadora_Settings.php' );

		$this->assertStringContainsString(
			'WcShippingSimulatorAdminFakeDisabled.COMPILED.js',
			$php,
			'O bundle do helper precisa ser enfileirado na aba de configurações.'
		);

		$this->assertStringContainsString(
			"'wc-shipping-simulator-admin-fake-disabled'",
			$php,
			'O handle do helper precisa existir para ser usado como dependência.'
		);

		// O script de settings usa o helper → precisa declará-lo nas dependências.
		$this->assertMatchesRegularExpression(
			"/WcShippingSimulatorAdminSettings\.COMPILED\.js'\s*\),\s*\[\s*'wc-shipping-simulator-admin-fake-disabled'\s*\]/",
			$php,
			'O script de settings precisa declarar o helper como dependência.'
		);
	}

	public function test_no_settings_screen_applies_the_disabled_attribute_to_option_fields() {
		// Permitido: `element.disabled = false` (limpeza) e os controles que NÃO são
		// campo de opção: o botão "Limpar o cache" (AdminLayout) e o botão do modal.
		$permitidos = [
			'/element\.disabled\s*=\s*false/',
			'/confirm\.disabled\s*=\s*/',
			'/clearCacheButton\.disabled\s*=\s*false/',
		];

		foreach ( self::ADMIN_SOURCES as $arquivo ) {
			$linhas = explode( "\n", $this->read( $arquivo ) );

			foreach ( $linhas as $indice => $linha ) {
				if ( ! preg_match( '/(\.disabled\s*=\s*(true|!))|(\.prop\(\s*\'disabled\'\s*,\s*(true|!))/', $linha ) ) {
					continue;
				}

				$permitido = false;

				foreach ( $permitidos as $regex ) {
					if ( preg_match( $regex, $linha ) ) {
						$permitido = true;
						break;
					}
				}

				$this->assertTrue(
					$permitido,
					sprintf(
						'%s:%d usa o atributo `disabled` num campo de configuração — use ' .
						'`window.wcShippingSimulatorFakeDisabled(...)` (AGENTS.md § 4-B). ' .
						'Campo `disabled` não vai no POST e a option mantém o valor antigo salvo.',
						$arquivo,
						$indice + 1
					)
				);
			}
		}
	}

	public function test_free_shipping_children_are_locked_through_the_helper() {
		$src = $this->read( 'Admin/js/WcShippingSimulatorAdminSettings.js' );

		foreach ( [ 'onlyFreeShippingRadios', 'avoidDuplicationRadios', 'progressBarRadios', 'minimumFreeShippingCalcBase' ] as $alvo ) {
			$this->assertStringContainsString(
				$alvo,
				$src,
				"O bloco de dependência do frete grátis deve continuar tratando `{$alvo}`."
			);

			$this->assertMatchesRegularExpression(
				'/setFakeDisabled\(\s*' . $alvo . '\s*,/',
				$src,
				"`{$alvo}` precisa passar pelo `setFakeDisabled(...)`, nunca pelo atributo `disabled`."
			);
		}

		// O valor marcado no JS precisa ir no POST: sem clicar (o clique é
		// interceptado pelo bloqueio visual), disparando `change` para avisar quem
		// depende do campo.
		$this->assertStringContainsString(
			"radio.dispatchEvent(new Event('change', { bubbles: true }));",
			$src,
			'Ao forçar o valor, dispare `change` em vez de `radio.click()` (o clique é bloqueado).'
		);
	}

	public function test_rule_is_documented_in_agents_md() {
		$src = $this->read( 'AGENTS.md' );

		$this->assertStringContainsString(
			'wcShippingSimulatorFakeDisabled',
			$src,
			'O AGENTS.md precisa apontar o helper como caminho obrigatório.'
		);

		$this->assertMatchesRegularExpression(
			'/nunca[\s\S]{0,120}disabled/i',
			$src,
			'O AGENTS.md precisa proibir o uso do atributo `disabled` para fingir desabilitado.'
		);
	}

	/**
	 * A classe de settings continua carregável (guarda contra quebra de sintaxe
	 * no arquivo que passou a enfileirar o helper).
	 */
	public function test_settings_class_still_loads() {
		$this->assertTrue( class_exists( Calculadora_Settings::class ) );
	}
}
