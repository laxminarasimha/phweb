<?php
/*
Plugin Name: ShareMyPost Pro
Description: Pro version of ShareMyPost with advanced sharing, tracking, and premium social integrations.
Version: 1.0.7
Author: Softaculous Team
Author URI: https://softaculous.com/
Text Domain: sharemypost-pro
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if(!defined('ABSPATH')){
    exit;
}

if(!function_exists('add_action')){
    echo 'You are not allowed to access this page directly.';
    exit;
}

// SHAREMYPOST Constants
define('SHAREMYPOST_PRO_VERSION', '1.0.7');
define('SHAREMYPOST_PRO_FILE', __FILE__);
define('SHAREMYPOST_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHAREMYPOST_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SHAREMYPOST_API', 'https://a.softaculous.com/sharemypost');

include_once SHAREMYPOST_PRO_PLUGIN_DIR . 'functions.php';

function sharemypost_pro_autoloader($class) {
    if(!preg_match('/^ShareMyPostPro\\\(.*)/is', $class, $m)){
        return;
    }

    $m[1] = str_replace('\\', '/', $m[1]);
    
    if(file_exists(SHAREMYPOST_PRO_PLUGIN_DIR . 'main/' . strtolower($m[1]) . '.php')) {
        include_once(SHAREMYPOST_PRO_PLUGIN_DIR . 'main/' . strtolower($m[1]) . '.php');
    }
}

spl_autoload_register('sharemypost_pro_autoloader');

$_tmp_plugins = get_option('active_plugins', []);
$free_plugin_file = 'sharemypost/sharemypost.php';
$free_plugin_installed = file_exists(WP_PLUGIN_DIR . '/sharemypost/sharemypost.php');
$_sc_version = get_option('sharemypost_version');

// Only load upgrader if free plugin is NOT installed, or is inactive, or needs an update
if(!(in_array($free_plugin_file, $_tmp_plugins) || sharemypost_pro_is_network_active('sharemypost')) || !file_exists(WP_PLUGIN_DIR . '/sharemypost/sharemypost.php')){
	include_once(SHAREMYPOST_PRO_PLUGIN_DIR .'/upgrader.php');
	return;
}

register_activation_hook(SHAREMYPOST_PRO_FILE, '\ShareMyPostPro\Install::activate');
register_deactivation_hook(SHAREMYPOST_PRO_FILE, '\ShareMyPostPro\Install::deactivate');
register_uninstall_hook(SHAREMYPOST_PRO_FILE, '\ShareMyPostPro\Install::uninstall');
add_action('plugins_loaded', 'sharemypost_pro_plugins_loaded', 20);

add_filter('site_transient_update_plugins', 'sharemypost_pro_disable_manual_update_for_plugin', 99);
add_filter('pre_site_transient_update_plugins', 'sharemypost_pro_disable_manual_update_for_plugin', 99);

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'sharemypost_pro_update_free_after_pro', 20, 2);

/**
 * Initialize plugin on plugins_loaded hook
 */
function sharemypost_pro_plugins_loaded() {
    global $sharemypost;

    if (empty($sharemypost)) {
        $sharemypost = new stdClass();
    }

    sharemypost_pro_load_license();

    sharemypost_pro_check_updates();

    include_once(SHAREMYPOST_PRO_PLUGIN_DIR . 'main/plugin-update-checker.php');
    $sharemypost_updater = ShareMyPost_PucFactory::buildUpdateChecker(sharemypost_pro_api_url() . '/updates.php?version=' . SHAREMYPOST_PRO_VERSION, SHAREMYPOST_PRO_FILE);

    // Add the license key to query arguments
    $sharemypost_updater->addQueryArgFilter('sharemypost_updater_filter_args');

    // Show the text to install the license key
    add_filter('puc_manual_final_check_link-sharemypost-pro', 'sharemypost_pro_updater_check_link', 10, 1);

	add_filter('sharemypost_networks', '\ShareMyPostPro\CustomNetworks::add_to_networks_filter');
	add_filter('sharemypost_networks', '\ShareMyPostPro\Shortcode::register_pro_networks');

	add_action('init', '\ShareMyPostPro\Loader::init');

	if(wp_doing_ajax()){
		\ShareMyPostPro\Ajax::hooks();
		return;
	}

	if (is_admin()) {
		add_action('init', '\ShareMyPostPro\Admin::init');
		add_action('init', '\ShareMyPostPro\Shortcode::init');
		add_action('init', '\ShareMyPostPro\CustomNetworks::init');
		return;
    }

    add_action('wp_enqueue_scripts', '\ShareMyPostPro\Loader::enqueue_frontend');
    add_action('wp_enqueue_scripts', '\ShareMyPostPro\Shortcode::load_ga4_tracking');
    add_action('wp_footer', '\ShareMyPostPro\Loader::render_floating_bar');
    add_action('wp_footer', '\ShareMyPostPro\Loader::render_universal_modal');
    add_shortcode('sharemypost_click_to_x','\ShareMyPostPro\Shortcode::render_click_to_tweet');
    add_action('init', '\ShareMyPostPro\Shortcode::init');
}

function sharemypost_pro_check_updates() {
    $current_version = get_option('sharemypost_pro_version');
    $version = (int) str_replace('.', '', $current_version);

    if(empty($current_version)) {
        \ShareMyPostPro\Install::activate();
        return;
    }

    update_option('sharemypost_pro_version', SHAREMYPOST_PRO_VERSION);
}