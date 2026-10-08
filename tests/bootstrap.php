<?php
/**
 * Bootstrap dos testes integrados (wp-phpunit) do
 * Shipping Simulator for WooCommerce.
 *
 * Fluxo:
 *   1. autoload do plugin
 *   2. wp-tests-config.php (constantes de DB + ABSPATH → WordPress do Local WP)
 *   3. `muplugins_loaded` → carrega o WooCommerce (sibling) e o nosso plugin
 *      ANTES do `init`, para que todos os hooks (ex.: integrações) registrem.
 *   4. bootstrap do wp-phpunit (instala o banco de teste e carrega o WP)
 *
 * REGRA DE OURO: o WooCommerce DEVE ser carregado ANTES do nosso plugin.
 *
 * @package Shipping_Simulator\Tests
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// 1. Arquivo de configuração dos testes.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php' );
}

// 2. Polyfills de PHPUnit (Yoast).
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define(
		'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
		dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php'
	);
}

$wc_main_file     = dirname( __DIR__, 2 ) . '/woocommerce/woocommerce.php';
$plugin_main_file = dirname( __DIR__ ) . '/main.php';

/*
 * Opções pré-definidas antes do `init`. A integração `Integration\Brazil` só
 * ativa em loja com moeda BRL, e as integrações são registradas durante o
 * `init` — por isso a moeda precisa estar definida antes do bootstrap carregar
 * o WordPress.
 */
$GLOBALS['wp_tests_options'] = array_merge(
	isset( $GLOBALS['wp_tests_options'] ) ? $GLOBALS['wp_tests_options'] : [],
	[
		'woocommerce_currency'              => 'BRL',
		'woocommerce_ship_to_countries'     => '',
		'woocommerce_default_country'       => 'BR:SP',
		'woocommerce_calc_taxes'            => 'no',
	]
);

// functions.php define `tests_add_filter()`, que permite plugar código antes do
// carregamento do WordPress.
require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () use ( $wc_main_file, $plugin_main_file ) {
		if ( ! file_exists( $wc_main_file ) ) {
			fwrite(
				STDERR,
				sprintf( '[Tests Bootstrap] ERRO: WooCommerce não encontrado em: %s' . PHP_EOL, $wc_main_file )
			);
			return;
		}

		// WooCommerce primeiro.
		require_once $wc_main_file;

		// Cria as tabelas do WooCommerce (o install do WP só cria as tabelas do
		// core). Sem isso, o `init` do WooCommerce dispara erros de tabela.
		// Usamos apenas `create_tables()` (não `install()`, que depende de
		// funções pluggable ainda não carregadas neste ponto).
		if ( class_exists( '\WC_Install' ) ) {
			\WC_Install::create_tables();
		}

		// Depois nosso plugin.
		require_once $plugin_main_file;
	}
);

require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
