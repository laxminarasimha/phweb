<?php
namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class InstagramSettings{

	static function validate_token($access_token){

		if(empty($access_token)){
			wp_send_json_error(['message' => __('Access token is required', 'socialfeeds-pro')]);
		}

		// Basic Display API endpoints shouldn't forcefully use a version, and they only support basic fields
		$api_base = 'https://graph.instagram.com';

		$url = add_query_arg([
			'fields' => 'id,username,account_type,media_count',
			'access_token' => $access_token,
		], $api_base . '/me');

		$response = wp_remote_get($url, ['timeout' => '30', 'sslverify' => true]);

		if(is_wp_error($response)){
			/* translators: %s: error message returned by WordPress HTTP request */
			wp_send_json_error(['message' => sprintf( __( 'Request failed: %s', 'socialfeeds-pro' ), $response->get_error_message())]);
		}

		$http_code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if(!is_array($data)){
			wp_send_json_error(['message' => __('Invalid response received from Instagram.', 'socialfeeds-pro')]);
		}

		if(!empty($data['error'])){
			wp_send_json_error(['message' => isset($data['error']['message']) ? $data['error']['message'] : __('Instagram returned an unknown error.', 'socialfeeds-pro')]);
		}

		if($http_code !== 200){
			/* translators: %d: HTTP response code returned by the Instagram API */
			wp_send_json_error(['message' => sprintf( __( 'Instagram API returned HTTP %d.', 'socialfeeds-pro'), $http_code ) ]);
		}

		if(!isset($data['id']) || !isset($data['username'])){
			wp_send_json_error(['message' => __('Invalid Instagram account data.', 'socialfeeds-pro')]);
		}

		return [
			'id' => $data['id'],
			'username' => isset($data['username']) ? $data['username'] : '',
			'name' => isset($data['name']) ? $data['name'] : '',
			'biography' => isset($data['biography']) ? $data['biography'] : '',
			'profile_picture_url' => isset($data['profile_picture_url']) ? $data['profile_picture_url'] : '',
			'website' => isset($data['website']) ? $data['website'] : '',
			'followers_count' => isset($data['followers_count']) ? $data['followers_count'] : 0,
			'media_count' => isset($data['media_count']) ? $data['media_count'] : 0,
			'account_type' => isset($data['account_type']) ? $data['account_type'] : 'PERSONAL',
		];
	}

	static function validate_token_advanced($access_token, $instagram_user_id = ''){

		if(empty($access_token)){
			wp_send_json_error(['message' => __('Access token is required', 'socialfeeds-pro')]);
		}

		$ig_account = null;

		// Strategy 1: Try graph.facebook.com/me/accounts (works for Facebook User tokens)
		$pages_url = add_query_arg([
			'fields' => 'id,name,instagram_business_account{id,username,name,biography,profile_picture_url,followers_count,media_count}',
			'access_token' => $access_token,
		], 'https://graph.facebook.com/v18.0/me/accounts');

		$pages_response = wp_remote_get($pages_url, ['timeout' => 30, 'sslverify' => true]);

		if(!is_wp_error($pages_response)){
			$pages_body = wp_remote_retrieve_body($pages_response);
			$pages_data = json_decode($pages_body, true);

			// Check if we got a valid pages response (not an error)
			if(is_array($pages_data) && empty($pages_data['error']) && !empty($pages_data['data']) && is_array($pages_data['data'])){
				// Look for Instagram Business account linked to a page
				foreach($pages_data['data'] as $page){
					if(!empty($page['instagram_business_account'])){
						$ig_account = $page['instagram_business_account'];
						break;
					}
				}

				// Pages found but no IG Business account linked
				if(empty($ig_account) && empty($instagram_user_id)){
					wp_send_json_error(['message' => __('No Instagram Business account linked to any of your Facebook Pages. Please provide your Instagram Business User ID or link your Instagram Business account to a Facebook Page.', 'socialfeeds-pro')]);
				}
			}
		}

		// Strategy 2: If me/accounts didn't work, use the provided instagram_user_id
		// or try /me to get the IG user ID, then fetch IG account details
		if(empty($ig_account)){
			$resolved_ig_id = '';

			// If user provided an Instagram User ID, use it directly
			if(!empty($instagram_user_id)){
				$resolved_ig_id = sanitize_text_field(wp_unslash($instagram_user_id));
			} else {
				// Try /me to get the ID (may be an IG-scoped token)
				$me_url = add_query_arg([
					'fields' => 'id',
					'access_token' => $access_token,
				], 'https://graph.facebook.com/v18.0/me');

				$me_response = wp_remote_get($me_url, ['timeout' => 30, 'sslverify' => true]);

				if(!is_wp_error($me_response)){
					$me_data = json_decode(wp_remote_retrieve_body($me_response), true);
					if(is_array($me_data) && empty($me_data['error']) && !empty($me_data['id'])){
						$resolved_ig_id = $me_data['id'];
					}
				}
			}

			if(empty($resolved_ig_id)){
				wp_send_json_error(['message' => __('Could not determine Instagram User ID. Please provide your Instagram Business User ID in the field below.', 'socialfeeds-pro')]);
			}

			// Now fetch the actual IG account details using the resolved ID
			$field_sets = [
				'id,username,name,biography,profile_picture_url,followers_count,media_count',
				'id,username,name,profile_picture_url',
				'id,username,name',
				'id,username',
				'id',
			];

			$ig_data = null;
			$last_error = '';

			foreach($field_sets as $fields){
				$ig_url = add_query_arg([
					'fields' => $fields,
					'access_token' => $access_token,
				], 'https://graph.facebook.com/v18.0/' . $resolved_ig_id);

				$ig_response = wp_remote_get($ig_url, ['timeout' => 30, 'sslverify' => true]);

				if(is_wp_error($ig_response)){
					$last_error = $ig_response->get_error_message();
					continue;
				}

				$ig_http_code = wp_remote_retrieve_response_code($ig_response);
				$ig_body = wp_remote_retrieve_body($ig_response);

				$response_data = json_decode($ig_body, true);

				if(!is_array($response_data) || !empty($response_data['error'])){
					$last_error = isset($response_data['error']['message']) ? $response_data['error']['message'] : 'Unknown error';
					continue;
				}

				if($ig_http_code === 200 && !empty($response_data['id'])){
					$ig_data = $response_data;
					break;
				}
			}

			if(!empty($ig_data)){
				$ig_account = $ig_data;
			} else {
				wp_send_json_error(['message' => !empty($last_error) ? $last_error : __('Could not validate token with the API. Please check your Instagram User ID and access token.', 'socialfeeds-pro')]);
			}
		}

		if(empty($ig_account) || empty($ig_account['id'])){
			wp_send_json_error(['message' => __('Could not retrieve Instagram account data. Please verify your access token and permissions.', 'socialfeeds-pro')]);
		}

		// Username may not always be returned
		$username = isset($ig_account['username']) ? $ig_account['username'] : '';

		return [
			'id' => $ig_account['id'],
			'username' => $username,
			'name' => isset($ig_account['name']) ? $ig_account['name'] : '',
			'biography' => isset($ig_account['biography']) ? $ig_account['biography'] : '',
			'profile_picture_url' => isset($ig_account['profile_picture_url']) ? $ig_account['profile_picture_url'] : '',
			'website' => '',
			'followers_count' => isset($ig_account['followers_count']) ? intval($ig_account['followers_count']) : 0,
			'media_count' => isset($ig_account['media_count']) ? intval($ig_account['media_count']) : 0,
			'account_type' => 'BUSINESS',
		];
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

	static function refresh_long_lived_token($long_lived_token, $app_id, $app_secret){
		return self::exchange_for_long_lived_token($long_lived_token, $app_id, $app_secret);
	}

	static function scheduled_token_refresh(){
		$options = get_option('socialfeeds_instagram_option', []);
		if(empty($options['instagram_connected_accounts'])){
			return;
		}

		$updated = false;
		foreach($options['instagram_connected_accounts'] as $k => $acct){
			// Only refresh advanced tokens with app credentials
			if(isset($acct['token_type']) && $acct['token_type'] === 'advanced' && !empty($acct['app_id']) && !empty($acct['app_secret'])){
				
				$expires_at = isset($acct['expires_at']) ? intval($acct['expires_at']) : 0;
				$last_refresh = isset($acct['last_refresh']) ? intval($acct['last_refresh']) : 0;

				$should_refresh = false;
				if(time() - $last_refresh > DAY_IN_SECONDS){
					if($expires_at == 0 || ($expires_at - time() < 15 * DAY_IN_SECONDS)){
						$should_refresh = true;
					}
				}

				if($should_refresh){
					$refresh_resp = self::refresh_long_lived_token($acct['token'], $acct['app_id'], $acct['app_secret']);
					if(!empty($refresh_resp['access_token'])){
						$options['instagram_connected_accounts'][$k]['token'] = $refresh_resp['access_token'];
						$options['instagram_connected_accounts'][$k]['last_refresh'] = time();
						if(!empty($refresh_resp['expires_in'])){
							$options['instagram_connected_accounts'][$k]['expires_at'] = time() + intval($refresh_resp['expires_in']);
						}
						$updated = true;

						// If this was the main token, update it too
						if($options['instagram_access_token'] === $acct['token']){
							$options['instagram_access_token'] = $refresh_resp['access_token'];
						}
					}
				}
			}
		}

		if($updated){
			update_option('socialfeeds_instagram_option', $options);
		}
	}


	static function fetch_posts($access_token, $feed_type, $user_id, $limit = 12, $source_input = '', $ignore_cache = false, $account_type = ''){
		$res = \SocialFeedsPro\Util::fetch_feed_data($access_token, $limit, $feed_type, $source_input, $user_id, $ignore_cache, $account_type);
		return $res;
	}

	static function get_account_id($token, $username_or_id, $api){

		if(ctype_digit($username_or_id)){
			return $username_or_id;
		}

		$url = add_query_arg([
			'user_id' => 'me',
			'fields'  => 'id,username',
			'search_string' => $username_or_id,
			'access_token'  => $token,
		], "$api/ig_users_search");

		$data = self::make_api_request($url);
		return isset($data[0]['id']) ? $data[0]['id'] : false;
	}

	static function make_api_request($url, $max_items = 0){

		$response = wp_remote_get($url, [
			'timeout'   => 30,
			'sslverify'=> true,
		]);

		if(is_wp_error($response)){
			return [];
		}

		$data = json_decode(wp_remote_retrieve_body($response), true);

		if(empty($data) || ! empty($data['error'])){
			return [];
		}

		if(isset($data['data'])){
			return $max_items > 0 ? array_slice($data['data'], 0, $max_items) : $data['data'];
		}

		return [];
	}

	static function clear_cache(){
		$instagram_opts = get_option('socialfeeds_instagram_option', []);
		$feeds = isset($instagram_opts['instagram_feeds']) ? $instagram_opts['instagram_feeds'] : [];
		
		$tokens = [];
		$limits_config = [];

		$g_limit_d = isset($instagram_opts['instagram_number_posts_desktop']) ? intval($instagram_opts['instagram_number_posts_desktop']) : 6;
		$g_limit_m = isset($instagram_opts['instagram_number_posts_mobile']) ? intval($instagram_opts['instagram_number_posts_mobile']) : 6;
		
		// Global token
		if(!empty($instagram_opts['instagram_access_token'])){
			$token = $instagram_opts['instagram_access_token'];
			$tokens[] = $token;
			$limits_config[] = ['token' => $token, 'limit' => $g_limit_d];
			$limits_config[] = ['token' => $token, 'limit' => $g_limit_m];
		}

		foreach($feeds as $feed){
			// Token resolution
			$token = isset($feed['account_token']) ? $feed['account_token'] : (isset($instagram_opts['instagram_access_token']) ? $instagram_opts['instagram_access_token'] : '');

			if(!empty($token)){
				$tokens[] = $token;

				$settings = isset($feed['settings']) ? $feed['settings'] : [];
				// Merge global with feed specific
				$merged = array_merge($instagram_opts, $settings);

				$limit_d = isset($merged['instagram_number_posts_desktop']) ? intval($merged['instagram_number_posts_desktop']) : 6;
				$limit_m = isset($merged['instagram_number_posts_mobile']) ? intval($merged['instagram_number_posts_mobile']) : 6;

				$limits_config[] = ['token' => $token, 'limit' => $limit_d];
				$limits_config[] = ['token' => $token, 'limit' => $limit_m];
			}
		}
		
		// Clear account info caches
		$tokens = array_unique($tokens);
		foreach($tokens as $t){
			delete_transient('socialfeeds_ig_account_' . md5($t));
		}
		
		// Clear feed caches by limit/token
		$serialized = array_map('serialize', $limits_config);
		$unique = array_unique($serialized);
		$limits_config = array_map('unserialize', $unique);
		
		foreach($limits_config as $conf){
			delete_transient('socialfeeds_ig_feed_' . md5($conf['token'] . $conf['limit']));
		}

	}
}
