<?php
/**
 * Bootstrap do módulo de compra dentro do loader do plugin.
 *
 * O `loader.php` instancia classes e chama `__start` no hook de início; o
 * módulo de compra é estático, então esta ponte existe só para entrar nesse
 * fluxo sem virar um objeto com estado.
 *
 * @package Shipping_Simulator\Billing
 */

namespace Shipping_Simulator\Billing;

defined( 'WPINC' ) || exit( 1 );

/**
 * Ponte entre o loader e o módulo estático.
 */
final class Bootstrap {

	/**
	 * Chamado pelo loader no início do plugin.
	 *
	 * @return void
	 */
	public function __start () {
		Billing::boot();
	}
}
