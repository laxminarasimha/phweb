<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class Admin{

	static function init(){
		
		add_action('admin_menu', '\SocialFeedsPro\Admin::add_menu', 100);
		add_action('admin_enqueue_scripts', '\SocialFeedsPro\Admin::admin_enqueue');
		add_action('admin_footer', '\SocialFeedsPro\Settings\UI::render_modals');
		add_action('socialfeeds_clear_instagram_cache', '\SocialFeedsPro\InstagramSettings::clear_cache');
		add_action('socialfeeds_clear_facebook_cache', '\SocialFeedsPro\Facebook::clear_cache');
		add_action('socialfeeds_pro_facebook_token_refresh', '\SocialFeedsPro\Facebook::scheduled_token_refresh');
		add_action('socialfeeds_youtube_feeds', '\SocialFeedsPro\Settings\UI::youtube_feed_type');
		add_action('socialfeeds_video_elements', '\SocialFeedsPro\Settings\UI::youtube_video_elements', 10, 1);
		add_action('socialfeeds_insta_quick_settings', '\SocialFeedsPro\Settings\UI::instagram_dashbord_settings');
		add_action('socialfeeds_render_license_page', '\SocialFeedsPro\Settings\License::template');
		add_action('socialfeeds_pro_youtube_layouts', '\SocialFeedsPro\Settings\UI::youtube_layouts', 10, 1);
		add_action('admin_notices', '\SocialFeedsPro\Admin::socialfeeds_pro_free_version_nag');
		add_action('init', '\SocialFeedsPro\Admin::advanced_token_refresh');
		add_action('socialfeeds_render_google_reviews_page', '\SocialFeedsPro\Settings\UI::google_reviews_page');
	}

	static function add_menu(){
		add_submenu_page('socialfeeds', __('License', 'socialfeeds-pro'), __('License', 'socialfeeds-pro'), 'manage_options', 'socialfeeds-license', '\SocialFeedsPro\Admin::dispatcher');
	}

	static function dispatcher(){
		echo '<div class="socialfeeds-wrap">';

		// Render global header
		\SocialFeeds\Admin::render_header();

		echo '<div class="socialfeeds-main-content">';
		echo '<div id="socialfeeds-license" class="socialfeeds-tab-content active">';
			\SocialFeedsPro\Settings\License::template();
		echo '</div>';
		
		echo '</div>'; // socialfeeds-main-content
		echo '</div>'; // socialfeeds-wrap
	}
	
	static function admin_enqueue($hook){
		if(false === strpos($hook, 'socialfeeds')){
			return;
		}

		wp_enqueue_style('socialfeeds-pro-admin', SOCIALFEEDS_PRO_PLUGIN_URL.'assets/css/admin.css', [], SOCIALFEEDS_PRO_VERSION);

		wp_enqueue_script('socialfeeds-pro-admin', SOCIALFEEDS_PRO_PLUGIN_URL.'assets/js/admin.js', ['jquery'], SOCIALFEEDS_PRO_VERSION, true);
		
		wp_localize_script('socialfeeds-pro-admin', 'socialfeeds_pro', [
			'nonce' => wp_create_nonce('socialfeeds_pro_admin_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'admin_page_url' => admin_url('admin.php?page=socialfeeds'),
		]);
	}

	static function render_instagram_settings(){
		\SocialFeedsPro\Settings\UI::instagram_connect_screen();
	}

	static function render_facebook_settings(){
		\SocialFeedsPro\Settings\UI::facebook_connect_screen();
	}

	static function render_google_settings(){
		\SocialFeedsPro\Settings\UI::google_reviews_connect_screen();
	}

	static function socialfeeds_pro_free_version_nag(){

		if(!defined('SOCIALFEEDS_VERSION')){
			return;
		}

		$dismissed_free = (int) get_option('socialfeeds_version_free_nag');
		$dismissed_pro = (int) get_option('socialfeeds_version_pro_nag');

		// Checking if time has passed since the dismiss.
		if(!empty($dismissed_free) && time() < $dismissed_pro && !empty($dismissed_pro) && time() < $dismissed_pro){
			return;
		}

		$showing_error = false;
		if(version_compare(SOCIALFEEDS_VERSION, SOCIALFEEDS_PRO_VERSION) > 0 && (empty($dismissed_pro) || time() > $dismissed_pro)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="socialfeeds-pro-version-notice" onclick="socialfeeds_pro_dismiss_notice(event)" data-type="pro">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of SocialFeeds Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the application.', 'socialfeeds-pro').'</p>
		</div>';
		}elseif(version_compare(SOCIALFEEDS_VERSION, SOCIALFEEDS_PRO_VERSION) < 0 && (empty($dismissed_free) || time() > $dismissed_free)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="socialfeeds-pro-version-notice" onclick="socialfeeds_pro_dismiss_notice(event)" data-type="free">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of socialfeeds. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the application.', 'socialfeeds-pro').'</p>
		</div>';
		}

		if(!empty($showing_error)){
			wp_register_script('socialfeeds-pro-version-notice', '', array('jquery'), SOCIALFEEDS_PRO_VERSION, true );
			wp_enqueue_script('socialfeeds-pro-version-notice');
			wp_add_inline_script('socialfeeds-pro-version-notice', '
		function socialfeeds_pro_dismiss_notice(e){
			e.preventDefault();
			let target = jQuery(e.target);

			if(!target.hasClass("notice-dismiss")){
				return;
			}

			let jEle = target.closest("#socialfeeds-pro-version-notice"),
			type = jEle.data("type");

			jEle.slideUp();
			
			jQuery.post("'.admin_url('admin-ajax.php').'", {
				security : "'.wp_create_nonce('socialfeeds_version_notice').'",
				action: "socialfeeds_pro_version_notice",
				type: type
			}, function(res){
				if(!res["success"]){
					alert(res["data"]);
				}
			}).fail(function(data){
				alert("There seems to be some issue dismissing this alert");
			});
		}');
		}
	}

	static function advanced_token_refresh(){
		$options = get_option('socialfeeds_instagram_option', []);

		if(!empty($options['instagram_token_type']) && $options['instagram_token_type'] === 'advanced' && !wp_next_scheduled('socialfeeds_pro_instagram_token_refresh')){
			wp_schedule_event(time(), 'daily', 'socialfeeds_pro_instagram_token_refresh');
		}

		$fb_options = get_option('socialfeeds_facebook_option', []);
		if(!empty($fb_options['facebook_token_type']) && $fb_options['facebook_token_type'] === 'advanced' && !wp_next_scheduled('socialfeeds_pro_facebook_token_refresh')){
			wp_schedule_event(time(), 'daily', 'socialfeeds_pro_facebook_token_refresh');
		}
	}
}
