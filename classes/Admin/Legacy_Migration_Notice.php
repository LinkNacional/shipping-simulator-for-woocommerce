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

	/** Ação AJAX para aplicar o rollback (retornar ao legado). */
	const ROLLBACK_AJAX_ACTION = 'wc_shipping_simulator_rollback_calculadora';

	/** Nonce da ação de rollback. */
	const ROLLBACK_NONCE_ACTION = 'wc_shipping_simulator_rollback_nonce';

	/** Transient que marca a mensagem de sucesso a exibir após o redirect. */
	const SUCCESS_TRANSIENT = 'wc_shipping_simulator_success';

	/** Transient que marca a mensagem de erro a exibir após o refresh. */
	const ERROR_TRANSIENT = 'wc_shipping_simulator_error';

	public function __start () {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( Config::get( 'FILE' ) ), [ $this, 'add_rollback_action_link' ] );
		add_action( 'admin_footer', [ $this, 'render_rollback_modal' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
		add_action( 'wp_ajax_' . self::ROLLBACK_AJAX_ACTION, [ $this, 'rollback' ] );
		add_action( 'admin_post_' . self::ADMIN_ACTION, [ $this, 'migrate' ] );
	}

	/**
	 * Enfileira os assets do aviso apenas quando ele será exibido.
	 *
	 * @return void
	 */
	public function enqueue_assets () {
		$success = get_transient( self::SUCCESS_TRANSIENT );
		$error   = get_transient( self::ERROR_TRANSIENT );

		if ( ! $this->should_show() && ! $this->should_show_rollback_action() && false === $success && false === $error ) {
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

		// Consome os transients imediatamente: a mensagem aparece uma única vez
		// e some ao recarregar (F5).
		$show_on_load  = '';
		$error_message = '';
		if ( false !== $error ) {
			$show_on_load  = 'error';
			$error_message = (string) $error;
			delete_transient( self::ERROR_TRANSIENT );
		} elseif ( false !== $success ) {
			$show_on_load = (string) $success;
			delete_transient( self::SUCCESS_TRANSIENT );
		}

		wp_localize_script( 'wc-shipping-simulator-notices', 'WcShippingSimulatorNotices', [
			'show_on_load'  => $show_on_load,
			'error_message' => $error_message,
			'success'       => [
				'title'    => __( 'Simulador de Frete para WooCommerce', 'shipping-simulator-for-woocommerce' ),
				'badge'    => __( 'Sucesso', 'shipping-simulator-for-woocommerce' ),
				'close'    => __( 'Fechar', 'shipping-simulator-for-woocommerce' ),
				'upgrade'  => __( 'O plugin Campos Checkout Brasileiro para WooCommerce foi atualizado com sucesso.', 'shipping-simulator-for-woocommerce' ),
				'rollback' => __( 'Configurações revertidas para a versão legada com sucesso.', 'shipping-simulator-for-woocommerce' ),
				'migrate'  => __( 'Migração para a nova Calculadora de Frete concluída com sucesso.', 'shipping-simulator-for-woocommerce' ),
			],
			'error'         => [
				'title' => __( 'Simulador de Frete para WooCommerce', 'shipping-simulator-for-woocommerce' ),
				'badge' => __( 'Erro', 'shipping-simulator-for-woocommerce' ),
				'close' => __( 'Fechar', 'shipping-simulator-for-woocommerce' ),
			],
		] );
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
		$plugin_name = __( 'Simulador de Frete para WooCommerce', 'shipping-simulator-for-woocommerce' );
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
				<form method="post" action="<?php echo esc_url( $form_action ); ?>">
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
		// atalho de rollback (retorno ao legado).
		update_option( self::OPTION_MIGRATED, 'yes' );

		set_transient( self::SUCCESS_TRANSIENT, 'migrate', 5 * MINUTE_IN_SECONDS );

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
	 * Adiciona o atalho "Settings Rollback" à linha de ações do plugin, logo
	 * após "Deactivate", para quem migrou para a nova Calculadora de Frete.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_rollback_action_link ( $links ) {
		if ( ! $this->should_show_rollback_action() ) {
			return $links;
		}

		$links['settings_rollback'] = '<a href="#" class="wc-simulator-rollback-open">' . esc_html__( 'Reverter Configurações', 'shipping-simulator-for-woocommerce' ) . '</a>';

		return $links;
	}

	/**
	 * Renderiza o modal de rollback (mesmo fluxo do antigo aviso) no footer da
	 * página de plugins, onde o atalho "Settings Rollback" está disponível.
	 *
	 * @return void
	 */
	public function render_rollback_modal () {
		if ( ! $this->is_plugins_page() || ! $this->should_show_rollback_action() ) {
			return;
		}

		$rollback_nonce = wp_create_nonce( self::ROLLBACK_NONCE_ACTION );
		?>
		<div class="wc-simulator-rollback-modal" role="dialog" aria-modal="true" aria-hidden="true" data-rollback-action="<?php echo esc_attr( self::ROLLBACK_AJAX_ACTION ); ?>" data-rollback-nonce="<?php echo esc_attr( $rollback_nonce ); ?>">
			<div class="wc-simulator-rollback-modal__backdrop"></div>
			<div class="wc-simulator-rollback-modal__dialog">
				<button type="button" class="wc-simulator-rollback-modal__close" aria-label="<?php esc_attr_e( 'Fechar', 'shipping-simulator-for-woocommerce' ); ?>"><span aria-hidden="true">&times;</span></button>
				<div class="wc-simulator-rollback-modal__title"><?php esc_html_e( 'Retornar à versão legada', 'shipping-simulator-for-woocommerce' ); ?></div>
				<p class="wc-simulator-rollback-modal__description"><?php esc_html_e( 'Conte-nos o motivo pelo qual deseja voltar às configurações anteriores. Seu feedback nos ajuda a melhorar.', 'shipping-simulator-for-woocommerce' ); ?></p>
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
		<?php
	}

	/**
	 * Aplica o rollback: reativa a inserção automática legada e desativa a
	 * nova Calculadora de Frete (produto e carrinho).
	 *
	 * O e-mail é coletado do usuário logado; o motivo é informado no modal.
	 *
	 * @return void
	 */
	public function rollback () {
		check_ajax_referer( self::ROLLBACK_NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		$reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( '' === trim( $reason ) ) {
			set_transient( self::ERROR_TRANSIENT, __( 'Informe o motivo do retorno.', 'shipping-simulator-for-woocommerce' ), 5 * MINUTE_IN_SECONDS );
			wp_send_json_error( [ 'message' => __( 'Informe o motivo do retorno.', 'shipping-simulator-for-woocommerce' ) ], 400 );
		}

		// E-mail do usuário logado (o atalho só aparece para usuários com
		// permissão de gerenciamento, portanto há sempre um usuário logado).
		$current_user = wp_get_current_user();
		$email        = $current_user instanceof \WP_User ? $current_user->user_email : '';

		update_option( h::prefix( 'auto_insert' ), 'yes' );
		update_option( h::prefix( 'calc_enable_product_page' ), 'no' );
		update_option( h::prefix( 'calc_enable_cart_page' ), 'no' );

		update_option( self::OPTION_MIGRATED, 'no' );
		update_option( self::OPTION_DISMISSED, 'yes' );

		$this->send_rollback_feedback( $email, $reason );

		set_transient( self::SUCCESS_TRANSIENT, 'rollback', 5 * MINUTE_IN_SECONDS );

		wp_send_json_success();
	}

	/**
	 * Envia o feedback do rollback por e-mail para a Link Nacional.
	 *
	 * @param string $email  E-mail informado pelo usuário.
	 * @param string $reason Motivo do retorno à versão legada.
	 * @return void
	 */
	private function send_rollback_feedback ( $email, $reason ) {
		$to      = 'contato@linknacional.com';
		$subject = __( '[Simulador de Frete] Feedback de rollback para a versão legada', 'shipping-simulator-for-woocommerce' );

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );

		$body  = __( 'Um usuário optou por retornar à versão legada do Simulador de Frete para WooCommerce.', 'shipping-simulator-for-woocommerce' ) . "\n\n";
		$body .= __( 'Site:', 'shipping-simulator-for-woocommerce' ) . ' ' . $site_name . ' (' . $site_url . ')' . "\n";
		$body .= __( 'E-mail do usuário:', 'shipping-simulator-for-woocommerce' ) . ' ' . $email . "\n";
		$body .= __( 'Motivo do retorno:', 'shipping-simulator-for-woocommerce' ) . "\n" . $reason . "\n";

		$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

		wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Verifica se o atalho "Settings Rollback" deve ser exibido.
	 *
	 * @return bool
	 */
	private function should_show_rollback_action () {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return false;
		}

		if ( $this->is_plugin_update_page() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( 'yes' !== get_option( self::OPTION_MIGRATED, 'no' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Verifica se a página admin atual é a de plugins.
	 *
	 * @return bool
	 */
	private function is_plugins_page () {
		$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
		return 'plugins.php' === $pagenow;
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

		return 'yes' === Settings::get_option( 'auto_insert' );
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
