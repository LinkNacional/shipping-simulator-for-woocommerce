<?php
/*
Plugin Name: Shipping Simulator for WooCommerce
Plugin URI: https://github.com/LinkNacional/shipping-simulator-for-woocommerce
Description: Allows your customers to calculate the shipping rates on the product page
Version: 2.4.4
Requires at least: 4.9
Requires PHP: 7.4
Author: Link Nacional
Author URI: https://linknacional.com.br/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: shipping-simulator-for-woocommerce
Domain Path: /languages
Requires Plugins: woocommerce

Shipping Simulator for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 of the License.

Shipping Simulator for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

You should have received a copy of the GNU General Public License
along with Shipping Simulator for WooCommerce. If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

// prevents your PHP files from being executed via direct browser access
defined( 'ABSPATH' ) || exit( 1 );

$wc_shipping_simulator_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $wc_shipping_simulator_autoload ) ) {
	// composer autoload
	include $wc_shipping_simulator_autoload;
	// start the plugin
	\Shipping_Simulator\Core\Main::start_plugin( __FILE__ );
} else {
	// display a error
	return add_action( 'admin_notices', function () {
		// error visible only for admin users
		if ( ! current_user_can( 'install_plugins' ) ) return;

		include_once ABSPATH . '/wp-includes/functions.php';
		list( $plugin_name ) = get_file_data( __FILE__, [ 'plugin name' ] );

		$message = sprintf(
			'Error on %1$s plugin activation: %2$s',
			'<strong>' . esc_html( $plugin_name ) . '</strong>',
			'<code>Autoload file not found</code><br><em>Download this plugin from WordPress repository and avoid downloading from other sources (Github, etc).</em>'
		);

		$allowed_html = [
			'strong' => [],
			'code'   => [],
			'br'     => [],
			'em'     => [],
		];

		echo '<div class="notice notice-error"><p>' . wp_kses( $message, $allowed_html ) . '</p></div>';
	} );
}
