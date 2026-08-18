<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Shortcode;
use Shipping_Simulator\Admin\Settings;

final class Tweaks {
	protected static $instace = null;

	public static function instance () {
		return self::$instace;
	}

	public function __start () {
		self::$instace = $this;
		add_action( 'woocommerce_single_product_summary', [ $this, 'auto_insert_shortcode' ], 35 );
		add_action( 'wc_shipping_simulator_form_start', [ $this, 'form_start' ] );
		add_action( 'wc_shipping_simulator_results_before', [ $this, 'results_before' ] );
		add_action( 'wc_shipping_simulator_results_after', [ $this, 'results_after' ] );
		add_filter( 'wc_shipping_simulator_package_rates', [ $this, 'update_customer_address' ], 10, 2 );
	}

	public function auto_insert_shortcode () {
		if ( 'yes' !== Settings::get_option( 'auto_insert' ) ) return;
		global $product;
		if ( $product ) {
			$tag = Shortcode::get_tag();
			echo do_shortcode( "[$tag]" );
		}
	}

	public function form_start () {
		$option = Settings::get_option( 'form_title' );
		$title = apply_filters(
			'wc_shipping_simulator_form_title',
			$option ? "<strong>$option</strong>" : ''
		);
		if ( $title ) {
			?>
			<div id="wc-shipping-sim-form-title"><?php echo wp_kses_post( $title ) ?></div>
			<?php
		}
	}

	public function results_before ( $data ) {
		$title = apply_filters(
			'wc_shipping_simulator_results_title',
			__( 'Shipping options for', 'shipping-simulator-for-woocommerce' )
		);
		if ( $title ) {
			$title .= ' ' . apply_filters(
				'wc_shipping_simulator_results_title_address',
				'<strong>' . $data['postcode'] . '</strong>',
				$data
			);
			?>
			<div id="wc-shipping-sim-results-title"><?php echo wp_kses_post( $title ) ?></div>
			<?php
		}
	}

	public function results_after () {
		$option = Settings::get_option( 'after_results' );
		$text = apply_filters(
			'wc_shipping_simulator_text_after_results',
			$option
		);
		if ( $text ) {
			?>
			<div id="wc-shipping-sim-results-after"><?php echo wp_kses_post( $text ) ?></div>
			<?php
		}
	}

	public function update_customer_address ( $rates, $package ) {
		$opt_update_address = (int) Settings::get_option( 'update_address' );
		$dest = $package['destination'] ?? [];
		$enabled = apply_filters(
			'wc_shipping_simulator_update_shipping_address',
			// option enabled AND has shipping options AND has country
			0 !== $opt_update_address && count( $rates ) > 0 && h::get( $dest['country'] ),
			$rates,
			$package
		);
		$customer = WC()->customer;
		if ( $enabled ) {
			WC()->shipping()->reset_shipping();

			$customer->set_shipping_location(
				h::get( $dest['country'] ),
				h::get( $dest['state'] ),
				h::get( $dest['postcode'] ),
				h::get( $dest['city'] )
			);
			$customer->set_shipping_address_1( $dest['address_1'] ?? '' );
			$customer->set_shipping_address_2( $dest['address_2'] ?? '' );

			h::logger()->info( 'Customer shipping address updated to ' . wp_json_encode( $dest ) );

			$should_update_billing_address = apply_filters(
				'wc_shipping_simulator_update_billing_address',
				2 === $opt_update_address,
				$rates,
				$package
			);
			if ( $should_update_billing_address ) {
				$customer->set_billing_location(
					h::get( $dest['country'] ),
					h::get( $dest['state'] ),
					h::get( $dest['postcode'] ),
					h::get( $dest['city'] )
				);
				$customer->set_billing_address_1( $dest['address_1'] ?? '' );
				$customer->set_billing_address_2( $dest['address_2'] ?? '' );

				h::logger()->info( 'Customer billing address updated to ' . wp_json_encode( $dest ) );
			}

			$customer->set_calculated_shipping( true );
			$customer->save();

			WC()->cart->calculate_totals();

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core action fired after recalculation, not a plugin-owned hook.
			do_action( 'woocommerce_calculated_shipping' );
		}
		return $rates;
	}
}
