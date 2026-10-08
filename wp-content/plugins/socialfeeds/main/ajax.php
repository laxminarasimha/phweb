<?php

namespace SocialFeeds;

if(!defined('ABSPATH')){
	exit;
}

class Ajax{

	static function hooks(){
		add_action('wp_ajax_socialfeeds_save_settings', '\SocialFeeds\Ajax::save_settings');
		add_action('wp_ajax_socialfeeds_youtube_preview_show', '\SocialFeeds\Ajax::youtube_preview');
		add_action('wp_ajax_socialfeeds_save_cache_settings', '\SocialFeeds\Ajax::save_cache_settings');
		add_action('wp_ajax_socialfeeds_clear_cache', '\SocialFeeds\Ajax::clear_cache');
		add_action('wp_ajax_socialfeeds_delete_feeds', '\SocialFeeds\Ajax::delete_feed');
		add_action('wp_ajax_socialfeeds_load_more_videos', '\SocialFeeds\Ajax::load_more_videos');
		add_action('wp_ajax_nopriv_socialfeeds_load_more_videos', '\SocialFeeds\Ajax::load_more_videos');
		add_action('wp_ajax_socialfeeds_update_feed_name', '\SocialFeeds\Ajax::update_feed_name');
	}

	static function save_settings(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}

		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		
		if(!is_array($youtube_opts)){
			$youtube_opts = [];
		}
		
		// 1. YouTube API Key
		if(isset($_POST['youtube_api_key'])){
			$youtube_opts['youtube_api_key'] = !empty($_POST['youtube_api_key']) ? \SocialFeeds\Util::encrypt_credential(sanitize_text_field(wp_unslash($_POST['youtube_api_key']))) : '';
		}

		// 2. Wizard Feed Save
		if(isset($_POST['feed_type'])){
			$feed_type = sanitize_text_field(wp_unslash($_POST['feed_type']));

			$feed_settings = [
				'youtube_display_style' => isset($_POST['youtube_display_style']) ? sanitize_text_field(wp_unslash($_POST['youtube_display_style'])) : 'grid',
				'youtube_grid_columns_desktop' => isset($_POST['youtube_grid_columns_desktop']) ? absint(wp_unslash($_POST['youtube_grid_columns_desktop'])): 3,
				'youtube_grid_columns_mobile' => isset($_POST['youtube_grid_columns_mobile']) ? absint(wp_unslash($_POST['youtube_grid_columns_mobile'])) : 1,
				'youtube_color_scheme' => isset($_POST['youtube_color_scheme']) ? sanitize_text_field(wp_unslash($_POST['youtube_color_scheme'])) : 'light',
				'youtube_custom_color' => isset($_POST['youtube_custom_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_custom_color'])) : '#000000',
				'youtube_header_enabled' => isset($_POST['youtube_header_enabled']) ? 1 : 0,
				'youtube_header_show_channel_name' => isset($_POST['youtube_header_show_channel_name']) ? 1 : 0,
				'youtube_header_show_logo' => isset($_POST['youtube_header_show_logo']) ? 1 : 0,
				'youtube_header_show_subscribers' => isset($_POST['youtube_header_show_subscribers']) ? 1 : 0,
				'youtube_header_show_banner' => isset($_POST['youtube_header_show_banner']) ? 1 : 0, 
				'youtube_header_show_description' => isset($_POST['youtube_header_show_description']) ? 1 : 0,
				'youtube_header_text' => isset($_POST['youtube_header_text']) ? sanitize_text_field(wp_unslash($_POST['youtube_header_text'])) : '',
				'youtube_load_more_enabled' => isset($_POST['youtube_load_more_enabled']) ? 1 : 0,
				'youtube_load_more_text' => isset($_POST['youtube_load_more_text']) ? sanitize_text_field(wp_unslash($_POST['youtube_load_more_text'])) : 'Load More',
				'youtube_load_more_bg_color' => isset($_POST['youtube_load_more_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_load_more_bg_color'])) : '#FF0000',
				'youtube_load_more_text_color' => isset($_POST['youtube_load_more_text_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_load_more_text_color'])) : '#FFFFFF',
				'youtube_load_more_hover_color' => isset($_POST['youtube_load_more_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_load_more_hover_color'])) : '#5f0505',
				'youtube_load_more_count' => isset($_POST['youtube_load_more_count']) ? intval($_POST['youtube_load_more_count']) : 9,
				'youtube_show_title' => isset($_POST['youtube_show_title']) ? 1 : 0,
				'youtube_show_desc' => isset($_POST['youtube_show_desc']) ? 1 : 0,
				'youtube_show_play_icon' => isset($_POST['youtube_show_play_icon']) ? 1 : 0,
				'youtube_show_duration' => isset($_POST['youtube_show_duration']) ? 1 : 0,
				'youtube_show_date' => isset($_POST['youtube_show_date']) ? 1 : 0,
				'youtube_show_views' => isset($_POST['youtube_show_views']) ? 1 : 0,
				'youtube_show_likes' => isset($_POST['youtube_show_likes']) ? 1 : 0,
				'youtube_show_comments' => isset($_POST['youtube_show_comments']) ? 1 : 0,
				'youtube_lazy_load' => isset($_POST['youtube_lazy_load']) ? 1 : 0,
				'youtube_videos_per_page' => isset($_POST['youtube_videos_per_page']) ? intval($_POST['youtube_videos_per_page']) : 12,
				'youtube_spacing' => isset($_POST['youtube_spacing']) ? intval($_POST['youtube_spacing']) : 16,
				'youtube_hover_effect' => isset($_POST['youtube_hover_effect']) ? sanitize_text_field(wp_unslash($_POST['youtube_hover_effect'])) : 'overlay',
				'youtube_click_action' => isset($_POST['youtube_click_action']) ? sanitize_text_field(wp_unslash($_POST['youtube_click_action'])) : 'newtab',
				'youtube_subscribe_button_enabled' => isset($_POST['youtube_subscribe_button_enabled']) ? 1 : 0,
				'youtube_subscribe_text' => isset($_POST['youtube_subscribe_text']) ? sanitize_text_field(wp_unslash($_POST['youtube_subscribe_text'])) : 'Subscribe',
				'youtube_subscribe_bg_color' => isset($_POST['youtube_subscribe_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_subscribe_bg_color'])) : '#FF0000',
				'youtube_subscribe_text_color' => isset($_POST['youtube_subscribe_text_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_subscribe_text_color'])) : '#FFFFFF',
				'youtube_subscribe_hover_color' => isset($_POST['youtube_subscribe_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['youtube_subscribe_hover_color'])) : '#CC0000',
			];

			$feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];
			$input_val = '';

			if(isset($_POST['channel_id'])){
				$input_val = sanitize_text_field(wp_unslash($_POST['channel_id']));
			} elseif(isset($_POST['playlist_id'])){
				$input_val = sanitize_text_field(wp_unslash($_POST['playlist_id']));
			} elseif(isset($_POST['search_term'])){
				$input_val = sanitize_text_field(wp_unslash($_POST['search_term']));
			} elseif(isset($_POST['socialwall_feeds']) && is_array($_POST['socialwall_feeds'])){
				$sel = array_map('sanitize_text_field', wp_unslash($_POST['socialwall_feeds']));
				$input_val = implode(',', $sel);
			} elseif(isset($_POST['video_ids'])){
				$input_val = sanitize_text_field(wp_unslash($_POST['video_ids']));
			}

			if(!empty($input_val)){
				$preview = isset($_POST['preview_url']) ? rawurldecode(sanitize_text_field(wp_unslash($_POST['preview_url']))) : '';
				$edit_id = isset($_POST['edit_id']) ? sanitize_text_field(wp_unslash($_POST['edit_id'])) : '';
				$channel_subscriber_count = isset($_POST['channel_subscriber_count']) ? sanitize_text_field(wp_unslash($_POST['channel_subscriber_count'])) : 0;
				
				if ($edit_id) {
					$updated = false;
					foreach($feeds as $k => $f){
						if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
							$feeds[$k]['settings'] = $feed_settings;
							$feeds[$k]['input'] = $input_val;
							$feeds[$k]['preview'] = $preview;
							$feeds[$k]['subscriber_count'] = $channel_subscriber_count;
							$updated = true;
							break;
						}
					}
					if(!$updated) $edit_id = '';
				}

				if(!$edit_id){
					$same_type_count = 0;
					foreach($feeds as $f){
						if(isset($f['type']) && $f['type'] === $feed_type) $same_type_count++;
					}
					$index = $same_type_count + 1;
					$name = 'YouTube Feed - ' . ucfirst($feed_type) . ' ' . $index;
					$feed_id = isset($_POST['client_feed_id']) ? sanitize_text_field(wp_unslash($_POST['client_feed_id'])) : '';
				
					if(empty($feed_id)){
						// Get global ID counter
						$global_counter = get_option('socialfeeds_global_id_counter', 0);
					
						// Collect all existing IDs from YouTube, Instagram and Facebook
						$all_existing_ids = [];
					
						// YouTube IDs
						foreach ($feeds as $f) {
							if (isset($f['id'])) {
								$all_existing_ids[] = intval($f['id']);
							}
						}
					
						// Instagram IDs
						$instagram_opts = get_option('socialfeeds_instagram_option', []);
						$instagram_feeds = isset($instagram_opts['instagram_feeds']) ? $instagram_opts['instagram_feeds'] : [];
						foreach($instagram_feeds as $f){
							if(isset($f['id'])){
								$all_existing_ids[] = intval($f['id']);
							}
						}

						// Facebook IDs
						$facebook_opts = get_option('socialfeeds_facebook_option', []);
						$facebook_feeds = isset($facebook_opts['facebook_feeds']) ? $facebook_opts['facebook_feeds'] : [];
						foreach($facebook_feeds as $f){
							if(isset($f['id'])){
								$all_existing_ids[] = intval($f['id']);
							}
						}

						//google reviews IDs
						$google_opts = get_option('socialfeeds_google_option', []);
						$google_feeds = isset($google_opts['google_reviews_feeds']) ? $google_opts['google_reviews_feeds'] : [];
						foreach($google_feeds as $f){
							if(isset($f['id'])){
								$all_existing_ids[] = intval($f['id']);
							}
						}
					
						// Find next available ID
						// If no feeds exist, start from 1 and reset counter
						if(empty($all_existing_ids)){
							$next_id = 1;
							// Reset global counter
							update_option('socialfeeds_global_id_counter', 2);
						} else {
							$next_id = max($global_counter, 1);
							while(in_array($next_id, $all_existing_ids)){
								$next_id++;
							}
							// Update global counter
							update_option('socialfeeds_global_id_counter', $next_id + 1);
						}
					
						$feed_id = $next_id;
					}
				
					$feeds[] = [
						'id' => $feed_id,
						'name' => $name,
						'shortcode' => '[socialfeeds id="' . $feed_id . '" platform="youtube"]',
						'type' => $feed_type,
						'input' => $input_val,
						'preview' => $preview,
						'settings' => $feed_settings,
						'subscriber_count' => $channel_subscriber_count,
						'created' => time(),
					];
				}

				$youtube_opts['youtube_feeds'] = $feeds;
				update_option('social_feed_youtube_settings', $feed_settings);
			}
		}

		// 3. Handle structured options from complex forms
		if(isset($_POST['social_feed_options']['youtube'])){
			$youtube_opts['youtube_channel_id'] = !empty($_POST['social_feed_options']['youtube']['channel_id']) ? sanitize_text_field(wp_unslash($_POST['social_feed_options']['youtube']['channel_id'])) : '';
			$youtube_opts['youtube_display_style'] = !empty($_POST['social_feed_options']['youtube']['display_style']) ? sanitize_text_field(wp_unslash($_POST['social_feed_options']['youtube']['display_style'])) : '';
			$youtube_opts['youtube_theme'] = !empty($_POST['social_feed_options']['youtube']['theme']) ? sanitize_text_field(wp_unslash($_POST['social_feed_options']['youtube']['theme'])) : '';

			$youtube_opts['youtube_click_action'] = !empty($_POST['social_feed_options']['youtube']['click_action']) ? sanitize_text_field(wp_unslash($_POST['social_feed_options']['youtube']['click_action'])) : '';
			$youtube_opts['youtube_url'] = !empty($_POST['social_feed_options']['youtube']['url']) ? esc_url_raw(wp_unslash($_POST['social_feed_options']['youtube']['url'])) : '';
			$youtube_opts['youtube_header_text'] = !empty($_POST['social_feed_options']['youtube']['header_text']) ? sanitize_text_field(wp_unslash($_POST['social_feed_options']['youtube']['header_text'])) : '';
			$youtube_opts['youtube_grid_columns'] = !empty($_POST['social_feed_options']['youtube']['grid_columns']) ? intval(wp_unslash($_POST['social_feed_options']['youtube']['grid_columns'])) : 3;
			
			$youtube_bools = [
				'youtube_show_title', 'youtube_show_desc', 'youtube_show_play_icon', 'youtube_show_duration',
				'youtube_show_date', 'youtube_show_views', 'youtube_show_likes', 'youtube_show_comments',
				'youtube_carousel_autoplay', 'youtube_header_enabled', 'youtube_header_show_channel_name',
				'youtube_header_show_logo', 'youtube_header_show_subscribers', 'youtube_lightbox_enabled', 'youtube_load_more_enabled',
				'youtube_subscribe_button_enabled', 'youtube_video_autoplay', 'instagram_display_videos', 'instagram_show_reels',
			];
			
			foreach($youtube_bools as $bool){
				$youtube_opts['social_feed_youtube_' . $bool] = (isset($_POST['social_feed_options']['youtube'][$bool]) && !empty($_POST['social_feed_options']['youtube'][$bool])) ? 1 : 0;
			}
		}

		update_option('socialfeeds_youtube_option', $youtube_opts);

		$response = [
			'message' => esc_html__('Settings saved successfully.', 'socialfeeds'),
			'feed_id' => !empty($feed_id) ? $feed_id : '',
			'feed_name' => !empty($name) ? $name : '',
		];

		wp_send_json_success($response);
	}
	
	static function youtube_preview(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}
		
		$feed_type = isset($_POST['feed_type']) ? sanitize_text_field(wp_unslash($_POST['feed_type'])) : '';
		$page_token = isset($_POST['pageToken']) ? sanitize_text_field(wp_unslash($_POST['pageToken'])) : '';

		$option = get_option('socialfeeds_youtube_option', []);
		$api_key = \SocialFeeds\Util::get_youtube_api_key($option);

		$per_page = isset($_POST['youtube_videos_per_page']) ? absint(wp_unslash($_POST['youtube_videos_per_page'])) : 6;
		$per_page = max(1, min(50, $per_page));

		if(empty($api_key)){
			wp_send_json_error(__('YouTube API key not configured.', 'socialfeeds'));
		}

		$items = [];
		$next_page_token = '';
		$channel_id = '';

		/* ---------------- CHANNEL (FREE) ---------------- */
		if($feed_type === 'channel'){
			$input = isset($_POST['channel_id']) ? sanitize_text_field(wp_unslash($_POST['channel_id'])) : '';

			if(!empty($input)){
				if(strpos($input, 'UC') === 0){
					$channel_id = $input;
				} else{
					$body = self::fetch_url(add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'type' => 'channel',
						'maxResults' => 1,
						'q' => $input,
					], 'https://www.googleapis.com/youtube/v3/search'));

					$channel_id = isset($body['items'][0]['id']['channelId']) ? $body['items'][0]['id']['channelId'] : '';
					if(!$channel_id){
						wp_send_json_error(__('Channel not found. Please check the channel name or ID.', 'socialfeeds'));
					}
				}
			}

			if(!empty($channel_id)){
				$query_args = [
					'key' => $api_key,
					'part' => 'snippet',
					'channelId' => $channel_id,
					'order' => 'date',
					'type' => 'video',
					'maxResults' => $per_page,
				];
				
				if(!empty($page_token)){
					$query_args['pageToken'] = $page_token;
				}
				
				$url = add_query_arg($query_args, 'https://www.googleapis.com/youtube/v3/search');

				$body = self::fetch_url($url);
				
				$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';

				foreach(isset($body['items']) ? $body['items'] : array() as $it){
					if(empty($it['id']['videoId'])){
						continue;
					}
					
					$snippet = isset($it['snippet']) ? $it['snippet'] : [];
					$items[] = array(
						'videoId' => isset($it['id']['videoId']) ? sanitize_text_field($it['id']['videoId']) : '',
						'title' => isset($snippet['title']) ? sanitize_text_field($snippet['title']) : '',
						'description' => isset($snippet['description']) ? sanitize_textarea_field($snippet['description']) : '',
						'thumbnails' => isset($snippet['thumbnails']) ? \SocialFeeds\Util::sanitize_thumbnails($snippet['thumbnails']) : [],
						'channelTitle' => isset($snippet['channelTitle']) ? sanitize_text_field($snippet['channelTitle']) : (isset($snippet['videoOwnerChannelTitle']) ? sanitize_text_field($snippet['videoOwnerChannelTitle']) : ''),
						'channelId'	=> isset($snippet['channelId']) ? sanitize_text_field($snippet['channelId']) : (isset($snippet['videoOwnerChannelId']) ? sanitize_text_field($snippet['videoOwnerChannelId']) : ''),
					);
				}
			}
		} else {

			if(class_exists('\SocialFeedsPro\YouTube') && method_exists('\SocialFeedsPro\YouTube', 'youtube_preview')){
				\SocialFeedsPro\YouTube::youtube_preview($feed_type, $api_key, $per_page, $page_token);
			}

			/* translators: %s: Feed type name */
			wp_send_json_error(sprintf(__('The %s feed type requires the Pro version.', 'socialfeeds'), $feed_type));
		}

		if(!$items){
			wp_send_json_error(__('No videos found for the provided input.', 'socialfeeds'));
		}

		/* ---------------- VIDEO DETAILS ---------------- */
		$video_ids = array_slice(array_column($items, 'videoId'), 0, $per_page);
		if(!empty($video_ids) && class_exists('\SocialFeedsPro\YouTube') && method_exists('\SocialFeedsPro\YouTube', 'fetch_video_details')){
			$items = \SocialFeedsPro\YouTube::fetch_video_details($items, $api_key, $per_page);
		}

		/* ---------------- CHANNEL INFO ---------------- */
		$preview_channel_id = isset($items[0]['channelId']) ? $items[0]['channelId'] : $channel_id;
		$channel_info = null;

		if(!empty($preview_channel_id)){
			$body = self::fetch_url(add_query_arg([
				'key'  => $api_key,
				'part' => 'snippet,brandingSettings,statistics',
				'id'   => $preview_channel_id,
			], 'https://www.googleapis.com/youtube/v3/channels'));

			$snippet = isset($body['items'][0]['snippet']) ? $body['items'][0]['snippet'] : null;

			if(!empty($snippet)){
				$subscriber_count = isset($body['items'][0]['statistics']['subscriberCount']) ?  $body['items'][0]['statistics']['subscriberCount'] : 0;
				$channel_info = \SocialFeeds\Util::sanitize_channel_info(array(
					'id' => $preview_channel_id,
					'title'	=> isset($snippet['title']) ? $snippet['title'] : '',
					'thumbnail' => isset($snippet['thumbnails']['default']['url']) ? $snippet['thumbnails']['default']['url'] : '',
					'description' => isset($snippet['description']) ? $snippet['description'] : '',
					'bannerExternalUrl' => isset($body['items'][0]['brandingSettings']['image']['bannerExternalUrl']) ? $body['items'][0]['brandingSettings']['image']['bannerExternalUrl'] : '',
					'subscriberCount' => $subscriber_count,
				));
			}
		}

		wp_send_json_success([
			'items' => \SocialFeeds\Util::sanitize_preview_items($items),
			'nextPageToken' => $next_page_token,
			'channel' => $channel_info,
		]);
	}

	static function delete_feed(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}

		$feed_id  = isset($_POST['feed_id']) ? sanitize_text_field(wp_unslash($_POST['feed_id'])) : '';
		$platform = isset($_POST['platform']) ? sanitize_text_field(wp_unslash($_POST['platform'])) : 'youtube';

		// Determine which feed list to modify
		if($platform === 'instagram'){
			$opts = get_option('socialfeeds_instagram_option', []);
			$key = 'instagram_feeds';
		} elseif($platform === 'facebook'){
			$opts = get_option('socialfeeds_facebook_option', []);
			$key = 'facebook_feeds';
		} elseif ($platform === 'google_reviews') {
			$opts = get_option('socialfeeds_google_option', []);
			$key = 'google_reviews_feeds';
		} else {
			$opts = get_option('socialfeeds_youtube_option', []);
			$key = 'youtube_feeds';
		}

		$feeds = isset($opts[$key]) ? $opts[$key] : [];

		// Filter out the feed to delete
		if($feed_id && !empty($feeds)){
			$filtered_feeds = [];
			foreach($feeds as $feed){
				if(empty($feed['id']) || (string)$feed['id'] !== (string)$feed_id){
					$filtered_feeds[] = $feed;
				}
			}
			
			$opts[$key] = $filtered_feeds;
			
			if($platform === 'instagram'){
				update_option('socialfeeds_instagram_option', $opts);
			} elseif($platform === 'facebook'){
				update_option('socialfeeds_facebook_option', $opts);
			} elseif ($platform === 'google_reviews') {
				update_option('socialfeeds_google_option', $opts);
			} else {
				update_option('socialfeeds_youtube_option', $opts);
			}

			wp_send_json_success(['message'  => esc_html__('Feed deleted successfully', 'socialfeeds')]);
		}

		wp_send_json_error(esc_html__('Feed not found or invalid request','socialfeeds'));
	}

	static function load_more_videos(){
		
		check_ajax_referer('socialfeeds_frontend_nonce', 'nonce');

		// RATE LIMIT 
		$client_ip = !empty($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

		if(!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)){
			$client_ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']));
		}

		$ip_hash = !empty($client_ip) && filter_var($client_ip, FILTER_VALIDATE_IP) ? md5($client_ip) : '';

		if (!empty($ip_hash)) {
			// Check temporary ban
			if (get_transient('sf_ban_' . $ip_hash)) {
				wp_send_json_error(__('Access temporarily suspended due to suspicious activity. Please try again later.', 'socialfeeds'), 429);
			}

			// Limit: Max 20 requests per minute (60 seconds)
			$rate_key = 'sf_rate_' . $ip_hash;
			$request_count = (int) get_transient($rate_key);

			if ($request_count >= 20) {
				// Ban the IP for 5 minutes (300 seconds)
				set_transient('sf_ban_' . $ip_hash, true, 300);
				delete_transient($rate_key);
				wp_send_json_error(__('Too many requests. Please slow down and try again in 5 minutes.', 'socialfeeds'), 429);
			}

			// Increment count
			set_transient($rate_key, $request_count + 1, 60);
		}
		// --------------------------------------------------

		$feed_id = isset($_POST['feed_id']) ? sanitize_text_field(wp_unslash($_POST['feed_id'])) : '';
		$limit = isset($_POST['limit']) ? absint(wp_unslash($_POST['limit'])) : 12;
		$pageToken = isset($_POST['pageToken']) ? sanitize_text_field(wp_unslash($_POST['pageToken'])) : '';
		$opts_global = get_option('socialfeeds_youtube_option', []);
		$feed_settings = [];
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : 3600;

		// If feed_id is provided, fetch settings from database
		if ($feed_id) {
			$feeds = isset($opts_global['youtube_feeds']) ? $opts_global['youtube_feeds'] : [];
			$found_feed = null;
			foreach ($feeds as $f) {
				if (isset($f['id']) && (string) $f['id'] === (string) $feed_id) {
					$found_feed = $f;
					break;
				}
			}

			if ($found_feed) {
				$feed_type = isset($found_feed['type']) ? $found_feed['type'] : '';
				$feed_input = isset($found_feed['input']) ? $found_feed['input'] : '';
				$feed_settings = isset($found_feed['settings']) ? $found_feed['settings'] : [];
				$limit = isset($feed_settings['youtube_load_more_count']) ? intval($feed_settings['youtube_load_more_count']) : $limit;
			}
		}

		$opts = array_merge($opts_global, $feed_settings);

		$api_key = \SocialFeeds\Util::get_youtube_api_key($opts_global);

		if(empty($feed_type) || empty($api_key)){
			wp_send_json_error(__('Missing required parameters', 'socialfeeds'), 400);
		}

		// ---------------- CACHE CHECK ----------------
		$cache_key = 'sf_yt_' . md5($feed_type . '_' . $feed_input . '_' . $limit . '_' . $pageToken);
		$cached_response = get_transient($cache_key);

		if (false !== $cached_response) {
			wp_send_json_success($cached_response);
		}
		// ---------------------------------------------

		$items = [];
		$nextPageToken = '';

		/* ---------------- CHANNEL (FREE) ---------------- */
		if($feed_type === 'channel'){
			$channelId = $feed_input;
			
			$search_url = add_query_arg([
				'key' => $api_key,
				'part' => 'snippet',
				'type' => 'channel',
				'maxResults' => 1,
				'q' => $channelId,
			], 'https://www.googleapis.com/youtube/v3/search');

			$body = self::fetch_url($search_url);
			if(!empty($body['items'][0]['id']['channelId'])){
				$channelId = $body['items'][0]['id']['channelId'];
			}

			$url = add_query_arg([
				'key' => $api_key,
				'part' => 'snippet',
				'channelId' => $channelId,
				'order' => 'date',
				'maxResults' => $limit,
				'pageToken' => $pageToken,
			], 'https://www.googleapis.com/youtube/v3/search');

			$body = self::fetch_url($url);
			
			$nextPageToken = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
			$items = self::extract_videos($body['items'] ?? []);
			
			// Enrich with statistics/details if filter available (e.g. Pro or custom)
			if(!empty($items)){
				$items = apply_filters('socialfeeds_youtube_video_items_details', $items, $api_key, $limit);
			}

			$response_data = ['items' => $items];
			if(!empty($nextPageToken)){
				$response_data['nextPageToken'] = $nextPageToken;
			}

		} else {
			// PRO
			do_action('socialfeeds_youtube_load_more_pro', $feed_type, $feed_input, $api_key, $limit, $pageToken, '\SocialFeeds\Ajax::extract_videos', $opts);
			/* translators: %s: requires the Pro*/
			wp_send_json_error(sprintf(__('Loading more for %s requires the Pro version.', 'socialfeeds'), $feed_type));
		}

		// Generate HTML
		if(!empty($response_data['items']) && class_exists('\SocialFeeds\Shortcodes')){
			$response_data['html'] = \SocialFeeds\Shortcodes::render_items($response_data['items'], $opts);
		}

		// ---------------- SAVE TO CACHE ----------------
		if (!empty($response_data['items']) && $cache_duration > 0) {
			set_transient($cache_key, $response_data, $cache_duration);
		}
		// -----------------------------------------------

		wp_send_json_success($response_data);
	}
	
	static function fetch_url($url){
		$resp = wp_remote_get($url);
		
		if(is_wp_error($resp)){
			wp_send_json_error($resp->get_error_message());
		}
		
		$code = wp_remote_retrieve_response_code($resp);
		if(200 !== $code){
			$body = json_decode(wp_remote_retrieve_body($resp), true);
			$error_msg = 'API request failed with code ' . $code;

			if(isset($body['error']['errors'][0]['reason'])){
				$reason = $body['error']['errors'][0]['reason'];
				if($reason === 'quotaExceeded'){
					$error_msg = __('YouTube API Quota Exceeded. Please try again later or use a different API key.', 'socialfeeds');
				} elseif($reason === 'keyInvalid'){
					$error_msg = __('Invalid YouTube API Key. Please check your settings.', 'socialfeeds');
				}
			}
			
			if (isset($body['error']['message'])) {
				$api_message = $body['error']['message'];
				// If we haven't set a specific custom message yet, use the API message
				if ($error_msg === 'API request failed with code ' . $code) {
					$error_msg = sanitize_text_field(wp_unslash($api_message));
				}
			}

			// If it's a 404 and we are checking a channel/resource
			if($code === 404){
				$error_msg = __('Resource not found. Please check your ID or username.', 'socialfeeds');
			}

			wp_send_json_error($error_msg);
		}
		
		return json_decode(wp_remote_retrieve_body($resp), true);
	}

	static function extract_videos($raw_items, $type = 'search'){
		$videos = [];
		foreach($raw_items as $it){
			$vid = '';
			$snippet = isset($it['snippet']) ? $it['snippet'] : [];

			switch($type){
				case 'playlist':
					$vid = isset($it['snippet']['resourceId']['videoId']) ? $it['snippet']['resourceId']['videoId']: '';		
					break;
				default: // channel, search, live
					$vid = isset($it['id']['videoId']) ? $it['id']['videoId'] : '';			
			}

			if(empty($vid)){
				continue;
			}

			$videos[] = [
				'videoId' => sanitize_text_field($vid),
				'title' => isset($snippet['title']) ? sanitize_text_field($snippet['title']) : '',
				'description' => isset($snippet['description']) ? sanitize_textarea_field($snippet['description']) : '',
				'thumbnails' => isset($snippet['thumbnails']) ? \SocialFeeds\Util::sanitize_thumbnails($snippet['thumbnails']) : [],
			];
		}
		return $videos;
	}

	static function save_cache_settings(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}

		$duration['cache_duration'] = isset($_POST['cache_interval']) ? sanitize_text_field(wp_unslash($_POST['cache_interval'])) : '';

		update_option('socialfeeds_settings_option', $duration);

		wp_send_json_success(__('Cache settings saved successfully!', 'socialfeeds'));
	}

	static function clear_cache(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}

		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		$api_key = \SocialFeeds\Util::get_youtube_api_key($youtube_opts);
		$feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];

		foreach($feeds as $feed){
			$type = isset($feed['type']) ? $feed['type'] : 'channel';
			$input = isset($feed['input']) ? trim($feed['input']) : '';
			$settings = isset($feed['settings']) ? $feed['settings'] : [];
			
			$merged_settings = array_merge($youtube_opts, $settings);
			$limit = isset($merged_settings['youtube_videos_per_page']) ? intval($merged_settings['youtube_videos_per_page']) : 12;

			if(empty($input)) continue;

			// Channel feed type (Free feature)
			if($type === 'channel'){
				$channelId = $input;

				if(!preg_match('/^UC/', $channelId)){
					$cache_key_ch_res = 'socialfeeds_yt_channel' . md5($channelId);
					$ch_body = get_transient($cache_key_ch_res);
					if($ch_body && !empty($ch_body['items'][0]['id']['channelId'])){
						$channelId = $ch_body['items'][0]['id']['channelId'];
					}

					delete_transient($cache_key_ch_res);
				}

				// Clear channel videos cache - construct URL to match cache key
				$url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet',
					'channelId' => $channelId,
					'order' => 'date',
					'type' => 'video',
					'maxResults' => $limit,
				], 'https://www.googleapis.com/youtube/v3/search');
				delete_transient('socialfeeds_yt_channel_name' . md5($url));

				// Clear channel header cache
				delete_transient('socialfeeds_yt_headers' . md5($channelId));

				// Clear channel description cache
				delete_transient('socialfeeds_yt_desc' . md5($channelId));
			}
		}

		// Clear YouTube Pro caches
		do_action('socialfeeds_clear_youtube_cache');
		
		// Clear Instagram caches
		do_action('socialfeeds_clear_instagram_cache');

		wp_send_json_success(__('All caches cleared successfully!', 'socialfeeds'));
	}

	static function update_feed_name(){
		check_ajax_referer('socialfeeds_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds'));
		}

		$feed_id = isset($_POST['feed_id']) ? sanitize_text_field(wp_unslash($_POST['feed_id'])) : '';
		$platform = isset($_POST['platform']) ? sanitize_text_field(wp_unslash($_POST['platform'])) : '';
		$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

		if(empty($feed_id) || empty($platform) || empty($name)){
			wp_send_json_error(['message' => esc_html__('Missing required fields', 'socialfeeds')]);
		}

		if($platform === 'facebook'){
			$option_key = 'socialfeeds_facebook_option';
			$feeds_key = 'facebook_feeds';
		} else {
			$option_key = $platform === 'youtube' ? 'socialfeeds_youtube_option' : 'socialfeeds_instagram_option';
			$feeds_key = $platform === 'youtube' ? 'youtube_feeds' : 'instagram_feeds';
		}
		$options = get_option($option_key, []);
		$feeds = isset($options[$feeds_key]) ? $options[$feeds_key] : [];

		$updated = false;
		foreach($feeds as $k => $f){
			if(isset($f['id']) && (string)$f['id'] === (string)$feed_id){
				$feeds[$k]['name'] = $name;
				$updated = true;
				break;
			}
		}

		if(empty($updated)){
			wp_send_json_error(['message' => esc_html__('Feed not found', 'socialfeeds')]);
		}

		$options[$feeds_key] = $feeds;
		update_option($option_key, $options);

		wp_send_json_success([
			'message' => esc_html__('Feed name updated', 'socialfeeds'),
			'name' => $name,
			'feed_id' => $feed_id,
		]);
	}

}
