<?php

if (!defined('ABSPATH')) exit;

require_once DESKUSS_DIR . '/src/Auth/Bridge.php';
require_once DESKUSS_DIR . '/src/Auth/WordPressStaffAuth.php';
require_once DESKUSS_DIR . '/src/Auth/WordPressClientAuth.php';

\StaffAuthenticationBackend::register('Deskuss\\Auth\\WordPressStaffAuth');
\UserAuthenticationBackend::register('Deskuss\\Auth\\WordPressClientAuth');