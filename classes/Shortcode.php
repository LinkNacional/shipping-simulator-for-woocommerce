<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Request;
use Shipping_Simulator\Admin\Settings;

final class Shortcode {
	/** @var \WC_Product $product */
	protected $product;

	public function __start () {
		add_shortcode( self::get_tag(), [ $this, 'render_shortcode' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts'] );
		add_action( 'wc_shipping_simulator_form_after', [ $this, 'display_results_wrapper' ] );
		add_filter( 'script_loader_tag', [ $this, 'prepare_script_tag' ], 10, 2 );
	}

	public static function get_tag () {
		return 'wc_shipping_simulator';
	}

	public function display_results_wrapper () {
		$wrapper = h::get_template( 'shipping-simulator-results-wrapper' );
		echo wp_kses_post( apply_filters( 'wc_shipping_simulator_results_wrapper', $wrapper ) );
	}



	public function render_shortcode ( $atts ) {
		$atts = shortcode_atts( [
			'product' => 0,
		], $atts, self::get_tag() );

		$atts['product'] = absint( $atts['product'] );
		$product = null;
		if ( $atts['product'] !== 0 ) {
			$product = wc_get_product( $atts['product'] );
		} else {
			$product = $GLOBALS['product'] ?? null;
		}

		if ( is_object( $product ) && h::product_needs_shipping( $product ) && false === Calculadora_Frete::disabled_shipping_mode() ) {
			$this->product = $product;

			do_action( 'wc_shipping_simulator_shortcode_included', $atts );

			return h::get_template( 'shipping-simulator-form', [
				'css_class' => implode( ' ', apply_filters(
					'wc_shipping_simulator_wrapper_css_class',
					[]
				) ),
				// customizable template variables
				'input_placeholder' => apply_filters(
					'wc_shipping_simulator_form_input_placeholder',
					Settings::get_option( 'input_placeholder' )
				),
				'input_type' => apply_filters(
					'wc_shipping_simulator_form_input_type',
					'text'
				),
				'input_value' => apply_filters(
					'wc_shipping_simulator_form_input_value',
					$this->get_customer_postcode()
				),
				'submit_label' => apply_filters(
					'wc_shipping_simulator_form_submit_label',
					Settings::get_option( 'submit_label' )
				),
				'params' => apply_filters(
					'wc_shipping_simulator_form_js_params',
					$this->get_script_params()
				),
			] );
		}
		return '';
	}

	public function enqueue_scripts () {
		$suffix = h::get_defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		$plugin_version = h::config_get( 'VERSION' );

		wp_enqueue_script(
			h::prefix( 'form' ),
			h::plugin_url( "assets/js/form$suffix.js" ),
			[],
			$plugin_version,
			(bool) apply_filters( 'wc_shipping_simulator_script_in_footer', false )
		);

		wp_enqueue_style(
			h::prefix( 'form' ),
			h::plugin_url( "assets/css/form$suffix.css" ),
			[],
			$plugin_version
		);

		do_action( 'wc_shipping_simulator_shortcode_enqueue_scripts' );
	}

	public function prepare_script_tag ( $tag, $handle ) {
		if ( h::prefix( 'form' ) !== $handle ) return $tag;

		$atts = [];

		if ( apply_filters( 'wc_shipping_simulator_script_use_defer', true ) ) {
			$atts[] = 'defer';
		}

		if ( apply_filters( 'wc_shipping_simulator_script_disable_cfrocket', true ) ) {
			$atts[] = 'data-cfasync="false"';
		}

		return str_replace( ' src=', ' ' . implode( ' ', $atts ) . ' src=', $tag );
	}

	/**
	 * @return string
	 */
	protected function get_customer_postcode () {
		$woo = WC();

		if ( null !== $woo->customer ) {
			$billing_postcode = $woo->customer->get_billing_postcode();
			$postcode = $billing_postcode ? $billing_postcode : $woo->customer->get_shipping_postcode();
			return h::sanitize_postcode( $postcode );
		}

		return '';
	}

	protected function get_script_params () {
		return [
			'ajax_url' => \admin_url( 'admin-ajax.php' ),
			'ajax_action' => Request::get_ajax_action(),
			'product_type' => $this->product->get_type(),
			'product_id' => $this->product->get_id(),
			'requires_variation' => 'yes' === Settings::get_option( 'requires_variation' ),
			'auto_submit' => true,
			'timeout' => 60000, // 1 minute in milliseconds
			'errors' => [
				'timeout' => esc_html__( 'The server took too long to respond. Please try again.', 'shipping-simulator-for-woocommerce' ),
				'unexpected' => esc_html__( 'An unexpected error occurred. Please refresh the page and try again.', 'shipping-simulator-for-woocommerce' ),
			],
			'postcode_mask' => apply_filters(
				'wc_shipping_simulator_form_input_mask',
				'' // no input mask by default
			),
			'root_display' => 'block'
		];
	}
}
