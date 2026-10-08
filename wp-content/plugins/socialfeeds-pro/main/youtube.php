<?php
namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class YouTube{

	static function init(){
		add_filter('socialfeeds_youtube_fetch_items', '\SocialFeedsPro\YouTube::fetch_items', 10, 4);
		add_filter('socialfeeds_youtube_video_items_details', '\SocialFeedsPro\YouTube::fetch_video_details', 10, 3);
		add_action('socialfeeds_youtube_load_more_pro', '\SocialFeedsPro\YouTube::handle_load_more', 10, 7);
		add_action('socialfeeds_clear_youtube_cache', '\SocialFeedsPro\YouTube::clear_cache');
	}

	static function api_request($url){
		$response = wp_remote_get($url);
		if(is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)){
			return null;
		}
		return json_decode(wp_remote_retrieve_body($response), true);
	}

	// Fetch items for Pro feed types (playlist, search, live-streams, single-videos)
	static function fetch_items($items, $feed, $api_key, $limit){
		$type = isset($feed['type']) ? $feed['type'] : '';
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';
		
		// Skip if it's a channel (Free feature)
		if($type === 'channel'){
			return $items;
		}

		switch($type){
			case 'playlist':
				$playlist_input = trim(isset($feed['input']) ? $feed['input'] : '');
				$playlist_id = $playlist_input;
				
				if(preg_match('/[?\&]list=([^\&\s]+)/i', $playlist_input, $m)){
					$playlist_id = $m[1];
				} elseif(false !== strpos($playlist_input, 'youtube.com') || false !== strpos($playlist_input, 'youtu.be')){
					$parts = wp_parse_url($playlist_input);
					if(!empty($parts['query'])){
						parse_str($parts['query'], $qs);
						if(!empty($qs['list'])){
							$playlist_id = $qs['list'];
						}
					}
				}

				$cache_key = 'socialfeeds_yt_playlist_' . md5($playlist_id . $limit);
				$data = get_transient($cache_key);
				
				if(false === $data){
					$url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'playlistId' => $playlist_id,
						'maxResults' => $limit,
					], 'https://www.googleapis.com/youtube/v3/playlistItems');
					
					$data = self::api_request($url);
					
					if(!empty($data)){
						set_transient($cache_key, $data, $cache_duration);
					}
				}
				
				if(!empty($data['items'])){
					foreach($data['items'] as $it){
						if(!empty($it['snippet']['resourceId']['videoId'])){
							$items[] = [
								'id' => ['videoId' => $it['snippet']['resourceId']['videoId']],
								'snippet' => $it['snippet'],
							];
						}
					}
				}
				break;

			case 'single-videos':
				$input = trim($feed['input']);
				$video_ids = array_filter(array_map('trim', explode(',', $input)));
				$ids_to_fetch = array_slice($video_ids, 0, $limit);
				$ids_str = implode(',', $ids_to_fetch);
				
				if(!empty($ids_str)){
					$cache_key = 'socialfeeds_yt_single_video' . md5($ids_str);
					$data = get_transient($cache_key);
					
					if(false === $data){
						$url = add_query_arg([
							'key' => $api_key,
							'part' => 'snippet',
							'id' => $ids_str,
						], 'https://www.googleapis.com/youtube/v3/videos');
						
						$data = self::api_request($url);
						if(!empty($data)){
							set_transient($cache_key, $data, $cache_duration);
						}
					}
					
					if(!empty($data['items'])){
						foreach($data['items'] as $item){
							$items[] = [
								'id' => ['videoId' => $item['id']],
								'snippet' => isset($item['snippet']) ? $item['snippet'] : [],
							];
						}
					}
				}
				break;

			case 'live-streams':
				$channelId = $feed['input'];
				$cache_key = 'socialfeeds_yt_live_streams' . md5($channelId . $limit . 'live');
				$data = get_transient($cache_key);

				if(false === $data){
					$url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'q' => $channelId,
						'eventType' => 'live',
						'type' => 'video',
						'maxResults' => $limit,
					], 'https://www.googleapis.com/youtube/v3/search');
					
					$data = self::api_request($url);

					if(!empty($data)){
						set_transient($cache_key, $data, $cache_duration);
					}
				}
				
				if(!empty($data['items'])){
					$items = array_merge($items, $data['items']);
				}
				break;

			case 'search':
				$search_term = $feed['input'];
				$url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet',
					'q' => $search_term,
					'type' => 'video',
					'maxResults' => $limit,
				], 'https://www.googleapis.com/youtube/v3/search');
				
				$cache_key = 'socialfeeds_yt_search' . md5($url);
				$data = get_transient($cache_key);
				
				if(false === $data){
					$data = self::api_request($url);
					if(!empty($data)){
						set_transient($cache_key, $data, $cache_duration);
					}
				}
				
				if(!empty($data['items'])){
					$items = array_merge($items, $data['items']);
				}
				break;
		}

		return $items;
	}

	//Handle preview for Pro feed types
	static function youtube_preview($feed_type, $api_key, $per_page, $pageToken){
	
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have permission to perform this action.', 'socialfeeds-pro'));
			return;
		}
		
		// This will be called via do_action, so we send JSON directly
		$items = [];
		$next_page_token = '';
		
	
		switch($feed_type){
			case 'playlist':
				$playlist_id = isset($_POST['playlist_id']) ? sanitize_text_field( wp_unslash( $_POST['playlist_id'] ) ) : '';
				if(!empty($playlist_id)){
					$query_args = [
						'key' => $api_key,
						'part' => 'snippet',
						'playlistId' => $playlist_id,
						'maxResults' => $per_page,
					];

					if(!empty($pageToken)){
						$query_args['pageToken'] = $pageToken;
					}

					$url = add_query_arg($query_args, 'https://www.googleapis.com/youtube/v3/playlistItems');
					$body = self::api_request($url);

					if(!empty($body)){
						$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
						foreach(isset($body['items']) ? $body['items'] : [] as $it) {
							$vid = isset($it['snippet']['resourceId']['videoId']) ? $it['snippet']['resourceId']['videoId'] : '';
							if(!empty($vid)){
								$s = isset($it['snippet']) ? $it['snippet'] : [];
								$items[] = [
									'videoId' => sanitize_text_field($vid),
									'title' => isset($s['title']) ? sanitize_text_field($s['title']) : '',
									'description' => isset($s['description']) ? sanitize_textarea_field($s['description']) : '',
									'thumbnails' => (isset($s['thumbnails']) && class_exists('\SocialFeeds\Util')) ? \SocialFeeds\Util::sanitize_thumbnails($s['thumbnails']) : (isset($s['thumbnails']) ? $s['thumbnails'] : []),
									'channelTitle' => isset($s['channelTitle']) ? sanitize_text_field($s['channelTitle']) : (isset($s['videoOwnerChannelTitle']) ? sanitize_text_field($s['videoOwnerChannelTitle']) : ''),
									'channelId' => isset($s['channelId']) ? sanitize_text_field($s['channelId']) : (isset($s['videoOwnerChannelId']) ? sanitize_text_field($s['videoOwnerChannelId']) : ''),
								];
							}
						}
					}
				}
				break;
			
			case 'search':
				$search_term = isset($_POST['search_term']) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';
				if(!empty($search_term)){
					$query_args = [
						'key' => $api_key,
						'part' => 'snippet',
						'type' => 'video',
						'q' => $search_term,
						'maxResults' => $per_page,
					];

					if(!empty($pageToken)){
						$query_args['pageToken'] = $pageToken;
					}

					$url = add_query_arg($query_args, 'https://www.googleapis.com/youtube/v3/search');
					$body = self::api_request($url);
					if(!empty($body)){
						$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
						foreach(isset($body['items']) ? $body['items'] : [] as $it){
							$vid = isset($it['id']['videoId']) ? $it['id']['videoId'] : '';
							if(!empty($vid)){
								$s = isset($it['snippet']) ? $it['snippet'] : [];
								$items[] = [
									'videoId' => sanitize_text_field($vid),
									'title' => isset($s['title']) ? sanitize_text_field($s['title']) : '',
									'description' => isset($s['description']) ? sanitize_textarea_field($s['description']) : '',
									'thumbnails' => (isset($s['thumbnails']) && class_exists('\SocialFeeds\Util')) ? \SocialFeeds\Util::sanitize_thumbnails($s['thumbnails']) : (isset($s['thumbnails']) ? $s['thumbnails'] : []),
									'channelTitle' => isset($s['channelTitle']) ? sanitize_text_field($s['channelTitle']) : '',
									'channelId' => isset($s['channelId']) ? sanitize_text_field($s['channelId']) : '',
								];
							}
						}
					}
				}
				break;
			
			case 'single-videos':
				$video_ids = isset($_POST['video_ids']) ? sanitize_text_field( wp_unslash( $_POST['video_ids'] ) ) : '';
				$ids = array_slice(array_filter(array_map('trim', explode(',', $video_ids))), 0, $per_page);
				if(!empty($ids)){
					$body = self::api_request(add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'id' => implode(',', $ids),
					], 'https://www.googleapis.com/youtube/v3/videos'));
					if(!empty($body)){
						foreach (isset($body['items']) ? $body['items'] : [] as $it) {
							$s = isset($it['snippet']) ? $it['snippet'] : [];
							$items[] = [
								'videoId' => isset($it['id']) ? sanitize_text_field($it['id']) : '',
								'title' => isset($s['title']) ? sanitize_text_field($s['title']) : '',
								'description' => isset($s['description']) ? sanitize_textarea_field($s['description']) : '',
								'thumbnails' => (isset($s['thumbnails']) && class_exists('\SocialFeeds\Util')) ? \SocialFeeds\Util::sanitize_thumbnails($s['thumbnails']) : (isset($s['thumbnails']) ? $s['thumbnails'] : []),
								'channelTitle' => isset($s['channelTitle']) ? sanitize_text_field($s['channelTitle']) : '',
								'channelId' => isset($s['channelId']) ? sanitize_text_field($s['channelId']) : '',
							];
						}
					}
				}
				break;

			case 'live-streams':
				$channel_id = isset($_POST['channel_id']) ? sanitize_text_field( wp_unslash( $_POST['channel_id'] ) ) : '';

				$query_args = [
					'key' => $api_key,
					'part' => 'snippet',
					'type' => 'video',
					'eventType' => 'live',
					'maxResults' => $per_page,
				];

				if(!empty($channel_id)){
					$query_args['q'] = $channel_id;
				}

				if(!empty($pageToken)){
					$query_args['pageToken'] = $pageToken;
				}

				$url = add_query_arg($query_args, 'https://www.googleapis.com/youtube/v3/search');
				$body = self::api_request($url);

				if(!empty($body)){
					
					$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';

					foreach(isset($body['items']) ? $body['items'] : [] as $it) {
						$vid = isset($it['id']['videoId']) ? $it['id']['videoId'] : '';
						if(!empty($vid)){
							$s = isset($it['snippet']) ? $it['snippet'] : [];
							$items[] = [
								'videoId' => sanitize_text_field($vid),
								'title' => isset($s['title']) ? sanitize_text_field($s['title']) : '',
								'description' => isset($s['description']) ? sanitize_textarea_field($s['description']) : '',
								'thumbnails' => (isset($s['thumbnails']) && class_exists('\SocialFeeds\Util')) ? \SocialFeeds\Util::sanitize_thumbnails($s['thumbnails']) : (isset($s['thumbnails']) ? $s['thumbnails'] : []),
								'is_live' => true,
								'channelTitle' => isset($s['channelTitle']) ? sanitize_text_field($s['channelTitle']) : '',
								'channelId' => isset($s['channelId']) ? sanitize_text_field($s['channelId']) : '',
							];
						}
					}
				}
				break;
		}

		// Fetch video details (statistics, contentDetails) before sending response
		if(!empty($items)){
			// Extract video IDs from items
			$video_ids = [];
			foreach($items as $item){
				if(!empty($item['videoId'])){
					$video_ids[] = $item['videoId'];
				}
			}
			
			// Fetch detailed video information if we have video IDs
			if(!empty($video_ids)){
				$ids_str = implode(',', array_unique($video_ids));
				$details_url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet,contentDetails,statistics',
					'id' => $ids_str,
				], 'https://www.googleapis.com/youtube/v3/videos');
				
				$details_body = self::api_request($details_url);
				
				// Merge detailed data into items
				if(!empty($details_body['items'])){
					$details_map = [];
					foreach($details_body['items'] as $detail){
						$vid = isset($detail['id']) ? $detail['id'] : '';
						if(!empty($vid)){
							$details_map[$vid] = $detail;
						}
					}
					
					// Update items with detailed information
					foreach($items as $idx => $item){
						$vid = isset($item['videoId']) ? $item['videoId'] : '';
						if($vid && isset($details_map[$vid])){
							$detail = $details_map[$vid];
							// Add statistics
							if(isset($detail['statistics'])){
								$items[$idx]['statistics'] = $detail['statistics'];
							}
							// Add contentDetails (includes duration)
							if(isset($detail['contentDetails'])){
								$items[$idx]['contentDetails'] = $detail['contentDetails'];
								$items[$idx]['duration'] = $detail['contentDetails']['duration'];
							}
							// Add publishedAt if not already present
							if(isset($detail['snippet']['publishedAt']) && empty($items[$idx]['publishedAt'])){
								$items[$idx]['publishedAt'] = $detail['snippet']['publishedAt'];
							}
						}
					}
				}
			}
			
			// Fetch channel information for header support
			$channel_info = null;
			$preview_channel_id = '';
			
			// Get channel ID from first item
			if(!empty($items[0]['channelId'])){
				$preview_channel_id = $items[0]['channelId'];
			}
			
			// Fetch channel details if we have a channel ID
			if(!empty($preview_channel_id)){
				$channel_url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet,brandingSettings',
					'id' => $preview_channel_id,
				], 'https://www.googleapis.com/youtube/v3/channels');
				
				$channel_body = self::api_request($channel_url);
				
				if(!empty($channel_body['items'][0]['snippet'])){
					$s = $channel_body['items'][0]['snippet'];
					$channel_info = [
						'id' => $preview_channel_id,
						'title' => isset($s['title']) ? $s['title'] : '',
						'thumbnail' => isset($s['thumbnails']['default']['url']) ? $s['thumbnails']['default']['url'] : '',
						'description' => isset($s['description']) ? $s['description'] : '',
						'bannerExternalUrl' => isset($channel_body['items'][0]['brandingSettings']['image']['bannerExternalUrl']) ? $channel_body['items'][0]['brandingSettings']['image']['bannerExternalUrl'] : '',
					];
					if(class_exists('\SocialFeeds\Util')){
						$channel_info = \SocialFeeds\Util::sanitize_channel_info($channel_info);
					}
				}
			}

			if(class_exists('\SocialFeeds\Util')){
				$items = \SocialFeeds\Util::sanitize_preview_items($items);
			}
			
			$response = ['items' => $items];
			if(!empty($next_page_token)){
				$response['nextPageToken'] = $next_page_token;
			}

			if(!empty($channel_info)){
				$response['channel'] = $channel_info;
			}
			
			wp_send_json_success($response);
		}
	}

	// Handle load more for Pro feed types
	static function handle_load_more($feed_type, $feed_input, $api_key, $limit, $pageToken, $extract_videos, $opts = []) {
		$items = [];
		$next_page_token = '';

		switch($feed_type){
			case 'playlist':
				$url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet',
					'playlistId' => $feed_input,
					'maxResults' => $limit,
					'pageToken' => $pageToken,
				], 'https://www.googleapis.com/youtube/v3/playlistItems');
				
				$body = self::api_request($url);
				if(!empty($body)){
					$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
					$items = $extract_videos($body['items'], 'playlist');
				}
				break;
			
			case 'search':
				$url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet',
					'q' => $feed_input,
					'type' => 'video',
					'maxResults' => $limit,
					'pageToken' => $pageToken,
				], 'https://www.googleapis.com/youtube/v3/search');
				
				$body = self::api_request($url);
				if(!empty($body)){
					$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
					$items = $extract_videos($body['items']);
				}
				break;
			
			case 'live-streams':
				$url = add_query_arg([
					'key' => $api_key,
					'part' => 'snippet',
					'channelId' => $feed_input,
					'eventType' => 'live',
					'type' => 'video',
					'maxResults' => $limit,
					'pageToken' => $pageToken,
				], 'https://www.googleapis.com/youtube/v3/search');
				
				$body = self::api_request($url);
				if(!empty($body)){
					$next_page_token = isset($body['nextPageToken']) ? $body['nextPageToken'] : '';
					$items = $extract_videos($body['items']);
				}
				break;
		}

		// Send response
		if(!empty($items)){
			$response = ['items' => $items];
			if(!empty($next_page_token)){
				$response['nextPageToken'] = $next_page_token;
			}

			if(class_exists('\SocialFeeds\Shortcodes') && is_array($opts)){
				$response['html'] = \SocialFeeds\Shortcodes::render_items($items, $opts);
			}

			wp_send_json_success($response);
		}
	}

	// Fetch details for video items (Snippet, Details, Stats)
	static function fetch_video_details($items, $api_key, $limit){
		if(empty($items)) return $items;
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';

		$video_ids = [];
		foreach($items as $it){
			if(isset($it['videoId'])){
				$video_ids[] = $it['videoId'];
			} elseif(isset($it['id']['videoId'])){
				$video_ids[] = $it['id']['videoId'];
			} elseif(isset($it['id']) && is_string($it['id'])){
				$video_ids[] = $it['id'];
			}
		}

		$video_ids = array_filter(array_unique($video_ids));
		if(empty($video_ids)) return $items;

		$ids_to_fetch = array_slice($video_ids, 0, $limit);
		$ids_str = implode(',', $ids_to_fetch);
		
		$cache_key = 'socialfeeds_yt_details_' . md5($ids_str);
		$details_body = get_transient($cache_key);
		
		if(false === $details_body){
			$url = add_query_arg([
				'key' => $api_key,
				'part' => 'snippet,contentDetails,statistics',
				'id' => $ids_str,
			], 'https://www.googleapis.com/youtube/v3/videos');
			
			$details_body = self::api_request($url);
			if(!empty($details_body)){
				set_transient($cache_key, $details_body, $cache_duration);
			}
		}

		if(!empty($details_body['items'])){
			return $details_body['items'];
		}

		return $items;
	}

	static function clear_cache(){
		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		$api_key = class_exists('\socialfeeds\Util') ? \socialfeeds\Util::get_youtube_api_key($youtube_opts) : (isset($youtube_opts['youtube_api_key']) ? $youtube_opts['youtube_api_key'] : '');
		$feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];

		foreach($feeds as $feed){
			$type = isset($feed['type']) ? $feed['type'] : '';
			$input = isset($feed['input']) ? trim($feed['input']) : '';
			$settings = isset($feed['settings']) ? $feed['settings'] : [];
			
			$merged_settings = array_merge($youtube_opts, $settings);
			$limit = isset($merged_settings['youtube_videos_per_page']) ? intval($merged_settings['youtube_videos_per_page']) : 12;

			if(empty($input) || empty($type)) continue;

			switch($type){
				case 'playlist':
					// Extract playlist ID from input (URL or ID)
					$playlist_id = $input;
					if(preg_match('/[?&]list=([^&\s]+)/i', $input, $m)){
						$playlist_id = $m[1];
					} elseif(false !== strpos($input, 'youtube.com') || false !== strpos($input, 'youtu.be')){
						$parts = wp_parse_url($input);
						if(!empty($parts['query'])){
							parse_str($parts['query'], $qs);
							if(!empty($qs['list'])){
								$playlist_id = $qs['list'];
							}
						}
					}
					delete_transient('socialfeeds_yt_playlist_' . md5($playlist_id . $limit));
					break;

				case 'single-videos':
					$video_ids = array_filter(array_map('trim', explode(',', $input)));
					$ids_to_fetch = array_slice($video_ids, 0, $limit);
					$ids_str = implode(',', $ids_to_fetch);
					if(!empty($ids_str)){
						delete_transient('socialfeeds_yt_single_video' . md5($ids_str));
						// Also clear video details cache
						delete_transient('socialfeeds_yt_details_' . md5($ids_str));
					}
					break;

				case 'live-streams':
					$channelId = $input;
					delete_transient('socialfeeds_yt_live_streams' . md5($channelId . $limit . 'live'));
					break;

				case 'search':
					$search_term = $input;
					$url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'q' => $search_term,
						'type' => 'video',
						'maxResults' => $limit,
					], 'https://www.googleapis.com/youtube/v3/search');
					delete_transient('socialfeeds_yt_search' . md5($url));
					break;
			}
		}
	}
}
