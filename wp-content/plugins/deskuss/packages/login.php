<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('client.inc.php');

if ($thisclient && $thisclient->getId()) {
    Http::redirect('tickets.php');
    exit;
}

$suggest_pwreset = $cfg && $cfg->allowPasswordReset();

$nav = new UserNav();
$nav->setActiveNav('status');

$login_email = isset($_POST['log']) ? sanitize_email($_POST['log']) : '';
$redirect_to = isset($_GET['redirect_to']) ? wp_validate_redirect($_GET['redirect_to'], DESKUSS_ROOT_PATH . 'tickets.php') : DESKUSS_ROOT_PATH . 'tickets.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    do_action('login_init');

    $credentials = array(
        'user_login'    => $login_email,
        'user_password' => $_POST['pwd'],
        'remember'      => !empty($_POST['rememberme']),
    );

    $user = wp_signon($credentials, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message() ?: __('Invalid email or password.');
        do_action('wp_login_failed', $login_email, $user);
    } else {
        do_action('wp_login', $user->user_login, $user);
        \Deskuss\Auth\Bridge::getCurrentClient();
        wp_redirect($redirect_to);
        exit;
    }
}

require deskuss_load_page('wp-login.php');