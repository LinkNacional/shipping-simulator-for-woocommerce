<?php

use Shipping_Simulator\Helpers as h;

defined( 'WPINC' ) || exit( 1 );

return [
	'woocommerce' => [
		'check' => 'function:WC',
		'message' => function () {
			return sprintf(
				/* translators: %s is replaced with a required plugin name */
				__( 'Install and activate the %s plugin.', 'shipping-simulator-for-woocommerce' ),
				'<strong>WooCommerce</strong>'
			);
		},
	],

	'woocommerce-shipping' => [
		'check' => function () {
			return 'disabled' !== get_option( 'woocommerce_ship_to_countries' );
		},
		'message' => function () {
			return sprintf(
				/* translators: %s is replaced with a required option */
				__( 'The WooCommerce option %s is disabled.', 'shipping-simulator-for-woocommerce' ),
				'<strong>' . esc_html__( 'Shipping location(s)', 'shipping-simulator-for-woocommerce' ) . '</strong>'
			);
		},
	]
];
