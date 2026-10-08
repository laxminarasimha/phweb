<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
    exit;
}

class Blocks{

	static function init(){
		add_action('init', '\SocialFeedsPro\Blocks::register_blocks');
		add_filter('block_categories_all','\SocialFeedsPro\Blocks::add_block_category', 10, 2);
	}

	static function add_block_category($categories, $post){

		array_unshift($categories, [
			'slug'  => 'socialfeeds',
			'title' => __('SocialFeeds', 'socialfeeds-pro'),
		]);

		return $categories;

	}

	// Register blocks
	static function register_blocks(){
		if(!function_exists('register_block_type')){
			return;
		}

		$script_path = SOCIALFEEDS_PRO_PLUGIN_DIR . 'assets/js/block.js';

		wp_register_script('socialfeeds-block', SOCIALFEEDS_PRO_PLUGIN_URL . 'assets/js/block.js', ['wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-server-side-render'], filemtime($script_path));

		wp_register_style('socialfeeds-pro-frontend', SOCIALFEEDS_PRO_PLUGIN_URL . 'assets/css/frontend.css', [], SOCIALFEEDS_PRO_VERSION);

		if(defined('SOCIALFEEDS_VERSION')){
			wp_register_style('socialfeeds-frontend', SOCIALFEEDS_PLUGIN_URL . 'assets/css/frontend.css', [], SOCIALFEEDS_VERSION);
		}

		$youtube_options = get_option('socialfeeds_youtube_option', []);
		$youtube_feeds = isset($youtube_options['youtube_feeds']) ? $youtube_options['youtube_feeds'] : [];

		$instagram_options = get_option('socialfeeds_instagram_option', []);
		$instagram_feeds = isset($instagram_options['instagram_feeds']) ? $instagram_options['instagram_feeds'] : [];

		$facebook_options = get_option('socialfeeds_facebook_option', []);
		$facebook_feeds = isset($facebook_options['facebook_feeds']) ? $facebook_options['facebook_feeds'] : [];
		
		$google_options = get_option('socialfeeds_google_option', []);
		$google_feeds = isset($google_options['google_reviews_feeds']) ? $google_options['google_reviews_feeds'] : [];

		wp_localize_script('socialfeeds-block', 'socialfeeds_blocks_data', [
			'youtube_feeds' => $youtube_feeds,
			'instagram_feeds' => $instagram_feeds,
			'facebook_feeds' => $facebook_feeds,
			'google_feeds' => $google_feeds,
		]);

		$blocks = [
			'youtube' => [
				'title' => __('YouTube Feed', 'socialfeeds-pro'),
				'description' => __('Display a YouTube feed', 'socialfeeds-pro'),
				'render_callback' => [__CLASS__, 'render_youtube_block'],
				'keywords' => ['youtube', 'video', 'feed', 'socialfeeds'],
			],
			'instagram' => [
				'title' => __('Instagram Feed', 'socialfeeds-pro'),
				'description' => __('Display an Instagram feed', 'socialfeeds-pro'),
				'render_callback' => [__CLASS__, 'render_instagram_block'],
				'keywords' => ['instagram', 'photo', 'feed', 'socialfeeds'],
			],
			'facebook' => [
				'title' => __('Facebook Feed', 'socialfeeds-pro'),
				'description' => __('Display a Facebook feed', 'socialfeeds-pro'),
				'render_callback' => [__CLASS__, 'render_facebook_block'],
				'keywords' => ['facebook', 'post', 'feed', 'socialfeeds'],
			],
			'google' => [
				'title' => __('Google review Feed', 'socialfeeds-pro'),
				'description' => __('Display a google reviews feeds', 'socialfeeds-pro'),
				'render_callback' => [__CLASS__, 'render_google_review_block'],
				'keywords' => ['google review', 'post', 'feed', 'socialfeeds'],
			]
		];

		foreach($blocks as $slug => $args){
			register_block_type("socialfeeds/{$slug}", array_merge($args, [
				'category' => 'socialfeeds',
				'editor_script' => 'socialfeeds-block',
				'editor_style' => ['socialfeeds-pro-frontend', 'socialfeeds-frontend'],
				'supports' => [
					'align' => ['wide', 'full'],
				],
				'attributes' => [
					'id' => [
						'type' => 'string',
						'default' => '',
					],
					'width' => [
						'type' => 'string',
						'default' => '',
					],
					'align' => [
						'type' => 'string',
						'default' => '',
					],
				],
			]));
		}
	}

	static function render_block($type, $attrs){
		$feed_id = isset($attrs['id']) ? esc_html($attrs['id']) : '';
		$width = isset($attrs['width']) ? $attrs['width'] : '';
		$align = isset($attrs['align']) ? $attrs['align'] : '';

		if(empty($feed_id)){
			return '<div style="padding:15px;background:#fff3cd;border:1px solid #ffeeba;">
			Please select a ' . ucfirst($type) . ' Feed in the block settings.
			</div>';
		}

		// Calculate what to pass to the internal shortcode renderer
		$render_width = $width;
		$render_align = $align;

		// If block alignment is wide/full, force internal width to 100% to fill the expanded container
		if($align === 'wide' || $align === 'full'){
			$render_width = '100%';
		}

		// If custom width is none, pass empty so it uses default
		if($render_width === 'none'){
			$render_width = '';
		}

		$output = '';

		if($type === 'youtube' && defined('SOCIALFEEDS_VERSION')){
			$output = \SocialFeeds\Shortcodes::youtube_feed_by_id([
				'id' => $feed_id,
				'width' => $render_width,
				'align' => $render_align,
			]);
		} elseif($type === 'instagram'){
			$output = \SocialFeedsPro\ShortcodeRender::instagram_feed([
				'id' => $feed_id,
				'width' => $render_width,
				'align' => $render_align,
			]);
		} elseif($type === 'facebook'){
			$output = \SocialFeedsPro\ShortcodeRender::facebook_feed([
				'id' => $feed_id,
				'width' => $render_width,
				'align' => $render_align,
			]);
		} elseif($type === 'google'){
			$output = \SocialFeedsPro\ShortcodeRender::google_reviews([
				'id' => $feed_id,
				'width' => $render_width,
				'align' => $render_align,
			]);
		} else {
			$output = '<div style="color:red;">'.ucfirst($type).' renderer not found.</div>';
		}

		$classes = ['socialfeeds-block-wrapper'];
		if($align === 'wide' || $align === 'full'){
			$classes[] = 'align' . $align;
		}

		$style = "";
		if($width && $width !== 'none'){
			$style .= "width: {$width}; max-width: 100%;";
		}

		if($align === 'center'){
			$style .= " margin-left: auto; margin-right: auto;";
		} elseif ($align === 'right'){
			$style .= " margin-left: auto; margin-right: 0;";
		} elseif ($align === 'left'){
			$style .= " margin-left: 0; margin-right: auto;";
		}

		// Internal CSS to ensure the feed container fills our wrapper when using block attributes
		$internal_css = '<style>
			.socialfeeds-block-wrapper .socialfeeds-instagram-feed, 
			.socialfeeds-block-wrapper .socialfeeds-facebook-feed, 
			.socialfeeds-block-wrapper .socialfeeds-youtube-feed { 
			max-width: 100% !important; 
			width: 100% !important;
			}
		</style>';

		return '<div class="' . esc_attr(implode(' ', $classes)) . '" style="' . esc_attr($style) . '">' . $internal_css . $output . '</div>';
	}

	static function render_youtube_block($attrs){
		return self::render_block('youtube', $attrs);
	}

	static function render_instagram_block($attrs){
		return self::render_block('instagram', $attrs);
	}

	static function render_facebook_block($attrs){
		return self::render_block('facebook', $attrs);
	}
	
	static function render_google_review_block($attrs){
		return self::render_block('google', $attrs);
	}
}