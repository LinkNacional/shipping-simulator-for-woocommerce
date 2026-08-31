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

	/** Opção que marca que o usuário migrou para a nova Calculadora de Frete. */
	const OPTION_MIGRATED = 'wc_shipping_simulator_calculadora_migrated';

	/** Opção que dispensa o aviso de rollback (retorno ao legado). */
	const OPTION_ROLLBACK_DISMISSED = 'wc_shipping_simulator_rollback_notice_dismissed';

	/** Ação AJAX para aplicar o rollback (retornar ao legado). */
	const ROLLBACK_AJAX_ACTION = 'wc_shipping_simulator_rollback_calculadora';

	/** Ação AJAX para dispensar o aviso de rollback. */
	const ROLLBACK_DISMISS_AJAX_ACTION = 'wc_shipping_simulator_dismiss_rollback_notice';

	/** Nonce da ação de rollback. */
	const ROLLBACK_NONCE_ACTION = 'wc_shipping_simulator_rollback_nonce';

	/** Nonce da ação de dispensar o aviso de rollback. */
	const ROLLBACK_DISMISS_NONCE_ACTION = 'wc_shipping_simulator_rollback_dismiss_nonce';

	public function __start () {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_rollback_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
		add_action( 'wp_ajax_' . self::ROLLBACK_AJAX_ACTION, [ $this, 'rollback' ] );
		add_action( 'wp_ajax_' . self::ROLLBACK_DISMISS_AJAX_ACTION, [ $this, 'dismiss_rollback_notice' ] );
		add_action( 'admin_post_' . self::ADMIN_ACTION, [ $this, 'migrate' ] );
	}

	/**
	 * Enfileira os assets do aviso apenas quando ele será exibido.
	 *
	 * @return void
	 */
	public function enqueue_assets () {
		if ( ! $this->should_show() && ! $this->should_show_rollback_notice() ) {
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

		// Marca que o usuário migrou para a nova calculadora, para exibir o
		// aviso de rollback (retorno ao legado).
		update_option( self::OPTION_MIGRATED, 'yes' );
		update_option( self::OPTION_ROLLBACK_DISMISSED, 'no' );

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
	 * Exibe o aviso de rollback para quem migrou para a nova Calculadora de
	 * Frete e ainda não optou por retornar ao legado.
	 *
	 * @return void
	 */
	public function maybe_render_rollback_notice () {
		if ( ! $this->should_show_rollback_notice() ) {
			return;
		}

		$plugin_name    = Config::get( 'NAME' );
		$icon_url       = h::plugin_url( 'assets/images/icon.svg' );
		$rollback_nonce = wp_create_nonce( self::ROLLBACK_NONCE_ACTION );
		$dismiss_nonce  = wp_create_nonce( self::ROLLBACK_DISMISS_NONCE_ACTION );
		?>
		<div class="notice notice-info wc-simulator-notice wc-simulator-notice--rollback" data-dismissible="wc-shipping-simulator-rollback" data-action="<?php echo esc_attr( self::ROLLBACK_DISMISS_AJAX_ACTION ); ?>" data-nonce="<?php echo esc_attr( $dismiss_nonce ); ?>">
			<div class="wc-simulator-notice__icon">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>">
			</div>
			<div class="wc-simulator-notice__content">
				<p class="wc-simulator-notice__title">
					<strong><?php echo esc_html( $plugin_name ); ?></strong>
					<span class="wc-simulator-notice__badge"><?php esc_html_e( 'Retorno', 'shipping-simulator-for-woocommerce' ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'Você migrou para a nova Calculadora de Frete. Se preferir, pode retornar à configuração anterior (inserção automática legada).', 'shipping-simulator-for-woocommerce' ); ?>
				</p>
				<button type="button" class="button button-secondary wc-simulator-rollback-open"><?php esc_html_e( 'Retornar à versão legada', 'shipping-simulator-for-woocommerce' ); ?></button>
			</div>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dispensar este aviso.', 'shipping-simulator-for-woocommerce' ); ?></span></button>

			<div class="wc-simulator-rollback-modal" role="dialog" aria-modal="true" aria-hidden="true" data-rollback-action="<?php echo esc_attr( self::ROLLBACK_AJAX_ACTION ); ?>" data-rollback-nonce="<?php echo esc_attr( $rollback_nonce ); ?>">
				<div class="wc-simulator-rollback-modal__backdrop"></div>
				<div class="wc-simulator-rollback-modal__dialog">
					<button type="button" class="wc-simulator-rollback-modal__close" aria-label="<?php esc_attr_e( 'Fechar', 'shipping-simulator-for-woocommerce' ); ?>"><span aria-hidden="true">&times;</span></button>
					<h2><?php esc_html_e( 'Retornar à versão legada', 'shipping-simulator-for-woocommerce' ); ?></h2>
					<p><?php esc_html_e( 'Para aplicar o rollback, informe seu e-mail e o motivo do retorno.', 'shipping-simulator-for-woocommerce' ); ?></p>
					<div class="wc-simulator-rollback-modal__field">
						<label for="wc-simulator-rollback-email"><?php esc_html_e( 'E-mail', 'shipping-simulator-for-woocommerce' ); ?></label>
						<input type="email" id="wc-simulator-rollback-email" name="rollback_email" required>
					</div>
					<div class="wc-simulator-rollback-modal__field">
						<label for="wc-simulator-rollback-reason"><?php esc_html_e( 'Motivo do retorno', 'shipping-simulator-for-woocommerce' ); ?></label>
						<textarea id="wc-simulator-rollback-reason" name="rollback_reason" required></textarea>
					</div>
					<div class="wc-simulator-rollback-modal__actions">
						<button type="button" class="button wc-simulator-rollback-cancel"><?php esc_html_e( 'Cancelar', 'shipping-simulator-for-woocommerce' ); ?></button>
						<button type="button" class="button button-primary wc-simulator-rollback-confirm" disabled><?php esc_html_e( 'Confirmar rollback', 'shipping-simulator-for-woocommerce' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Aplica o rollback: reativa a inserção automática legada e desativa a
	 * nova Calculadora de Frete (produto e carrinho).
	 *
	 * Exige e-mail e motivo informados no modal; registra o feedback.
	 *
	 * @return void
	 */
	public function rollback () {
		check_ajax_referer( self::ROLLBACK_NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( ! is_email( $email ) || '' === trim( $reason ) ) {
			wp_send_json_error( [ 'message' => __( 'Informe um e-mail válido e o motivo do retorno.', 'shipping-simulator-for-woocommerce' ) ], 400 );
		}

		update_option( h::prefix( 'auto_insert' ), 'yes' );
		update_option( h::prefix( 'calc_enable_product_page' ), 'no' );
		update_option( h::prefix( 'calc_enable_cart_page' ), 'no' );

		update_option( self::OPTION_MIGRATED, 'no' );
		update_option( self::OPTION_ROLLBACK_DISMISSED, 'yes' );
		update_option( self::OPTION_DISMISSED, 'yes' );

		update_option( 'wc_shipping_simulator_rollback_feedback_email', $email );
		update_option( 'wc_shipping_simulator_rollback_feedback_reason', $reason );

		wp_send_json_success();
	}

	/**
	 * Dispensa permanentemente o aviso de rollback.
	 *
	 * @return void
	 */
	public function dismiss_rollback_notice () {
		check_ajax_referer( self::ROLLBACK_DISMISS_NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		update_option( self::OPTION_ROLLBACK_DISMISSED, 'yes' );

		wp_send_json_success();
	}

	/**
	 * Verifica se o aviso de rollback deve ser exibido.
	 *
	 * @return bool
	 */
	private function should_show_rollback_notice () {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( 'yes' !== get_option( self::OPTION_MIGRATED, 'no' ) ) {
			return false;
		}

		return 'yes' !== get_option( self::OPTION_ROLLBACK_DISMISSED, 'no' );
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
