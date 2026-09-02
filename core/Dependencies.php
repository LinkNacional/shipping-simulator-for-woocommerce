<?php

namespace Shipping_Simulator\Core;

final class Dependencies {
	/** @var array */
	protected static $dependencies;

	/** @var bool */
	protected static $initialized = false;

	/**
	 * @return void
	 * @throws \Exception
	 */
	public static function init () {
		if ( self::$initialized ) {
			throw new \Exception( __CLASS__ . ' already initialized' );
		}

		$root = Config::get( 'DIR' );
		self::$dependencies = include_once $root . '/dependencies.php';
		if ( ! is_array( self::$dependencies ) ) {
			throw new \Exception( esc_html( $root ) . '/dependencies.php must return an Array' );
		}

		\add_action( 'init', [ __CLASS__, 'maybe_start_plugin' ], 0 );

		self::$initialized = true;
	}

	/**
	 * @return void
	 */
	public static function maybe_start_plugin () {
		$result = self::check_dependencies();

		if ( $result['success'] ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Hook name is built by Loader::get_hook_start_plugin() => "start_plugin_" . plugin file path.
			do_action( Loader::get_hook_start_plugin() );
		} else {
			self::display_notice_missing_deps( $result['messages'] );
		}
	}

	/**
	 * @return array{success: bool, messages: array<int, array{text: string, is_error: bool}>}
	 * @throws \Exception
	 */
	public static function check_dependencies () {
		$result = [
			'success' => null,
			'messages' => [],
		];
		$errors = 0;

		foreach ( self::$dependencies as $key => $dep ) {
			$check = $dep['check'] ?? null;
			$message = $dep['message'] ?? null;
			$message = is_callable( $message ) ? call_user_func( $message ) : $message;

			// check the message
			if ( ! is_string( $message ) || '' === trim( $message ) ) {
				$id = is_integer( $key ) ? '#' . ( 1 + $key ) : $key;
				throw new \Exception( sprintf( "Dependency %s has an invalid 'message': its must be a string and and it cannot be empty.", esc_html( $id ) ) );
			}

			// check the requirement
			$found = false;
			if ( is_string( $check ) ) {
				$found = self::handle_shortcut( $check );
			} elseif ( is_callable( $check ) ) {
				$found = call_user_func( $check );
			}

			$result['messages'][] = [
				'text' => $message,
				'is_error' => ! boolval( $found ),
			];

			if ( ! boolval( $found ) ) $errors++;
		}

		$result['success'] = ( 0 === $errors );

		return $result;
	}

	/**
	 * @param string $shortcut
	 * @return bool
	 * @throws \Exception
	 */
	protected static function handle_shortcut ( $shortcut ) {
		$parts = explode( ':', $shortcut );
		$value = trim( implode( ':', array_slice( $parts, 1 ) ) );
		$type = trim( $parts[0] );
		if ( ! $value || ! $type ) {
			throw new \Exception( sprintf( 'Invalid shortcut syntax: %s', esc_html( $shortcut ) ) );
		}
		switch ( $type ) {
			case 'class':
				return class_exists( $value );
			case 'function':
				return function_exists( $value );
			case 'plugin':
				if ( ! function_exists( 'is_plugin_active' ) ) {
					include_once \ABSPATH . 'wp-admin/includes/plugin.php';
				}
				return \is_plugin_active( $value );
			case 'module': // alias for 'extension'
			case 'extension':
				return extension_loaded( $value );
			case 'const': // alias for 'defined'
			case 'defined':
				return defined( $value );
			case 'wp': // alias for 'wordpress'
			case 'wordpress':
				return version_compare( \get_bloginfo( 'version' ), $value, '>=' );
			default:
				break;
		}

		throw new \Exception( sprintf( 'Unexpected shortcut: %s', esc_html( $shortcut ) ) );
	}

	/**
	 * @param array<int, array{text:string, is_error:bool}> $messages
	 * @return void
	 */
	protected static function display_notice_missing_deps ( $messages ) {
		if ( ! \is_admin() ) return;
		if ( ! \current_user_can( 'install_plugins' ) ) return;
		if ( 0 === count( $messages ) ) return;

		// Não exibe na página de atualização/instalação de plugins.
		$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
		if ( in_array( $pagenow, [ 'update.php', 'update-core.php', 'update-core-network.php' ], true ) ) return;

		\usort(
			$messages,
			function ( $a, $b ) {
				return $b['is_error'] <=> $a['is_error'];
			}
		);

		\add_action( 'admin_notices', function () use ( $messages ) {
			$plugin_name = '<strong>' . esc_html( Config::get( 'NAME' ) ) . '</strong>';
			$intro = sprintf(
				/* translators: %s is replaced with plugin name */
				__( 'O plugin %s precisa das seguintes dependências para funcionar:', 'shipping-simulator-for-woocommerce' ),
				$plugin_name
			);

			$indent = \str_repeat( '&nbsp;', 4 );
			$missing = esc_html__( 'Ausente', 'shipping-simulator-for-woocommerce' );
			$allowed_html = [
				'a' => [ 'href' => [], 'target' => [] ],
				'span' => [ 'class' => [], 'style' => [] ],
				'em' => [],
				'strong' => [],
				'code' => [],
				's' => [],
				'br' => [],
			];

			echo '<div class="notice notice-error"><p>';
			echo wp_kses( $intro, $allowed_html );

			foreach ( $messages as $message ) {
				$line = \sprintf(
					'<span style="color:%s;"><span class="dashicons dashicons-%s">&nbsp;</span>%s%s</span>',
					$message['is_error'] ? '#e03131' : '#2b8a3e',
					$message['is_error'] ? 'minus' : 'yes',
					$message['is_error'] ? "$missing: " : '',
					\wp_kses( $message['text'], $allowed_html )
				);
				$line = $message['is_error'] ? $line : "<s>$line</s>";
				echo wp_kses( "<br> {$indent} {$line}", $allowed_html );
			}

			echo '</p></div>';
		} );
	}
}
