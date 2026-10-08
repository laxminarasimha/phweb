<?php
/*
Plugin Name: SocialFeeds pro
Description: Advanced YouTube and Instagram, Facebook, Google Reviews feeds for WordPress with easy Get Started and Settings options.
Version: 1.0.9
Author: Softaculous
Author URI: https://softaculous.com/
Text Domain: socialfeeds-pro
*/

if(!defined('ABSPATH')){
	exit;
}

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

//SOCIALFEEDS
define('SOCIALFEEDS_PRO_VERSION', '1.0.9');
define('SOCIALFEEDS_PRO_FILE', __FILE__);
define('SOCIALFEEDS_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOCIALFEEDS_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SOCIALFEEDS_API', 'https://a.softaculous.com/socialfeeds');

include_once SOCIALFEEDS_PRO_PLUGIN_DIR . 'functions.php';

function socialfeeds_pro_autoloader($class){
	if(!preg_match('/^SocialFeedsPro\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	// Include file
	if(file_exists(SOCIALFEEDS_PRO_PLUGIN_DIR . 'main/'.strtolower($m[1]).'.php')){
		include_once(SOCIALFEEDS_PRO_PLUGIN_DIR.'main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register('socialfeeds_pro_autoloader');

$_tmp_plugins = get_option('active_plugins', []);
$free_plugin_file = 'socialfeeds/socialfeeds.php';
$free_plugin_installed = file_exists(WP_PLUGIN_DIR . '/socialfeeds/socialfeeds.php');
$_sc_version = get_option('socialfeeds_version');

// Only load upgrader if free plugin is NOT installed
if(!$free_plugin_installed || (empty($_sc_version) && !in_array($free_plugin_file, $_tmp_plugins) && !socialfeeds_pro_is_network_active('socialfeeds'))){
    
    include_once(SOCIALFEEDS_PRO_PLUGIN_DIR .'/upgrader.php');
	return;
}

register_activation_hook(SOCIALFEEDS_PRO_FILE, '\SocialFeedsPro\Install::activate');
register_deactivation_hook(SOCIALFEEDS_PRO_FILE, '\SocialFeedsPro\Install::deactivate');
register_uninstall_hook(SOCIALFEEDS_PRO_FILE, '\SocialFeedsPro\Install::uninstall');
add_action('plugins_loaded', 'socialfeeds_pro_load_plugin');

// Prevent update of socialfeeds free
// This also work for auto update
add_filter('site_transient_update_plugins', 'socialfeeds_pro_disable_manual_update_for_plugin', 99);
add_filter('pre_site_transient_update_plugins', 'socialfeeds_pro_disable_manual_update_for_plugin', 99);

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'socialfeeds_pro_update_free_after_pro', 20, 2);

/**
 * Initialize plugin on plugins_loaded hook
 */
function socialfeeds_pro_load_plugin(){
	global $socialfeeds;

	if(empty($socialfeeds)){
		$socialfeeds = new stdClass();
	}

	socialfeeds_pro_load_license();

	socialfeeds_check_updates();

	include_once(SOCIALFEEDS_PRO_PLUGIN_DIR . 'main/plugin-update-checker.php');
	$socialfeeds_updater = SocialFeeds_PucFactory::buildUpdateChecker(socialfeeds_pro_api_url().'/updates.php?version='.SOCIALFEEDS_PRO_VERSION, SOCIALFEEDS_PRO_FILE);

	// Add the license key to query arguments
	$socialfeeds_updater->addQueryArgFilter('socialfeeds_pro_updater_filter_args');

	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-socialfeeds-pro', 'socialfeeds_pro_updater_check_link', 10, 1);

	if(wp_doing_ajax()){
		\SocialFeedsPro\Ajax::hooks();
	}

	\SocialFeedsPro\YouTube::init();
	\SocialFeedsPro\Facebook::init();
	\SocialFeedsPro\Blocks::init();

	add_action('socialfeeds_pro_instagram_token_refresh', '\SocialFeedsPro\InstagramSettings::scheduled_token_refresh');
	add_action('socialfeeds_pro_facebook_token_refresh', '\SocialFeedsPro\Facebook::scheduled_token_refresh');

	if(is_admin()){
		\SocialFeedsPro\Admin::init();
		return;
	}

	if(!is_admin()){
		add_filter('socialfeeds_allowed_click_actions', '\SocialFeedsPro\Loader::youtube_play_actions');
		add_action('wp_enqueue_scripts', '\SocialFeedsPro\Loader::enqueue_frontend');
	}
}

function socialfeeds_check_updates(){

	$current_version = get_option('socialfeeds_pro_version');
	$version = (int) str_replace('.', '', $current_version);

	// Is it first run ?
	if(empty($current_version)){
		\SocialFeedsPro\Install::activate();
		return;
	}

	// Till 1.0.3 we used to update free using the Pro version so we need to remove the scheduler
	if(wp_next_scheduled('check_plugin_updates-socialfeeds')){
		wp_clear_scheduled_hook('check_plugin_updates-socialfeeds');
	}

	update_option('socialfeeds_pro_version', SOCIALFEEDS_PRO_VERSION);
}


