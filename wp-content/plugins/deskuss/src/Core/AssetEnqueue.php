<?php

namespace Deskuss\Core;

class AssetEnqueue
{
    private static $scripts = array();
    private static $styles = array();
    private static $enqueued = false;

    public static function addScript($handle, $src, $deps = array(), $ver = null, $inFooter = true)
    {
        self::$scripts[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver ?: DESKUSS_VERSION,
            'inFooter' => $inFooter,
        );
    }

    public static function addStyle($handle, $src, $deps = array(), $ver = null, $media = 'all')
    {
        self::$styles[$handle] = array(
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver ?: DESKUSS_VERSION,
            'media' => $media,
        );
    }

    public static function enqueueAll()
    {
        if (self::$enqueued) {
            return;
        }
        self::$enqueued = true;

        foreach (self::$styles as $handle => $args) {
            wp_enqueue_style($handle, $args['src'], $args['deps'], $args['ver'], $args['media']);
        }

        foreach (self::$scripts as $handle => $args) {
            wp_enqueue_script($handle, $args['src'], $args['deps'], $args['ver'], $args['inFooter']);
        }
    }

    public static function jsUrl()
    {
        $args = func_get_args();
        $js = implode(',', $args);
        return DESKUSS_ROOT_PATH . 'themes/default/js/givejs.php?give=' . $js . '&' . THIS_VERSION;
    }

    public static function cssUrl()
    {
        $args = func_get_args();
        $css = implode(',', $args);
        return DESKUSS_ROOT_PATH . 'themes/default/css/givecss.php?give=' . $css . '&' . THIS_VERSION;
    }
}