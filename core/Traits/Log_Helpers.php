<?php

namespace Shipping_Simulator\Core\Traits;

use Shipping_Simulator\Core\Config;

trait Log_Helpers {

    /**
	 * @param mixed ...$values
	 * @return void
	 */
    public static function log ( ...$values ) {
		if ( ! WP_DEBUG || ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) return;

        $prefix = '[' . Config::get( 'SLUG' ) . ']';
        $message = '';

		foreach ( $values as $value ) {
			if ( \is_bool( $value ) ) {
				$message .= $value ? 'true' : 'false';
			} else {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Intentional debug logging, gated by WP_DEBUG && WP_DEBUG_LOG above.
				$message .= print_r( $value, true );
			}
			$message .= ' ';
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging, gated by WP_DEBUG && WP_DEBUG_LOG above.
		\error_log( "$prefix $message" );
	}

    /**
	 * @param \Throwable|string $err
	 * @return string The error message
	 */
	public static function log_critical ( $err ) {
		if ( is_a( $err, \Throwable::class ) ) {
			$error = $err->getMessage();
			$file = $err->getFile();
			$line = $err->getLine();

			$message = sprintf(
				/* translators: 1: error message, 2: file path, 3: line number */
				__( '%1$s in %2$s on line %3$s', 'shipping-simulator-for-woocommerce' ),
				$error,
				$file,
				$line
			);
			$message .= PHP_EOL . 'Stack trace:' . PHP_EOL . $err->getTraceAsString() . PHP_EOL;
		} else {
			$message = $err;
		}

		self::log( $message );

		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = \wc_get_logger();
			$prefix = '[' . Config::get( 'SLUG' ) . ']';
			$logger->critical( "$prefix $message", [ 'source' => 'fatal-errors' ]);
		}

		return $message;
	}
}
