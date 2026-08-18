<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Admin\Settings;

final class Debug_Box {
	public function __start () {
		if ( Settings::debug_enabled() ) {
			add_action( 'wc_shipping_simulator_wrapper_end', [ $this, 'form_before' ] );
		}
	}

	public function form_before () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) return;

		global $product;

		$yes = esc_html__( 'Yes', 'shipping-simulator-for-woocommerce' );
		$no = esc_html__( 'No', 'shipping-simulator-for-woocommerce' );

		$lines = [
			__( 'Product type:', 'shipping-simulator-for-woocommerce' ) . ' ' . $product->get_type(),
			__( 'Product ID:', 'shipping-simulator-for-woocommerce' ) . ' ' . '#' . $product->get_id(),
		];

		if ( $product->is_type( 'simple' ) ) {
			$lines = array_merge(
				$lines,
				[
					__( 'Has weight?', 'shipping-simulator-for-woocommerce' ) . ' ' . ( $product->has_weight() ? $yes : $no ),
					__( 'Has dimensions?', 'shipping-simulator-for-woocommerce' ) . ' ' . ( $product->has_dimensions() ? $yes : $no ),
				]
			);
		} elseif ( $product->is_type( 'variable' ) ) {
			$lines = array_merge(
				$lines,
				[
					__( 'Has weight?', 'shipping-simulator-for-woocommerce' ) . ' ' . ( $product->get_weight() ? $yes : $no ),
					__( 'Has dimensions?', 'shipping-simulator-for-woocommerce' ) . ' ' . ( $product->get_length() || $product->get_height() || $product->get_width() ? $yes : $no ),
				]
			);

			foreach ( $product->get_visible_children() as $id ) {
				$child = wc_get_product( $id );
				$lines = array_merge(
					$lines,
					[
						'<strong class="line-variation">' . __( 'VARIATION', 'shipping-simulator-for-woocommerce' ) . " #$id</strong>",
						__( 'Attributes:', 'shipping-simulator-for-woocommerce' )  . ' ' . esc_html( wp_json_encode( $child->get_attributes() ) ),
						__( 'Has weight?', 'shipping-simulator-for-woocommerce' )  . ' ' . ( $child->has_weight() ? $yes : $no ),
						__( 'Has dimensions?', 'shipping-simulator-for-woocommerce' ) . ' ' . ( $child->has_dimensions() ? $yes : $no ),
						__( 'Is virtual?', 'shipping-simulator-for-woocommerce' ) . ' ' .  ( $child->is_virtual() ? $yes : $no ),
					]
				);
			}
		}

		$lines = apply_filters( 'wc_shipping_simulator_debug_box_lines', $lines, $product );

		?>
		<style>
			#wc-shipping-sim-debug-box {
				background-color: #fff4e6;
				padding: 1em;
			}

			#wc-shipping-sim-debug-box h4 {
				margin: 0!important;
			}
		</style>
		<div id="wc-shipping-sim-debug-box">
			<h4><?php esc_html_e( 'DEBUG MODE ENABLED', 'shipping-simulator-for-woocommerce' ) ?></h4>

			<?php foreach ( $lines as $text ) : ?>
				<section><?php echo wp_kses_post( $text ) ?></section>
			<?php endforeach ?>

			<section><em><?php esc_html_e( 'This box not appears for your customers.', 'shipping-simulator-for-woocommerce' ) ?></em></section>
		</div>
		<?php
	}
}
