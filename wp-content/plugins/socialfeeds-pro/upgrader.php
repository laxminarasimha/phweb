<?php

// Are we being accessed directly ?
if(!defined('SOCIALFEEDS_PRO_VERSION')){
	exit('Hacking Attempt !');
}

add_action('plugins_loaded', 'social_feeds_pro_load_plugin');

function social_feeds_pro_load_plugin(){
	global $socialfeeds;

	if(empty($socialfeeds)){
		$socialfeeds = new stdClass();
	}

	// Load license
	socialfeeds_pro_load_license();

	socialfeeds_check_updates();

	// Check for updates
	include_once(SOCIALFEEDS_PRO_PLUGIN_DIR.'/main/plugin-update-checker.php');
	$socialfeeds_updater = SocialFeeds_PucFactory::buildUpdateChecker(socialfeeds_pro_api_url().'updates.php?version='.SOCIALFEEDS_PRO_VERSION, SOCIALFEEDS_PRO_FILE);

	// Add the license key to query arguments
	$socialfeeds_updater->addQueryArgFilter('socialfeeds_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-socialfeeds-pro', 'socialfeeds_pro_updater_check_link', 10, 1);
	
	// Nag informing the user to install the free version.
	if(current_user_can('activate_plugins')){
		add_action('admin_notices', 'socialfeeds_pro_free_version_nag', 9);
		add_action('admin_menu', 'socialfeeds_pro_add_menu', 9);
	}

	$is_network_wide = socialfeeds_pro_is_network_active('socialfeeds-pro');
	$_do_version = get_option('socialfeeds_version');
	$req_free_update = !empty($_do_version) && version_compare($_do_version, '1.0.7', '<'); 

	if($is_network_wide){
		$free_installed = get_site_option('socialfeeds_free_installed');
	} else{
		$free_installed = get_option('socialfeeds_free_installed');
	}
	
	if(!empty($free_installed)){
		return;
	}
	
	// Include the necessary stuff
	include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	include_once(ABSPATH . 'wp-admin/includes/file.php');
	
	if(file_exists(WP_PLUGIN_DIR . '/socialfeeds/socialfeeds.php') && is_plugin_inactive('socialfeeds/socialfeeds.php') && empty($req_free_update)) {

		if($is_network_wide){
			update_site_option('socialfeeds_free_installed', time());
		}else{
			update_option('socialfeeds_free_installed', time());
		}

		return;
	}
	
	// Includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
	include_once(ABSPATH . 'wp-admin/includes/misc.php');
	include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

	// Filter to prevent the activate text
	add_filter('install_plugin_complete_actions', 'socialfeeds_pro_prevent_activation_text', 10, 3);

	$upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
	
	// Upgrade the plugin to the latest version of free already installed.
	if(!empty($req_free_update)){
		$installed = $upgrader->upgrade('socialfeeds/socialfeeds.php');
	}else{
		$installed = $upgrader->install('https://downloads.wordpress.org/plugin/socialfeeds.zip');
	}
	
	if(!is_wp_error($installed) && $installed){
		
		if($is_network_wide){
			update_site_option('socialfeeds_free_installed', time());
		}else{
			update_option('socialfeeds_free_installed', time());
		}
		
		activate_plugin('socialfeeds/socialfeeds.php', '', $is_network_wide);
		remove_action('admin_notices', 'socialfeeds_pro_free_version_nag', 9);
		remove_action('admin_menu', 'socialfeeds_pro_add_menu', 9);
	}
}

// Do not shows the activation text if 
function socialfeeds_pro_prevent_activation_text($install_actions, $api, $plugin_file){
	if($plugin_file == 'socialfeeds/socialfeeds.php'){
		return array();
	}

	return $install_actions;
}

function socialfeeds_pro_free_version_nag(){

	if(file_exists(WP_PLUGIN_DIR . '/socialfeeds/socialfeeds.php')){
		$message = __('SocialFeeds Free version is installed but not active. SocialFeeds Pro depends on the free version, so you must activate it first in order to use SocialFeeds Pro.');
		$button_text = __('Go to Plugins', 'socialfeeds-pro');
		$button_url = admin_url('plugins.php');
	} else {
		$message = __('You have not installed the free version of SocialFeeds. SocialFeeds Pro depends on the free version, so you must install it first in order to use SocialFeeds Pro.');
		$button_text = __('Install Now', 'socialfeeds-pro');
		$button_url = admin_url('plugin-install.php?s=socialfeeds&tab=search');
	}

	echo '<div class="notice notice-error">
		<p style="font-size:16px;">'.esc_html($message).' <a href="'.esc_url($button_url).'" class="button button-primary">'.esc_html($button_text).'</a></p>
	</div>';
}

function socialfeeds_pro_add_menu(){
	add_menu_page('SocialFeeds Settings', 'SocialFeeds', 'activate_plugins', 'socialfeeds', 'socialfeeds_pro_menu_page');
}

function socialfeeds_pro_menu_page(){
	$free_installed = file_exists(WP_PLUGIN_DIR . '/socialfeeds/socialfeeds.php');
	echo '<div style="color: #333;padding: 50px;text-align: center;">
		<h1 style="font-size: 2em;margin-bottom: 10px;">'. ($free_installed ? esc_html__('SocialFeeds Free version is not active!', 'socialfeeds-pro') : esc_html__('SocialFeeds Free version is not installed / outdated!', 'socialfeeds-pro')) .'</h1>
		<p style=" font-size: 16px;margin-bottom: 20px; font-weight:400;">'. ($free_installed ? esc_html__('SocialFeeds Pro depends on the free version of SocialFeeds, so you need to activate the free version first.', 'socialfeeds-pro') : esc_html__('SocialFeeds Pro depends on the free version of SocialFeeds, so you need to install / update the free version first.', 'socialfeeds-pro')) .'</p>
		<a href="'. ($free_installed ? admin_url('plugins.php') : admin_url('plugin-install.php?s=socialfeeds&tab=search')) .'" style="text-decoration: none;font-size:16px;">'. ($free_installed ? esc_html__('Go to Plugins', 'socialfeeds-pro') : esc_html__('Install/Update Now', 'socialfeeds-pro')) .'</a>
	</div>';
}
