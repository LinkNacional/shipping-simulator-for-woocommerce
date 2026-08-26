<?php

namespace Shipping_Simulator;

/**
 * Endpoints AJAX/REST da "Calculadora de frete" migrados do woo-better para o
 * shipping-simulator.
 *
 * Mesma requisição/resposta do woo-better, apenas com IDs próprios (prefixo
 * `wc_shipping_simulator_`). Os scripts `custom-product-postcode` e
 * `custom-cart-postcode` chamam estes endpoints.
 *
 * @since 2.6.0
 */
final class Calculadora_Api {
	const ACTION_GET_USER_POSTCODE     = 'wc_shipping_simulator_get_user_postcode';
	const ACTION_GET_NONCE             = 'wc_shipping_simulator_get_nonce';
	const ACTION_PERSIST_POSTCODE      = 'wc_shipping_simulator_persist_postcode';
	const ACTION_REGISTER_PRODUCT      = 'wc_shipping_simulator_register_product_address';
	const ACTION_REGISTER_CART         = 'wc_shipping_simulator_register_cart_address';
	const ACTION_GET_CART_STATUS       = 'wc_shipping_simulator_get_cart_shipping_status';
	const REST_NAMESPACE               = 'wc-shipping-simulator/v1';

	public function __start () {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		add_action( 'wp_ajax_' . self::ACTION_GET_USER_POSTCODE, [ $this, 'get_user_postcode' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_GET_USER_POSTCODE, [ $this, 'get_user_postcode' ] );

		add_action( 'wp_ajax_' . self::ACTION_GET_NONCE, [ $this, 'get_nonce' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_GET_NONCE, [ $this, 'get_nonce' ] );

		add_action( 'wp_ajax_' . self::ACTION_PERSIST_POSTCODE, [ $this, 'persist_postcode' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_PERSIST_POSTCODE, [ $this, 'persist_postcode' ] );

		add_action( 'wp_ajax_' . self::ACTION_REGISTER_PRODUCT, [ $this, 'register_product_address' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_REGISTER_PRODUCT, [ $this, 'register_product_address' ] );

		add_action( 'wp_ajax_' . self::ACTION_REGISTER_CART, [ $this, 'register_cart_address' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_REGISTER_CART, [ $this, 'register_cart_address' ] );

		add_action( 'wp_ajax_' . self::ACTION_GET_CART_STATUS, [ $this, 'get_cart_shipping_status' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION_GET_CART_STATUS, [ $this, 'get_cart_shipping_status' ] );
	}

	/**
	 * Registra a rota REST de consulta de CEP.
	 */
	public function register_rest_routes () {
		register_rest_route( self::REST_NAMESPACE, '/cep/', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_cep_info' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'postcode' => [
					'required' => true,
				],
			],
		] );
	}

	/**
	 * Endpoint REST para consultar CEP (BrasilAPI com fallback ViaCEP).
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_cep_info ( \WP_REST_Request $request ) {
		$cep = $request->get_param( 'postcode' );

		if ( $this->is_playground_environment() ) {
			return new \WP_REST_Response( [
				'status'      => true,
				'city'        => 'Cidade',
				'state_sigla' => 'SP',
				'state'       => 'Sao Paulo',
				'address'     => 'Endereço',
			], 200 );
		}

		$country = 'BR';
		if ( function_exists( 'WC' ) && WC()->customer && method_exists( WC()->customer, 'get_shipping_country' ) ) {
			$country = WC()->customer->get_shipping_country();
		}

		if ( isset( $country ) && strtolower( $country ) !== 'br' ) {
			return new \WP_REST_Response( [
				'status'  => false,
				'message' => 'Somente CEPs do Brasil são aceitos.',
			], 400 );
		}

		if ( ! preg_match( '/^\d{8}$/', $cep ) && ! preg_match( '/^\d{5}-\d{3}$/', $cep ) ) {
			return new \WP_REST_Response( [
				'status'  => false,
				'message' => 'CEP inválido. O formato correto é XXXXX-XXX ou XXXXXXXX.',
			], 400 );
		}

		if ( preg_match( '/^\d{5}-\d{3}$/', $cep ) ) {
			$cep = str_replace( '-', '', $cep );
		}

		$response  = wp_remote_get( "https://brasilapi.com.br/api/cep/v2/{$cep}" );
		$http_code = wp_remote_retrieve_response_code( $response );
		$data      = [];

		if ( is_wp_error( $response ) || $http_code !== 200 ) {
			$ws_response      = wp_remote_get( "https://viacep.com.br/ws/{$cep}/json/" );
			$ws_response_body = wp_remote_retrieve_body( $ws_response );
			$ws_response_data = json_decode( $ws_response_body, true );

			if ( isset( $ws_response_data['cep'] ) ) {
				$data = [
					'status'      => true,
					'cep'         => $ws_response_data['cep'],
					'city'        => $ws_response_data['localidade'],
					'state_sigla' => $ws_response_data['uf'],
					'state'       => $ws_response_data['estado'],
					'street'      => $ws_response_data['logradouro'],
				];
			} else {
				return new \WP_REST_Response( [
					'status'  => false,
					'message' => 'CEP inválido.',
				], 400 );
			}
		} else {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
		}

		if ( isset( $data['cep'] ) ) {
			$state = $this->get_state_name_from_sigla( $data['state'] );

			return new \WP_REST_Response( [
				'status'      => true,
				'city'        => $data['city'],
				'state_sigla' => $data['state'],
				'state'       => $state,
				'address'     => $data['street'],
			], 200 );
		}

		if ( isset( $data['errors'] ) && ! empty( $data['errors'] ) ) {
			return new \WP_REST_Response( [
				'status'  => false,
				'message' => 'Cep não encontrado ou inválido.',
			], 404 );
		}

		return new \WP_REST_Response( [
			'status'  => false,
			'message' => 'CEP não encontrado.',
		], 404 );
	}

	/**
	 * AJAX: retorna um nonce atualizado para a ação informada.
	 */
	public function get_nonce () {
		if ( ! isset( $_REQUEST['action_nonce'] ) || empty( $_REQUEST['action_nonce'] ) ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'Parâmetro action_nonce obrigatório.',
			], 400 );
		}

		$action = sanitize_text_field( wp_unslash( $_REQUEST['action_nonce'] ) );
		$nonce  = wp_create_nonce( $action );
		wp_send_json_success( [ 'nonce' => $nonce ] );
	}

	/**
	 * AJAX: obtém o CEP do usuário da sessão.
	 */
	public function get_user_postcode () {
		header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::ACTION_GET_USER_POSTCODE ) ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'Falha na verificação de segurança (nonce).',
			], 403 );
		}

		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'WooCommerce não está disponível.',
			], 400 );
		}

		$cart_cep = '';
		if ( WC()->customer ) {
			$cart_cep = WC()->customer->get_billing_postcode();
			if ( empty( $cart_cep ) ) {
				$cart_cep = WC()->customer->get_shipping_postcode();
			}
		}

		wp_send_json_success( [ 'postcode' => $cart_cep ] );
	}

	/**
	 * AJAX: persiste apenas o CEP no WC()->customer (usado quando a API de CEP
	 * retorna erro — CEP inválido).
	 */
	public function persist_postcode () {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::ACTION_GET_USER_POSTCODE ) ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'Falha na verificação de segurança (nonce).',
			], 403 );
		}

		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'WooCommerce não está disponível.',
			], 400 );
		}

		$postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';

		if ( empty( $postcode ) || ! WC()->customer ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'CEP inválido ou cliente não disponível.',
			], 400 );
		}

		WC()->customer->set_shipping_postcode( $postcode );
		WC()->customer->set_billing_postcode( $postcode );
		WC()->customer->set_shipping_country( 'BR' );
		WC()->customer->set_billing_country( 'BR' );

		WC()->customer->set_shipping_address_1( '' );
		WC()->customer->set_shipping_address_2( '' );
		WC()->customer->set_shipping_city( '' );
		WC()->customer->set_shipping_state( '' );
		WC()->customer->set_billing_address_1( '' );
		WC()->customer->set_billing_address_2( '' );
		WC()->customer->set_billing_city( '' );
		WC()->customer->set_billing_state( '' );

		WC()->customer->save();

		if ( WC()->session ) {
			WC()->session->__unset( 'billing_number' );
			WC()->session->__unset( 'shipping_number' );
			WC()->session->__unset( 'billing_neighborhood' );
			WC()->session->__unset( 'shipping_neighborhood' );
			WC()->session->__unset( 'billing_address_1' );
			WC()->session->__unset( 'shipping_address_1' );
			WC()->session->__unset( 'billing_city' );
			WC()->session->__unset( 'shipping_city' );
			WC()->session->__unset( 'billing_state' );
			WC()->session->__unset( 'shipping_state' );
			WC()->session->__unset( 'billing_postcode' );
			WC()->session->__unset( 'shipping_postcode' );
			WC()->session->save_data();
		}

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'billing_number', '' );
			update_user_meta( $user_id, 'shipping_number', '' );
			update_user_meta( $user_id, 'billing_neighborhood', '' );
			update_user_meta( $user_id, 'shipping_neighborhood', '' );
		}

		wp_send_json_success( [ 'postcode' => $postcode ] );
	}

	/**
	 * AJAX: registra o endereço e calcula o frete para um produto único.
	 */
	public function register_product_address () {
		$nonce = isset( $_SERVER['HTTP_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_NONCE'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::ACTION_REGISTER_PRODUCT ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'Requisição não autorizada.',
			], 403 );
		}

		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'WooCommerce não está carregado.',
			], 500 );
		}

		$shipping = isset( $_POST['shipping'] ) && is_array( $_POST['shipping'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['shipping'] ) )
			: [];

		if ( is_array( $shipping ) ) {
			$shipping = array_map( 'sanitize_text_field', $shipping );
		}

		if ( empty( $shipping ) || ! is_array( $shipping ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'O parâmetro "shipping" é obrigatório e deve ser um array.',
			], 400 );
		}

		$shipping_data = [
			'first_name' => isset( $shipping['first_name'] ) ? sanitize_text_field( $shipping['first_name'] ) : null,
			'last_name'  => isset( $shipping['last_name'] ) ? sanitize_text_field( $shipping['last_name'] ) : null,
			'company'    => isset( $shipping['company'] ) ? sanitize_text_field( $shipping['company'] ) : null,
			'address_1'  => isset( $shipping['address_1'] ) ? sanitize_text_field( $shipping['address_1'] ) : null,
			'address_2'  => isset( $shipping['address_2'] ) ? sanitize_text_field( $shipping['address_2'] ) : null,
			'city'       => isset( $shipping['city'] ) ? sanitize_text_field( $shipping['city'] ) : null,
			'state'      => isset( $shipping['state'] ) ? sanitize_text_field( $shipping['state'] ) : null,
			'postcode'   => isset( $shipping['postcode'] ) ? sanitize_text_field( $shipping['postcode'] ) : null,
			'country'    => isset( $shipping['country'] ) ? sanitize_text_field( $shipping['country'] ) : 'BR',
			'phone'      => isset( $shipping['phone'] ) ? sanitize_text_field( $shipping['phone'] ) : null,
		];

		WC()->customer->set_props( [
			'shipping_first_name' => $shipping_data['first_name'],
			'shipping_last_name'  => $shipping_data['last_name'],
			'shipping_company'    => $shipping_data['company'],
			'shipping_address_1'  => $shipping_data['address_1'],
			'shipping_address_2'  => $shipping_data['address_2'],
			'shipping_city'       => $shipping_data['city'],
			'shipping_state'      => $shipping_data['state'],
			'shipping_postcode'   => $shipping_data['postcode'],
			'shipping_country'    => $shipping_data['country'],
			'shipping_phone'      => $shipping_data['phone'],
			'billing_first_name'  => $shipping_data['first_name'],
			'billing_last_name'   => $shipping_data['last_name'],
			'billing_company'     => $shipping_data['company'],
			'billing_address_1'   => $shipping_data['address_1'],
			'billing_address_2'   => $shipping_data['address_2'],
			'billing_city'        => $shipping_data['city'],
			'billing_state'       => $shipping_data['state'],
			'billing_postcode'    => $shipping_data['postcode'],
			'billing_country'     => $shipping_data['country'],
			'billing_phone'       => $shipping_data['phone'],
		] );

		WC()->customer->save();

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

		if ( ! $product_id || ! get_post( $product_id ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'Produto inválido ou não encontrado.',
			], 400 );
		}

		if ( $variation_id > 0 ) {
			$product = wc_get_product( $variation_id );
			if ( ! $product || $product->get_parent_id() !== $product_id ) {
				wp_send_json_error( [
					'status'  => false,
					'message' => 'Variação de produto inválida.',
				], 400 );
			}
		} else {
			$product = wc_get_product( $product_id );
		}

		if ( ! $product ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'Produto não encontrado.',
			], 400 );
		}

		if ( $product->is_virtual() || $product->is_downloadable() ) {
			wp_send_json_success( [
				'status'       => true,
				'digital'      => true,
				'product_name' => $product->get_name(),
				'message'      => 'O produto é digital ou baixável e não requer cálculo de frete.',
			], 200 );
		}

		$product_price = floatval( $product->get_price() );

		$quantity = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;
		if ( $quantity <= 0 ) {
			$quantity = 1;
		}

		$line_total = $product_price * $quantity;

		$simulated_key = 'simulated_' . wp_rand( 1000, 99999 );

		$simulated_item = [
			'key'               => $simulated_key,
			'product_id'        => $product_id,
			'variation_id'      => $variation_id,
			'variation'         => [],
			'quantity'          => $quantity,
			'data'              => $product,
			'line_total'        => $line_total,
			'line_subtotal'     => $line_total,
			'line_tax'          => 0,
			'line_subtotal_tax' => 0,
			'line_tax_data'     => [ 'total' => [], 'subtotal' => [] ],
		];

		$original_cart_contents = WC()->cart->cart_contents;

		WC()->cart->cart_contents = [ $simulated_key => $simulated_item ];

		$package = [
			'contents'        => WC()->cart->cart_contents,
			'contents_cost'   => $line_total,
			'applied_coupons' => [],
			'user'            => [
				'ID' => get_current_user_id(),
			],
			'destination'     => [
				'country'   => $shipping_data['country'],
				'state'     => $shipping_data['state'],
				'postcode'  => $shipping_data['postcode'],
				'city'      => $shipping_data['city'],
				'address_1' => $shipping_data['address_1'],
				'address_2' => $shipping_data['address_2'],
			],
		];

		$shipping = WC()->shipping();
		$shipping->load_shipping_methods();
		Calculadora_Frete::$is_product_address_calculation = true;
		$calculated_package = $shipping->calculate_shipping_for_package( $package, 0 );
		Calculadora_Frete::$is_product_address_calculation = false;

		WC()->cart->cart_contents = $original_cart_contents;

		$shipping_rates     = [];
		$currency_symbol    = get_woocommerce_currency_symbol();
		$currency_minor_unit = wc_get_price_decimals();

		$product_info = [
			'name'                => $product->get_name(),
			'quantity'            => $quantity,
			'currency_symbol'     => $currency_symbol,
			'currency_minor_unit' => $currency_minor_unit,
		];

		if ( isset( $calculated_package['rates'] ) && is_array( $calculated_package['rates'] ) ) {
			foreach ( $calculated_package['rates'] as $rate ) {
				$shipping_rates[] = [
					'id'        => $rate->get_id(),
					'label'     => $rate->get_label(),
					'cost'      => $rate->get_cost(),
					'meta_data' => $rate->get_meta_data(),
				];
			}
		}

		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( ! WC()->customer ) {
			WC()->initialize_session();
		}

		if ( ! is_null( $shipping_data['address_1'] ) ) {
			WC()->customer->set_shipping_address_1( $shipping_data['address_1'] );
			WC()->customer->set_billing_address_1( $shipping_data['address_1'] );
		}
		if ( ! is_null( $shipping_data['address_2'] ) ) {
			WC()->customer->set_shipping_address_2( $shipping_data['address_2'] );
			WC()->customer->set_billing_address_2( $shipping_data['address_2'] );
		}
		if ( ! is_null( $shipping_data['city'] ) ) {
			WC()->customer->set_shipping_city( $shipping_data['city'] );
			WC()->customer->set_billing_city( $shipping_data['city'] );
		}
		if ( ! is_null( $shipping_data['state'] ) ) {
			WC()->customer->set_shipping_state( $shipping_data['state'] );
			WC()->customer->set_billing_state( $shipping_data['state'] );
		}
		if ( ! is_null( $shipping_data['postcode'] ) ) {
			WC()->customer->set_shipping_postcode( $shipping_data['postcode'] );
			WC()->customer->set_billing_postcode( $shipping_data['postcode'] );
		}
		if ( ! is_null( $shipping_data['country'] ) ) {
			WC()->customer->set_shipping_country( 'BR' );
			WC()->customer->set_billing_country( 'BR' );
		}

		WC()->customer->save();
		WC()->session->save_data();

		wp_send_json_success( [
			'message'        => 'Endereço de envio registrado com sucesso e frete calculado.',
			'product'        => $product_info,
			'shipping_rates' => $shipping_rates,
		] );
	}

	/**
	 * AJAX: registra o endereço e calcula o frete para o carrinho.
	 */
	public function register_cart_address () {
		$nonce = isset( $_SERVER['HTTP_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_NONCE'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::ACTION_REGISTER_CART ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'Requisição não autorizada.',
			], 403 );
		}

		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'WooCommerce não está carregado.',
			], 500 );
		}

		$shipping = isset( $_POST['shipping'] ) && is_array( $_POST['shipping'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['shipping'] ) )
			: [];

		if ( empty( $shipping ) || ! is_array( $shipping ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'O parâmetro "shipping" é obrigatório e deve ser um array.',
			], 400 );
		}

		$shipping_data = [
			'first_name' => isset( $shipping['first_name'] ) ? sanitize_text_field( $shipping['first_name'] ) : null,
			'last_name'  => isset( $shipping['last_name'] ) ? sanitize_text_field( $shipping['last_name'] ) : null,
			'company'    => isset( $shipping['company'] ) ? sanitize_text_field( $shipping['company'] ) : null,
			'address_1'  => isset( $shipping['address_1'] ) ? sanitize_text_field( $shipping['address_1'] ) : null,
			'address_2'  => isset( $shipping['address_2'] ) ? sanitize_text_field( $shipping['address_2'] ) : null,
			'city'       => isset( $shipping['city'] ) ? sanitize_text_field( $shipping['city'] ) : null,
			'state'      => isset( $shipping['state'] ) ? sanitize_text_field( $shipping['state'] ) : null,
			'postcode'   => isset( $shipping['postcode'] ) ? sanitize_text_field( $shipping['postcode'] ) : null,
			'country'    => isset( $shipping['country'] ) ? sanitize_text_field( $shipping['country'] ) : 'BR',
			'phone'      => isset( $shipping['phone'] ) ? sanitize_text_field( $shipping['phone'] ) : null,
		];

		WC()->customer->set_props( [
			'shipping_first_name' => $shipping_data['first_name'],
			'shipping_last_name'  => $shipping_data['last_name'],
			'shipping_company'    => $shipping_data['company'],
			'shipping_address_1'  => $shipping_data['address_1'],
			'shipping_address_2'  => $shipping_data['address_2'],
			'shipping_city'       => $shipping_data['city'],
			'shipping_state'      => $shipping_data['state'],
			'shipping_postcode'   => $shipping_data['postcode'],
			'shipping_country'    => $shipping_data['country'],
			'shipping_phone'      => $shipping_data['phone'],
			'billing_first_name'  => $shipping_data['first_name'],
			'billing_last_name'   => $shipping_data['last_name'],
			'billing_company'     => $shipping_data['company'],
			'billing_address_1'   => $shipping_data['address_1'],
			'billing_address_2'   => $shipping_data['address_2'],
			'billing_city'        => $shipping_data['city'],
			'billing_state'       => $shipping_data['state'],
			'billing_postcode'    => $shipping_data['postcode'],
			'billing_country'     => $shipping_data['country'],
			'billing_phone'       => $shipping_data['phone'],
		] );

		WC()->customer->save();

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			if ( ! is_null( $shipping_data['first_name'] ) ) {
				update_user_meta( $user_id, 'shipping_first_name', $shipping_data['first_name'] );
			}
			if ( ! is_null( $shipping_data['last_name'] ) ) {
				update_user_meta( $user_id, 'shipping_last_name', $shipping_data['last_name'] );
			}
			if ( ! is_null( $shipping_data['company'] ) ) {
				update_user_meta( $user_id, 'shipping_company', $shipping_data['company'] );
			}
			if ( ! is_null( $shipping_data['address_1'] ) ) {
				update_user_meta( $user_id, 'shipping_address_1', $shipping_data['address_1'] );
			}
			update_user_meta( $user_id, 'shipping_address_2', '' );

			if ( ! is_null( $shipping_data['city'] ) ) {
				update_user_meta( $user_id, 'shipping_city', $shipping_data['city'] );
			}
			if ( ! is_null( $shipping_data['state'] ) ) {
				update_user_meta( $user_id, 'shipping_state', $shipping_data['state'] );
			}
			if ( ! is_null( $shipping_data['postcode'] ) ) {
				update_user_meta( $user_id, 'shipping_postcode', $shipping_data['postcode'] );
			}
			if ( ! is_null( $shipping_data['country'] ) ) {
				update_user_meta( $user_id, 'shipping_country', $shipping_data['country'] );
			}
			if ( ! is_null( $shipping_data['phone'] ) ) {
				update_user_meta( $user_id, 'shipping_phone', $shipping_data['phone'] );
			}
			update_user_meta( $user_id, 'shipping_neighborhood', '' );
			update_user_meta( $user_id, 'shipping_number', '' );

			if ( ! is_null( $shipping_data['first_name'] ) ) {
				update_user_meta( $user_id, 'billing_first_name', $shipping_data['first_name'] );
			}
			if ( ! is_null( $shipping_data['last_name'] ) ) {
				update_user_meta( $user_id, 'billing_last_name', $shipping_data['last_name'] );
			}
			if ( ! is_null( $shipping_data['company'] ) ) {
				update_user_meta( $user_id, 'billing_company', $shipping_data['company'] );
			}
			if ( ! is_null( $shipping_data['address_1'] ) ) {
				update_user_meta( $user_id, 'billing_address_1', $shipping_data['address_1'] );
			}
			update_user_meta( $user_id, 'billing_address_2', '' );

			if ( ! is_null( $shipping_data['city'] ) ) {
				update_user_meta( $user_id, 'billing_city', $shipping_data['city'] );
			}
			if ( ! is_null( $shipping_data['state'] ) ) {
				update_user_meta( $user_id, 'billing_state', $shipping_data['state'] );
			}
			if ( ! is_null( $shipping_data['postcode'] ) ) {
				update_user_meta( $user_id, 'billing_postcode', $shipping_data['postcode'] );
			}
			if ( ! is_null( $shipping_data['country'] ) ) {
				update_user_meta( $user_id, 'billing_country', $shipping_data['country'] );
			}
			if ( ! is_null( $shipping_data['phone'] ) ) {
				update_user_meta( $user_id, 'billing_phone', $shipping_data['phone'] );
			}
			update_user_meta( $user_id, 'billing_neighborhood', '' );
			update_user_meta( $user_id, 'billing_number', '' );
		}

		$cart_items = WC()->cart->get_cart();

		if ( empty( $cart_items ) ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => 'O carrinho está vazio.',
			], 400 );
		}

		$only_digital = true;
		foreach ( $cart_items as $cart_item ) {
			$product = $cart_item['data'];
			if ( ! $product->is_virtual() && ! $product->is_downloadable() ) {
				$only_digital = false;
				break;
			}
		}

		if ( $only_digital ) {
			$cart_count = WC()->cart->get_cart_contents_count();

			$message = $cart_count === 1
				? 'O produto no carrinho é digital ou baixável e não requer cálculo de frete.'
				: 'Todos os produtos no carrinho são digitais ou baixáveis e não requerem cálculo de frete.';

			wp_send_json_success( [
				'status'     => true,
				'digital'    => true,
				'cart_count' => $cart_count,
				'message'    => $message,
			], 200 );
		}

		$contents_cost = 0;
		foreach ( $cart_items as $cart_item ) {
			$contents_cost += floatval( $cart_item['line_total'] );
		}

		$package = [
			'contents'        => $cart_items,
			'contents_cost'   => $contents_cost,
			'applied_coupons' => WC()->cart->get_applied_coupons(),
			'user'            => [
				'ID' => get_current_user_id(),
			],
			'destination'     => [
				'country'   => $shipping_data['country'],
				'state'     => $shipping_data['state'],
				'postcode'  => $shipping_data['postcode'],
				'city'      => $shipping_data['city'],
				'address_1' => $shipping_data['address_1'],
				'address_2' => $shipping_data['address_2'],
			],
		];

		WC()->shipping()->reset_shipping();

		WC()->customer->set_shipping_location(
			$shipping_data['country'],
			$shipping_data['state'],
			$shipping_data['postcode'],
			$shipping_data['city']
		);

		WC()->cart->calculate_totals();

		$packages = WC()->shipping()->get_packages();

		$shipping_rates      = [];
		$currency_symbol     = get_woocommerce_currency_symbol();
		$currency_minor_unit = wc_get_price_decimals();

		foreach ( $packages as $package ) {
			if ( isset( $package['rates'] ) && is_array( $package['rates'] ) ) {
				foreach ( $package['rates'] as $rate ) {
					$shipping_rates[] = [
						'id'        => $rate->get_id(),
						'label'     => $rate->get_label(),
						'cost'      => $rate->get_cost(),
						'meta_data' => $rate->get_meta_data(),
					];
				}
			}
		}

		$total_quantity = 0;
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$total_quantity += $cart_item['quantity'];
		}

		wp_send_json_success( [
			'message' => 'Endereço de envio registrado com sucesso e frete calculado.',
			'cart'    => [
				'currency_symbol'     => $currency_symbol,
				'currency_minor_unit' => $currency_minor_unit,
				'quantity'            => $total_quantity,
			],
			'shipping_rates' => $shipping_rates,
		] );
	}

	/**
	 * AJAX: retorna total do carrinho e status de frete grátis.
	 *
	 * Usado pela barra de progresso de frete grátis.
	 */
	public function get_cart_shipping_status () {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( [
				'error'   => true,
				'message' => 'WooCommerce não está disponível.',
			], 400 );
		}

		$cart     = WC()->cart;
		$customer = WC()->customer;

		$calc_base = \Shipping_Simulator\Admin\Calculadora_Settings::get_option( 'woo_better_free_shipping_calc_base', 'subtotal' );

		if ( 'total' === $calc_base ) {
			$cart_total = (float) $cart->get_subtotal() - (float) $cart->get_discount_total();
		} else {
			$cart_total = $cart->get_displayed_subtotal();
		}

		$has_free_shipping              = false;
		$is_free_shipping_by_product_rate = false;

		if ( $customer && method_exists( $customer, 'get_shipping_postcode' ) && ! empty( $customer->get_shipping_postcode() ) ) {
			$cart->calculate_shipping();

			$packages = $cart->get_shipping_packages();

			foreach ( $packages as $package_key => $package ) {
				$session_key  = 'shipping_for_package_' . $package_key;
				$stored_rates = WC()->session->get( $session_key );

				if ( ! empty( $stored_rates['rates'] ) ) {
					foreach ( $stored_rates['rates'] as $rate_id => $rate ) {
						if ( floatval( $rate->cost ) === 0.0 ) {
							$has_free_shipping = true;

							if ( method_exists( $rate, 'get_label' ) && strpos( $rate->get_label(), 'Frete Grátis (Produto)' ) !== false ) {
								$is_free_shipping_by_product_rate = true;
							}
							break 2;
						}
					}
				}
			}
		}

		wp_send_json_success( [
			'freeShipping'           => $has_free_shipping,
			'cartTotal'              => $cart_total,
			'freeShippingByProduct'  => $is_free_shipping_by_product_rate,
		] );
	}

	/**
	 * Converte sigla de estado em nome por extenso.
	 *
	 * @param string $sigla
	 * @return string
	 */
	private function get_state_name_from_sigla ( $sigla ) {
		$estados = [
			'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
			'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal',
			'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão',
			'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
			'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco',
			'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
			'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima',
			'SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'SE' => 'Sergipe',
			'TO' => 'Tocantins',
		];

		return array_key_exists( $sigla, $estados ) ? $estados[ $sigla ] : $sigla;
	}

	/**
	 * Verifica se está rodando no WordPress Playground.
	 *
	 * @return bool
	 */
	private function is_playground_environment () {
		$current_url = home_url();

		if ( is_multisite() ) {
			$network_url = network_home_url();
			if ( strpos( $network_url, 'playground.wordpress.net' ) !== false ) {
				return true;
			}
		}

		$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
		if ( strpos( $server_name, 'playground.wordpress.net' ) !== false ) {
			return true;
		}

		return strpos( $current_url, 'playground.wordpress.net' ) !== false;
	}
}
