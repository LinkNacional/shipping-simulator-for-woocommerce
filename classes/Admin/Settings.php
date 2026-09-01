<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Helpers as h;

final class Settings {
	protected static $fields = null;

	public function __start () {
		// WooCommerce custom settings in Shipping Tab
		add_filter( 'woocommerce_get_sections_shipping', [ $this, 'add_section' ] );
		add_filter( 'woocommerce_get_settings_shipping', [ $this, 'add_settings' ], 10, 2 );

		// plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( h::config_get( 'FILE' ) ), [ $this, 'add_plugin_action_links' ] );

		// Maybe enable integration: Autofill Addresses
		add_filter( 'wc_shipping_simulator_integration_autofill_br_addresses_enabled', [ $this, 'enable_autofill_addresses' ] );

		// Template de cards (layout legado) na seção "Simulador de Frete (Legado)".
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_legacy_template' ] );

		// Renderiza o card da Link Nacional (com links/estrelas/versões) na
		// seção legada, que não passa por Calculadora_Settings::output().
		add_action( 'woocommerce_settings_' . sanitize_title( self::get_prefix() . 'settings' ), [ $this, 'render_legacy_card' ] );
	}

	public static function get_option ( $key ) {
		$key = self::get_prefix() . $key;
		$option = \get_option( $key );
		if ( false === $option ) {
			$fields = self::get_fields();
			return h::get( $fields[ $key ][ 'default' ], false );
		}
		return $option;
	}

	public static function get_id () {
		return h::config_get( 'SLUG' );
	}

	public static function get_prefix () {
		return h::prefix();
	}

	public static function debug_enabled () {
		return 'yes' === self::get_option( 'debug_mode' );
	}

	protected static function get_fields ( $assoc = true ) {
		if ( null === self::$fields ) {
			$fields = include __DIR__ . '/inc/settings_fields.php';
			self::$fields = [];
			foreach ( $fields as $i => $field ) {
				$type = h::get( $field['type'], 'text' );
				$key = in_array( $type, [ 'title', 'sectionend' ] ) ? $i : $field['id'];
				self::$fields[ $key ] = $field;
			}
			self::$fields = apply_filters(
				'wc_shipping_simulator_settings_fields',
				self::$fields
			);
		}
		return $assoc ? self::$fields : array_values( self::$fields );
	}

	public function add_section ( $sections ) {
		$sections[ self::get_id() ] = esc_html__( 'Simulador de Frete (Legado)', 'shipping-simulator-for-woocommerce' );
		return $sections;
	}

	public function add_settings ( $settings, $current_section ) {
		if ( self::get_id() === $current_section ) {
			$settings = self::get_fields( false );
		}

		return $settings;
	}

	public function add_plugin_action_links ( $actions ) {
		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=' . Calculadora_Settings::TAB_ID );
		return array_merge(
			[
				"<a href=\"$settings_url\">" . esc_html__( 'Settings', 'shipping-simulator-for-woocommerce' ) .  "</a>",
			],
			$actions
		);
	}

	public function enable_autofill_addresses ( $value ) {
		$value = 'yes' === self::get_option( 'autofill_addresses' );
		return $value;
	}

	/**
	 * Renderiza o card da Link Nacional (links, estrelas e versões) na seção
	 * "Simulador de Frete (Legado)".
	 *
	 * O JS do layout legado procura `#WcShippingSimulatorLinkSettingsCard` e o
	 * move para a coluna lateral; aqui apenas emitimos o HTML no DOM.
	 *
	 * @return void
	 */
	public function render_legacy_card () {
		echo Calculadora_Settings::render_settings_card(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template já escapa internamente.
	}

	/**
	 * Enfileira o template de cards (layout legado) apenas na seção
	 * "Simulador de Frete (Legado)" de WooCommerce > Configurações > Envio.
	 *
	 * Usa um JS próprio (cópia do layout da "Calculadora de frete"), sem
	 * alterar o template original nem as opções da seção.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public function enqueue_legacy_template ( $hook_suffix ) {
		if ( 'woocommerce_page_wc-settings' !== $hook_suffix ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

		if ( 'shipping' !== $tab || self::get_id() !== $section ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_style(
			'wc-shipping-simulator-admin-settings',
			h::plugin_url( 'Admin/cssCompiled/WcShippingSimulatorAdminSettings.COMPILED.css' ),
			[],
			$version
		);

		wp_enqueue_style(
			'wc-shipping-simulator-admin-card',
			h::plugin_url( 'Admin/cssCompiled/WcShippingSimulatorAdminCard.COMPILED.css' ),
			[],
			$version
		);

		wp_enqueue_script(
			'wc-shipping-simulator-admin-legacy-layout',
			h::plugin_url( 'Admin/jsCompiled/WcShippingSimulatorAdminLegacyLayout.COMPILED.js' ),
			[ 'jquery' ],
			$version,
			true
		);

		$woo_better_slug = 'woo-better-shipping-calculator-for-brazil';

		wp_localize_script( 'wc-shipping-simulator-admin-legacy-layout', 'wcShippingSimulatorLegacy', [
			'calculator_url'            => admin_url( 'admin.php?page=wc-settings&tab=' . Calculadora_Settings::TAB_ID ),
			'plugin_slug'               => $woo_better_slug,
			'install_nonce'             => wp_create_nonce( 'install-plugin_' . $woo_better_slug ),
			'woo_better_plugin_installed' => file_exists( WP_PLUGIN_DIR . '/' . $woo_better_slug . '/wc-better-shipping-calculator-for-brazil.php' ),
		] );
	}
}
