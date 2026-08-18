<?php

namespace Shipping_Simulator\Core;

final class Main {

	/**
	 * @param string $main_file The file that contains the plugin headers
	 * @return void
	 */
	public static function start_plugin ( $main_file ) {
		if ( ! file_exists( $main_file ) ) {
			throw new \Exception( 'Invalid plugin main file path in ' . __CLASS__ );
		}

		Config::init( $main_file );
		Loader::init();

		add_action( 'init', [ __CLASS__, 'load_textdomain' ], 0 );

		Dependencies::init();
	}

	/**
	 * @return void
	 */
	public static function load_textdomain () {
		$languages_dir = Config::get( 'DOMAIN_PATH', 'languages' );
		load_plugin_textdomain(
			'shipping-simulator-for-woocommerce',
			false,
			dirname( plugin_basename( Config::get( 'FILE' ) ) ) . "/$languages_dir/"
		);
	}
}
