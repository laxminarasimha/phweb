<?php

namespace ShareMyPost;

if(!defined('ABSPATH')){
	exit;
}

class Util{

	static function get_default_settings() {

		$all_networks = array_keys(\ShareMyPost\Helpers::get_networks());

		$defaults = [
			// General
			'mobile' => 1,
			'inline_enabled' => 1,

			// Inline
			'inline_position' => 'below',
			'inline_post_types' => ['post'],
			'inline_enabled_networks' => [ 'twitter','pinterest','whatsapp','facebook',],
			'inline_network_order' => $all_networks,
			'inline_button_color_type' => 'original',
			'inline_button_color' => '#1f75e1',
			'inline_show_labels' => 'icon_only',

			// Shared
			'container_width' => 'auto',
		];

		return apply_filters('sharemypost_default_settings', $defaults);
	}

	static function get_settings(){
		return wp_parse_args(get_option('sharemypost_settings', []), self::get_default_settings());
	}
}