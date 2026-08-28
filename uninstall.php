<?php
/*
Always check for the constant WP_UNINSTALL_PLUGIN in uninstall.php before doing anything. This protects against direct access.
The constant will be defined by WordPress during the uninstall.php invocation.
The constant is NOT defined when uninstall is performed by `register_uninstall_hook`.
Reference: https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/#method-2-uninstall-php
*/
defined( 'WP_UNINSTALL_PLUGIN' ) || exit( 1 );

global $wpdb;

$wc_shipping_simulator_prefix = 'wc_shipping_simulator_';
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE %s",
        $wc_shipping_simulator_prefix . '%'
    )
);

