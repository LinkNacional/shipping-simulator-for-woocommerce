<?php

namespace Shipping_Simulator;

use Shipping_Simulator\Helpers as h;
use Shipping_Simulator\Admin\Calculadora_Settings;

/**
 * Aplica os recursos migrados da "Calculadora de frete" (woo-better) no
 * frontend do shipping-simulator de forma ADITIVA.
 *
 * Não sobrescreve o componente original: o formulário (template + CSS) do
 * shipping-simulator permanece intacto. Os recursos (estilo do campo/ícone/
 * fonte/posição) são aplicados por um script JS próprio, a exemplo do que o
 * woo-better faz com os scripts `CustomCartPostcode`/`CustomProductPostcode`.
 *
 * @since 2.6.0
 */
final class Public_Calculadora {
	public function __start () {
		add_action( 'template_redirect', [ $this, 'setup_cart_hooks' ] );

		add_filter( 'wc_shipping_simulator_wrapper_css_class', [ $this, 'wrapper_class' ] );
		add_filter( 'wc_shipping_simulator_form_input_placeholder', [ $this, 'form_input_placeholder' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
	}

	/**
	 * Contexto atual do frontend.
	 *
	 * @return string 'cart' | 'product'
	 */
	private function context () {
		return is_cart() ? 'cart' : 'product';
	}

	/**
	 * Lê uma opção migrada para o contexto atual (produto ou carrinho).
	 *
	 * @param string $suffix Sufixo do nome da opção (ex.: `input_placeholder`).
	 * @param mixed  $default
	 * @return mixed
	 */
	private function ctx_option ( $suffix, $default = '' ) {
		return Calculadora_Settings::get_option(
			'woo_better_calc_' . $this->context() . '_' . $suffix,
			$default
		);
	}

	/**
	 * Registra a barra de frete grátis no carrinho.
	 */
	public function setup_cart_hooks () {
		if ( ! is_cart() ) return;

		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_min_free_shipping', 'no' ) ) {
			return;
		}

		add_action( 'woocommerce_before_cart_totals', [ $this, 'render_cart_progress_bar' ], 10 );
	}

	/**
	 * Adiciona a classe de contexto ao wrapper do formulário.
	 *
	 * @param array<int, string> $classes
	 * @return array<int, string>
	 */
	public function wrapper_class ( $classes ) {
		$classes[] = 'is-' . $this->context();
		return $classes;
	}

	/**
	 * Placeholder do campo (contexto produto/carrinho).
	 *
	 * @param string $placeholder
	 * @return string
	 */
	public function form_input_placeholder ( $placeholder ) {
		$value = $this->ctx_option( 'input_placeholder', '' );
		return '' !== $value ? $value : $placeholder;
	}

	/**
	 * Enfileira o script que aplica os recursos migrados no frontend.
	 */
	public function enqueue_scripts () {
		if ( ! is_product() && ! is_cart() ) return;

		$version = h::get_plugin_version();

		wp_enqueue_script(
			h::prefix( 'calculadora' ),
			h::plugin_url( 'assets/js/calculadora.min.js' ),
			[],
			$version,
			true
		);

		wp_localize_script( h::prefix( 'calculadora' ), 'wcShippingSimulatorCalcData', $this->get_js_data() );
	}

	/**
	 * Monta os dados localizados para o script JS.
	 *
	 * @return array<string, mixed>
	 */
	private function get_js_data () {
		$ctx = $this->context();

		$get = function ( $suffix, $default = '' ) use ( $ctx ) {
			return Calculadora_Settings::get_option( 'woo_better_calc_' . $ctx . '_' . $suffix, $default );
		};

		$icon_name  = $get( 'input_icon', 'transit' );
		$icon_color = $get( 'input_icon_color', 'blue-icon' );

		$font_source = Calculadora_Settings::get_option( 'woo_better_calc_font_source', 'yes' );

		$position = $ctx === 'product'
			? Calculadora_Settings::get_option( 'woo_better_calc_product_input_position', 'top' )
			: 'top';

		return [
			'context'        => $ctx,
			'fontFamily'     => 'yes' === $font_source ? "'Poppins', sans-serif" : 'inherit',
			'icon'           => h::plugin_url( 'assets/admin/icons/postcodeOptions/' . $icon_name . '.svg' ),
			'iconColor'      => $icon_color,
			'position'       => $position,
			'customSelector' => Calculadora_Settings::get_option( 'woo_better_calc_product_custom_position', '' ),
			'inputStyles'    => [
				'backgroundColor' => $get( 'input_background_color_field', '#ffffff' ),
				'color'           => $get( 'input_color_field', '#2C3338' ),
				'borderWidth'     => $get( 'input_border_width', '1px' ),
				'borderStyle'     => $get( 'input_border_style', 'solid' ),
				'borderColor'     => $get( 'input_border_color_field', '#ccc' ),
				'borderRadius'    => $get( 'input_border_radius', '4px' ),
			],
			'buttonStyles'   => [
				'backgroundColor' => $get( 'button_background_color_field', '#0073aa' ),
				'color'           => $get( 'button_color_field', '#ffffff' ),
				'borderWidth'     => $get( 'button_border_width', '1px' ),
				'borderStyle'     => $get( 'button_border_style', 'none' ),
				'borderColor'     => $get( 'button_border_color_field', 'transparent' ),
				'borderRadius'    => $get( 'button_border_radius', '4px' ),
			],
		];
	}

	/**
	 * Renderiza a barra de progresso de frete grátis (carrinho).
	 */
	public function render_cart_progress_bar () {
		if ( 'yes' !== Calculadora_Settings::get_option( 'woo_better_enable_min_free_shipping', 'no' ) ) return;

		$min = (float) Calculadora_Settings::get_option( 'woo_better_min_free_shipping_value', '0' );
		if ( $min <= 0 ) return;

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;

		$base    = Calculadora_Settings::get_option( 'woo_better_free_shipping_calc_base', 'subtotal' );
		$current = 'total' === $base
			? (float) WC()->cart->get_total( 'edit' )
			: (float) WC()->cart->get_subtotal();

		$remaining = max( 0.0, $min - $current );
		$percent   = $min > 0 ? min( 100, ( $current / $min ) * 100 ) : 0;

		if ( $remaining <= 0 ) {
			$message = (string) Calculadora_Settings::get_option(
				'woo_better_min_free_shipping_success_message',
				'Parabéns! Você tem frete grátis!'
			);
			$status_class = 'is-complete';
		} else {
			$message = (string) Calculadora_Settings::get_option(
				'woo_better_min_free_shipping_message',
				'Falta(m) apenas mais {value} para obter FRETE GRÁTIS'
			);
			$status_class = 'is-pending';
		}

		$message = str_replace( '{value}', wc_price( $remaining ), $message );

		$track_bg = '#e6e6e6';
		$fill_bg  = 'is-complete' === $status_class ? '#4caf50' : '#0073aa';
		?>
		<div class="wc-shipping-simulator-free-shipping-bar <?php echo esc_attr( $status_class ); ?>">
			<div class="wc-shipping-simulator-free-shipping-bar-track" style="background: <?php echo esc_attr( $track_bg ); ?>; border-radius: 10px; height: 12px; overflow: hidden;">
				<div class="wc-shipping-simulator-free-shipping-bar-fill" style="height: 100%; width: <?php echo esc_attr( (string) round( $percent, 2 ) ); ?>%; background: <?php echo esc_attr( $fill_bg ); ?>; border-radius: 10px; transition: width 0.3s ease;"></div>
			</div>
			<p class="wc-shipping-simulator-free-shipping-bar-message" style="margin: 6px 0 0; font-size: 13px; color: #333;"><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}
}
