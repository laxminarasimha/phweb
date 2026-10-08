<?php

namespace ShareMyPostPro;

if(!defined('ABSPATH')){
	exit;
}

class Install{

	static function activate(){
		self::default_settings();
		update_option('sharemypost_pro_version', SHAREMYPOST_PRO_VERSION);
	}

	static function deactivate(){
		delete_option('sharemypost_pro_version');
	}

	static function uninstall(){
		delete_option('sharemypost_pro_version');
		delete_option('sharemypost_pro_tweet_settings');
		delete_option('sharemypost_custom_networks');
	}

	static function default_settings(){

		$current_version = get_option('sharemypost_pro_version');

		if(empty($current_version)){
			$tweet_settings = get_option('sharemypost_pro_tweet_settings', []);

			$tweet_settings = wp_parse_args($tweet_settings,\ShareMyPostPro\Shortcode::get_default_tweet_settings());

			update_option('sharemypost_pro_tweet_settings', $tweet_settings);

			$settings = \ShareMyPost\Util::get_settings();
			if(empty($settings['inline_ai_enabled_networks'])){
				$settings['inline_ai_enabled_networks'] = ['chatgpt', 'gemini',''];
			}

			// Ensure floating bar defaults
			$floating_defaults = [
				'floating_enabled' => 1,
				'floating_position' => 'right',
				'floating_post_types' => ['post'],
				'floating_enabled_networks' => ['facebook', 'twitter', 'whatsapp', 'linkedin'],
				'floating_network_order' => array_keys(\ShareMyPost\Helpers::get_networks()),
				'floating_button_shape' => 'rounded',
				'floating_button_size' => 20,
				'floating_space_between_icons' => 10,
				'floating_button_style' => 'filled',
				'floating_button_color_type' => 'original',
				'floating_button_color' => '#1f75e1',
				'floating_show_labels' => 'icon_only',
				'floating_universal_enabled' => 1,
				'floating_universal_color' => '#1f75e1',
				'floating_mobile_breakpoint' => 768,
			];
			foreach($floating_defaults as $key => $value){
				if(!isset($settings[$key])){
					$settings[$key] = $value;
				}
			}
			update_option('sharemypost_settings', $settings);
		}
	}
}