<?php

namespace Deskuss\Core;

class Bootstrap
{
    public static function init()
    {
        if (!defined('DESKUSS_ROOT_PATH')) {
            $home_path = rtrim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
            $slug = get_option('deskuss_url_slug', 'deskuss');
            define('DESKUSS_ROOT_PATH', ($home_path ? $home_path : '') . '/' . $slug . '/');
        }
    }

    public static function setupServerEnv($route, $file, $path_info = null)
    {
        $site_url = home_url();
        $site_url_path = rtrim(parse_url($site_url, PHP_URL_PATH) ?? '', '/');
        if (empty($site_url_path)) {
            $site_url_path = '';
        }

        $slug = get_option('deskuss_url_slug', 'deskuss');
        $_SERVER['SCRIPT_NAME'] = $site_url_path . '/' . $slug . '/' . $route;
        $_SERVER['SCRIPT_FILENAME'] = $file;

        if ($path_info !== null) {
            $_SERVER['PATH_INFO'] = $path_info;
            $_SERVER['ORIG_PATH_INFO'] = $path_info;
        }
    }
}