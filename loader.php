<?php

namespace Shipping_Simulator;

defined( 'WPINC' ) || exit( 1 );

return [
	Admin\Settings::class,
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
