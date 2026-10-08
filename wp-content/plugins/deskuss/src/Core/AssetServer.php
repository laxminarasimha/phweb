<?php

namespace Deskuss\Core;

class AssetServer
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

    public static function serve($route)
    {
        $assets_dir = DESKUSS_DIR . '/assets';
        $ext = strtolower(pathinfo($route, PATHINFO_EXTENSION));

        if (!in_array($ext, self::$staticExtensions)) {
            return false;
        }

        $file = $assets_dir . '/' . $route;
        if (!is_file($file)) {
            return false;
        }

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
            return true;
        }

        readfile($file);
        return true;
    }
}