<?php

namespace Deskuss\Core;

class Constants
{
    public static function register()
    {
        if (!defined('DESKUSS_VIEWS_DIR')) {
            define('DESKUSS_VIEWS_DIR', DESKUSS_DIR . '/views/');
        }
        if (!defined('DESKUSS_CONFIG_DIR')) {
            define('DESKUSS_CONFIG_DIR', DESKUSS_DIR . '/config/');
        }
        if (!defined('DESKUSS_DB_DIR')) {
            define('DESKUSS_DB_DIR', DESKUSS_DIR . '/db/');
        }
        if (!defined('STAFFINC_DIR')) {
            define('STAFFINC_DIR', DESKUSS_VIEWS_DIR . 'staff/');
        }
        if (!defined('CLIENTINC_DIR')) {
            define('CLIENTINC_DIR', DESKUSS_VIEWS_DIR . 'client/');
        }
    }
}