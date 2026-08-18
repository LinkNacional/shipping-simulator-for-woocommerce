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
		$sections[ self::get_id() ] = esc_html__( 'Shipping simulator', 'shipping-simulator-for-woocommerce' );
		return $sections;
	}

	public function add_settings ( $settings, $current_section ) {
		if ( self::get_id() === $current_section ) {
			$settings = self::get_fields( false );
		}

		return $settings;
	}

	public function add_plugin_action_links ( $actions ) {
		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=' . self::get_id() );
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
}
