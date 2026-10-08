<?php

namespace Deskuss\Core;

class Router
{
    private static $staticExtensions = array(
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'pdf', 'map', 'json', 'xml', 'txt',
        'html', 'htm', 'otf', 'webp', 'bmp', 'tiff', 'tif'
    );

    private static $mimeTypes = array(
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'bmp'   => 'image/bmp',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'eot'   => 'application/vnd.ms-fontobject',
        'pdf'   => 'application/pdf',
        'map'   => 'application/json',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'txt'   => 'text/plain',
    );

    public static function parseRequest($wp)
    {
        if (!isset($wp->query_vars['deskuss_route'])) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            // Strip query string before processing
            $uri_path = strtok($uri, '?');
            $home_path = rtrim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
            $path = $uri_path;
            if ($home_path && strpos($uri_path, $home_path) === 0) {
                $path = substr($uri_path, strlen($home_path));
            }
            $path = ltrim($path, '/');

            $slug = get_option('deskuss_url_slug', 'deskuss');
            if (strpos($path, $slug . '/') === 0) {
                $wp->query_vars['deskuss_route'] = substr($path, strlen($slug) + 1);
            } elseif ($path === $slug) {
                $wp->query_vars['deskuss_route'] = '';
            } else {
                return;
            }
        }

        $route = $wp->query_vars['deskuss_route'];
        $public_html = DESKUSS_DIR . '/packages';
        $assets_dir = DESKUSS_DIR . '/assets';
        $site_url = home_url();
        $site_url_path = rtrim(parse_url($site_url, PHP_URL_PATH) ?? '', '/');
        if (empty($site_url_path)) {
            $site_url_path = '';
        }

        $ext = strtolower(pathinfo($route, PATHINFO_EXTENSION));
        // Strip any query string for the file check
        $route_path = strtok($route, '?');

        // Reject path traversal in the static-asset branch (was missing — only
        // resolveRoute() had the '..' guard, and the static branch runs first).
        if (strpos($route_path, '..') !== false) {
            return;
        }

        if (strpos($route, 'assets/') === 0 && in_array($ext, self::$staticExtensions)) {
            // Strip leading 'assets/' from the route since $assets_dir already points to the assets directory
            $asset_route = $route_path;
            if (strpos($asset_route, 'assets/') === 0) {
                $asset_route = substr($asset_route, 7);
            }
            $real_assets = realpath($assets_dir);
            $real_file = realpath($assets_dir . '/' . $asset_route);
            if ($real_file === false || $real_assets === false) {
                return;
            }
            // Ensure the resolved file is strictly inside the assets directory.
            if (strpos($real_file, $real_assets . DIRECTORY_SEPARATOR) !== 0) {
                return;
            }
            self::serveStaticFile($real_file, $ext);
            exit;
        }

        $file = self::resolveRoute($route, $public_html);
        if ($file === false) {
            return;
        }

        // Defense in depth: confirm the resolved PHP file is strictly inside
        // the packages directory (guards against symlinks / future route bugs).
        $real_public = realpath($public_html);
        $real_file = realpath($file);
        if ($real_file === false || $real_public === false
            || strpos($real_file, $real_public . DIRECTORY_SEPARATOR) !== 0
        ) {
            return;
        }
        $file = $real_file;

        $path_info = self::computePathInfo($route);

        if (!defined('DESKUSS_ROOT_PATH')) {
            $home_path = rtrim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
            $slug = get_option('deskuss_url_slug', 'deskuss');
            define('DESKUSS_ROOT_PATH', ($home_path ? $home_path : '') . '/' . $slug . '/');
        }

        $slug = get_option('deskuss_url_slug', 'deskuss');
        $_SERVER['SCRIPT_NAME'] = $site_url_path . '/' . $slug . '/' . $route;
        $_SERVER['SCRIPT_FILENAME'] = $file;

        if ($path_info !== null) {
            $_SERVER['PATH_INFO'] = $path_info;
            $_SERVER['ORIG_PATH_INFO'] = $path_info;
        }

        chdir(dirname($file));
        include $file;
        exit;
    }

    public static function resolveRoute($route, $public_html)
    {
if (strpos($route, '..') !== false) {
            return;
        }

        if ($route === '' || $route === '/') {
            $this->includeIndex($public_html);
        }

        $direct = $public_html . '/' . $route;
        if (is_file($direct) && pathinfo($direct, PATHINFO_EXTENSION) === 'php') {
            return $direct;
        }

        if (is_file($direct)) {
            return $direct;
        }

        if (preg_match('#^api(/.*)?$#', $route)) {
            return $public_html . '/api/http.php';
        }

        if (preg_match('#^apps(/.*)?$#', $route)) {
            return $public_html . '/apps/dispatcher.php';
        }

        if (preg_match('#^pages(/.*)?$#', $route)) {
            return $public_html . '/pages/index.php';
        }

        if (preg_match('#^admin/apps(/.*)?$#', $route)) {
            return $public_html . '/admin/apps/dispatcher.php';
        }

        if ($route === 'admin' || $route === 'admin/') {
            return $public_html . '/admin/index.php';
        }

        if (preg_match('#^admin/([a-zA-Z0-9_-]+?)/?$#', $route, $m)) {
            $admin_file = $public_html . '/admin/' . $m[1] . '.php';
            if (is_file($admin_file)) {
                return $admin_file;
            }
        }

        // admin/<file>.php/<sub-route> — PHP dispatcher with PATH_INFO.
        // Examples: admin/ajax.php/tickets/55/canned-resp/1.json,
        //           admin/ajax.php/forms/manage, admin/heartbeat.php
        if (preg_match('#^admin/([a-zA-Z0-9_.-]+\.php)(/.*)?$#', $route, $m)) {
            $admin_file = $public_html . '/admin/' . $m[1];
            if (is_file($admin_file)) {
                return $admin_file;
            }
        }

        if (is_dir($public_html . '/' . $route) && is_file($public_html . '/' . rtrim($route, '/') . '/index.php')) {
            return $public_html . '/' . rtrim($route, '/') . '/index.php';
        }

        $parts = explode('/', $route);
        for ($i = 1; $i <= count($parts); $i++) {
            $candidate = implode('/', array_slice($parts, 0, $i));
            $candidate_file = $public_html . '/' . $candidate;
            if (is_file($candidate_file) && pathinfo($candidate_file, PATHINFO_EXTENSION) === 'php') {
                return $candidate_file;
            }
        }

        return false;
    }

    public static function computePathInfo($route)
    {
        if (preg_match('#^api(/.*)$#', $route, $m)) {
            return $m[1];
        }
        if (preg_match('#^apps(/.*)$#', $route, $m)) {
            return $m[1];
        }
        if (preg_match('#^pages(/.*)$#', $route, $m)) {
            return $m[1];
        }
        if (preg_match('#^admin/apps(/.*)$#', $route, $m)) {
            return $m[1];
        }

        $parts = explode('/', $route);
        for ($i = 1; $i < count($parts); $i++) {
            $candidate = implode('/', array_slice($parts, 0, $i));
            if (preg_match('/\.php$/', $candidate)) {
                return '/' . implode('/', array_slice($parts, $i));
            }
        }

        return null;
    }

    public static function serveStaticFile($file, $ext)
    {
        $content_type = isset(self::$mimeTypes[$ext]) ? self::$mimeTypes[$ext] : 'application/octet-stream';
        $last_modified = filemtime($file);
        $etag = md5($file . $last_modified);

        header('Content-Type: ' . $content_type);
        header('ETag: "' . $etag . '"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
        header('Cache-Control: public, max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        if (
            (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag)
        ) {
            header('HTTP/1.1 304 Not Modified');
            return;
        }

        readfile($file);
    }

    public static function addRewriteRules()
    {
        $slug = get_option('deskuss_url_slug', 'deskuss');
        add_rewrite_rule('^' . preg_quote($slug, '/') . '/(.*)?$', 'index.php?deskuss_route=$matches[1]', 'top');
    }

    public static function registerQueryVars($vars)
    {
        $vars[] = 'deskuss_route';
        return $vars;
    }
}
