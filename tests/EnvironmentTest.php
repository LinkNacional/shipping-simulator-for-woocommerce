<?php
/**
 * Teste de fumaça — confirma que o ambiente de testes integrados está de pé.
 *
 * @package Shipping_Simulator\Tests
 */

namespace Shipping_Simulator\Tests;

use WP_UnitTestCase;

class EnvironmentTest extends WP_UnitTestCase {

	public function test_wordpress_is_loaded() {
		$this->assertTrue( function_exists( 'wp' ) );
		$this->assertTrue( function_exists( 'add_filter' ) );
		$this->assertTrue( defined( 'ABSPATH' ) );
	}

	public function test_woocommerce_is_loaded() {
		$this->assertTrue( class_exists( 'WooCommerce' ) );
		$this->assertNotNull( WC() );
	}

	public function test_plugin_classes_are_loaded() {
		$this->assertTrue( class_exists( \Shipping_Simulator\Calculadora_Api::class ) );
		$this->assertTrue( class_exists( \Shipping_Simulator\Integration\Brazil::class ) );
		$this->assertTrue( class_exists( \Shipping_Simulator\Integration\Autofill_Brazilian_Addresses::class ) );
	}

	/**
	 * As integrações do simulador legado só funcionam se registrarem seus
	 * filtros durante o `init`. Este teste garante que o bootstrap carrega o
	 * plugin a tempo (via `muplugins_loaded`).
	 */
	public function test_brazil_integration_filters_are_registered() {
		$this->assertNotFalse( has_filter( 'wc_shipping_simulator_request_update_package' ) );
		$this->assertNotFalse( has_filter( 'wc_shipping_simulator_package_data', [ \Shipping_Simulator\Integration\Brazil::instance(), 'sync_customer_shipping_address' ] ) );
	}
}
