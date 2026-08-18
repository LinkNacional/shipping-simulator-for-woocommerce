<?php

use Shipping_Simulator\Shortcode;
use Shipping_Simulator\Admin\Settings;

$wc_shipping_simulator_prefix = Settings::get_prefix();
$wc_shipping_simulator_shortcode = Shortcode::get_tag();

return [
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'title',
		'name' => esc_html__( 'Shipping Simulator Settings', 'shipping-simulator-for-woocommerce' ),
		'desc' => esc_html__( 'The following options are used to configure the Shipping Simulator.', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'auto_insert',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Enable auto-insert', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => sprintf(
			// translators: %s is a shortcode tag
			esc_html__( 'Display automatically the shipping simulator in product pages. Alternatively you can manually insert the shipping simulator using the %s shortcode.', 'shipping-simulator-for-woocommerce' ),
			"<code>[$wc_shipping_simulator_shortcode]</code>"
		),
		'default'  => 'yes'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'requires_variation',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Product variation is required', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => esc_html__( 'Disable this option to allow customers simulate shipping rates even when a variation is not selected on variable products. However, always make sure that the variable product has a defined weight.', 'shipping-simulator-for-woocommerce' ),
		'default'  => 'yes'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'autofill_addresses',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Display full address', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => esc_html__( 'When this option is activated, the street, neighborhood and city will be displayed in the shipping simulator.', 'shipping-simulator-for-woocommerce' ),
		'default'  => 'yes'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'update_address',
		'type'     => 'radio',
		'name'     => esc_html__( 'Update customer address', 'shipping-simulator-for-woocommerce' ),
		'options'  => [
			'0' => esc_html__( "Don't update", 'shipping-simulator-for-woocommerce' ),
			'1' => esc_html__( "Update only shipping address", 'shipping-simulator-for-woocommerce' ),
			'2' => esc_html__( "Update billing and shipping address", 'shipping-simulator-for-woocommerce' ),
		],
		'desc_tip' => esc_html__( 'The customer address can be updated when a shipping simulation returns shipping options.', 'shipping-simulator-for-woocommerce' ),
		'default'  => '0'
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'form_title',
		'type'     => 'text',
		'name'     => esc_html__( 'Title', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears before the simulator fields.', 'shipping-simulator-for-woocommerce' ),
		'default'  => __( 'Check shipping cost and delivery time:', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'input_placeholder',
		'type'     => 'text',
		'name'     => esc_html__( 'Input placeholder', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears when the postcode field is empty.', 'shipping-simulator-for-woocommerce' ),
		'default'  => __( 'Type your postcode', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'submit_label',
		'type'     => 'text',
		'name'     => esc_html__( 'Button Text', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Text that appears on the shipping simulator button.', 'shipping-simulator-for-woocommerce' ),
		'default'  => __( 'Apply', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'after_results',
		'type'     => 'textarea',
		'name'     => esc_html__( 'Text after results.', 'shipping-simulator-for-woocommerce' ),
		'default'  => __( 'Delivery times start from the confirmation of payment.', 'shipping-simulator-for-woocommerce' ),
		'css' => 'height: 6rem',
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'no_results',
		'type'     => 'textarea',
		'name'     => esc_html__( 'Text when there are no results.', 'shipping-simulator-for-woocommerce' ),
		'default'  => __( 'Unfortunately at this moment this product cannot be delivered to the specified region.', 'shipping-simulator-for-woocommerce' ),
		'css' => 'height: 6rem;',
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'sectionend',
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings_debug',
		'type' => 'title',
		'name' => esc_html__( 'Debug', 'shipping-simulator-for-woocommerce' ),
	],
	[
		'id'       => $wc_shipping_simulator_prefix . 'debug_mode',
		'type'     => 'checkbox',
		'name'     => esc_html__( 'Debug mode', 'shipping-simulator-for-woocommerce' ),
		'desc'     => esc_html__( 'Enable', 'shipping-simulator-for-woocommerce' ),
		'desc_tip' => __( 'Enable debug mode to log your shipping simulations and display helpful informations in product page.', 'shipping-simulator-for-woocommerce' ),
		'default'  => 'no'
	],
	[
		'id' => $wc_shipping_simulator_prefix . 'settings',
		'type' => 'sectionend',
	],
];
