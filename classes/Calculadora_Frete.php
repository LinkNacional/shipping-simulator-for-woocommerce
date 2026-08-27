<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Admin\Calculadora_Settings;

/**
 * Hooks/funções de comportamento da aba "Calculadora de frete" migrados do
 * plugin woo-better-shipping-calculator-for-brazil.
 *
 * Implementa o efeito real de cada opção da aba:
 *  - Desabilitar frete/endereço (all/digital).
 *  - Frete grátis por valor mínimo do carrinho.
 *  - Frete grátis por produto (checkbox + filtro de pacote).
 *
 * Tudo é ADITIVO: não sobrescreve os recursos originais do shipping-simulator.
 *
 * @since 2.6.0
 */
final class Calculadora_Frete {
	/**
	 * Marca o cálculo de frete na página do produto (simulação de um único
	 * item via `Calculadora_Api::register_product_address`).
	 *
	 * Nesse contexto o subtotal real do carrinho não é confiável, então o
	 * cálculo do frete grátis por valor mínimo usa `contents_cost` do pacote.
	 *
	 * @var bool
	 */
	public static $is_product_address_calculation = false;

	/**
	 * Marca o cálculo de totais em andamento (usado para compatibilidade com
	 * o Melhor Envio, que lê os produtos direto do carrinho).
	 *
	 * @var bool
	 */
	protected $is_shipping_calculation_active = false;

	public function __start () {
		// Desabilitar frete/endereço.
		add_filter( 'woocommerce_cart_needs_shipping', [ $this, 'cart_needs_shipping' ], 10, 1 );
		add_filter( 'woocommerce_cart_needs_shipping_address', [ $this, 'cart_needs_shipping' ], 10, 1 );

		// Esconde os campos de endereço nativos (blocos/Gutenberg + clássico/shortcode).
		add_filter( 'woocommerce_get_country_locale', [ $this, 'hide_address_fields_in_locale' ], 999, 1 );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'hide_checkout_address_fields' ], 999, 1 );

		// Injeção de frete grátis (valor mínimo + por produto).
		add_filter( 'woocommerce_package_rates', [ $this, 'control_rates' ], 10, 2 );

		// Frete grátis por produto.
		add_action( 'woocommerce_product_options_shipping', [ $this, 'add_free_shipping_checkbox' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_free_shipping_checkbox' ] );
		add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'filter_free_shipping_products_from_packages' ], 999, 1 );
		add_filter( 'woocommerce_get_cart_contents', [ $this, 'filter_free_shipping_from_cart' ], 999, 1 );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'set_shipping_calculation_flag' ], 10, 1 );
		add_action( 'woocommerce_after_calculate_totals', [ $this, 'reset_shipping_calculation_flag' ], 10, 1 );

		// Força o recálculo das taxas nas páginas de carrinho/checkout.
		add_action( 'template_redirect', [ $this, 'force_shipping_recalc' ], 5 );
	}

	/**
	 * Desabilita frete/endereço conforme a opção `disabled_shipping`.
	 *
	 * Porta de `lkn_custom_disable_shipping` do woo-better.
	 *
	 * @param bool $needs_shipping
	 * @return bool
	 */
	public function cart_needs_shipping ( $needs_shipping ) {
		return false === self::disabled_shipping_mode() ? $needs_shipping : false;
	}

	/**
	 * Retorna o modo ativo de "desabilitar frete/endereço".
	 *
	 * @return string|false 'all', 'digital' ou false.
	 */
	public static function disabled_shipping_mode () {
		$option = Calculadora_Settings::get_option( 'woo_better_calc_disabled_shipping', 'default' );

		if ( 'all' === $option ) {
			return 'all';
		}

		if ( 'digital' === $option && self::cart_has_only_virtual_products() ) {
			return 'digital';
		}

		return false;
	}

	/**
	 * Esconde os campos de endereço nativos via locale (checkout em blocos e clássico).
	 *
	 * @param array<string, array<string, mixed>> $locale
	 * @return array<string, array<string, mixed>>
	 */
	public function hide_address_fields_in_locale ( $locale ) {
		if ( ! is_array( $locale ) || is_admin() || ! is_checkout() ) {
			return $locale;
		}

		if ( false === self::disabled_shipping_mode() ) {
			return $locale;
		}

		$fields_to_hide = [ 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ];

		// REASON: O checkout em blocos (Gutenberg) monta os campos a partir de
		// `countryData[country].locale` e ignora a entrada `default` do locale.
		// Iterar apenas as chaves já presentes no array recebido deixaria países
		// sem entrada própria (ex.: Brasil) com os campos visíveis. Por isso
		// aplicamos o override em todos os países de venda/envio.
		$countries = function_exists( 'WC' ) && isset( WC()->countries )
			? array_keys( array_merge(
				WC()->countries->get_allowed_countries(),
				WC()->countries->get_shipping_countries()
			) )
			: array_keys( $locale );

		foreach ( $countries as $country ) {
			$fields = isset( $locale[ $country ] ) && is_array( $locale[ $country ] )
				? $locale[ $country ]
				: [];

			foreach ( $fields_to_hide as $field ) {
				$existing = isset( $fields[ $field ] ) && is_array( $fields[ $field ] )
					? $fields[ $field ]
					: [];

				$fields[ $field ] = array_merge( $existing, [
					'hidden'   => true,
					'required' => false,
				] );
			}

			$locale[ $country ] = $fields;
		}

		return $locale;
	}

	/**
	 * Remove os campos de endereço do checkout clássico/shortcode.
	 *
	 * @param array<string, array<string, mixed>> $fields
	 * @return array<string, array<string, mixed>>
	 */
	public function hide_checkout_address_fields ( $fields ) {
		if ( false === self::disabled_shipping_mode() ) {
			return $fields;
		}

		$address_fields = [ 'company', 'address_1', 'address_2', 'city', 'state', 'postcode' ];

		foreach ( [ 'billing', 'shipping' ] as $group ) {
			foreach ( $address_fields as $field ) {
				$key = $group . '_' . $field;
				if ( isset( $fields[ $group ][ $key ] ) ) {
					unset( $fields[ $group ][ $key ] );
				}
			}
		}

		return $fields;
	}

	/**
	 * Verifica se um produto é digital (virtual ou baixável).
	 *
	 * @param \WC_Product|false|null $product
	 * @return bool
	 */
	public static function product_is_digital ( $product ) {
		return $product
			&& is_object( $product )
			&& ( $product->is_virtual() || $product->is_downloadable() );
	}

	/**
	 * Injeta fretes grátis (valor mínimo e/ou por produto) nas taxas do carrinho.
	 *
	 * Porta de `lkn_woo_better_control_rates` do woo-better.
	 *
	 * @param array<string, \WC_Shipping_Rate> $rates
	 * @param array<string, mixed>             $package
	 * @return array<string, \WC_Shipping_Rate>
	 */
	public function control_rates ( $rates, $package ) {
		$enable_by_product    = Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_by_product', 'no' );
		$enable_min           = Calculadora_Settings::get_option( 'woo_better_enable_min_free_shipping', 'no' );
		$min_value            = (float) Calculadora_Settings::get_option( 'woo_better_min_free_shipping_value', 0 );
		$calc_base            = Calculadora_Settings::get_option( 'woo_better_free_shipping_calc_base', 'subtotal' );
		$only_free_shipping   = Calculadora_Settings::get_option( 'woo_better_only_free_shipping', 'yes' );
		$avoid_duplication    = Calculadora_Settings::get_option( 'woo_better_avoid_free_shipping_duplication', 'no' );

		$has_free_shipping = false;
		if ( 'yes' === $avoid_duplication ) {
			foreach ( $rates as $rate ) {
				if ( $rate && method_exists( $rate, 'get_cost' ) && 0.0 === (float) $rate->get_cost() ) {
					$has_free_shipping = true;
					break;
				}
			}
		}

		// Prioridade 1: frete grátis por valor mínimo do carrinho.
		if ( 'yes' === $enable_min && ! $has_free_shipping ) {
			$cart_total = $this->get_cart_total_for_free_shipping( $calc_base, $package );

			if ( $cart_total >= $min_value ) {
				$label = __( 'Frete Gratuito (Valor mínimo)', 'shipping-simulator-for-woocommerce' );
				$time  = (string) Calculadora_Settings::get_option( 'woo_better_min_free_shipping_delivery_time', '' );
				if ( '' !== $time ) {
					$label .= ' (' . $time . ')';
				}

				$free_rate = new \WC_Shipping_Rate( 'free_shipping_min', $label, 0, [], 'free_shipping' );

				return $this->merge_free_shipping_rate( $rates, $free_rate, 'free_shipping_min', $only_free_shipping );
			}
		}

		// Prioridade 2: frete grátis por produto.
		if ( 'yes' === $enable_by_product && ! $has_free_shipping ) {
			if ( $this->all_products_free_shipping() ) {
				$label = __( 'Frete Grátis (Produto)', 'shipping-simulator-for-woocommerce' );
				$time  = (string) Calculadora_Settings::get_option( 'woo_better_free_shipping_by_product_delivery_time', '' );
				if ( '' !== $time ) {
					$label .= ' (' . $time . ')';
				}

				$free_rate = new \WC_Shipping_Rate( 'free_shipping_product', $label, 0, [], 'free_shipping' );

				return $this->merge_free_shipping_rate( $rates, $free_rate, 'free_shipping_product', $only_free_shipping );
			}
		}

		return $rates;
	}

	/**
	 * Adiciona o checkbox de frete grátis na aba de entrega do produto.
	 *
	 * Porta de `lkn_add_free_shipping_product_checkbox` do woo-better.
	 *
	 * @return void
	 */
	public function add_free_shipping_checkbox () {
		woocommerce_wp_checkbox( [
			'id'          => '_wc_better_free_shipping',
			'label'       => __( 'Frete Grátis para este Produto', 'shipping-simulator-for-woocommerce' ),
			'description' => __( 'Ativa o frete grátis exclusivamente para este item. Se o cliente adicionar este produto junto com outros que possuem frete pago no carrinho, o cálculo do frete ignorará este item e cobrará apenas o valor de envio dos demais produtos.', 'shipping-simulator-for-woocommerce' ),
			'desc_tip'    => true,
		] );
	}

	/**
	 * Salva o checkbox de frete grátis por produto.
	 *
	 * Porta de `lkn_save_free_shipping_product_checkbox` do woo-better.
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function save_free_shipping_checkbox ( $post_id ) {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_by_product', 'no' ) ) {
			return;
		}

		$free_shipping = isset( $_POST['_wc_better_free_shipping'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_wc_better_free_shipping', $free_shipping );
	}

	/**
	 * Remove produtos com frete grátis dos pacotes de cálculo, para que os
	 * plugins de frete calculem apenas os itens com frete pago.
	 *
	 * Porta de `lkn_filter_free_shipping_products_from_packages` do woo-better.
	 *
	 * @param array<int, array<string, mixed>> $packages
	 * @return array<int, array<string, mixed>>
	 */
	public function filter_free_shipping_products_from_packages ( $packages ) {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_by_product', 'no' ) ) {
			return $packages;
		}

		if ( ! self::is_valid_woocommerce_context() || ! isset( WC()->cart ) ) {
			return $packages;
		}

		foreach ( $packages as $package_key => $package ) {
			if ( ! isset( $package['contents'] ) || ! is_array( $package['contents'] ) ) {
				continue;
			}

			$free_items = [];
			$paid_items = [];

			foreach ( $package['contents'] as $item_key => $item ) {
				$product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;

				if ( 'yes' === get_post_meta( $product_id, '_wc_better_free_shipping', true ) ) {
					$free_items[ $item_key ] = $item;
				} else {
					$paid_items[ $item_key ] = $item;
				}
			}

			// Mistura de itens: calcula o frete apenas com os itens pagos.
			if ( ! empty( $free_items ) && ! empty( $paid_items ) ) {
				$packages[ $package_key ]['contents'] = $paid_items;

				$new_contents_cost = 0;
				foreach ( $paid_items as $item ) {
					$new_contents_cost += (float) ( isset( $item['line_total'] ) ? $item['line_total'] : $item['line_subtotal'] );
				}
				$packages[ $package_key ]['contents_cost'] = $new_contents_cost;
			}
			// Todos com frete grátis: mantém o pacote intacto (frete grátis
			// total é tratado em control_rates()).
		}

		return $packages;
	}

	/**
	 * Remove produtos com frete grátis de `WC()->cart->get_cart()` durante o
	 * cálculo de frete (compatibilidade com o Melhor Envio).
	 *
	 * Porta de `lkn_filter_free_shipping_from_cart` do woo-better.
	 *
	 * @param array<string, array<string, mixed>> $cart_contents
	 * @return array<string, array<string, mixed>>
	 */
	public function filter_free_shipping_from_cart ( $cart_contents ) {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_free_shipping_by_product', 'no' ) ) {
			return $cart_contents;
		}

		if ( ! $this->is_shipping_calculation_active ) {
			return $cart_contents;
		}

		if ( ! self::is_valid_woocommerce_context() || ! isset( WC()->cart ) ) {
			return $cart_contents;
		}

		$free_items = [];
		$paid_items = [];

		foreach ( $cart_contents as $item_key => $item ) {
			$product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;

			if ( 'yes' === get_post_meta( $product_id, '_wc_better_free_shipping', true ) ) {
				$free_items[ $item_key ] = $item;
			} else {
				$paid_items[ $item_key ] = $item;
			}
		}

		if ( ! empty( $free_items ) && ! empty( $paid_items ) ) {
			return $paid_items;
		}

		return $cart_contents;
	}

	/**
	 * Ativa a flag de cálculo e limpa o cache de sessão das taxas.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function set_shipping_calculation_flag ( $cart ) {
		$this->is_shipping_calculation_active = true;

		if ( WC()->session ) {
			for ( $i = 0; $i < 10; $i++ ) {
				WC()->session->__unset( 'shipping_for_package_' . $i );
			}
		}
	}

	/**
	 * Reseta a flag de cálculo após o cálculo dos totais.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function reset_shipping_calculation_flag ( $cart ) {
		$this->is_shipping_calculation_active = false;
	}

	/**
	 * Força a limpeza do cache de sessão das taxas nas páginas de
	 * carrinho/checkout, garantindo que o filtro `woocommerce_package_rates`
	 * dispare e o frete grátis seja injetado.
	 *
	 * Porta de `lkn_force_shipping_recalc` do woo-better.
	 *
	 * @return void
	 */
	public function force_shipping_recalc () {
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		if ( function_exists( 'WC' ) && WC()->session ) {
			for ( $i = 0; $i < 10; $i++ ) {
				WC()->session->__unset( 'shipping_for_package_' . $i );
			}
		}
	}

	/**
	 * Calcula a base de valor do carrinho para o frete grátis por valor mínimo.
	 *
	 * @param string               $calc_base
	 * @param array<string, mixed> $package
	 * @return float
	 */
	private function get_cart_total_for_free_shipping ( $calc_base, $package ) {
		if ( self::$is_product_address_calculation ) {
			return (float) h::get( $package['contents_cost'], 0 );
		}

		if ( 'total' === $calc_base && isset( WC()->cart ) ) {
			return (float) WC()->cart->get_subtotal() - (float) WC()->cart->get_discount_total();
		}

		return isset( WC()->cart )
			? (float) WC()->cart->get_displayed_subtotal()
			: (float) h::get( $package['contents_cost'], 0 );
	}

	/**
	 * Mescla uma nova taxa de frete grátis mantendo (ou não) as demais taxas.
	 *
	 * @param array<string, \WC_Shipping_Rate> $rates
	 * @param \WC_Shipping_Rate                $free_rate
	 * @param string                           $free_key
	 * @param string                           $only_free_shipping 'yes'|'no'
	 * @return array<string, \WC_Shipping_Rate>
	 */
	private function merge_free_shipping_rate ( $rates, $free_rate, $free_key, $only_free_shipping ) {
		$new_rates = [ $free_key => $free_rate ];

		foreach ( $rates as $key => $rate ) {
			if ( $key === $free_key ) {
				continue;
			}

			if ( 'yes' === $only_free_shipping ) {
				if ( method_exists( $rate, 'get_cost' ) && 0.0 === (float) $rate->get_cost() ) {
					$new_rates[ $key ] = $rate;
				}
			} else {
				$new_rates[ $key ] = $rate;
			}
		}

		return $new_rates;
	}

	/**
	 * Verifica se TODOS os produtos do carrinho possuem a flag de frete grátis.
	 *
	 * @return bool
	 */
	private function all_products_free_shipping () {
		if ( ! self::is_valid_woocommerce_context() || ! isset( WC()->cart ) ) {
			return false;
		}

		$cart = WC()->cart->get_cart();
		if ( empty( $cart ) ) {
			return false;
		}

		foreach ( $cart as $cart_item ) {
			$product_id = isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : 0;

			if ( 'yes' !== get_post_meta( $product_id, '_wc_better_free_shipping', true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Verifica se o carrinho só possui produtos virtuais/baixáveis.
	 *
	 * @return bool
	 */
	public static function cart_has_only_virtual_products () {
		if ( ! self::is_valid_woocommerce_context() || ! isset( WC()->cart ) ) {
			return false;
		}

		$only_virtual = false;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];

			if ( $product->is_virtual() || $product->is_downloadable() ) {
				$only_virtual = true;
			} else {
				return false;
			}
		}

		return $only_virtual;
	}

	/**
	 * Verifica se o WooCommerce está disponível no contexto atual.
	 *
	 * @return bool
	 */
	public static function is_valid_woocommerce_context () {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		if ( is_multisite() ) {
			global $blog_id;
			return ! empty( $blog_id ) && $blog_id > 0;
		}

		return true;
	}
}
