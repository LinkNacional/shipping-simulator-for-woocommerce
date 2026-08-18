<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Shipping_Package;
use Shipping_Simulator\Admin\Settings;
use Shipping_Simulator\Error;

final class Request {
	/**
	 * @var array
	 */
	protected $data = null;

	/**
	 * @var string
	 */
	protected $form_notice = null;

	public function __start () {
		$action = self::get_ajax_action();
		add_action( "wp_ajax_$action", [ $this, 'handle_ajax_request' ] );
		add_action( "wp_ajax_nopriv_$action", [ $this, 'handle_ajax_request' ] );

		add_action( 'wc_shipping_simulator_shortcode_included', [ $this, 'handle_form_request' ], 10 );
		add_action( 'wc_shipping_simulator_results_wrapper', [ $this, 'maybe_display_form_notice' ] );
	}

	public static function get_ajax_action () {
		return 'wc_shipping_simulator';
	}

	public function handle_ajax_request () {
		$response = [ 'success' => true ];
		$status_code = 200;

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			$response['success'] = false;
			$response['error'] = 'Method Not Allowed';
			$status_code = 405;
			wp_send_json( $response, $status_code );
		}

		try {
			h::logger()->info( 'Doing ajax request...' );
			$rates = $this->calculate_shipping( $_POST );
			$response['results_html'] = $this->get_results( $rates );
			$response['data'] = $this->data;
		} catch ( Error $e ) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
			$status_code = 400;
			h::logger()->error( $e->getMessage() );
		} catch ( \Throwable $e ) {
			throw $e;
		}

		$status_code = apply_filters(
			'wc_shipping_simulator_request_response_status',
			$status_code,
			$response
		);

		$response = apply_filters(
			'wc_shipping_simulator_request_response',
			$response,
			$status_code
		);

		if ( ! Settings::debug_enabled() ) {
			$cache_max_age = (int) apply_filters(
				'wc_shipping_simulator_request_cache_max_age',
				600 // 10 minutes
			);
			if ( $cache_max_age > 0 ) {
				add_filter( 'nocache_headers', '__return_empty_array', 999 );
				header( "Cache-Control: max-age=$cache_max_age, must-revalidate" );
			}
		}

		wp_send_json( $response, $status_code );
	}

	public function handle_form_request () {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) return;

		if ( current_user_can( 'manage_options' ) ) {
			$this->form_notice = __( 'Your browser does not have JavaScript enabled or there are JavaScript errors preventing the shipping simulator from working. Check your browser console or disable other plugins to try to find any conflicts.', 'shipping-simulator-for-woocommerce' );
		}
	}

	public function maybe_display_form_notice ( $html ) {
		if ( $this->form_notice ) {
			$html .= '<div class="woocommerce-info wc-shipping-simulator-notice">' . esc_html( $this->form_notice ) . '</div>';
		}
		return $html;
	}

	protected function calculate_shipping ( $args ) {
		h::logger()->info( 'Request raw data: ' . wp_json_encode( $args ) );

		$this->data = $this->prepare_request_data( $args );
		h::logger()->info( 'Request sanitized data: ' . wp_json_encode( $this->data ) );

		$this->validate_request_data( $this->data );
		h::logger()->info( 'Request data validated!');

		$package = new Shipping_Package();

		/** @var Shipping_Package */
		$package = apply_filters( 'wc_shipping_simulator_request_update_package', $package, $this->data );

		if ( ! $package->ready ) {
			$package->add_product( $this->data['product'], $this->data['quantity'], $this->data['variation'] );
			$package->set_destination( [
				'postcode' => $this->data['postcode']
			] );
		}

		return $package->calculate_shipping();
	}

	protected function get_results ( $rates ) {
		$args = [
			'rates' => $rates,
			'notice' => apply_filters(
				'wc_shipping_simulator_no_results_notice',
				Settings::get_option( 'no_results' )
			),
			'data' => $this->data,
		];
		return \apply_filters(
			'wc_shipping_simulator_request_results_html',
			h::get_template( 'shipping-simulator-results', $args ),
			$rates,
			$this->data
		);
	}

	protected function prepare_request_data ( $args ) {
		$data = [
			'postcode' => h::sanitize_postcode( h::get( $args['postcode'], '' ) ),
			'product' => absint( h::get( $args['product_id'], 0 ) ),
			'variation' => absint( h::get( $args['variation_id'], 0 ) ),
			'quantity' => absint( h::get( $args['quantity'], 1 ) ),
		];
		return apply_filters(
			'wc_shipping_simulator_prepare_request_data',
			$data,
			$args
		);
	}

	protected function validate_request_data ( $args ) {
		do_action( 'wc_shipping_simulator_validate_request_data', $args );

		if ( apply_filters( 'wc_shipping_simulator_use_default_validations', true, $args ) ) {
			h::throw_if(
				! $args['postcode'],
				esc_html__( 'The postcode is required.', 'shipping-simulator-for-woocommerce' )
			);
			$product = wc_get_product( $args['variation'] ? $args['variation'] : $args['product'] );
			h::throw_if(
				! $product,
				esc_html__( 'Invalid product.', 'shipping-simulator-for-woocommerce' )
			);
			h::throw_if(
				$args['quantity'] < 1,
				esc_html__( 'The quantity must be greater than zero.', 'shipping-simulator-for-woocommerce' )
			);
		}
	}
}
