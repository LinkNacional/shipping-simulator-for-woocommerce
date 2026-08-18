<?php

namespace Shipping_Simulator\Admin;

use Shipping_Simulator\Helpers as h;

final class Plugin_Meta {
	public function __start () {
		add_filter( 'plugin_row_meta', [ $this, 'plugin_meta' ], 10, 2 );
	}

	public function plugin_meta ( $plugin_meta, $plugin_file ) {
		if ( plugin_basename( h::config_get( 'FILE' ) ) === $plugin_file ) {
			$forum_url = h::config_get( 'PLUGIN_FORUM' );

			$plugin_meta[] = "<a href=\"$forum_url\" target='blank' rel='noopener'>" . esc_html__( 'Community support', 'shipping-simulator-for-woocommerce' ) .  "</a>";
		}
		return $plugin_meta;
	}
}
