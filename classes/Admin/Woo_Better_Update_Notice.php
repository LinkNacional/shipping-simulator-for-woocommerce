<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Core\Config;
use Shipping_Simulator\Helpers as h;

/**
 * Aviso de woo-better desatualizado.
 *
 * Exibido quando o plugin Calculadora de Frete e Campos Checkout para o
 * Brasil (woo-better) está instalado, ativo e em uma versão anterior à que
 * removeu a calculadora de frete. Informa que os recursos migraram para o
 * Shipping Simulator e oferece um botão para atualizar o woo-better, além do
 * "x" para dispensar permanentemente.
 *
 * @since 3.0.0
 */
final class Woo_Better_Update_Notice {

	/** Ação AJAX para dispensar o aviso permanentemente. */
	const AJAX_ACTION = 'wc_shipping_simulator_dismiss_woo_better_update';

	/** Ação do nonce para dispensar o aviso. */
	const NONCE_ACTION = 'wc_shipping_simulator_woo_better_update_nonce';

	/** Opção que marca o aviso como dispensado permanentemente. */
	const OPTION_DISMISSED = 'wc_shipping_simulator_woo_better_update_dismissed';

	/** Caminho relativo do arquivo principal do woo-better. */
	const WOO_BETTER_PLUGIN = 'woo-better-shipping-calculator-for-brazil/wc-better-shipping-calculator-for-brazil.php';

	public function __start () {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
	}

	/**
	 * Enfileira os assets do aviso apenas quando ele será exibido.
	 *
	 * @return void
	 */
	public function enqueue_assets () {
		if ( ! $this->should_show() ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_style(
			'wc-shipping-simulator-notices',
			h::plugin_url( 'Admin/cssCompiled/WcShippingSimulatorNotices.COMPILED.css' ),
			[],
			$version
		);

		wp_enqueue_script(
			'wc-shipping-simulator-notices',
			h::plugin_url( 'Admin/jsCompiled/WcShippingSimulatorNotices.COMPILED.js' ),
			[],
			$version,
			true
		);
	}

	/**
	 * Exibe o aviso quando o woo-better está ativo e desatualizado.
	 *
	 * @return void
	 */
	public function maybe_render_notice () {
		if ( ! $this->should_show() ) {
			return;
		}

		$nonce       = wp_create_nonce( self::NONCE_ACTION );
		$plugin_name = Config::get( 'NAME' );
		$icon_url    = h::plugin_url( 'assets/images/icon.svg' );
		$update_url  = wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . self::WOO_BETTER_PLUGIN ),
			'upgrade-plugin_' . self::WOO_BETTER_PLUGIN
		);
		?>
		<div class="notice notice-warning is-dismissible wc-simulator-notice wc-simulator-notice--update" data-dismissible="wc-shipping-simulator-woo-better-update" data-action="<?php echo esc_attr( self::AJAX_ACTION ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<div class="wc-simulator-notice__icon">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>">
			</div>
			<div class="wc-simulator-notice__content">
				<p class="wc-simulator-notice__title">
					<strong><?php echo esc_html( $plugin_name ); ?></strong>
					<span class="wc-simulator-notice__badge"><?php esc_html_e( 'Atualização', 'shipping-simulator-for-woocommerce' ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'A Calculadora de Frete e Campos Checkout para o Brasil (woo-better) está desatualizada. Os recursos da calculadora de frete migraram para este plugin. Atualize o woo-better para evitar componentes duplicados e manter tudo funcionando corretamente.', 'shipping-simulator-for-woocommerce' ); ?>
				</p>
				<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary"><?php esc_html_e( 'Atualizar Calculadora de Frete e Campos Checkout para o Brasil', 'shipping-simulator-for-woocommerce' ); ?></a>
			</div>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dispensar este aviso.', 'shipping-simulator-for-woocommerce' ); ?></span></button>
		</div>
		<?php
	}

	/**
	 * Dispensa o aviso permanentemente.
	 *
	 * @return void
	 */
	public function dismiss_notice () {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		update_option( self::OPTION_DISMISSED, 'yes' );

		wp_send_json_success();
	}

	/**
	 * Verifica se o aviso deve ser exibido.
	 *
	 * @return bool
	 */
	private function should_show () {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( 'yes' === get_option( self::OPTION_DISMISSED, 'no' ) ) {
			return false;
		}

		if ( ! $this->woo_better_active() ) {
			return false;
		}

		return Calculadora_Settings::woo_better_is_outdated();
	}

	/**
	 * Verifica se o woo-better está ativo.
	 *
	 * @return bool
	 */
	private function woo_better_active () {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( self::WOO_BETTER_PLUGIN );
	}
}
