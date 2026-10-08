<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'client.inc.php';

$suggest_pwreset = $cfg && $cfg->allowPasswordReset();

if (!$cfg || !$cfg->isClientRegistrationEnabled()) {
	Http::redirect('index.php');
}

if ($thisclient && $thisclient->getId() && !$thisclient->isGuest()) {
	Http::redirect('tickets.php');
}

$errors = array();
$nav = new UserNav();
$nav->setActiveNav('status');

$reg_email = '';
$reg_name = '';

$redirect_to = isset($_GET['redirect_to']) ? wp_validate_redirect($_GET['redirect_to'], DESKUSS_ROOT_PATH . 'tickets.php') : DESKUSS_ROOT_PATH . 'tickets.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $reg_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $reg_name = isset($_POST['user_name']) ? sanitize_text_field($_POST['user_name']) : '';
    $passwd1 = $_POST['passwd1'];
    $passwd2 = $_POST['passwd2'];

    $errors = new WP_Error();

    if (empty($reg_email)) {
        $errors->add('empty_email', __('Email address is required.'));
    } elseif (!is_email($reg_email)) {
        $errors->add('invalid_email', __('Please enter a valid email address.'));
    } elseif (email_exists($reg_email)) {
        $errors->add('email_exists', sprintf(__('This email is already registered. Would you like to %1$s sign in %2$s?'),
            '<a href="' . esc_url(DESKUSS_ROOT_PATH . 'login.php?e=' . urlencode($reg_email)) . '" style="color:inherit"><strong>', '</strong></a>'));
    }

    if (empty($reg_name)) {
        $errors->add('empty_name', __('Full name is required.'));
    }

    if (empty($passwd1)) {
        $errors->add('empty_password', __('Password is required.'));
    } elseif (strlen($passwd1) < 6) {
        $errors->add('short_password', __('Password must be at least 6 characters.'));
    } elseif ($passwd1 !== $passwd2) {
        $errors->add('password_mismatch', __('Passwords do not match.'));
    }

    $sanitized_login = sanitize_user($reg_email, true);
    do_action('register_post', $sanitized_login, $reg_email, $errors);
    $_errors = apply_filters('registration_errors', $errors, $sanitized_login, $reg_email);

    if ($_errors->has_errors()) {
        $errors->add('registration_errors', $_errors->get_error_message());
    } else {
        $user_id = wp_create_user($reg_email, $passwd1, $reg_email);

        if (is_wp_error($user_id)) {
			$errors->add('create_account_errors', __('Unable to create account. Please try again.'));
        } else {
            $user = get_user_by('id', $user_id);
            $user->set_role('support_client');

            $name_parts = array_map('trim', explode(' ', $reg_name, 2));
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => isset($name_parts[0]) ? $name_parts[0] : '',
                'last_name' => isset($name_parts[1]) ? $name_parts[1] : '',
                'display_name' => $reg_name,
            ));

            \Deskuss\Auth\Bridge::autoCreateUser($user);

            wp_set_auth_cookie($user_id, true);
            do_action('wp_login', $user->user_login, $user);
            \Deskuss\Auth\Bridge::getCurrentClient();

            wp_redirect($redirect_to);
            exit;
        }
    }
}

require deskuss_load_page('wp-register.php');