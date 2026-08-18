<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Error as Plugin_Error;
use Shipping_Simulator\Core\Traits\Common_Helpers;
use Shipping_Simulator\Core\Traits\Config_Helpers;
use Shipping_Simulator\Core\Traits\Debug_Helpers;
use Shipping_Simulator\Core\Traits\String_Helpers;
use Shipping_Simulator\Core\Traits\Template_Helpers;
use Shipping_Simulator\Core\Traits\Throw_Helpers;
use Shipping_Simulator\Core\Traits\WordPress_Helpers;

abstract class Helpers {
	use Common_Helpers,
		Config_Helpers,
		Debug_Helpers,
		String_Helpers,
		Template_Helpers,
		Throw_Helpers,
		WordPress_Helpers;

	// YOUR CUSTOM HELPERS (ALWAYS STATIC)
	// public static function foo () {
	//     return 'bar';
	// }

	public static function get_error_class () {
		return Plugin_Error::class;
	}

	public static function user_is_admin ( $user_id = null ) {
		return $user_id ? user_can( $user_id, 'administrator' ) : current_user_can(  'administrator' );
	}

	public static function sanitize_postcode ( $postcode ) {
		$sanitized = preg_replace( '/[^0-9]/', '', $postcode );
		return (string) apply_filters(
			'wc_shipping_simulator_sanitize_postcode',
			$sanitized,
			$postcode
		);
	}

	public static function product_needs_shipping ( $product ) {
		$needs_shipping = false;

		if ( 'instock' === $product->get_stock_status() || $product->is_on_backorder() ) {
			$type = $product->get_type();
			if ( 'variable' === $type ) {
				$variations = $product->get_available_variations();
				foreach ( $variations as $variation ) {
					if ( ! $variation['is_virtual'] ) {
						$needs_shipping = true;
						break;
					}
				}
			} elseif ( ! in_array( $type, [ 'external', 'grouped' ] ) ) {
				$needs_shipping = $product->needs_shipping();
			}
		}

		return apply_filters(
			'wc_shipping_simulator_product_needs_shipping',
			$needs_shipping,
			$product
		);
	}

	public static function logger () {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Hook is dynamically prefixed via h::prefix() => wc_shipping_simulator_get_logger.
		return \apply_filters( h::prefix( 'get_logger' ), null );
	}

	public static function get_estimating_delivery ( $days ) {
		$days = intval( $days );
		$result = '';

		if ( $days > 0 ) {
			/* translators: %d: days to delivery */
			$result = sprintf( _n( 'Delivery within %d working day', 'Delivery within %d working days', $days, 'shipping-simulator-for-woocommerce' ), $days );
		} //woocommerce-correios

		return apply_filters( 'wc_shipping_simulator_get_estimating_delivery', $result, $days );
	}
}
