<?php

namespace SocialFeeds;

if(!defined('ABSPATH')){
	exit;
}

class Util{

	//Sanitize YouTube channel metadata before it is sent to the admin preview.
	static function sanitize_channel_info($channel_info){
		if(!is_array($channel_info)){
			return [];
		}

		return [
			'id' => isset($channel_info['id']) ? sanitize_text_field($channel_info['id']) : '',
			'title' => isset($channel_info['title']) ? sanitize_text_field($channel_info['title']) : '',
			'thumbnail' => isset($channel_info['thumbnail']) ? esc_url_raw($channel_info['thumbnail']) : '',
			'description' => isset($channel_info['description']) ? sanitize_textarea_field($channel_info['description']) : '',
			'bannerExternalUrl' => isset($channel_info['bannerExternalUrl']) ? esc_url_raw($channel_info['bannerExternalUrl']) : '',
			'subscriberCount' => isset($channel_info['subscriberCount']) ? absint($channel_info['subscriberCount']) : 0,
		];
	}

	//Sanitize a YouTube thumbnails map (size => {url,width,height}). 
	static function sanitize_thumbnails($thumbnails){
		if(!is_array($thumbnails)){
			return [];
		}

		$clean = [];
		foreach($thumbnails as $size => $data){
			if(!is_array($data)){
				continue;
			}

			$entry = [];
			if(isset($data['url'])){
				$entry['url'] = esc_url_raw($data['url']);
			}
			if(isset($data['width'])){
				$entry['width'] = absint($data['width']);
			}
			if(isset($data['height'])){
				$entry['height'] = absint($data['height']);
			}

			$clean[sanitize_key($size)] = $entry;
		}

		return $clean;
	}

	//Sanitize YouTube preview/load-more items (title, description, ids, thumbnail URLs).
	static function sanitize_preview_items($items){
		if(!is_array($items)){
			return [];
		}

		foreach($items as $i => $item){
			if(!is_array($item)){
				continue;
			}

			if(isset($item['title'])){
				$items[$i]['title'] = sanitize_text_field($item['title']);
			}
			if(isset($item['description'])){
				$items[$i]['description'] = sanitize_textarea_field($item['description']);
			}
			if(isset($item['videoId'])){
				$items[$i]['videoId'] = sanitize_text_field($item['videoId']);
			}
			if(isset($item['channelTitle'])){
				$items[$i]['channelTitle'] = sanitize_text_field($item['channelTitle']);
			}
			if(isset($item['channelId'])){
				$items[$i]['channelId'] = sanitize_text_field($item['channelId']);
			}
			if(!empty($item['thumbnails']) && is_array($item['thumbnails'])){
				$items[$i]['thumbnails'] = \SocialFeeds\Util::sanitize_thumbnails($item['thumbnails']);
			}
			if(isset($item['snippet']) && is_array($item['snippet'])){
				if(isset($item['snippet']['title'])){
					$items[$i]['snippet']['title'] = sanitize_text_field($item['snippet']['title']);
				}
				if(isset($item['snippet']['description'])){
					$items[$i]['snippet']['description'] = sanitize_textarea_field($item['snippet']['description']);
				}
				if(!empty($item['snippet']['thumbnails']) && is_array($item['snippet']['thumbnails'])){
					$items[$i]['snippet']['thumbnails'] = \SocialFeeds\Util::sanitize_thumbnails($item['snippet']['thumbnails']);
				}
			}
		}

		return $items;
	}
        
    /**
     * Encrypt a credential for storage in the database.
     * Uses AES-256-CBC keyed from WordPress auth salts so a DB-only leak
     * (backup dump, SQLi in another plugin) does not expose the raw API key.
     */
	static function encrypt_credential($value){
		if(!is_string($value) || $value === ''){
			return '';
		}

		if(strpos($value, 'sfenc:') === 0){
			return $value;
		}

		if(!function_exists('openssl_encrypt')){
			return $value;
		}

		$key = hash('sha256', wp_salt('auth'), true);
		$iv = random_bytes(16);
		$encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		if(false === $encrypted){
			return $value;
		}

		return 'sfenc:' . base64_encode($iv . $encrypted);
	}

    /**
     * Decrypt a credential previously stored by encrypt_credential().
     * Plaintext values (legacy installs) are returned unchanged.
     */
	static function decrypt_credential($value){
		if(!is_string($value) || $value === ''){
			return '';
		}

		if(strpos($value, 'sfenc:') !== 0){
			return $value;
		}

		if(!function_exists('openssl_decrypt')){
			return '';
		}

		$raw = base64_decode(substr($value, 6), true);
		if(false === $raw || strlen($raw) < 17){
			return '';
		}

		$key = hash('sha256', wp_salt('auth'), true);
		$iv = substr($raw, 0, 16);
		$ciphertext = substr($raw, 16);
		$decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		return false === $decrypted ? '' : $decrypted;
	}

    /**
     * Return the decrypted YouTube API key from an options array or the database.
     */
    static function get_youtube_api_key($opts = null){
		if(!is_array($opts)){
			$opts = get_option('socialfeeds_youtube_option', []);
		}

		$key = isset($opts['youtube_api_key']) ? $opts['youtube_api_key'] : '';
		return \SocialFeeds\Util::decrypt_credential($key);
	}

    /**
     * One-time migration: encrypt a legacy plaintext YouTube API key at rest.
     */
	static function maybe_encrypt_stored_api_key(){
		$opts = get_option('socialfeeds_youtube_option', []);
		if(!is_array($opts) || empty($opts['youtube_api_key']) || !is_string($opts['youtube_api_key'])){
			return;
		}

		if(strpos($opts['youtube_api_key'], 'sfenc:') === 0){
			return;
		}

		$encrypted = self::encrypt_credential($opts['youtube_api_key']);
		if($encrypted === $opts['youtube_api_key']){
			return;
		}

		$opts['youtube_api_key'] = $encrypted;
		update_option('socialfeeds_youtube_option', $opts);
	}
}