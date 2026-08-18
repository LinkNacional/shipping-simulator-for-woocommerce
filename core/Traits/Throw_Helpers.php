<?php

namespace Shipping_Simulator\Core\Traits;

trait Throw_Helpers {

	/**
	 * @return \Exception
	 */
	public static function get_error_class () {
		return \Exception::class;
	}

	/**
	 * If the condition is falsy, throws a Exception.
	 *
	 * @param bool $condition
	 * @param callable|mixed $message
	 * @param \Throwable|null $exception_class
	 * @return void
	 * @throws \Throwable
	 */
	public static function throw_if ( $condition, $message, $exception_class = null ) {
		if ( $condition ) {
			if ( ! is_string( $message ) && \is_callable( $message ) ) {
				$message = $message();
			}
			$exception_class = $exception_class ? $exception_class : self::get_error_class();
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Message is plain text (not HTML); callers supply sanitized/translated values, and it is emitted as JSON/alert/log.
			throw new $exception_class( $message );
		}
	}

	/**
	 * If the $var is a WP_Error instance, throws a Exception.
	 *
	 * @param mixed $var
	 * @param string $code
	 * @param \Throwable|null $exception_class
	 * @return void
	 * @throws \Throwable
	 */
	public static function throw_wp_error ( $var, $code = null, $exception_class = null ) {
		if ( \is_wp_error( $var ) ) self::throw_if( true, $var->get_error_message( $code ), $exception_class );
	}
}
