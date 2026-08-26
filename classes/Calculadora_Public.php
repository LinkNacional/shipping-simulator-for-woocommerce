<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Admin\Calculadora_Settings;

/**
 * Enfileira os scripts da "Calculadora de frete" (migrados do woo-better) no
 * frontend do shipping-simulator, gerando um componente próprio com prefixo
 * `wc-shipping-simulator-*` (sem conflitar com o componente original).
 *
 * É a versão shipping-simulator dos scripts `CustomCartPostcode` e
 * `CustomProductPostcode` do woo-better.
 *
 * @since 2.6.0
 */
final class Calculadora_Public {
	public function __start () {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 20 );
	}

	/**
	 * Enfileira script + CSS + localize para o contexto atual.
	 */
	public function enqueue () {
		if ( is_product() ) {
			$this->enqueue_product();
		} elseif ( is_cart() ) {
			$this->enqueue_cart();
		}

		$this->enqueue_progress_bar();
	}

	/**
	 * URL do admin-ajax.php (compatível com multisite).
	 *
	 * @return string
	 */
	private function ajax_url () {
		if ( is_multisite() ) {
			return get_admin_url( get_current_blog_id(), 'admin-ajax.php' );
		}
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Classe de fonte conforme a opção migrada.
	 *
	 * @return string
	 */
	private function font_class () {
		$source = Calculadora_Settings::get_option( 'woo_better_calc_font_source', 'yes' );
		return 'no' === $source
			? 'wc-shipping-simulator-inherit-family'
			: 'wc-shipping-simulator-poppins-family';
	}

	/**
	 * Verifica se o componente deve ser escondido quando só há produtos
	 * digitais (opção `hide_calculator_digital` do woo-better).
	 *
	 * @return bool
	 */
	private function should_hide_for_digital_products () {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_calc_hide_calculator_digital', 'no' ) ) {
			return false;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];

			if ( ! $product->is_virtual() && ! $product->is_downloadable() ) {
				return false;
			}
		}

		return ! WC()->cart->is_empty();
	}

	/**
	 * Dados comuns do localize (WcShippingSimulatorData).
	 *
	 * @return array<string, mixed>
	 */
	private function common_data () {
		$icons_dir       = h::plugin_url( 'assets/admin/icons/' );
		$postcode_dir    = h::plugin_url( 'assets/admin/icons/postcodeOptions/' );
		$enable_search   = Calculadora_Settings::get_option( 'woo_better_calc_enable_auto_postcode_search', 'yes' );
		$cache_time      = Calculadora_Settings::get_option( 'woo_better_calc_cache_expiration_time', '0' );
		$cache_token     = Calculadora_Settings::get_option( 'woo_better_calc_enable_auto_cache_reset', 'WCBCB_9X2K4M7P5R8T3N6Y1Q' );

		return [
			'font_class'       => $this->font_class(),
			'display_icon'     => [
				'up'   => $icons_dir . 'upButton.svg',
				'down' => $icons_dir . 'downButton.svg',
			],
			'update_icon'      => [
				'updates' => $icons_dir . 'updates.svg',
			],
			'wooUrl'           => get_site_url( get_current_blog_id() ),
			'ajaxurl'          => $this->ajax_url(),
			'product_id'       => get_the_ID(),
			'quantity'         => 1,
			'enable_search'    => $enable_search,
			'cache_time'       => $cache_time,
			'cache_token'      => $cache_token,
			'get_postcode_nonce' => wp_create_nonce( Calculadora_Api::ACTION_GET_USER_POSTCODE ),
		];
	}

	/**
	 * Enfileira o script da barra de progresso de frete grátis (carrinho/checkout).
	 */
	private function enqueue_progress_bar () {
		if ( ! is_cart() && ! is_checkout() ) return;

		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_min_free_shipping', 'no' ) ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_script(
			'wc-shipping-simulator-progress-bar',
			h::plugin_url( 'assets/js/progress-bar.min.js' ),
			[ 'jquery' ],
			$version,
			true
		);

		// Verifica se todos os produtos são digitais (virtuais ou downloadables).
		$only_digital_products = false;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$has_digital_only = true;
			$has_products     = false;
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$has_products = true;
				$product      = $cart_item['data'];
				if ( ! $product->is_virtual() && ! $product->is_downloadable() ) {
					$has_digital_only = false;
					break;
				}
			}
			$only_digital_products = $has_products && $has_digital_only;
		}

		$data = [
			'min_free_shipping_value'            => Calculadora_Settings::get_option( 'woo_better_min_free_shipping_value', 0 ),
			'free_shipping_calc_base'            => Calculadora_Settings::get_option( 'woo_better_free_shipping_calc_base', 'subtotal' ),
			'currency_symbol'                    => get_woocommerce_currency_symbol(),
			'min_free_shipping_message'          => Calculadora_Settings::get_option( 'woo_better_min_free_shipping_message', '' ),
			'min_free_shipping_success_message'  => Calculadora_Settings::get_option( 'woo_better_min_free_shipping_success_message', '' ),
			'enable_progress_bar_value'          => Calculadora_Settings::get_option( 'woo_better_enable_progress_bar_value', 'no' ),
			'enable_free_shipping_detection'     => Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_detection', 'yes' ),
			'has_cart_block'                     => function_exists( 'has_block' ) && has_block( 'woocommerce/cart' ),
			'has_checkout_block'                 => function_exists( 'has_block' ) && has_block( 'woocommerce/checkout' ),
			'only_digital_products'              => $only_digital_products,
			'ajax_url'                           => $this->ajax_url(),
			'free_shipping_by_product_enabled'   => 'yes' === Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_by_product', 'no' ),
			'free_shipping_by_product_message'   => __( 'Frete grátis disponível por produto.', 'shipping-simulator-for-woocommerce' ),
			'min_free_shipping_delivery_time'    => Calculadora_Settings::get_option( 'woo_better_min_free_shipping_delivery_time', '' ),
			'free_shipping_by_product_delivery_time' => Calculadora_Settings::get_option( 'woo_better_free_shipping_by_product_delivery_time', '' ),
		];

		wp_localize_script( 'wc-shipping-simulator-progress-bar', 'wcShippingSimulatorProgress', $data );
	}

	/**
	 * Enfileira o script da página do produto.
	 */
	private function enqueue_product () {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_calc_enable_product_page', 'yes' ) ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_script(
			'wc-shipping-simulator-custom-product-postcode',
			h::plugin_url( 'assets/js/custom-product-postcode.min.js' ),
			[],
			$version,
			true
		);

		wp_enqueue_style(
			'wc-shipping-simulator-custom-postcode',
			h::plugin_url( 'assets/css/custom-postcode.min.css' ),
			[],
			$version
		);

		$postcode_dir = h::plugin_url( 'assets/admin/icons/postcodeOptions/' );
		$icons_dir    = h::plugin_url( 'assets/admin/icons/' );
		$icon         = Calculadora_Settings::get_option( 'woo_better_calc_product_input_icon', 'transit' );

		$data = array_merge( $this->common_data(), [
			'placeholder'     => Calculadora_Settings::get_option( 'woo_better_calc_product_input_placeholder', 'Insira seu CEP' ),
			'position'        => Calculadora_Settings::get_option( 'woo_better_calc_product_input_position', 'top' ),
			'custom_position' => Calculadora_Settings::get_option( 'woo_better_calc_product_custom_position', 'h1[class*="title"]' ),
			'inputStyles'     => [
				'backgroundColor' => Calculadora_Settings::get_option( 'woo_better_calc_product_input_background_color_field', '#ffffff' ),
				'color'           => Calculadora_Settings::get_option( 'woo_better_calc_product_input_color_field', '#000000' ),
				'borderWidth'     => Calculadora_Settings::get_option( 'woo_better_calc_product_input_border_width', '1px' ),
				'borderStyle'     => Calculadora_Settings::get_option( 'woo_better_calc_product_input_border_style', 'solid' ),
				'borderColor'     => Calculadora_Settings::get_option( 'woo_better_calc_product_input_border_color_field', '#cccccc' ),
				'borderRadius'    => Calculadora_Settings::get_option( 'woo_better_calc_product_input_border_radius', '4px' ),
				'fontClass'       => $this->font_class(),
			],
			'buttonStyles'    => [
				'backgroundColor' => Calculadora_Settings::get_option( 'woo_better_calc_product_button_background_color_field', '#0073aa' ),
				'color'           => Calculadora_Settings::get_option( 'woo_better_calc_product_button_color_field', '#ffffff' ),
				'borderWidth'     => Calculadora_Settings::get_option( 'woo_better_calc_product_button_border_width', '1px' ),
				'borderStyle'     => Calculadora_Settings::get_option( 'woo_better_calc_product_button_border_style', 'none' ),
				'borderColor'     => Calculadora_Settings::get_option( 'woo_better_calc_product_button_border_color_field', '#0073aa' ),
				'borderRadius'    => Calculadora_Settings::get_option( 'woo_better_calc_product_button_border_radius', '4px' ),
			],
			'icon'            => $postcode_dir . $icon . '.svg',
			'iconColor'       => Calculadora_Settings::get_option( 'woo_better_calc_product_input_icon_color', 'blue-icon' ),
			'details_icon'    => [
				'product'  => $icons_dir . 'product.svg',
				'quantity' => $icons_dir . 'quantity.svg',
			],
		] );

		wp_localize_script( 'wc-shipping-simulator-custom-product-postcode', 'WcShippingSimulatorData', $data );
	}

	/**
	 * Enfileira o script da página do carrinho.
	 */
	private function enqueue_cart () {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_calc_enable_cart_page', 'yes' ) ) {
			return;
		}

		// Esconde o componente quando só há produtos digitais (opção migrada).
		if ( $this->should_hide_for_digital_products() ) {
			return;
		}

		// Mesma regra do woo-better: recurso só disponível no WooCommerce 10+.
		if ( ! defined( 'WC_VERSION' ) || ! version_compare( WC_VERSION, '10.0.0', '>=' ) ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_script(
			'wc-shipping-simulator-custom-cart-postcode',
			h::plugin_url( 'assets/js/custom-cart-postcode.min.js' ),
			[],
			$version,
			true
		);

		wp_enqueue_style(
			'wc-shipping-simulator-custom-postcode',
			h::plugin_url( 'assets/css/custom-postcode.min.css' ),
			[],
			$version
		);

		$postcode_dir = h::plugin_url( 'assets/admin/icons/postcodeOptions/' );
		$icons_dir    = h::plugin_url( 'assets/admin/icons/' );
		$icon         = Calculadora_Settings::get_option( 'woo_better_calc_cart_input_icon', 'transit' );

		$is_blocks_cart = false;
		$post = get_post();
		if ( $post && is_a( $post, 'WP_Post' ) ) {
			$is_blocks_cart = function_exists( 'has_block' ) && has_block( 'woocommerce/cart', $post );
		}

		$data = array_merge( $this->common_data(), [
			'placeholder'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_placeholder', 'Insira seu CEP' ),
			'position'        => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_position', 'top' ),
			'custom_position' => Calculadora_Settings::get_option( 'woo_better_calc_cart_custom_position', 'h2[class*="order"]' ),
			'is_blocks_cart'  => $is_blocks_cart,
			'inputStyles'     => [
				'backgroundColor' => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_background_color_field', '#ffffff' ),
				'color'           => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_color_field', '#000000' ),
				'borderWidth'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_border_width', '1px' ),
				'borderStyle'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_border_style', 'solid' ),
				'borderColor'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_border_color_field', '#cccccc' ),
				'borderRadius'    => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_border_radius', '4px' ),
				'fontClass'       => $this->font_class(),
			],
			'buttonStyles'    => [
				'backgroundColor' => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_background_color_field', '#0073aa' ),
				'color'           => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_color_field', '#ffffff' ),
				'borderWidth'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_border_width', '1px' ),
				'borderStyle'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_border_style', 'none' ),
				'borderColor'     => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_border_color_field', '#0073aa' ),
				'borderRadius'    => Calculadora_Settings::get_option( 'woo_better_calc_cart_button_border_radius', '4px' ),
			],
			'icon'            => $postcode_dir . $icon . '.svg',
			'iconColor'       => Calculadora_Settings::get_option( 'woo_better_calc_cart_input_icon_color', 'blue-icon' ),
			'details_icon'    => [
				'cart'     => $icons_dir . 'product.svg',
				'quantity' => $icons_dir . 'quantity.svg',
			],
		] );

		wp_localize_script( 'wc-shipping-simulator-custom-cart-postcode', 'WcShippingSimulatorData', $data );
	}
}
