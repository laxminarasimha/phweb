<?php

if (!defined('ABSPATH')) exit;

require_once DESKUSS_DIR . '/src/Auth/Bridge.php';

function deskuss_get_current_staff() {
	return \Deskuss\Auth\Bridge::getCurrentStaff();
}

function deskuss_auto_create_staff($wp_user) {
	return \Deskuss\Auth\Bridge::autoCreateStaff($wp_user);
}

function deskuss_sync_staff($staff, $wp_user) {
	return \Deskuss\Auth\Bridge::syncStaff($staff, $wp_user);
}

function deskuss_get_current_client() {
	return \Deskuss\Auth\Bridge::getCurrentClient();
}

function deskuss_auto_create_user($wp_user) {
	return \Deskuss\Auth\Bridge::autoCreateUser($wp_user);
}

function deskuss_sync_user($user, $wp_user) {
	return \Deskuss\Auth\Bridge::syncUser($user, $wp_user);
}

function deskuss_can_access_client_area() {
	return \Deskuss\Auth\Bridge::canAccessClientArea();
}

function deskuss_can_access_staff_area() {
	return \Deskuss\Auth\Bridge::canAccessStaffArea();
}

function deskuss_client_login_redirect($redirect_url = '') {
	\Deskuss\Auth\Bridge::clientLoginRedirect($redirect_url);
}

function deskuss_staff_login_redirect($redirect_url = '') {
	\Deskuss\Auth\Bridge::staffLoginRedirect($redirect_url);
}

function deskuss_logout() {
	\Deskuss\Auth\Bridge::logout();
}

function deskuss_handle_login_post() {
	return \Deskuss\Auth\Bridge::handleLoginPost();
}

function deskuss_handle_register_post() {
	return \Deskuss\Auth\Bridge::handleRegisterPost();
}