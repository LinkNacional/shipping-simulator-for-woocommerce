<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Core\Config;
use Shipping_Simulator\Helpers as h;

/**
 * Aviso de migração automática da versão legada para a nova Calculadora de
 * Frete (página do produto e do carrinho).
 *
 * Exibido apenas quando a opção legada `auto_insert` está ativa — ou seja,
 * para usuários antigos que atualizaram o plugin. Oferece um botão de
 * migração automática (desativa o legado, ativa a nova calculadora e
 * redireciona para a aba "Produto") e um "x" para dispensar permanentemente.
 *
 * @since 3.0.0
 */
final class Legacy_Migration_Notice {

	/** Ação AJAX para dispensar o aviso permanentemente. */
	const AJAX_ACTION = 'wc_shipping_simulator_dismiss_legacy_migration';

	/** Ação admin-post para executar a migração automática. */
	const ADMIN_ACTION = 'wc_shipping_simulator_legacy_migration';

	/** Nonce compartilhado pelas ações de dispensar e migrar. */
	const NONCE_ACTION = 'wc_shipping_simulator_legacy_migration_nonce';

	/** Opção que marca o aviso como dispensado permanentemente. */
	const OPTION_DISMISSED = 'wc_shipping_simulator_legacy_migration_dismissed';

	public function __start () {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
		add_action( 'admin_post_' . self::ADMIN_ACTION, [ $this, 'migrate' ] );
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
	 * Exibe o aviso quando a opção legada estiver habilitada e o aviso ainda
	 * não tiver sido dispensado.
	 *
	 * @return void
	 */
	public function maybe_render_notice () {
		if ( ! $this->should_show() ) {
			return;
		}

		$form_action = esc_url( admin_url( 'admin-post.php' ) );
		$nonce       = wp_create_nonce( self::NONCE_ACTION );
		$plugin_name = Config::get( 'NAME' );
		$icon_url    = h::plugin_url( 'assets/images/icon.svg' );
		?>
		<div class="notice notice-info is-dismissible wc-simulator-notice wc-simulator-notice--brand" data-dismissible="wc-shipping-simulator-legacy-migration" data-action="<?php echo esc_attr( self::AJAX_ACTION ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<div class="wc-simulator-notice__icon">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>">
			</div>
			<div class="wc-simulator-notice__content">
				<p class="wc-simulator-notice__title">
					<strong><?php echo esc_html( $plugin_name ); ?></strong>
					<span class="wc-simulator-notice__badge"><?php esc_html_e( 'Novidade', 'shipping-simulator-for-woocommerce' ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'Você está usando a versão legada do simulador (inserção automática por shortcode). A nova Calculadora de Frete já está disponível nas páginas de produto e carrinho, com visual e opções próprias.', 'shipping-simulator-for-woocommerce' ); ?>
				</p>
				<form method="post" action="<?php echo $form_action; ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ADMIN_ACTION ); ?>">
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Migrar automaticamente', 'shipping-simulator-for-woocommerce' ); ?></button>
				</form>
			</div>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dispensar este aviso.', 'shipping-simulator-for-woocommerce' ); ?></span></button>
		</div>
		<?php
	}

	/**
	 * Executa a migração automática e redireciona para a aba "Produto".
	 *
	 * Desativa a inserção automática legada (`auto_insert`) e ativa a nova
	 * calculadora nas páginas de produto e carrinho.
	 *
	 * @return void
	 */
	public function migrate () {
		check_admin_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para realizar esta ação.', 'shipping-simulator-for-woocommerce' ) );
		}

		update_option( h::prefix( 'auto_insert' ), 'no' );
		update_option( h::prefix( 'calc_enable_product_page' ), 'yes' );
		update_option( h::prefix( 'calc_enable_cart_page' ), 'yes' );

		wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=' . Calculadora_Settings::TAB_ID . '#produto' ) );
		exit;
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

		return 'yes' === Settings::get_option( 'auto_insert' );
	}
}
