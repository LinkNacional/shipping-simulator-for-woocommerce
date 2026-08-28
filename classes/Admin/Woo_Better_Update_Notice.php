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
		add_action( 'admin_notices', [ $this, 'maybe_render_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'dismiss_notice' ] );
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

		$ajax_url    = esc_url( admin_url( 'admin-ajax.php' ) );
		$nonce       = wp_create_nonce( self::NONCE_ACTION );
		$plugin_name = Config::get( 'NAME' );
		$icon_url    = h::plugin_url( 'assets/images/icon.svg' );
		$update_url  = wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . self::WOO_BETTER_PLUGIN ),
			'upgrade-plugin_' . self::WOO_BETTER_PLUGIN
		);
		?>
		<style>
			.wc-shipping-simulator-woo-better-update { display: flex; gap: 14px; align-items: flex-start; padding: 16px; border-left: 4px solid #dba617; }
			.wc-shipping-simulator-woo-better-update__icon { flex: 0 0 auto; width: 40px; height: 40px; }
			.wc-shipping-simulator-woo-better-update__icon img { width: 40px; height: 40px; display: block; }
			.wc-shipping-simulator-woo-better-update__content { flex: 1 1 auto; }
			.wc-shipping-simulator-woo-better-update__content p { margin: 0 0 12px; }
			.wc-shipping-simulator-woo-better-update__title { display: flex; align-items: center; gap: 8px; margin: 0 0 6px; flex-wrap: wrap; }
			.wc-shipping-simulator-woo-better-update__badge { display: inline-block; padding: 2px 8px; border-radius: 10px; background: #dba617; color: #1d2327; font-size: 11px; font-weight: 600; }
		</style>
		<div class="notice notice-warning is-dismissible wc-shipping-simulator-woo-better-update" data-dismissible="wc-shipping-simulator-woo-better-update" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<div class="wc-shipping-simulator-woo-better-update__icon">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>">
			</div>
			<div class="wc-shipping-simulator-woo-better-update__content">
				<p class="wc-shipping-simulator-woo-better-update__title">
					<strong><?php echo esc_html( $plugin_name ); ?></strong>
					<span class="wc-shipping-simulator-woo-better-update__badge"><?php esc_html_e( 'Atualização', 'shipping-simulator-for-woocommerce' ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'A Calculadora de Frete e Campos Checkout para o Brasil (woo-better) está desatualizada. Os recursos da calculadora de frete migraram para este plugin. Atualize o woo-better para evitar componentes duplicados e manter tudo funcionando corretamente.', 'shipping-simulator-for-woocommerce' ); ?>
				</p>
				<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary"><?php esc_html_e( 'Atualizar Calculadora de Frete e Campos Checkout para o Brasil', 'shipping-simulator-for-woocommerce' ); ?></a>
			</div>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dispensar este aviso.', 'shipping-simulator-for-woocommerce' ); ?></span></button>
		</div>
		<script>
		(function () {
			var notice = document.querySelector('[data-dismissible="wc-shipping-simulator-woo-better-update"]');
			if (!notice) return;
			var dismiss = notice.querySelector('.notice-dismiss');
			if (!dismiss) return;
			dismiss.addEventListener('click', function () {
				var formData = new FormData();
				formData.append('action', <?php echo wp_json_encode( self::AJAX_ACTION ); ?>);
				formData.append('nonce', notice.getAttribute('data-nonce'));
				fetch(<?php echo wp_json_encode( $ajax_url ); ?>, { method: 'POST', credentials: 'same-origin', body: formData })
					.then(function () { notice.remove(); })
					.catch(function () { notice.remove(); });
			});
		})();
		</script>
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
