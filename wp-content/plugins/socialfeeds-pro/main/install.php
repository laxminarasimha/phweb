<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class Install{

	static function activate(){
		update_option('socialfeeds_pro_version', SOCIALFEEDS_PRO_VERSION);
	}

	static function deactivate(){
		delete_option('socialfeeds_pro_version');
	}
	
	static function uninstall(){
		delete_option('socialfeeds_pro_version');
		delete_option('socialfeeds_instagram_option');
	}

}