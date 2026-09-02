<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Helpers as h;

/**
 * Aviso de woo-better desatualizado.
 *
 * Exibido quando o plugin Fields for Brazilian Checkout for WooCommerce
 * (woo-better) está instalado, ativo e em uma versão anterior à que
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

	/** Ação AJAX para atualizar e ativar o woo-better. */
	const AJAX_UPDATE_ACTION = 'wc_shipping_simulator_update_woo_better';

	/** Ação do nonce para a atualização do woo-better. */
	const UPDATE_NONCE_ACTION = 'wc_shipping_simulator_update_woo_better_nonce';

	/** Versão do woo-better a partir da qual o redirecionamento é liberado. */
	const WOO_BETTER_REDIRECT_THRESHOLD = '5.0.0';

	/** Aba de configurações dos campos brasileiros do woo-better. */
	const WOO_BETTER_SETTINGS_TAB = 'wc-better-calc-checkout';

	public function __start () {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_UPDATE_ACTION, [ $this, 'update_woo_better' ] );
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

		wp_enqueue_script(
			'wc-shipping-simulator-plugin-install',
			h::plugin_url( 'Admin/jsCompiled/WcShippingSimulatorPluginInstall.COMPILED.js' ),
			[],
			$version,
			true
		);

		wp_localize_script( 'wc-shipping-simulator-plugin-install', 'WcShippingSimulatorPluginInstall', [
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'action'       => self::AJAX_UPDATE_ACTION,
			'nonce'        => wp_create_nonce( self::UPDATE_NONCE_ACTION ),
			'fallback_url' => admin_url( 'plugins.php' ),
		] );
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
		$plugin_name = __( 'Simulador de Frete para WooCommerce', 'shipping-simulator-for-woocommerce' );
		$icon_url    = h::plugin_url( 'assets/images/icon.svg' );
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
					<?php esc_html_e( 'O Campos Checkout Brasileiro para WooCommerce (woo-better) está desatualizado. Os recursos da calculadora de frete migraram para este plugin. Atualize o woo-better para evitar componentes duplicados e manter tudo funcionando corretamente.', 'shipping-simulator-for-woocommerce' ); ?>
				</p>
				<button type="button" class="button button-primary wc-simulator-plugin-update-button" data-install-action="upgrade">
					<span class="wc-simulator-plugin-update-button__bar" aria-hidden="true"></span>
					<span class="wc-simulator-plugin-update-button__text"><?php esc_html_e( 'Atualizar Campos Checkout Brasileiro para WooCommerce', 'shipping-simulator-for-woocommerce' ); ?></span>
				</button>
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
	 * Atualiza e ativa o woo-better via AJAX, retornando a URL de
	 * redirecionamento já validada pela versão instalada.
	 *
	 * @return void
	 */
	public function update_woo_better () {
		check_ajax_referer( self::UPDATE_NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		$result = $upgrader->upgrade( self::WOO_BETTER_PLUGIN );
		if ( is_wp_error( $result ) ) {
			set_transient( Legacy_Migration_Notice::ERROR_TRANSIENT, $result->get_error_message(), 5 * MINUTE_IN_SECONDS );
			wp_send_json_error( [ 'message' => $result->get_error_message() ], 400 );
		}

		if ( ! is_plugin_active( self::WOO_BETTER_PLUGIN ) ) {
			$activation = activate_plugin( self::WOO_BETTER_PLUGIN );
			if ( is_wp_error( $activation ) ) {
				set_transient( Legacy_Migration_Notice::ERROR_TRANSIENT, $activation->get_error_message(), 5 * MINUTE_IN_SECONDS );
				wp_send_json_error( [ 'message' => $activation->get_error_message() ], 400 );
			}
		}

		$version  = $this->woo_better_version();
		$redirect = version_compare( $version, self::WOO_BETTER_REDIRECT_THRESHOLD, '>=' )
			? admin_url( 'admin.php?page=wc-settings&tab=' . self::WOO_BETTER_SETTINGS_TAB )
			: admin_url( 'plugins.php' );

		// O próprio shipping-simulator lê este transient após o redirect para
		// exibir o cartão de sucesso.
		set_transient( Legacy_Migration_Notice::SUCCESS_TRANSIENT, 'upgrade', 5 * MINUTE_IN_SECONDS );

		wp_send_json_success( [
			'version'      => $version,
			'redirect_url' => $redirect,
		] );
	}

	/**
	 * Lê a versão do woo-better diretamente do arquivo (sem cache).
	 *
	 * @return string
	 */
	private function woo_better_version () {
		$file = WP_PLUGIN_DIR . '/' . self::WOO_BETTER_PLUGIN;

		if ( ! file_exists( $file ) ) {
			return '';
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$data = get_plugin_data( $file, false, false );

		return isset( $data['Version'] ) ? $data['Version'] : '';
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

		if ( $this->is_plugin_update_page() ) {
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

	/**
	 * Verifica se a página admin atual é de atualização/instalação de plugins.
	 *
	 * @return bool
	 */
	private function is_plugin_update_page () {
		$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
		return in_array( $pagenow, [ 'update.php', 'update-core.php', 'update-core-network.php' ], true );
	}
}
