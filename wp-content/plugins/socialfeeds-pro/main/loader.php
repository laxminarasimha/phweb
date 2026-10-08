<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class Loader{

	static function enqueue_frontend(){
		wp_enqueue_style('dashicons');
		wp_enqueue_style('socialfeeds-pro-frontend', SOCIALFEEDS_PRO_PLUGIN_URL.'assets/css/frontend.css', [], SOCIALFEEDS_PRO_VERSION);

		wp_enqueue_script('socialfeeds-pro-frontend', SOCIALFEEDS_PRO_PLUGIN_URL.'assets/js/frontend.js', ['jquery'], SOCIALFEEDS_PRO_VERSION, true);
		
		wp_localize_script('socialfeeds-pro-frontend', 'socialfeeds_pro_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('socialfeeds_frontend_nonce'),
		]);
	}

	static function youtube_play_actions($allowed){
		return array_merge($allowed, ['lightbox', 'inline']);
	}

}