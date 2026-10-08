<?php
namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Util{

	
	static function get_form_data($form){
		if(is_numeric($form)){
			$form = get_post($form);
		}

		if(empty($form) || !is_object($form)){
			return [
				'title' => '',
				'fields' => [],
				'settings' => [],
			];
		}

		$content = isset($form->post_content) ? ltrim((string) $form->post_content) : '';
		if($content !== '' && $content[0] === '{'){
			$data = json_decode($form->post_content, true);
			if(is_array($data) && isset($data['fields'])){
				if(!isset($data['settings']) || !is_array($data['settings'])){
					$data['settings'] = [];
				}
				if(empty($data['title'])){
					$data['title'] = $form->post_title;
				}
				return $data;
			}
		}

		$cached = get_post_meta($form->ID, '_formlayer_form_data', true);
		$settings_raw = get_post_meta($form->ID, '_formlayer_form_settings', true);
		$settings = is_string($settings_raw) && $settings_raw !== '' ? json_decode($settings_raw, true) : (is_array($settings_raw) ? $settings_raw : null);

		if(is_array($cached) && isset($cached['fields'])){
			if(is_array($settings) && !empty($settings)){
				$cached['settings'] = $settings;
			} elseif(empty($cached['settings']) || !is_array($cached['settings'])){
				$cached['settings'] = is_array($settings) ? $settings : [];
			}
			$cached['title'] = $form->post_title;
			return $cached;
		}

		if($content !== '' && has_blocks($form->post_content) && class_exists('\FormLayer\Blocks')){
			return [
				'title' => $form->post_title,
				'fields' => \FormLayer\Blocks::blocks_to_fields(parse_blocks($form->post_content)),
				'settings' => is_array($settings) ? $settings : [],
			];
		}

		return [
			'title' => $form->post_title,
			'fields' => [],
			'settings' => is_array($settings) ? $settings : [],
		];
	}

	static function get_form_field_labels($form_id) {
		$field_labels = [];
		$form_data = self::get_form_data($form_id);
		if(!empty($form_data['fields']) && is_array($form_data['fields'])){
			foreach($form_data['fields'] as $field){
				$name = self::get_field_name($field);
				$label = !empty($field['label']) ? $field['label'] : '';
				if(empty($label) && isset($field['type']) && $field['type'] === 'email'){
					$label = __('Email', 'formlayer');
				}
				$field_labels[$name] = !empty($label) ? $label : $name;
			}
		}
		return $field_labels;
	}

	static function get_form_field_types($form_id){
		$field_types = [];
		$form_data = self::get_form_data($form_id);
		if(!empty($form_data['fields']) && is_array($form_data['fields'])){
			foreach($form_data['fields'] as $field){
				$name = self::get_field_name($field);
				$field_types[$name] = isset($field['type']) ? $field['type'] : 'text';
			}
		}

		return $field_types;
	}

	static function get_field_name($field) {
		if (!empty($field['name_attr'])) {
			return $field['name_attr'];
		}
		$id = isset($field['id']) ? $field['id'] : sanitize_title(isset($field['label']) ? $field['label'] : '');
		return 'field_' . $id;
	}

	static function get_display_id($post_id) {
		if (empty($post_id)) {
			return 0;
		}

		$display_id = (int) get_post_meta($post_id, '_formlayer_display_id', true);
		if (!$display_id) {
			$counter = (int) get_option('formlayer_id_counter', 0);
			$counter++;
			update_option('formlayer_id_counter', $counter);
			update_post_meta($post_id, '_formlayer_display_id', $counter);
			$display_id = $counter;
		}

		return $display_id;
	}

	static function get_post_id_by_display_id($display_id) {
		$posts = get_posts([
			'post_type' => 'formlayer_form',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => [
				[
					'key' => '_formlayer_display_id',
					'value' => $display_id,
					'compare' => '='
				]
			],
			'posts_per_page' => 1,
			'post_status' => 'any'
		]);
		return !empty($posts) ? $posts[0]->ID : 0;
	}
}