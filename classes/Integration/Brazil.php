<?php

namespace Shipping_Simulator\Integration;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Calculadora_Api;

final class Brazil {
	protected static $instance = null;

	public static function instance () {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __start () {
		add_action( 'wc_shipping_simulator_load_integrations', [ $this, 'add_hooks' ] );
	}

	public function is_enabled () {
		return apply_filters(
			'wc_shipping_simulator_integration_brazil_enabled',
			'BRL' === get_woocommerce_currency()
		);
	}

	public function add_hooks () {
		if ( $this->is_enabled() ) {
			add_filter( 'wc_shipping_simulator_form_input_mask', [ $this, 'form_input_mask' ] );

			add_action( 'wc_shipping_simulator_form_end', [ $this, 'add_cep_finder_link' ] );

			add_filter( 'wc_shipping_simulator_prepare_request_data', [ $this, 'prepare_request_data' ], 10, 2 );

			add_action( 'wc_shipping_simulator_validate_request_data', [ $this, 'validate_request_data' ] );

			add_filter( 'wc_shipping_simulator_request_update_package', [ $this, 'update_package' ], 10, 2 );

			// Sincroniza o endereço de entrega do cliente com o destino do pacote
			// ANTES do cálculo do frete. Roda depois do autofill (prioridade 10).
			add_filter( 'wc_shipping_simulator_package_data', [ $this, 'sync_customer_shipping_address' ], 20 );

			add_filter( 'wc_shipping_simulator_wrapper_css_class', [ $this, 'wrapper_css_class' ] );

			add_filter( 'wc_shipping_simulator_form_input_type', [ $this, 'form_input_type' ] );

			add_filter( 'wc_shipping_simulator_results_title_address', [ $this, 'results_title_address' ], 10, 2 );
		}
	}

	public function form_input_type ( $type ) {
		$type = 'tel';
		return $type;
	}

	public function wrapper_css_class ( $css_class ) {
		$css_class[] = 'inline-inputs';
		return $css_class;
	}

	public function prepare_request_data ( $data, $posted ) {
		$data['country'] = h::get( $posted['country'], 'BR' );
		return $data;
	}

	public function validate_request_data ( $posted ) {
		if ( 'BR' !== h::get( $posted['country'] ) ) return;

		h::throw_if(
			null === $this->get_state_by_postcode( $posted['postcode'] ),
			'O CEP informado não existe ou está incompleto.'
		);
	}

	public function update_package ( $package, $posted ) {
		if ( 'BR' !== h::get( $posted['country'] ) ) {
			return $package;
		}

		$postcode = h::get( $posted['postcode'] );

		// Monta o destino completo a partir do CEP, usando a MESMA fonte da
		// calculadora nova (BrasilAPI com fallback ViaCEP). Sem isso, métodos de
		// frete que exigem cidade/endereço podem não retornar tarifas — o
		// cenário do chamado "produto não pode ser entregue na região informada".
		$info = Calculadora_Api::lookup_cep( $postcode );

		$state = ! empty( $info['state_sigla'] )
			? $info['state_sigla']
			: $this->get_state_by_postcode( $postcode );

		$destination = [
			'country' => 'BR',
			'state'   => $state,
		];

		$address      = h::get( $info['address'], '' );
		$neighborhood = h::get( $info['neighborhood'], '' );
		$city         = h::get( $info['city'], '' );

		if ( '' !== $city ) {
			$destination['city'] = $city;
		}

		if ( '' !== $address ) {
			$destination['address_1'] = $address;
		}

		if ( '' !== $address || '' !== $neighborhood || '' !== $city ) {
			$destination['address'] = implode( ', ', array_filter( [ $address, $neighborhood, $city ] ) );
		}

		$package->set_destination( $destination );

		return $package;
	}

	/**
	 * Sincroniza o endereço de entrega do `WC()->customer` com o destino já
	 * resolvido do pacote, ANTES do cálculo do frete.
	 *
	 * O WooCommerce (>= 9.8) só calcula frete quando existe endereço de entrega
	 * completo (`WC_Customer::has_full_shipping_address()`), e vários plugins de
	 * frete leem o endereço direto do cliente/carrinho. A calculadora nova já faz
	 * isso; aqui igualamos o comportamento no simulador legado.
	 *
	 * Não chama `save()`: a alteração vale só para esta requisição (o simulador é
	 * read-only e não deve persistir o endereço do visitante).
	 *
	 * @param array<string, mixed> $package
	 * @return array<string, mixed>
	 */
	public function sync_customer_shipping_address ( $package ) {
		if ( ! function_exists( 'WC' ) || ! WC()->customer ) {
			return $package;
		}

		$destination = isset( $package['destination'] ) && is_array( $package['destination'] )
			? $package['destination']
			: [];

		$country = h::get( $destination['country'] );

		if ( ! h::filled( $country ) ) {
			return $package;
		}

		$customer = WC()->customer;

		if ( is_callable( [ $customer, 'set_shipping_location' ] ) ) {
			$customer->set_shipping_location(
				(string) $country,
				(string) h::get( $destination['state'], '' ),
				(string) h::get( $destination['postcode'], '' ),
				(string) h::get( $destination['city'], '' )
			);
		}

		$address_1 = h::get( $destination['address_1'], '' );

		if ( '' !== $address_1 && is_callable( [ $customer, 'set_shipping_address_1' ] ) ) {
			$customer->set_shipping_address_1( $address_1 );
		}

		return $package;
	}

	public function form_input_mask ( $mask ) {
		return 'XXXXX-XXX';
	}

	public function add_cep_finder_link () {
		$cep_finder_link = apply_filters(
			'wc_shipping_simulator_brazil_cep_finder_link',
			'https://buscacepinter.correios.com.br/app/endereco/'
		);
		$cep_finder_label = apply_filters(
			'wc_shipping_simulator_brazil_cep_finder_label',
			'Não sei meu cep'
		);
		?>
		<div id="wc-shipping-sim-br-cep-finder">
			<a href="<?php echo esc_url( $cep_finder_link ) ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( $cep_finder_label ) ?></a>
		</div>
		<?php
	}

	public function results_title_address ( $address_string, $data ) {
		$country = $data['country'] ?? '';
		$postcode = $data['postcode'] ?? '';

		if ( 'BR' === $country ) {
			$state = $this->get_state_by_postcode( $postcode );
			if ( $state ) {
				$address_string = '<strong>' . $this->format_cep( $postcode ) . ', ' . $state . ', Brasil</strong>';
			}
		}

		return $address_string;
	}

	protected function format_cep ( $postcode ) {
		$postcode = h::sanitize_postcode( $postcode );
		$mask = $this->form_input_mask( null );
		return $mask ? h::str_mask( $postcode, $mask ) : $postcode;
	}

	protected function get_state_by_postcode ( $postcode ) {
		$state = Calculadora_Api::state_from_postcode( $postcode );

		return '' !== $state ? $state : null;
	}

}
