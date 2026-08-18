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

		Dependencies::init();
	}
}
