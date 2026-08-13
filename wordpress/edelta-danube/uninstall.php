<?php
/**
 * edelta-danube — uninstall cleanup.
 *
 * @package edelta-danube
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'edelta_danube_options' );

global $wpdb;

// Remove any cached API responses (transients prefixed edelta_danube_).
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_edelta_danube_%',
		'_transient_timeout_edelta_danube_%'
	)
);
