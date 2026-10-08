<?php
/**
 * AJAX.
 *
 * @package deskuss
 */

// AJAX handler to dismiss the expired license notice
add_action('wp_ajax_deskuss_dismiss_expired_licenses', 'deskuss_dismiss_expired_licenses_ajax');
function deskuss_dismiss_expired_licenses_ajax(){
	check_ajax_referer('deskuss_expiry_notice', 'security');
	
	if(!current_user_can('activate_plugins')){
		wp_send_json_error(__('Insufficient permissions.', 'deskuss'));
	}
	
	update_option('softaculous_expired_licenses', time());
	wp_send_json_success();
}