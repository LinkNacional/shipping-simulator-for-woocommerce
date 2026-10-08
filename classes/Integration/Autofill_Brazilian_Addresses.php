<?php

namespace Shipping_Simulator\Integration;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Calculadora_Api;
use function wp_remote_get;

final class Autofill_Brazilian_Addresses {

	protected static $instance = null;

	/**
	 * @var array|null
	 */
	private $address_cache = null;

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
			'wc_shipping_simulator_integration_autofill_br_addresses_enabled',
			false
		);
	}

	public function add_hooks () {
		if ( $this->is_enabled() ) {
			add_filter( 'wc_shipping_simulator_package_data', [ $this, 'fill_package_destination' ] );
			add_filter( 'wc_shipping_simulator_results_title_address', [ $this, 'results_title_address' ], 20, 2 );
		}
	}

	public function fill_package_destination ( $package ) {
		$dest = $package['destination'];

		if ( 'BR' !== h::get( $dest['country'] ) ) return $package;

		$postcode = h::get( $dest['postcode'] );
		$address = $this->get_address( $postcode );

		if ( $address ) {
			$package['destination'] = [
				'postcode' => $address['postcode'],
				'address' => implode(
					', ',
					array_filter( [
						$address['address_1'],
						$address['neighborhood'],
						$address['city'],
					] )
				),
				'address_1' => $address['address_1'],
				'address_2' => '',
				'city' => $address['city'],
				'state' => $address['state'],
				'country' => 'BR',
			];
		}

		return $package;
	}

	public function results_title_address ( $address_string, $data ) {
		$postcode = h::get( $data['postcode'] );
		$address = $this->get_address( $postcode );
		if ( $address ) {
			$parts = [
				$address['address_1'],
				$address['city'],
				$address['state']
			];
			$address_string = '<strong>' . implode( ', ', array_filter( $parts ) ) . '</strong>';
		}
		return $address_string;
	}

	/**
	 * @param string $postcode
	 * @return array|false
	 */
	private function get_address ( $postcode ) {
		$address = $this->address_cache;
		$postcode = h::sanitize_postcode( $postcode );

		if ( ! $postcode ) return false;
		if ( $address && $postcode === $address['postcode'] ) return $address;

		// Fonte principal: mesma consulta da calculadora nova (BrasilAPI com
		// fallback ViaCEP), garantindo o destino completo de forma confiável.
		$info = Calculadora_Api::lookup_cep( $postcode );

		if ( ! empty( $info['status'] ) ) {
			$address = [
				'postcode'     => $postcode,
				'address_1'    => h::get( $info['address'], '' ),
				'neighborhood' => h::get( $info['neighborhood'], '' ),
				'city'         => h::get( $info['city'], '' ),
				'state'        => h::get( $info['state_sigla'], '' ),
				'country'      => 'BR',
			];
			$this->address_cache = $address;
			return $address;
		}

		// Fallback: OpenCEP (comportamento anterior).
		$url = 'https://opencep.com/v1/' . $postcode;
		$response = wp_remote_get( $url );

		h::logger()->info( "Requesting address to {$url}" );

		if ( is_wp_error( $response ) ) {
			h::logger()->error( "Request failed: " . $response->get_error_message() );
			return false;
		}

		$status = (int) $response['response']['code'];
		$body = (string) $response['body'];

		h::logger()->info( "Response status code: {$status}" );
		h::logger()->info( "Response body: {$body}" );

		if ( 200 === $status ) {
			$address = json_decode( $body, true );
			if ( is_array( $address ) ) {
				$address = $this->format_address( $address );
				$this->address_cache = $address;
				return $address;
			}
		}

		return false;
	}

	/**
	 * @param array $address
	 * @return array
	 */
	private function format_address ( $address ) {
		return [
			'postcode' => h::sanitize_postcode( h::get( $address['cep'], '' ) ),
			'address_1' => h::get( $address['logradouro'], '' ),
			'neighborhood' => h::get( $address['bairro'], '' ),
			'city' => h::get( $address['localidade'], '' ),
			'state' => h::get( $address['uf'], '' ),
			'country' => 'BR'
		];
	}
}
