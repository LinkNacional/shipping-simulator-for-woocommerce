<?php

namespace Shipping_Simulator;

defined( 'WPINC' ) || exit( 1 );

return [
	Admin\Settings::class,
	Admin\Calculadora_Settings::class,
	Calculadora_Api::class,
	Calculadora_Public::class,
	Calculadora_Frete::class,
	Admin\Plugin_Meta::class,
	Integration\Brazil::instance(),
	Integration\Autofill_Brazilian_Addresses::instance(),
	Integration\Free_Shipping::instance(),
	Integration\Melhor_Envio::instance(),
	Integration\Estimating_Delivery::instance(),
	Shortcode::class,
	Request::class,
	Tweaks::class,
	Logger::class,
	Integrations::class,
	Debug_Box::class
];
