<?php
/*
Plugin Name: ShareMyPost
Plugin URI: https://sharemypost.net
Description: A lightweight WordPress social sharing plugin with inline share bars and advanced customization.
Version: 1.0.7
Author: Softaculous Team
Author URI: https://softaculous.com/
Text Domain: sharemypost
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

// SHAREMYPOST
define('SHAREMYPOST_VERSION', '1.0.7');
define('SHAREMYPOST_FILE', __FILE__);
define('SHAREMYPOST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHAREMYPOST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SHAREMYPOST_ASSETS_URL', SHAREMYPOST_PLUGIN_URL . 'assets');

function sharemypost_autoloader($class){
	if(!preg_match('/^ShareMyPost\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	if(file_exists(SHAREMYPOST_PLUGIN_DIR . 'main/'.strtolower($m[1]).'.php')){
		include_once(SHAREMYPOST_PLUGIN_DIR.'main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register('sharemypost_autoloader');
register_activation_hook(SHAREMYPOST_FILE, '\ShareMyPost\Install::activate');
register_deactivation_hook(SHAREMYPOST_FILE, '\ShareMyPost\Install::deactivate');
register_uninstall_hook(SHAREMYPOST_FILE, '\ShareMyPost\Install::uninstall');
add_action('plugins_loaded', 'sharemypost_load_plugin');

/**
 * Initialize plugin on plugins_loaded hook
 */
function sharemypost_load_plugin() {
	global $sharemypost;

	if(empty($sharemypost)){
		$sharemypost = new stdClass();
	}

	if(is_admin()){
		\ShareMyPost\Admin::init();
		return;
	}

	add_action('wp_enqueue_scripts', 'ShareMyPost\Helpers::enqueue_frontend_assets');
	add_filter('the_content', '\ShareMyPost\Helpers::append_sharebar');
}


