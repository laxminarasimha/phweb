<?php
/*
Plugin Name: SocialFeeds
Plugin URI: https://socialfeeds.org
Description: YouTube feeds for WordPress with simple Setup and Settings options.
Version: 1.0.9
Author: Softaculous Team
Author URI: https://softaculous.com/
Text Domain: socialfeeds
License: GPLv2
*/

if(!defined('ABSPATH')){
	exit;
}

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

//SOCIALFEEDS
define('SOCIALFEEDS_VERSION', '1.0.9');
define('SOCIALFEEDS_FILE', __FILE__);
define('SOCIALFEEDS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOCIALFEEDS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SOCIALFEEDS_ASSETS_URL', SOCIALFEEDS_PLUGIN_URL . 'assets');

function socialfeeds_autoloader($class){
	if(!preg_match('/^SocialFeeds\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	// Include file
	if(file_exists(SOCIALFEEDS_PLUGIN_DIR . 'main/'.strtolower($m[1]).'.php')){
		include_once(SOCIALFEEDS_PLUGIN_DIR.'main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register('socialfeeds_autoloader');
register_activation_hook(SOCIALFEEDS_FILE, '\SocialFeeds\Install::activate');
register_deactivation_hook(SOCIALFEEDS_FILE, '\SocialFeeds\Install::deactivate');
register_uninstall_hook(SOCIALFEEDS_FILE, '\SocialFeeds\Install::uninstall');
add_action('plugins_loaded', 'socialfeeds_load_plugin');

/**
 * Initialize plugin on plugins_loaded hook
 */
function socialfeeds_load_plugin(){
	global $socialfeeds;

	if(empty($socialfeeds)){
		$socialfeeds = new stdClass();
	}

	//load all the options
	$socialfeeds->youtube_settings = get_option('socialfeeds_youtube_option', []);
	\SocialFeeds\Util::maybe_encrypt_stored_api_key();

	if(wp_doing_ajax()){
		\SocialFeeds\Ajax::hooks();
	}

	\SocialFeeds\Shortcodes::init();

	if(is_admin()){
		\SocialFeeds\Admin::init();
		return;
	}

	if(!is_admin()){
		add_action('wp_enqueue_scripts', '\SocialFeeds\Loader::enqueue_frontend_assets');
	}
}




