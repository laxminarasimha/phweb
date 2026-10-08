<?php

namespace SocialFeeds;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Install{

	static function activate(){
		update_option('socialfeeds_version', SOCIALFEEDS_VERSION);
	}

	static function deactivate(){
		delete_option('socialfeeds_version');
	}

	static function uninstall(){
		delete_option('socialfeeds_version');
		delete_option('socialfeeds_youtube_option');
	}

}