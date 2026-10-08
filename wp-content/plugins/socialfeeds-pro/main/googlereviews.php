<?php
namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class GoogleReviews{

	static function fetch_google_place_details($place_id, $api_key){
	
		$url = add_query_arg([
			'place_id' => $place_id,
			'fields' => 'name,formatted_address',
			'key' => $api_key,
		], 'https://maps.googleapis.com/maps/api/place/details/json');

		$res = wp_remote_get($url, ['timeout' => 15]);

		// Mock response (optional testing)

		if(is_wp_error($res)){
			return [
				'error' => $res->get_error_message()
			];
		}

		$body = wp_remote_retrieve_body($res);
		$data = json_decode($body, true);

		if(!is_array($data)){
			return [
				'error' => __('Invalid API response.', 'socialfeeds-pro')
			];
		}

		if(!isset($data['result']) || !is_array($data['result']) || empty($data['result'])){

			if(isset($data['error_message']) && !empty($data['error_message'])){
				return [
					'error' => $data['error_message']
				];
			}

			return [
				'error' => __('Failed to fetch details.', 'socialfeeds-pro')
			];
		}

		$result = $data['result'];

		$name = isset($result['name']) ? $result['name'] : '';
		$address = isset($result['formatted_address']) ? $result['formatted_address'] : '';

		return [
			'name' => $name,
			'address' => $address,
		];
	}

	static function fetch_google_place_reviews($place_id, $api_key) {

		$url = add_query_arg([
			'place_id' => $place_id,
			'fields' => 'name,formatted_address,rating,user_ratings_total,url,reviews',
			'key' => $api_key,
			'reviews_no_translations' => 'true',
		], 'https://maps.googleapis.com/maps/api/place/details/json');

		$res = wp_remote_get($url, ['timeout' => 15]);

		// Mock response (optional)

		if(is_wp_error($res)){
			return [
				'error' => $res->get_error_message()
			];
		}

		$body = wp_remote_retrieve_body($res);
		$data = json_decode($body, true);

		if(!is_array($data)){
			return [
				'error' => __('Invalid API response.', 'socialfeeds-pro')
			];
		}

		if(!isset($data['result']) || empty($data['result']) || !is_array($data['result'])){
			if(isset($data['error_message']) && !empty($data['error_message'])){
				return [
					'error' => $data['error_message']
				];
			}
			return [
				'error' => __('Failed to fetch reviews.', 'socialfeeds-pro')
			];
		}

		$r = $data['result'];

		$reviews_raw = isset($r['reviews']) ? $r['reviews'] : [];
		if(!is_array($reviews_raw)){
			$reviews_raw = [];
		}

		$reviews = array_map(function ($rev) {

			return [
				'author_name' => isset($rev['author_name']) ? $rev['author_name'] : '',
				'author_url' => isset($rev['author_url']) ? $rev['author_url'] : '',
				'profile_photo_url' => isset($rev['profile_photo_url']) ? $rev['profile_photo_url'] : '',
				'language' => isset($rev['language']) ? $rev['language'] : '',
				'original_language' => isset($rev['original_language']) ? $rev['original_language'] : '',
				'translated' => isset($rev['translated']) ? (bool) $rev['translated'] : false,
				'time' => isset($rev['time']) ? (int) $rev['time'] : null,
				'rating' => isset($rev['rating']) ? $rev['rating'] : null,
				'relative_time_description' => isset($rev['relative_time_description']) ? $rev['relative_time_description'] : '',
				'text' => isset($rev['text']) ? $rev['text'] : '',
			];

		}, $reviews_raw);

		return [
			'place_name' => isset($r['name']) ? $r['name'] : '',
			'place_id' => $place_id,
			'address' => isset($r['formatted_address']) ? $r['formatted_address'] : '',
			'rating' => isset($r['rating']) ? $r['rating'] : null,
			'review_count' => isset($r['user_ratings_total']) ? (int) $r['user_ratings_total'] : 0,
			'url' => isset($r['url']) ? $r['url'] : '',
			'reviews' => $reviews,
		];
	}
}