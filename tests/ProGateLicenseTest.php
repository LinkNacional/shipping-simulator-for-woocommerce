<?php
/**
 * Regressão: o portão do PRO exige plugin ATIVO **e** licença em vigor.
 *
 * O bug: quem baixava o `.zip` do PRO e ativava o plugin — sem licença nenhuma —
 * via a tela do gratuito com tudo liberado. As pílulas `PRO` sumiam, o banner de
 * aviso sumia e os campos PRO passavam pelo `guard_settings()` e eram GRAVADOS.
 * Só que o PRO não registra nenhum hook sem licença (`features_enabled()`),
 * então o lojista configurava um recurso que não existia no front.
 *
 * A decisão passou a ser `is_pro_unlocked()`: `is_pro_active()` **e** a resposta
 * do `Manager` do PRO. Instalar não licencia.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use Shipping_Simulator\Admin\Calculadora_Settings;
use Shipping_Simulator\Admin\Pro_Gate;

class ProGateLicenseTest extends TestCase {

	/**
	 * Roda o callback com o "PRO liberado" simulado (ativo + licenciado).
	 *
	 * O filtro é o mesmo ponto de extensão que a tela consulta, então isto
	 * exercita o caminho real do `guard_settings()` sem depender de licença de
	 * verdade (que precisa de rede e de um servidor de licenças no ar).
	 */
	private function with_pro_unlocked ( $assertions ) {
		add_filter( 'wc_shipping_simulator_is_pro_unlocked', '__return_true' );

		try {
			$assertions();
		} finally {
			remove_filter( 'wc_shipping_simulator_is_pro_unlocked', '__return_true' );
		}
	}

	/**
	 * Render cru da tela de configurações.
	 */
	private function render_screen () {
		$page = new Calculadora_Settings();

		ob_start();
		$page->output();
		$html = ob_get_clean();

		return is_string( $html ) ? $html : '';
	}

	/**
	 * Campo PRO de mentira, com `default` diferente do valor enviado.
	 */
	private function pro_field () {
		return array(
			'id'      => 'wc_shipping_test_pro_option',
			'type'    => 'text',
			'pro'     => true,
			'default' => 'padrao',
		);
	}

	/**
	 * O PRO ATIVO, mas sem licença, NÃO libera os recursos.
	 *
	 * É o cenário do bug: `.zip` baixado, plugin ativado na tela de plugins,
	 * licença nunca informada. "Ativo" vem do filtro (o mesmo que a tela usa); a
	 * licença é perguntada ao `Manager` do PRO, que aqui responde "não" — que é
	 * exatamente o que ele responde num site sem licença.
	 */
	public function test_pro_ativo_sem_licenca_continua_bloqueado () {
		add_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );

		try {
			$this->assertTrue( Pro_Gate::is_pro_active(), 'Pré-condição: PRO ativo.' );
			$this->assertFalse(
				Pro_Gate::is_pro_unlocked(),
				'PRO ativo sem licença não pode liberar os recursos.'
			);
			$this->assertTrue(
				Pro_Gate::should_render_pill( $this->pro_field() ),
				'Sem licença o campo PRO volta a mostrar a pílula.'
			);
		} finally {
			remove_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );
		}
	}

	/**
	 * O portão pergunta a licença ao `Manager` do PRO — e obedece a resposta.
	 *
	 * Garante que a ponte entre os dois plugins é real (não um `true` fixo): o
	 * portão consulta o SINGLETON do PRO, o mesmo objeto que o `activate_key()`
	 * invalida. Aqui a resposta do `Manager` é substituída por reflexão para os
	 * dois lados (ligado/desligado), sem depender de licença nem de rede.
	 */
	public function test_portao_obedece_o_manager_do_pro () {
		$manager = 'Lkn\\ShippingSimulatorForWooCommercePro\\Includes\\License\\Manager';

		if ( ! class_exists( $manager ) && ! $this->load_pro_license_module() ) {
			$this->markTestSkipped( 'Módulo de licença do PRO não está no disco deste ambiente.' );
		}

		$instance = method_exists( $manager, 'instance' ) ? $manager::instance() : new $manager();
		$cache    = new \ReflectionProperty( $instance, 'state_cache' );
		$cache->setAccessible( true );

		add_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );

		try {
			$cache->setValue( $instance, array( 'features' => false ) );
			$this->assertFalse(
				Pro_Gate::is_pro_unlocked(),
				'Licença fora de vigor: o portão tem de fechar mesmo com o PRO ativo.'
			);

			$cache->setValue( $instance, array( 'features' => true ) );
			$this->assertTrue(
				Pro_Gate::is_pro_unlocked(),
				'Licença em vigor: o portão tem de abrir (é o que o Manager responde).'
			);
		} finally {
			// Devolve o estado real: as outras telas do mesmo processo releem.
			$cache->setValue( $instance, null );
			remove_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );
		}
	}

	/**
	 * Carrega as classes do módulo de licença do PRO, se ele estiver no disco.
	 *
	 * A suíte carrega só o plugin gratuito, então o autoload do PRO não está
	 * registrado. Carregar arquivo a arquivo é o mesmo caminho do `uninstall.php`
	 * do PRO (e evita o `vendor/autoload.php`, que colide com cópia duplicada do
	 * plugin).
	 *
	 * @return bool `true` quando as classes ficaram disponíveis.
	 */
	private function load_pro_license_module () {
		$dir = WP_PLUGIN_DIR . '/shipping-simulator-for-woocommerce-pro/Includes/License/';

		foreach ( array( 'Config', 'Certificate', 'Api_Client', 'Store', 'Manager' ) as $file ) {
			$path = $dir . $file . '.php';

			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}

		return class_exists( 'Lkn\\ShippingSimulatorForWooCommercePro\\Includes\\License\\Manager' );
	}

	/**
	 * Bloqueado, o campo PRO não é salvo e a pílula aparece.
	 */
	public function test_bloqueado_mostra_pilula_e_nao_salva () {
		$field = $this->pro_field();

		delete_option( $field['id'] );
		update_option( $field['id'], 'valor-digitado' );

		$guard = Pro_Gate::guard_settings(
			array(
				'comum' => array(
					'id'   => 'wc_shipping_test_common_option',
					'type' => 'text',
				),
				'pro'   => $field,
			)
		);

		$this->assertArrayHasKey( 'comum', $guard, 'O campo comum continua sendo salvo.' );
		$this->assertArrayNotHasKey( 'pro', $guard, 'O campo PRO não pode ir para o save_fields.' );
		$this->assertSame( 'padrao', get_option( $field['id'] ), 'A option PRO volta ao default.' );

		delete_option( $field['id'] );
	}

	/**
	 * Liberado (ativo + licença), tudo passa: sem pílula, com salvamento.
	 */
	public function test_liberado_nao_mostra_pilula_e_salva () {
		$field = $this->pro_field();

		$this->with_pro_unlocked(
			function () use ( $field ) {
				$this->assertFalse(
					Pro_Gate::should_render_pill( $field ),
					'Com a licença em vigor a pílula sai.'
				);

				$guard = Pro_Gate::guard_settings(
					array(
						'comum' => array(
							'id'   => 'wc_shipping_test_common_option',
							'type' => 'text',
						),
						'pro'   => $field,
					)
				);

				$this->assertArrayHasKey( 'pro', $guard, 'Com a licença em vigor o campo PRO é salvo.' );
			}
		);
	}

	/**
	 * A tela bloqueada explica o motivo CERTO ao lojista.
	 *
	 * Com o PRO ativo e sem licença, dizer "enquanto o PRO não estiver ativo"
	 * seria mentira — o plugin está ativo, o que falta é a licença.
	 */
	public function test_banner_separa_pro_ausente_de_licenca_ausente () {
		add_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );

		try {
			if ( ! Pro_Gate::is_pro_unlocked() ) {
				$html = $this->render_screen();

				$this->assertStringContainsString( 'enquanto a licença do PRO não estiver ativa', $html );
			}
		} finally {
			remove_filter( 'wc_shipping_simulator_is_pro_active', '__return_true' );
		}

		add_filter( 'wc_shipping_simulator_is_pro_active', '__return_false' );

		try {
			$html = $this->render_screen();

			$this->assertStringContainsString( 'enquanto o PRO não estiver ativo', $html );
			$this->assertStringNotContainsString( 'enquanto a licença do PRO não estiver ativa', $html );
		} finally {
			remove_filter( 'wc_shipping_simulator_is_pro_active', '__return_false' );
		}
	}
}
