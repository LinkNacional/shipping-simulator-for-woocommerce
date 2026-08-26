<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Helpers as h;

/**
 * Aba "Calculadora de frete" (migrada do plugin
 * woo-better-shipping-calculator-for-brazil).
 *
 * Registra uma nova aba no topo de WooCommerce > Configurações com o
 * mesmo nome/descrições da página original, porém usando IDs próprios do
 * shipping-simulator (prefixo `wc_shipping_simulator_`).
 *
 * Quando o plugin woo-better está ativo, o valor de cada opção é usado como
 * default (fallback) da opção migrada — assim nenhum dado é perdido.
 *
 * @since 2.6.0
 */
final class Calculadora_Settings {
	/** @var array|null */
	protected static $fields = null;

	/**
	 * Slug da aba em WooCommerce > Configurações.
	 */
	const TAB_ID = 'wc-shipping-simulator-calculadora';

	/**
	 * Slug do plugin de origem no formato {pasta}/{arquivo}.php.
	 */
	const WOO_BETTER_PLUGIN = 'woo-better-shipping-calculator-for-brazil/wc-better-shipping-calculator-for-brazil.php';

	public function __start () {
		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_tab' ], 999 );
		add_action( 'woocommerce_settings_' . self::TAB_ID, [ $this, 'output' ] );
		add_action( 'woocommerce_settings_save_' . self::TAB_ID, [ $this, 'save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Adiciona a aba no topo de WooCommerce > Configurações, na mesma
	 * posição que a aba original do woo-better.
	 *
	 * @param array<string, string> $tabs
	 * @return array<string, string>
	 */
	public function add_settings_tab ( $tabs ) {
		$label = __( 'Calculadora de frete', 'shipping-simulator-for-woocommerce' );

		// Evita duplicata caso o callback rode mais de uma vez.
		unset( $tabs[ self::TAB_ID ] );

		return self::insert_tab( $tabs, self::TAB_ID, $label );
	}

	/**
	 * Insere a aba na posição equivalente à da versão original.
	 *
	 * Ordem de âncoras:
	 * 1. Antes da aba original do woo-better (`wc-better-calc`).
	 * 2. Depois de "Ponto de venda".
	 * 3. Antes de "Avançado".
	 *
	 * @param array<string, string> $tabs
	 * @param string                $new_id
	 * @param string                $new_label
	 * @return array<string, string>
	 */
	private static function insert_tab ( $tabs, $new_id, $new_label ) {
		$keys = array_keys( $tabs );

		// 1) Mesma posição da aba original do woo-better (antes dela).
		$before = array_search( 'wc-better-calc', $keys, true );

		// 2) Fallback: logo depois de "Ponto de venda".
		if ( false === $before ) {
			$pos = array_search( 'point-of-sale', $keys, true );
			$before = ( false !== $pos ) ? $pos + 1 : false;
		}

		// 3) Último fallback: antes de "Avançado".
		if ( false === $before ) {
			$before = array_search( 'advanced', $keys, true );
		}

		// Sem âncora válida: insere no final.
		if ( false === $before || ! isset( $keys[ $before ] ) ) {
			$tabs[ $new_id ] = $new_label;
			return $tabs;
		}

		$anchor_key = $keys[ $before ];
		$result = [];
		$inserted = false;

		foreach ( $tabs as $id => $label ) {
			if ( ! $inserted && $id === $anchor_key ) {
				$result[ $new_id ] = $new_label;
				$inserted = true;
			}
			$result[ $id ] = $label;
		}

		// Segurança: âncora encontrada mas não percorrida (não deveria ocorrer).
		if ( ! $inserted ) {
			$result[ $new_id ] = $new_label;
		}

		return $result;
	}

	/**
	 * Renderiza o card lateral e os campos da aba.
	 */
	public function output () {
		echo $this->render_settings_card(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template já escapa internamente.
		\WC_Admin_Settings::output_fields( $this->get_settings() );
	}

	/**
	 * Salva os campos da aba.
	 */
	public function save () {
		\WC_Admin_Settings::save_fields( $this->get_settings() );
	}

	/**
	 * Enfileira os assets (JS/CSS compilados) e localiza os dados.
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue_assets ( $hook_suffix ) {
		if ( 'woocommerce_page_wc-settings' !== $hook_suffix ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		if ( self::TAB_ID !== $tab ) {
			return;
		}

		$version = h::get_plugin_version();

		wp_enqueue_script(
			'wc-shipping-simulator-admin-layout',
			h::plugin_url( 'Admin/jsCompiled/WcShippingSimulatorAdminLayout.COMPILED.js' ),
			[ 'jquery' ],
			$version,
			true
		);

		wp_enqueue_script(
			'wc-shipping-simulator-admin-settings',
			h::plugin_url( 'Admin/jsCompiled/WcShippingSimulatorAdminSettings.COMPILED.js' ),
			[],
			$version,
			true
		);

		wp_enqueue_style(
			'wc-shipping-simulator-admin-settings',
			h::plugin_url( 'Admin/cssCompiled/WcShippingSimulatorAdminSettings.COMPILED.css' ),
			[],
			$version
		);

		wp_enqueue_style(
			'wc-shipping-simulator-admin-card',
			h::plugin_url( 'Admin/cssCompiled/WcShippingSimulatorAdminCard.COMPILED.css' ),
			[],
			$version
		);

		$font_source = self::get_option( 'woo_better_calc_font_source', 'yes' );
		$font_class  = 'yes' === $font_source
			? 'wc-shipping-simulator-poppins-family'
			: 'wc-shipping-simulator-inherit-family';

		$icons_base = h::plugin_url( 'assets/admin/icons/postcodeOptions/' );

		$invoice_slug = 'invoice-payment-for-woocommerce';

		wp_localize_script( 'wc-shipping-simulator-admin-layout', 'wcShippingSimulatorAjax', [
			'font_class'               => $font_class,
			'install_nonce'            => wp_create_nonce( 'install-plugin_' . $invoice_slug ),
			'plugin_slug'              => $invoice_slug,
			'invoice_plugin_installed' => $this->is_invoice_plugin_installed(),
		] );

		wp_localize_script( 'wc-shipping-simulator-admin-layout', 'wcShippingSimulatorIcons', [
			'bill'     => $icons_base . 'bill.svg',
			'postcode' => $icons_base . 'postcode.svg',
			'transit'  => $icons_base . 'transit.svg',
			'zipcode'  => $icons_base . 'zipcode.svg',
			'truck'    => $icons_base . 'truck.svg',
			'consult'  => $icons_base . 'textFieldConsult.svg',
		] );

		$images_base = h::plugin_url( 'assets/admin/images/' );

		wp_localize_script( 'wc-shipping-simulator-admin-layout', 'wcShippingSimulatorBarImages', [
			'with_label'    => $images_base . 'barWithLabel.png',
			'without_label' => $images_base . 'barWithoutLabel.png',
		] );
	}

	/**
	 * Renderiza o card lateral (Link Nacional).
	 *
	 * @return string
	 */
	private function render_settings_card () {
		$versions = 'Shipping Simulator v' . h::get_plugin_version();
		if ( function_exists( 'WC' ) && WC()->version ) {
			$versions .= ' | WooCommerce v' . WC()->version;
		}

		$icons_dir  = h::plugin_url( 'assets/admin/icons/' );
		$icons_path = h::config_get( 'DIR' ) . '/assets/admin/icons/';

		$has_migrated = file_exists( $icons_path . 'linkNacionalLogo.webp' );

		return h::get_template( 'WcShippingSimulatorAdminSettingsCard', [
			'backgrounds' => [
				'right' => $icons_dir . 'backgroundCardRight.svg',
				'left'  => $icons_dir . 'backgroundCardLeft.svg',
			],
			'logo'     => $has_migrated ? $icons_dir . 'linkNacionalLogo.webp' : h::plugin_url( 'assets/images/icon.svg' ),
			'whatsapp' => $has_migrated ? $icons_dir . 'whatsapp.svg' : '',
			'telegram' => $has_migrated ? $icons_dir . 'telegram.svg' : '',
			'versions' => $versions,
		] );
	}

	/**
	 * Prefixo usado nos IDs dos campos migrados.
	 *
	 * @return string
	 */
	public static function get_prefix_migrated () {
		return h::prefix();
	}

	/**
	 * Retorna a lista de campos da aba, já com os defaults resolvidos a
	 * partir do woo-better quando ele estiver ativo.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_settings () {
		return self::get_fields();
	}

	/**
	 * Retorna a lista de campos da aba, já com os defaults resolvidos a
	 * partir do woo-better quando ele estiver ativo.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_fields () {
		if ( null === self::$fields ) {
			$fields = include __DIR__ . '/inc/calculadora_settings_fields.php';

			foreach ( $fields as $i => $field ) {
				$type = h::get( $field['type'], 'text' );

				// Campos de título/seção não guardam opção: pular.
				if ( in_array( $type, [ 'title', 'sectionend' ] ) ) {
					continue;
				}

				$woo_id = isset( $field['id'] ) ? self::woo_better_id( $field['id'] ) : '';
				$fallback = h::get( $field['default'], '' );

				$fields[ $i ]['default'] = self::resolve_default( $woo_id, $fallback );
			}

			self::$fields = apply_filters(
				'wc_shipping_simulator_calculadora_settings_fields',
				$fields
			);
		}

		return self::$fields;
	}

	/**
	 * Lê uma opção migrada com verificação de fallback.
	 *
	 * Ordem de resolução:
	 * 1. Opção própria (`wc_shipping_simulator_*`).
	 * 2. Opção do woo-better, caso o plugin esteja ativo.
	 * 3. Default informado.
	 *
	 * @param string $woo_key Nome da opção no woo-better (ex.: `woo_better_calc_enable_product_page`).
	 * @param mixed  $default Default a usar quando nada estiver salvo.
	 * @return mixed
	 */
	public static function get_option ( $woo_key, $default = '' ) {
		$own_id = self::migrated_id( $woo_key );
		$own = get_option( $own_id, false );

		if ( false !== $own ) {
			return $own;
		}

		if ( self::woo_better_active() ) {
			$woo = get_option( $woo_key, false );
			if ( false !== $woo ) {
				return $woo;
			}
		}

		return $default;
	}

	/**
	 * Converte um ID do shipping-simulator no ID equivalente do woo-better.
	 *
	 * @param string $id
	 * @return string
	 */
	private static function woo_better_id ( $id ) {
		return str_replace( 'wc_shipping_simulator', 'woo_better', $id );
	}

	/**
	 * Converte um ID do woo-better no ID migrado do shipping-simulator.
	 *
	 * @param string $woo_id
	 * @return string
	 */
	private static function migrated_id ( $woo_id ) {
		return str_replace( 'woo_better', 'wc_shipping_simulator', $woo_id );
	}

	/**
	 * Resolve o default de um campo: usa o valor do woo-better quando ativo,
	 * senão o fallback declarado.
	 *
	 * @param string $woo_id
	 * @param mixed  $fallback
	 * @return mixed
	 */
	private static function resolve_default ( $woo_id, $fallback ) {
		if ( '' === $woo_id ) {
			return $fallback;
		}

		if ( self::woo_better_active() ) {
			$woo = get_option( $woo_id, false );
			if ( false !== $woo ) {
				return $woo;
			}
		}

		return $fallback;
	}

	/**
	 * Verifica se o plugin woo-better está ativo.
	 *
	 * @return bool
	 */
	private static function woo_better_active () {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( self::WOO_BETTER_PLUGIN );
	}

	/**
	 * Verifica se o plugin "Invoice Payment for WooCommerce" está instalado.
	 *
	 * Igual ao woo-better: confere apenas o slug oficial do WordPress.org.
	 * A pasta local `woocommerce-invoice-payment` é a versão de dev e NÃO deve
	 * contar como instalada (senão o botão "Instalar" some indevidamente).
	 *
	 * @return bool
	 */
	private function is_invoice_plugin_installed () {
		return file_exists( WP_PLUGIN_DIR . '/invoice-payment-for-woocommerce/wc-invoice-payment.php' );
	}
}
