<?php
namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}
// such as instgram have cutomize BUTTON TEXT option for load more add give this customize text for facebook too
class Facebook{


	public static $last_after_cursor = null;
	public static $last_has_next = false;

	static function init(){
		add_filter('socialfeeds_facebook_fetch_items', '\SocialFeedsPro\Facebook::fetch_items', 10, 5);
		add_action('socialfeeds_clear_facebook_cache', '\SocialFeedsPro\Facebook::clear_cache');
	}

	static function api_request($url){
		$response = wp_remote_get($url, ['timeout' => 30, 'sslverify' => true]);
		if(is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)){
			return null;
		}
		return json_decode(wp_remote_retrieve_body($response), true);
	}

	// $after_cursor: Facebook cursor string for load-more pagination (empty = first page)
	static function fetch_items($items, $feed, $token, $limit, $after_cursor = ''){
		$type = isset($feed['type']) ? $feed['type'] : 'timeline';
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';

		$page_id = isset($feed['input']) ? trim($feed['input']) : '';
		if(empty($page_id) || empty($token)){
			return $items;
		}
		
		$page_token = self::get_page_access_token($page_id, $token);
		if(empty($page_token)){
			return $items; // token exchange failed
		}

		$token = $page_token;

		// Include cursor in cache key so each load-more page is cached separately
		$cache_key = 'socialfeeds_fb_feed_' . md5($page_id . $limit . $type . $token . $after_cursor);
		$data = get_transient($cache_key);
		
		if(false === $data){
			$fields = '';
			$endpoint = '';
			
			switch($type){
				case 'albums':
					$fields = 'id,name,description,created_time,link,cover_photo{id,picture,source},count';
					$endpoint = 'albums';
					break;
				case 'events':
					$fields = 'id,name,description,start_time,end_time,place,cover,ticket_uri';
					$endpoint = 'events';
					break;
				case 'timeline':
				default:
					$fields = 'id,message,created_time,full_picture,permalink_url,status_type,attachments{media_type,media,type,target},from{name,picture},likes.limit(0).summary(true).as(like_count),comments.limit(0).summary(true).as(comment_count)';
					$endpoint = 'posts';
					break;
			}
			
			$query_args = [
				'fields'       => $fields,
				'limit'        => $limit,
				'access_token' => $token,
			];

			// Use Facebook's cursor-based pagination — numeric offset causes repeated posts
			if(!empty($after_cursor)){
				$query_args['after'] = $after_cursor;
			}

			$url = add_query_arg($query_args, "https://graph.facebook.com/v18.0/{$page_id}/{$endpoint}");
			
			$data = self::api_request($url);
			
			if(!empty($data)){
				set_transient($cache_key, $data, $cache_duration);
			}
		}
		
		if(!empty($data['data'])){
			foreach($data['data'] as $post){
				if($type === 'albums'){
					$items[] = [
						'id' => isset($post['id']) ? $post['id'] : '',
						'message' => isset($post['name']) ? $post['name'] : '',
						'description' => isset($post['description']) ? $post['description'] : '',
						'created_time' => isset($post['created_time']) ? $post['created_time'] : '',
						'full_picture' => isset($post['cover_photo']['source']) ? $post['cover_photo']['source'] : (isset($post['cover_photo']['picture']) ? $post['cover_photo']['picture'] : ''),
						'permalink_url' => isset($post['link']) ? $post['link'] : '',
						'count' => isset($post['count']) ? $post['count'] : 0,
						'type' => 'album'
					];
				} elseif($type === 'events'){
					$items[] = [
						'id' => isset($post['id']) ? $post['id'] : '',
						'message' => isset($post['name']) ? $post['name'] : '',
						'description' => isset($post['description']) ? $post['description'] : '',
						'created_time' => isset($post['start_time']) ? $post['start_time'] : '',
						'full_picture' => isset($post['cover']['source']) ? $post['cover']['source'] : '',
						'permalink_url' => 'https://www.facebook.com/events/' . $post['id'],
						'start_time' => isset($post['start_time']) ? $post['start_time'] : '',
						'end_time' => isset($post['end_time']) ? $post['end_time'] : '',
						'place' => isset($post['place']) ? $post['place'] : null,
						'type' => 'event'
					];
				} else {
					// Check status_type first, then check attachments media_type
					$is_video = false;
					if( isset($post['status_type']) && $post['status_type'] === 'added_video' ){
						$is_video = true;
					} elseif( isset($post['attachments']['data'][0]['media_type']) && $post['attachments']['data'][0]['media_type'] === 'video' ){
						$is_video = true;
					} elseif( isset($post['attachments']['data'][0]['type']) && $post['attachments']['data'][0]['type'] === 'video_inline' ){
						$is_video = true;
					}

					$items[] = [
						'id' => isset($post['id']) ? $post['id'] : '',
						'message' => isset($post['message']) ? $post['message'] : '',
						'created_time' => isset($post['created_time']) ? $post['created_time'] : '',
						'full_picture' => isset($post['full_picture']) ? $post['full_picture'] : '',
						'permalink_url' => isset($post['permalink_url']) ? $post['permalink_url'] : '',
						'attachments' => isset($post['attachments']) ? $post['attachments'] : [],
						'from' => isset($post['from']) ? $post['from'] : [],
						'like_count' => isset($post['like_count']['summary']['total_count']) ? $post['like_count']['summary']['total_count'] : 0,
						'comment_count'=> isset($post['comment_count']['summary']['total_count']) ? $post['comment_count']['summary']['total_count'] : 0,
						'type' => $is_video ? 'video' : 'timeline',
					];
				}
			}
		}

		// Store the cursor for the next page so ajax.php can return it to the frontend
		self::$last_after_cursor = isset($data['paging']['cursors']['after']) ? $data['paging']['cursors']['after'] : null;
		self::$last_has_next = !empty($data['paging']['next']);

		return $items;
	}

	static function get_page_access_token($page_id, $user_token){
		$url = add_query_arg([
			'access_token' => $user_token,
		], "https://graph.facebook.com/v21.0/{$page_id}?fields=access_token");

		$data = self::api_request($url);
		return !empty($data['access_token']) ? $data['access_token'] : null;
	}

	static function validate_token($access_token, $page_id = ''){
		if(empty($access_token)){
			return ['error' => __('Access token is required', 'socialfeeds-pro')];
		}

		$api_url = "https://graph.facebook.com/v18.0/me?fields=id,name,picture.width(500).height(500)";

		if(!empty($page_id)){
			$api_url = "https://graph.facebook.com/v18.0/{$page_id}?fields=id,name,picture.width(500).height(500)";
		}

		$response = wp_remote_get($api_url, [
			'timeout' => 30,
			'sslverify' => true,
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			]
		]);

		if(is_wp_error($response)){
			return ['error' => $response->get_error_message()];
		}

		$data = json_decode(wp_remote_retrieve_body($response), true);

		if(!empty($data['error'])){
			return ['error' => $data['error']['message']];
		}

		return $data;
	}

	static function clear_cache(){
		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$feeds = isset($fb_opts['facebook_feeds']) ? $fb_opts['facebook_feeds'] : [];
		
		foreach($feeds as $feed){
			$token = isset($feed['account_token']) ? $feed['account_token'] : (isset($fb_opts['facebook_access_token']) ? $fb_opts['facebook_access_token'] : '');
			$limit = isset($feed['settings']['facebook_posts_per_page']) ? intval($feed['settings']['facebook_posts_per_page']) : (isset($feed['settings']['facebook_number_posts_desktop']) ? intval($feed['settings']['facebook_number_posts_desktop']) : 12);
			$page_id = isset($feed['input']) ? trim($feed['input']) : '';
			$type = isset($feed['type']) ? $feed['type'] : 'timeline';

			if(!empty($page_id) && !empty($token)){
				delete_transient('socialfeeds_fb_feed_' . md5($page_id . $limit . $type . $token . 0));
			}
		}
	}

	static function exchange_for_long_lived_token($short_lived_token, $app_id, $app_secret){
		$url = add_query_arg([
			'grant_type' => 'fb_exchange_token',
			'client_id' => $app_id,
			'client_secret' => $app_secret,
			'fb_exchange_token' => $short_lived_token,
		], 'https://graph.facebook.com/v18.0/oauth/access_token');

		$response = wp_remote_get($url, ['timeout' => 30, 'sslverify' => true]);

		if(is_wp_error($response)){
			return ['error' => $response->get_error_message()];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if(!empty($data['error'])){
			return ['error' => isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error during token exchange'];
		}

		return $data;
	}

	static function scheduled_token_refresh(){
		$options = get_option('socialfeeds_facebook_option', []);
		if(empty($options['facebook_connected_accounts'])){
			return;
		}

		$updated = false;
		foreach($options['facebook_connected_accounts'] as $k => $acct){
			// Both Advanced and Basic can support long-lived tokens if app credentials exist
			if(!empty($acct['app_id']) && !empty($acct['app_secret'])){
				
				$expires_at = isset($acct['expires_at']) ? intval($acct['expires_at']) : 0;
				$last_refresh = isset($acct['last_refresh']) ? intval($acct['last_refresh']) : 0;

				$should_refresh = false;
				if(time() - $last_refresh > DAY_IN_SECONDS){
					if($expires_at == 0 || ($expires_at - time() < 15 * DAY_IN_SECONDS)){
						$should_refresh = true;
					}
				}

				if($should_refresh){
					$refresh_resp = self::exchange_for_long_lived_token($acct['token'], $acct['app_id'], $acct['app_secret']);
					if(!empty($refresh_resp['access_token'])){
						$options['facebook_connected_accounts'][$k]['token'] = $refresh_resp['access_token'];
						$options['facebook_connected_accounts'][$k]['last_refresh'] = time();
						if(!empty($refresh_resp['expires_in'])){
							$options['facebook_connected_accounts'][$k]['expires_at'] = time() + intval($refresh_resp['expires_in']);
						}
						$updated = true;

						if(isset($options['facebook_access_token']) && $options['facebook_access_token'] === $acct['token']){
							$options['facebook_access_token'] = $refresh_resp['access_token'];
						}
					}
				}
			}
		}

		if($updated){
			update_option('socialfeeds_facebook_option', $options);
		}
	}
}