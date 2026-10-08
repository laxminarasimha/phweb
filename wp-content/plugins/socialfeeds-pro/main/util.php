<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class Util{

	static function get_account_info($token, $token_type = 'basic'){
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';
		
		$cache_key = 'socialfeeds_ig_account_' . md5($token);
		$cached_info = get_transient($cache_key);
		
		if($cached_info !== false){
			return $cached_info;
		}

		// Explicitly check the token prefix to prevent mismatches
		if(strpos($token, 'EA') === 0){
			$token_type = 'advanced';
		} elseif(strpos($token, 'IG') === 0){
			$token_type = 'basic';
		} else {
			// If no token_type provided, try to determine from saved options
			if(empty($token_type) || $token_type === 'basic'){
				$options = get_option('socialfeeds_instagram_option', []);
				if(!empty($options['instagram_token_type'])){
					$token_type = $options['instagram_token_type'];
				}
			}
		}

		$data = [];

		if($token_type === 'advanced'){
			// Strategy 1: Try me/accounts (Facebook User tokens)
			$pages_url = add_query_arg([
				'fields' => 'id,name,instagram_business_account{id,username,name,biography,profile_picture_url,followers_count,media_count}',
				'access_token' => $token,
			], 'https://graph.facebook.com/v18.0/me/accounts');

			$response = wp_remote_get($pages_url);

			if(!is_wp_error($response)){
				$pages_data = json_decode(wp_remote_retrieve_body($response), true);
				if(is_array($pages_data) && empty($pages_data['error']) && !empty($pages_data['data']) && is_array($pages_data['data'])){
					foreach($pages_data['data'] as $page){
						if(!empty($page['instagram_business_account'])){
							$data = $page['instagram_business_account'];
							break;
						}
					}
				}
			}

			if(empty($data)){
				// Try to find the saved Instagram account ID for this token
				$ig_account_id = '';
				$options = get_option('socialfeeds_instagram_option', []);
				$connected_accounts = isset($options['instagram_connected_accounts']) ? $options['instagram_connected_accounts'] : [];
				foreach($connected_accounts as $acct){
					if(isset($acct['token']) && $acct['token'] === $token && !empty($acct['id'])){
						$ig_account_id = $acct['id'];
						break;
					}
				}

				// Fallback to global account_id
				if(empty($ig_account_id) && !empty($options['instagram_account_id'])){
					$ig_account_id = $options['instagram_account_id'];
				}

				if(!empty($ig_account_id)){
					$field_sets = [
						'id,username,name,biography,profile_picture_url,followers_count,media_count',
						'id,username,name,profile_picture_url',
						'id,username,name',
						'id,username',
						'id',
					];

					foreach($field_sets as $fields){
						$ig_url = add_query_arg([
							'fields' => $fields,
							'access_token' => $token,
						], 'https://graph.facebook.com/v18.0/' . $ig_account_id);

						$ig_response = wp_remote_get($ig_url);

						if(!is_wp_error($ig_response)){
							$ig_data = json_decode(wp_remote_retrieve_body($ig_response), true);
							if(is_array($ig_data) && empty($ig_data['error']) && !empty($ig_data['id'])){
								$data = $ig_data;
								break;
							}
						}
					}
				}
			}
		} else {
			// For Basic tokens, use Instagram Graph API
			$url = add_query_arg([
				'fields' => 'id,username,account_type,media_count',
				'access_token' => $token,
			], 'https://graph.instagram.com/me');

			$response = wp_remote_get($url);

			if(!is_wp_error($response)){
				$data = json_decode(wp_remote_retrieve_body($response), true);
			}
		}

		$info = (!empty($data) && empty($data['error'])) ? $data : [];
		
		if(!empty($info)){
			set_transient($cache_key, $info, $cache_duration);
		}
		
		return $info;
	}

	static function get_facebook_account_info($token, $page_id){
		if(empty($token) || empty($page_id)){
			return [];
		}

		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';
		
		$cache_key = 'socialfeeds_fb_account_' . md5($token . $page_id);
		$cached_info = get_transient($cache_key);
		
		if($cached_info !== false){
			return $cached_info;
		}

		$url = add_query_arg([
			'fields' => 'id,name,about,fan_count,followers_count,picture.width(500).height(500){url},cover{source}',
			'access_token' => $token,
		], 'https://graph.facebook.com/v18.0/' . $page_id);

		$response = wp_remote_get($url);
		$data = [];

		if(!is_wp_error($response)){
			$data = json_decode(wp_remote_retrieve_body($response), true);
		}

		$info = (!empty($data) && empty($data['error'])) ? $data : [];
		
		if(!empty($info)){
			set_transient($cache_key, $info, $cache_duration);
		}
		
		return $info;
	}

	static function fetch_feed_data($access_token, $limit = 12, $feed_type = 'username', $source_input = '', $user_id = '', $ignore_cache = false, $account_type = ''){
		$token = trim(str_replace('Bearer', '', $access_token));
		$limit = max(1, intval($limit));
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';
		$cache_key = 'socialfeeds_ig_feed_' . md5($token . $limit . $feed_type . $source_input);
		$cached_data = get_transient($cache_key);

		if(!$ignore_cache && $cached_data !== false){
			return $cached_data;
		}

		$api = 'https://graph.instagram.com';

		$endpoint_base = "";
		$api_url_base = "";

		// Ensure reliable check based on token string itself. Basic tokens start with IG, Advanced start with EA.
		$is_basic_token = (strpos($token, 'IG') === 0);
		$is_business_token = (strpos($token, 'EA') === 0) || (!$is_basic_token && in_array(strtoupper($account_type), ['BUSINESS', 'CREATOR', 'MEDIA_CREATOR'], true));
		$is_business_account = $is_business_token;

		// Always use connected user ID for the API endpoint to ensure we have access to the data.
		// For Tagged feeds, we filter by source_input (author) locally if provided.
		$resolved_user_id = $user_id;

		if($feed_type === 'hashtag' || $feed_type === 'manual'){
			if(empty($resolved_user_id) && !$is_basic_token){
				return ['data' => [], 'error' => 'Instagram User ID is missing. This feed type requires a Business/Creator account ID.'];
			}
		}

		// Handle multiple hashtags
		$hashtags = [];
		if($feed_type === 'hashtag' && !empty($source_input)) {
			// Parse multiple hashtags (comma-separated, space-separated, or newline-separated)
			$source_input = str_replace([',', "\n"], ' ', $source_input);
			$tags = array_filter(array_map('trim', explode(' ', $source_input)));
			
			foreach($tags as $tag) {
				$tag = ltrim($tag, '#');
				if(!empty($tag)) {
					$hashtags[] = strtolower($tag);
				}
			}
			$hashtags = array_unique($hashtags);
		}

		// Handle Tagged Posts (feed_type 'manual') - requires Business/Creator account
		if($feed_type === 'manual' && $is_business_token && !empty($resolved_user_id)){
			$tagged_fields = 'id,caption,media_type,media_url,permalink,timestamp';
			$tagged_found = false;
			
			// Try the /tags endpoint via graph.instagram.com first (works with IGAA + right permissions)
			$tags_url = add_query_arg([
				'fields' => $tagged_fields,
				'limit' => min($limit, 25),
				'access_token' => $token,
			], "{$api}/{$resolved_user_id}/tags");
			
			$tags_response = wp_remote_get($tags_url, ['timeout' => 15, 'sslverify' => true]);
			
			if(!is_wp_error($tags_response)){
				$tags_data = json_decode(wp_remote_retrieve_body($tags_response), true);
				if(!empty($tags_data['data'])){
					$endpoint_base = "{$api}/{$resolved_user_id}/tags";
					$api_url_base = 'graph.instagram.com';
					$tagged_found = true;
				}
			}
			
			// Also try graph.facebook.com (works with EA tokens)
			if(!$tagged_found){
				$fb_tags_url = add_query_arg([
					'fields' => $tagged_fields,
					'limit' => min($limit, 25),
					'access_token' => $token,
				], "https://graph.facebook.com/v18.0/{$resolved_user_id}/tags");
				
				$fb_tags_response = wp_remote_get($fb_tags_url, ['timeout' => 15, 'sslverify' => true]);
				
				if(!is_wp_error($fb_tags_response)){
					$fb_tags_data = json_decode(wp_remote_retrieve_body($fb_tags_response), true);
					if(!empty($fb_tags_data['data'])){
						$endpoint_base = "https://graph.facebook.com/v18.0/{$resolved_user_id}/tags";
						$api_url_base = 'graph.facebook.com';
						$tagged_found = true;
					}
				}
			}
			
			// If no tagged posts found, return a helpful error
			if(empty($tagged_found)){
				return [
					'data' => [], 
					'paging' => null, 
					'error' => __('No tagged posts could be retrieved. Your access token may be missing the "instagram_business_manage_comments" permission which is required for tagged posts. Please regenerate your token with this permission included, or check that your account actually has posts where other users have tagged you.', 'socialfeeds-pro')
				];
			}
		}

		$is_hashtag_fallback = false;
		if($feed_type === 'hashtag' && ($api_url_base !== 'graph.facebook.com' || count($hashtags) > 1)){
			$is_hashtag_fallback = true;
			// For business/EA tokens, use graph.facebook.com; for basic tokens, use graph.instagram.com
			if($is_business_token && !empty($resolved_user_id)){
				$endpoint_base = "https://graph.facebook.com/v18.0/{$resolved_user_id}/media";
				$api_url_base = 'graph.facebook.com';
			} else {
				$endpoint_base = "$api/me/media";
				$api_url_base = 'graph.instagram.com';
			}
		}

		$api_fallback_queue = [];
		if(empty($endpoint_base)){
			if($is_business_token && !empty($resolved_user_id)){
				$api_fallback_queue[] = [
					"url" => "https://graph.facebook.com/v18.0/{$resolved_user_id}/media", 
					"base" => "graph.facebook.com", 
					// Graph API allows like/comments
					"fields" => "id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count"
				];
			}
			// Only add graph.instagram.com endpoints for non-business (basic) tokens
			// EA tokens (business/advanced) cannot be used with graph.instagram.com
			if(!$is_business_token){
				// Try the new Instagram API
				$api_fallback_queue[] = [
					"url" => "https://graph.instagram.com/v18.0/me/media", 
					"base" => "graph.instagram.com", 
					"fields" => "id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count"
				];
				// Fallback to basic display API (no likes/comments allowed)
				$api_fallback_queue[] = [
					"url" => "https://graph.instagram.com/me/media", 
					"base" => "graph.instagram.com", 
					"fields" => "id,caption,media_type,media_url,thumbnail_url,permalink,timestamp"
				];
			}
		} else {
			// Hashtag or Tagged endpoints are already resolved
			$fields = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count';
			if($feed_type === 'manual') {
				$fields = 'id,caption,media_type,media_url,permalink,timestamp';
			} elseif ($feed_type === 'hashtag') {
				$fields = 'id,caption,media_type,media_url,permalink,timestamp';
			}
			$api_fallback_queue[] = ["url" => $endpoint_base, "base" => $api_url_base, "fields" => $fields];
		}

		// Determine limit per page logic
		$all_posts = [];
		$after = null;
		$last_paging = null;
		$per_page = 25; 
		$fetch_target = $limit;
		$page_depth = 0;
		$max_pages = 10; 
		
		$current_api = null; // Stores the working API config

		while(count($all_posts) < $fetch_target && $page_depth < $max_pages){
			
			// If we haven't locked in a working API yet, try the queue
			if(empty($current_api) && count($api_fallback_queue) > 0){
				$current_api = array_shift($api_fallback_queue);
			}

			if(empty($current_api)){
				// No more fallbacks available
				break;
			}

			$args = [
				'fields' => $current_api['fields'],
				'limit'  => $per_page,
				'access_token' => $token,
			];
			
			if($feed_type === 'hashtag' && $resolved_user_id && $current_api['base'] === 'graph.facebook.com') {
				$args['user_id'] = $resolved_user_id;
			}

			if($after){
				$args['after'] = $after; 
			}

			$url = add_query_arg($args, $current_api['url']);
			$response = wp_remote_get($url, ['timeout' => 30, 'sslverify' => true]);

			if(is_wp_error($response)){
				// Total HTTP failure - try next in queue
				$current_api = null; 
				continue; 
			}

			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);

			// If API returns an error, discard this endpoint and try next
			if(!empty($data['error'])){
				$current_api = null;
				// If we have no more fallbacks, return the last error
				if(empty($api_fallback_queue)){
					return ['data' => $all_posts, 'error' => $data['error']['message']];
				}
				continue;
			}

			if(empty($data) || empty($data['data'])){
				if (!empty($data['error'])) {
					return ['data' => $all_posts, 'error' => $data['error']['message']];
				}
				break;
			}

			$items = $data['data'];
			
			// Tagged posts filtering by specific account (author)
			if($feed_type === 'manual' && !empty($source_input)){
				$filtered_items = [];
				foreach($items as $item){
					$owner_id = isset($item['owner']['id']) ? (string)$item['owner']['id'] : '';
					$owner_username = isset($item['owner']['username']) ? strtolower($item['owner']['username']) : '';
					
					$match = false;
					if(ctype_digit($source_input)){
						if($owner_id === (string)$source_input) $match = true;
					} else {
						if($owner_username === strtolower($source_input)) $match = true;
					}
					
					if($match){
						$filtered_items[] = $item;
					}
				}
				$items = $filtered_items;
			}

			// Enhanced hashtag filtering for multiple hashtags
			if($feed_type === 'hashtag' && !empty($hashtags)){
				$filtered_items = [];
				foreach($items as $item){
					$caption = isset($item['caption']) ? strtolower($item['caption']) : '';
					
					// Check if the post contains ANY of the requested hashtags
					$matches = false;
					foreach($hashtags as $tag) {
						// Check for hashtag with # symbol or without
						if(strpos($caption, '#' . $tag) !== false || 
						strpos($caption, ' ' . $tag) !== false || 
						strpos($caption, $tag . ' ') !== false ||
						preg_match('/\b' . preg_quote($tag, '/') . '\b/i', $caption)) {
							$matches = true;
							break;
						}
					}
					
					if($matches || empty($hashtags)){
						$filtered_items[] = $item;
					}
				}
				$items = $filtered_items;
			}

			$all_posts = array_merge($all_posts, $items);

			$last_paging = isset($data['paging']) ? $data['paging'] : null;

			if(count($all_posts) >= $fetch_target){
				break; 
			}

			$page_depth++;

			if(!isset($data['paging']['cursors']['after'])){
				$last_paging = null; // No more pages
				break; 
			}
			$after = $data['paging']['cursors']['after'];
		}

		$result_posts = array_slice($all_posts, 0, $limit);

		$result = [
			'data' => $result_posts,
			'paging' => $last_paging
		];

		if(!empty($result_posts)){
			set_transient($cache_key, $result, $cache_duration);
		}

		return $result;
	}
}