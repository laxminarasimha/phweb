<?php

namespace ShareMyPost;

if(!defined('ABSPATH')){
	exit;
}

class Install{

	static function activate(){
		self::default_settings();
		update_option('sharemypost_version', SHAREMYPOST_VERSION);
	}

	static function deactivate(){

	}

	static function uninstall(){
		delete_option('sharemypost_version');
		delete_option('sharemypost_settings');
	}

	static function default_settings(){

		$current_version = get_option('sharemypost_version');

		if(empty($current_version)){
			$settings = get_option('sharemypost_settings', []);
			$settings = wp_parse_args($settings, \ShareMyPost\Util::get_default_settings());
			update_option('sharemypost_settings', $settings);
		}
	}
}