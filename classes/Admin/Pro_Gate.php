<?php

namespace Shipping_Simulator\Admin;

// Impede acesso direto.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portão das opções exclusivas da versão PRO.
 *
 * O plugin gratuito EXIBE todos os campos da versão PRO (para o usuário
 * conhecer o recurso e poder mexer na interface), mas:
 *
 * - o valor enviado nunca é salvo: em `guard_settings()` os campos marcados
 *   com `'pro' => true` saem da lista entregue ao
 *   `WC_Admin_Settings::save_fields()` e a opção volta ao `default` declarado;
 * - nenhuma lógica é executada (a implementação vive apenas no plugin PRO);
 * - cada bloco PRO (e cada card de campo dentro dele) recebe a pílula
 *   vermelha `PRO`, que aponta para a página comercial (constante abaixo,
 *   ainda sem URL definitiva).
 *
 * Basta o plugin PRO estar ativo (constante de versão definida) para o portão
 * se abrir: os mesmos campos passam a ser salvos normalmente e as lógicas do
 * PRO entram em ação.
 *
 * @since 3.0.0
 */
final class Pro_Gate {
	/**
	 * URL da página comercial da versão PRO.
	 *
	 * Pode ser trocada por filtro (`wc_shipping_simulator_pro_upgrade_url`) ou pela
	 * constante `WC_SHIPPING_SIMULATOR_PRO_UPGRADE_URL`.
	 */
	const UPGRADE_URL = 'https://www.linknacional.com.br/wordpress/woocommerce/calculadora-de-frete/';

	/**
	 * Constante definida pelo plugin PRO quando ele está ativo.
	 */
	const PRO_VERSION_CONSTANT = 'SHIPPING_SIMULATOR_FOR_WOOCOMMERCE_PRO_VERSION';

	/**
	 * A versão PRO está ativa neste site?
	 *
	 * O filtro existe para permitir cenários de teste (ou de PRO embutida em
	 * outro plugin).
	 *
	 * @return bool
	 */
	public static function is_pro_active () {
		$active = defined( self::PRO_VERSION_CONSTANT );

		return (bool) apply_filters( 'wc_shipping_simulator_is_pro_active', $active );
	}

	/**
	 * URL da página comercial da versão PRO.
	 *
	 * @return string
	 */
	public static function upgrade_url () {
		$url = self::UPGRADE_URL;

		if ( defined( 'WC_SHIPPING_SIMULATOR_PRO_UPGRADE_URL' ) ) {
			$url = (string) constant( 'WC_SHIPPING_SIMULATOR_PRO_UPGRADE_URL' );
		}

		return (string) apply_filters( 'wc_shipping_simulator_pro_upgrade_url', $url );
	}

	/**
	 * A definição (campo ou bloco) é exclusiva da versão PRO?
	 *
	 * @param array $definition Campo ou bloco (type `title`).
	 * @return bool
	 */
	public static function is_pro_definition ( $definition ) {
		return is_array( $definition ) && ! empty( $definition['pro'] );
	}

	/**
	 * Deve exibir a pílula `PRO` para esta definição?
	 *
	 * A pílula só faz sentido enquanto o recurso está bloqueado (PRO inativa).
	 *
	 * @param array $definition Campo ou bloco.
	 * @return bool
	 */
	public static function should_render_pill ( $definition ) {
		return self::is_pro_definition( $definition ) && ! self::is_pro_active();
	}

	/**
	 * Ajusta a lista de campos antes de salvar.
	 *
	 * Com a PRO ativa, devolve tudo (o salvamento nativo do WooCommerce cuida do
	 * resto). Com a PRO inativa, remove os campos PRO da lista (o que o usuário
	 * digitou é descartado) e regrava o `default` de cada um deles.
	 *
	 * @param array $settings Campos da aba.
	 * @return array Campos que realmente serão salvos.
	 */
	public static function guard_settings ( $settings ) {
		if ( ! is_array( $settings ) || self::is_pro_active() ) {
			return $settings;
		}

		$allowed = array();

		foreach ( $settings as $key => $field ) {
			if ( ! self::is_pro_definition( $field ) ) {
				$allowed[ $key ] = $field;
				continue;
			}

			self::restore_default( $field );
		}

		return $allowed;
	}

	/**
	 * Regrava o `default` declarado de um campo PRO.
	 *
	 * @param array $field Definição do campo.
	 * @return void
	 */
	private static function restore_default ( $field ) {
		$option_id = isset( $field['id'] ) ? (string) $field['id'] : '';

		// Campos somente de leitura (`is_option => false`) não são opções.
		if ( '' === $option_id || ( isset( $field['is_option'] ) && false === $field['is_option'] ) ) {
			return;
		}

		$default = array_key_exists( 'default', $field ) ? $field['default'] : '';

		\update_option( $option_id, $default );
	}
}
