<?php

namespace SocialFeeds;

if(!defined('ABSPATH')){
	exit;
}

class Admin{

	static function init(){
		add_action('admin_menu', '\SocialFeeds\Admin::add_admin_menu');
		add_action('admin_enqueue_scripts', '\SocialFeeds\Admin::enqueue_admin_assets');
	}

	static function add_admin_menu(){

		// Register main menu
		add_menu_page('Social Feeds Plugin', 'SocialFeeds', 'manage_options', 'socialfeeds', '\SocialFeeds\Admin::dispatcher', SOCIALFEEDS_ASSETS_URL.'/img/socialfeeds-logo-30.png');
	}

	static function dispatcher(){
		echo '<div class="socialfeeds-wrap">';

		// Render global header
		self::render_header();

		echo '<div class="socialfeeds-main-content">';

		echo '<div id="socialfeeds-dashboard" class="socialfeeds-tab-content active">';
			\SocialFeeds\Settings\UI::dashboard_tab();
		echo '</div>';

		echo '<div id="socialfeeds-youtube" class="socialfeeds-tab-content" style="display:none;">';
			self::render_youtube_page();
		echo '</div>';

		echo '<div id="socialfeeds-instagram" class="socialfeeds-tab-content" style="display:none;">';
			if(defined('SOCIALFEEDS_PRO_VERSION')){
				\SocialFeedsPro\Admin::render_instagram_settings();
			} else {
				self::pro_placeholder_page();
			}
		echo '</div>';
		
		echo '<div id="socialfeeds-facebook" class="socialfeeds-tab-content" style="display:none;">';
			if(defined('SOCIALFEEDS_PRO_VERSION')){
				\SocialFeedsPro\Admin::render_facebook_settings();
			} else {
				self::pro_placeholder_page();
			}
		echo '</div>';

		echo '<div id="socialfeeds-google" class="socialfeeds-tab-content" style="display:none;">';
			if(defined('SOCIALFEEDS_PRO_VERSION')){
				do_action('socialfeeds_render_google_reviews_page');
			} else {
				self::pro_placeholder_page();
			}
		echo '</div>';

		echo '<div id="socialfeeds-feeds" class="socialfeeds-tab-content" style="display:none;">';
			\SocialFeeds\Settings\UI::settings_tab();
		echo '</div>';

		echo '<div id="socialfeeds-support" class="socialfeeds-tab-content" style="display:none;">';
			self::support_page();
		echo '</div>';

		if(defined('SOCIALFEEDS_PRO_VERSION')){
			echo '<div id="socialfeeds-license" class="socialfeeds-tab-content" style="display:none;">';
			do_action('socialfeeds_render_license_page');
			echo '</div>';
		}

		echo '</div>'; // socialfeeds-main-content
		echo '</div>'; // socialfeeds-wrap
	}

	static function render_header(){
		$tabs = [
			'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard'],
			'youtube' => ['label' => 'YouTube', 'icon' => 'youtube'],
			'instagram' => ['label' => 'Instagram', 'icon' => 'instagram', 'pro' => true],
			'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'pro' => true],
			'google' => ['label' => 'Google Reviews', 'icon' => 'google', 'pro' => true],
			'feeds' => ['label' => 'Feeds', 'icon' => 'admin-settings'],
			'support' => ['label' => 'Support', 'icon' => 'sos'],
		];

		if(defined('SOCIALFEEDS_PRO_VERSION')){
			$tabs['license'] = ['label' => 'License', 'icon' => 'admin-network'];
		}

		echo '<div class="socialfeeds-admin-header">
				<div class="socialfeeds-header-left">
					<div class="socialfeeds-logo">
						<img alt="'.esc_html__('Socialfeeds logo', 'socialfeeds').'" height="40" src="'. esc_url(SOCIALFEEDS_ASSETS_URL).'/img/socialfeeds-logo.png'.'" width="70"/>
					</div>
					<nav class="socialfeeds-nav-tabs">';
					foreach($tabs as $slug => $tab){
						$url = '#' . $slug;
						echo '<a '.($url ? 'href="' . esc_url($url) . '"' : '' ).' class="socialfeeds-nav-tab" data-tab="'.esc_attr($slug).'"><span class="dashicons dashicons-'.esc_attr( $tab['icon'] ).'"></span>' . esc_html( $tab['label'] ) . '
							'.( ! defined( 'SOCIALFEEDS_PRO_VERSION' ) && ! empty($tab['pro']) ? ' <span class="socialfeeds-pro-tag">PRO</span>': '') . '</a>';
					}

				echo '</nav>

				</div>
				<div class="socialfeeds-header-right">';
					echo'<div class="socialfeeds-header-icons">
						<span class="socialfeeds-header-version-badge">V'.esc_html(SOCIALFEEDS_VERSION).'</span>
					</div>
				</div>
			</div>';
	}

	static function render_youtube_page(){
		global $socialfeeds;

		$youtube_key = \SocialFeeds\Util::get_youtube_api_key($socialfeeds->youtube_settings);

		if(empty($youtube_key)){
			echo '<div class="socialfeeds-apikey-notice">
				<div class="socialfeeds-apikey-notice-icon">
					<span class="dashicons dashicons-admin-network"></span>
				</div>
				<div class="socialfeeds-apikey-notice-content">
					<strong>'.esc_html__('API Key Required', 'socialfeeds').'</strong>
					<p>'.esc_html__('Please provide your YouTube API key to enable video feeds and unlock full functionality.', 'socialfeeds').'</p>
				</div>
				<button type="button" class="socialfeeds-apikey-notice-btn" id="socialfeeds-provide-api-key-btn">'.esc_html__('Provide API Key', 'socialfeeds').'</button>
			</div>';
		}

		\SocialFeeds\Settings\UI::render_connect_screen();
	}

	static function support_page(){
		echo '<div class="socialfeeds-support-page">
				<div class="socialfeeds-logo" style="margin: 0 auto 50px; width:60px; height:30px;">
					<img alt="'.esc_html__('Socialfeeds logo', 'socialfeeds').'" width="300px" height="60px" src="'. esc_url(SOCIALFEEDS_ASSETS_URL).'/img/socialfeeds-banner-logo.png'.'"/>	
				</div>
				<h2>'.esc_html__('Help & Support', 'socialfeeds').'</h2>
				<p>'.esc_html__('You can contact the SocialFeeds team via email at ', 'socialfeeds').'<a href="mailto:support@socialfeeds.org">support@socialfeeds.org</a> '.esc_html__('or through our Support Ticket System.', 'socialfeeds').'</p>
				<p>'.esc_html__('You can also check the documentation here:', 'socialfeeds').' <a href="https://socialfeeds.org/docs/" target="_blank" rel="noopener noreferrer">https://socialfeeds.org/docs/</a></p>
			</div>';
	}

	static function pro_placeholder_page(){
		echo '<div class="socialfeeds-placeholder-page">
			<div class="notice notice-warning">
				<p>'.esc_html__('This is a part of Socialfeeds Pro, so update/upgrade to pro to utilize this feature', 'socialfeeds').'</p>
			</div>
		</div>';
	}

	static function enqueue_admin_assets($hook){

		if(false === strpos($hook, 'socialfeeds')){
			return;
		}

		wp_enqueue_style('socialfeeds-admin', SOCIALFEEDS_PLUGIN_URL.'assets/css/admin.css', [], SOCIALFEEDS_VERSION);

		wp_enqueue_media();
		wp_enqueue_script('socialfeeds-admin', SOCIALFEEDS_PLUGIN_URL.'assets/js/admin.js', ['jquery'], SOCIALFEEDS_VERSION, true);

		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		$feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];
		$feed_ids = array_column($feeds, 'id');

		$instagram_opts = get_option('socialfeeds_instagram_option', []);
		$ig_feeds = isset($instagram_opts['instagram_feeds']) ? $instagram_opts['instagram_feeds'] : [];
		$ig_ids = array_column($ig_feeds, 'id');
		$feed_ids = array_merge($feed_ids, $ig_ids);

		$facebook_opts = get_option('socialfeeds_facebook_option', []);
		$fb_feeds = isset($facebook_opts['facebook_feeds']) ? $facebook_opts['facebook_feeds'] : [];
		$fb_ids = array_column($fb_feeds, 'id');
		$feed_ids = array_merge($feed_ids, $fb_ids);

		$google_opts = get_option('socialfeeds_google_option', []);
		$google_feeds = isset($google_opts['google_reviews_feeds']) ? $google_opts['google_reviews_feeds'] : [];
		$google_ids = array_column($google_feeds, 'id');
		$feed_ids = array_merge($feed_ids, $google_ids);

		// Get global ID counter
		$global_counter = get_option('socialfeeds_global_id_counter', 0);

		wp_localize_script('socialfeeds-admin', 'socialfeedsData', [
			'nonce' => wp_create_nonce('socialfeeds_admin_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'existing_ids' => $feed_ids,
			'global_id_counter' => $global_counter,
			'is_pro_active' => defined('SOCIALFEEDS_PRO_VERSION'),
		]);
	}
}
