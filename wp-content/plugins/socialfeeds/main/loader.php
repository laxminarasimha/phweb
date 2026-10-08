<?php
/**
 * Loader - Handles asset enqueueing
 */

namespace SocialFeeds;

if(!defined('ABSPATH')){
	exit;
}

class Loader{

	static function enqueue_frontend_assets(){
		wp_enqueue_style('socialfeeds-frontend', SOCIALFEEDS_PLUGIN_URL.'assets/css/frontend.css', [], SOCIALFEEDS_VERSION);
		
		wp_enqueue_script('socialfeeds-frontend', SOCIALFEEDS_PLUGIN_URL.'assets/js/frontend.js', ['jquery'], SOCIALFEEDS_VERSION, true);
		
		wp_localize_script('socialfeeds-frontend', 'socialfeeds_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('socialfeeds_frontend_nonce'),
		]);
	}
}
