<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Admin\Settings;

final class Shipping_Package {
	public $ready = false;

	protected $package = null;
	protected $destination = null;
	protected $contents = [];
	protected $original_cart_info = [];

	public function __construct () {
		$this->contents = [];
		$this->destination = apply_filters(
			'wc_shipping_simulator_destination_fields',
			[
				'postcode' => '',
				'country' => '',
				'state' => '',
				'city' => '',
				'address' => '',
				'address_1' => '',
				'address_2' => '',
			]
		);
	}

	public function set_destination ( $args ) {
		foreach ( $args as $key => $value ) {
			if ( isset( $this->destination[ $key ] ) ) {
				$this->destination[ $key ] = $value;
			}
		}
	}

	public function add_product ( $product_id, $quantity, $variation_id = null ) {
		$product_id = absint( $product_id );
		$variation_id = $variation_id ? absint( $variation_id ) : 0;
		$variation = $variation_id ? wc_get_product_variation_attributes( $variation_id ) : [];
		$quantity = max( $quantity, 1 );
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );
		$price = (float) $product->get_price();
		$is_virtual = $product->is_virtual();

		if ( apply_filters( 'wc_shipping_simulator_package_validate_virtual_product', true ) ) {
			h::throw_if(
				$is_virtual,
				esc_attr__( 'This product is virtual and can not shippable.', 'shipping-simulator-for-woocommerce' )
			);
		}

		h::throw_if(
			'yes' === Settings::get_option( 'requires_variation' ) && 0 === $variation_id && $product->is_type( 'variable' ),
			esc_attr__( 'Please select some product options first.', 'shipping-simulator-for-woocommerce' )
		);

		$price_total = $price * $quantity;
		$this->contents[] = apply_filters(
			'wc_shipping_simulator_package_item',
			[
				'product_id' => $product_id,
				'variation_id' => $variation_id,
				'variation' => $variation,
				'quantity' => $quantity,
				'data' => $product,
				'line_tax' => 0,
				'line_tax_data' => [ 'subtotal' => [], 'total' => [] ],
				'line_subtotal' => $price_total,
				'line_subtotal_tax' => 0,
				'line_total' => $price_total,
			]
		);

		return true;
	}

	public function get_package () {
		$contents_total = 0;
		$has_variations = false;

		foreach ( $this->contents as $item ) {
			$contents_total += h::get( $item['line_total'], 0 );
			if ( ! $has_variations ) {
				$has_variations = 0 !== $item['variation_id'];
			}
		}

		$package = apply_filters(
			'wc_shipping_simulator_package_data',
			[
				'contents' => $this->contents,
				'destination' => $this->destination,
				'applied_coupons' => [],
				'user' => [ 'ID' => get_current_user_id() ],
				'contents_cost' => $contents_total,
				'cart_subtotal' => $contents_total,
				'has_variations' => $has_variations
			]
		);

		$package['DOING_SHIPPING_SIMULATION'] = true;

		return $package;
	}

	public function calculate_shipping () {
		$wc_shipping = WC()->shipping();

		// backup the current WC_Shipping::packages
		$original_shipping_packages = $wc_shipping->packages;

		$this->package = $this->get_package();
		h::logger()->info( 'Calculating shipping rates for ...' );
		$this->log_package( $this->package );

		// sync the cart contents and totals
		$this->modify_cart_info();

		add_filter( 'option_woocommerce_shipping_cost_requires_address', '__return_false', 999 );

		// calculate
		$result = $wc_shipping->calculate_shipping( [ $this->package ] );

		remove_filter( 'option_woocommerce_shipping_cost_requires_address', '__return_false', 999 );

		// restore the cart contents and totals
		$this->restore_cart_info();

		// restore the WC_Shipping::packages
		$wc_shipping->packages = $original_shipping_packages;

		// get the results
		$rates = h::get( $result[0]['rates'], [] );
		h::logger()->info( 'Result: ' . wp_json_encode( array_keys( $rates ) ) );

		// sort results in ASC order
		if ( count( $rates ) > 1 ) {
			uasort( $rates, function ( $a, $b ) {
				return $a->get_cost() <=> $b->get_cost();
			} );
		}

		$rates = apply_filters(
			'wc_shipping_simulator_package_rates',
			$rates,
			$this->package
		);

		return $rates;
	}

	public function modify_cart_info () {
		$cart = WC()->cart;
		$this->original_cart_info = [
			'cart_contents' => $cart->get_cart_contents(),
			'cart_contents_total' => $cart->get_cart_contents_total(),
			'subtotal' => $cart->get_subtotal(),
		];
		$cart->set_cart_contents( $this->package['contents' ]);
		$cart->set_cart_contents_total( $this->package['contents_cost'] );
		$cart->set_subtotal( $this->package['contents_cost'] );
	}

	public function restore_cart_info () {
		if ( 0 === count( $this->original_cart_info ) ) return;
		$cart = WC()->cart;
		foreach ( $this->original_cart_info as $prop => $value ) {
			$cart->{'set_' . $prop}( $value );
			unset( $this->original_cart_info[ $prop ] );
		}
	}

	protected function log_package ( $package ) {
		if ( Settings::debug_enabled() ) {
			$i = 0;
			foreach ( $package['contents'] as $item ) {
				$i++;
				$item_data = [
					'name' => $item['data']->get_name(),
					'product_id' => $item['product_id'],
					'quantity' => $item['quantity'],
					'weight' => $item['data']->get_weight(),
					'variation_id' => $item['variation_id'],
					'variation' => $item['variation'],
				];
				h::logger()->info( "Package item #$i: " . wp_json_encode( $item_data ) );
			}
			h::logger()->info( 'Package destination: ' . wp_json_encode( $package['destination'] ) );
			h::logger()->info( 'Package total: ' . wp_json_encode( $package['contents_cost'] ) );
		}
	}
}
