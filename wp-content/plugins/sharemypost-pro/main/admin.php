<?php
namespace ShareMyPostPro;

if (!defined('ABSPATH')) {
    exit;
}

class Admin {

	static function init(){
		add_action('admin_enqueue_scripts', '\ShareMyPostPro\Admin::admin_enqueue');
		add_action('sharemypost_render_license_page', '\ShareMyPostPro\Settings\License::template');
		add_action('sharemypost_inline_content_settings', '\ShareMyPostPro\Settings\UI::render_inline_content_settings', 10, 2);
		add_action('sharemypost_floating_bar_settings','\ShareMyPostPro\Settings\UI::render_floating_bar_settings', 10, 2);
		add_action('sharemypost_click_to_x_settings', '\ShareMyPostPro\Settings\UI::click_to_tweet_settings');
		add_action('sharemypost_configuration_settings', '\ShareMyPostPro\Settings\UI::render_configuration_settings');
		add_action('sharemypost_inline_style_settings', '\ShareMyPostPro\Settings\UI::render_inline_style_settings', 10, 2);
		add_action('sharemypost_floating_style_settings', '\ShareMyPostPro\Settings\UI::render_floating_style_settings', 10, 2);
		add_action('sharemypost_inline_ai_network_settings', '\ShareMyPostPro\Settings\UI::render_inline_ai_network_settings', 10, 2);
		
		add_action('admin_notices', '\ShareMyPostPro\Admin::sharemypost_pro_free_version_nag');
	}

	static function admin_enqueue($hook) {
		if(false === strpos($hook, 'sharemypost')){
			return;
		}

		wp_enqueue_style('sharemypost-pro-admin', SHAREMYPOST_PRO_PLUGIN_URL . 'assets/css/admin.css', ['sharemypost-admin'], SHAREMYPOST_PRO_VERSION);
		wp_enqueue_script('sharemypost-pro-admin', SHAREMYPOST_PRO_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], SHAREMYPOST_PRO_VERSION, true);

		wp_localize_script('sharemypost-pro-admin', 'sharemypost_pro', [
			'nonce' => wp_create_nonce('sharemypost_pro_admin_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'admin_page_url' => admin_url('admin.php?page=sharemypost'),
		]);

		$custom_networks = \ShareMyPostPro\CustomNetworks::get_all();
		if(!empty($custom_networks)){
			$tile_css = '';
			foreach ($custom_networks as $network) {
				$slug  = 'custom_' . $network['slug'];
				$color = !empty($network['color']) ? sanitize_hex_color($network['color']) : '#1f75e1';
				$tile_css .= " .sharemypost-network-item[data-network=\"{$slug}\"] .sharemypost-icon { background: {$color} !important; color: #fff !important; }\n";
				$tile_css .= " .sharemypost-network-item[data-network=\"{$slug}\"].is-active .sharemypost-icon { background: {$color} !important; color: #fff !important; }\n";
			}
			wp_add_inline_style('sharemypost-pro-admin', $tile_css);
		}
	}
	
	static function sharemypost_pro_free_version_nag(){
		if(!defined('SHAREMYPOST_VERSION')){
			return;
		}

		$dismissed_free = (int) get_option('sharemypost_version_free_nag');
		$dismissed_pro = (int) get_option('sharemypost_version_pro_nag');

		// Checking if time has passed since the dismiss.
		if(!empty($dismissed_free) && time() < $dismissed_pro && !empty($dismissed_pro) && time() < $dismissed_pro){
			return;
		}

		$showing_error = false;
		if(version_compare(SHAREMYPOST_VERSION, SHAREMYPOST_PRO_VERSION) > 0 && (empty($dismissed_pro) || time() > $dismissed_pro)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="sharemypost-pro-version-notice" onclick="sharemypost_pro_dismiss_notice(event)" data-type="pro">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of ShareMyPost Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the application.', 'sharemypost-pro').'</p>
		</div>';
		}elseif(version_compare(SHAREMYPOST_VERSION, SHAREMYPOST_PRO_VERSION) < 0 && (empty($dismissed_free) || time() > $dismissed_free)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="sharemypost-pro-version-notice" onclick="sharemypost_pro_dismiss_notice(event)" data-type="free">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of ShareMyPost. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the application.', 'sharemypost-pro').'</p>
		</div>';
		}

		if(!empty($showing_error)){
			wp_register_script('sharemypost-pro-version-notice', '', array('jquery'), SHAREMYPOST_PRO_VERSION, true );
			wp_enqueue_script('sharemypost-pro-version-notice');
			wp_add_inline_script('sharemypost-pro-version-notice', '
		function sharemypost_pro_dismiss_notice(e){
			e.preventDefault();
			let target = jQuery(e.target);

			if(!target.hasClass("notice-dismiss")){
				return;
			}

			let jEle = target.closest("#sharemypost-pro-version-notice"),
			type = jEle.data("type");

			jEle.slideUp();
			
			jQuery.post("'.admin_url('admin-ajax.php').'", {
				security : "'.wp_create_nonce('sharemypost_version_notice').'",
				action: "sharemypost_pro_version_notice",
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
}