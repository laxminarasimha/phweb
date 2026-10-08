<?php
/*
* FormLayer Pro
* https://formlayer.net
* (c) FormLayer Team
*/

namespace FormLayerPro;

if(!defined('ABSPATH')){
	exit;
}

class Fields{

	static function add_categories($categories){
		$categories[] = ['id' => 'pro', 'label' => __('Premium Fields', 'formlayer-pro'), 'open' => false];
		return $categories;
	}

	static function add_field_types($field_types){
		$pro_fields = [
			['type' => 'address', 'label' => 'Address Fields', 'icon' => 'dashicons-location', 'category' => 'pro'],
			['type' => 'date', 'label' => 'Date / Time Picker', 'icon' => 'dashicons-calendar-alt', 'category' => 'pro'],
			['type' => 'url', 'label' => 'Website / URL', 'icon' => 'dashicons-admin-site', 'category' => 'pro'],
			['type' => 'password', 'label' => 'Password', 'icon' => 'dashicons-lock', 'category' => 'pro'],
			['type' => 'hidden', 'label' => 'Hidden Field', 'icon' => 'dashicons-visibility-faint', 'category' => 'pro'],
			['type' => 'image', 'label' => 'Image Upload', 'icon' => 'dashicons-format-image', 'category' => 'pro'],
			['type' => 'file', 'label' => 'File Upload', 'icon' => 'dashicons-upload', 'category' => 'pro'],
			['type' => 'phone', 'label' => 'Phone Number', 'icon' => 'dashicons-phone', 'category' => 'pro'],
			['type' => 'camera', 'label' => 'Camera Field', 'icon' => 'dashicons-camera', 'category' => 'pro'],
			['type' => 'richtext', 'label' => 'Rich Text Editor', 'icon' => 'dashicons-editor-paragraph', 'category' => 'pro'],
		];

		return array_merge($field_types, $pro_fields);
	}

	static function add_gutenberg_blocks($blocks){
		$pro_blocks = [
			'address' => [
				'type' => 'address',
				'name' => 'formlayer/address-field',
				'title' => __('Address Fields', 'formlayer-pro'),
				'icon' => 'location',
				'category' => 'pro',
				'keywords' => ['address', 'street', 'city'],
				'preview' => 'address',
			],
			'date' => [
				'type' => 'date',
				'name' => 'formlayer/date-field',
				'title' => __('Date / Time Picker', 'formlayer-pro'),
				'icon' => 'calendar-alt',
				'category' => 'pro',
				'keywords' => ['date', 'time'],
				'preview' => 'date',
			],
			'url' => [
				'type' => 'url',
				'name' => 'formlayer/url-field',
				'title' => __('Website / URL', 'formlayer-pro'),
				'icon' => 'admin-site',
				'category' => 'pro',
				'keywords' => ['url', 'website'],
				'preview' => 'text',
			],
			'password' => [
				'type' => 'password',
				'name' => 'formlayer/password-field',
				'title' => __('Password', 'formlayer-pro'),
				'icon' => 'lock',
				'category' => 'pro',
				'keywords' => ['password'],
				'preview' => 'password',
			],
			'hidden' => [
				'type' => 'hidden',
				'name' => 'formlayer/hidden-field',
				'title' => __('Hidden Field', 'formlayer-pro'),
				'icon' => 'visibility',
				'category' => 'pro',
				'keywords' => ['hidden'],
				'preview' => 'hidden',
			],
			'image' => [
				'type' => 'image',
				'name' => 'formlayer/image-field',
				'title' => __('Image Upload', 'formlayer-pro'),
				'icon' => 'format-image',
				'category' => 'pro',
				'keywords' => ['image', 'upload'],
				'preview' => 'image',
			],
			'file' => [
				'type' => 'file',
				'name' => 'formlayer/file-field',
				'title' => __('File Upload', 'formlayer-pro'),
				'icon' => 'upload',
				'category' => 'pro',
				'keywords' => ['file', 'upload'],
				'preview' => 'file',
			],
			'phone' => [
				'type' => 'phone',
				'name' => 'formlayer/phone-field',
				'title' => __('Phone Number', 'formlayer-pro'),
				'icon' => 'phone',
				'category' => 'pro',
				'keywords' => ['phone', 'tel'],
				'preview' => 'phone',
			],
			'camera' => [
				'type' => 'camera',
				'name' => 'formlayer/camera-field',
				'title' => __('Camera Field', 'formlayer-pro'),
				'icon' => 'camera',
				'category' => 'pro',
				'keywords' => ['camera', 'photo'],
				'preview' => 'camera',
			],
			'richtext' => [
				'type' => 'richtext',
				'name' => 'formlayer/richtext-field',
				'title' => __('Rich Text Editor', 'formlayer-pro'),
				'icon' => 'editor-paragraph',
				'category' => 'pro',
				'keywords' => ['richtext', 'editor'],
				'preview' => 'textarea',
			],
		];

		return array_merge($blocks, $pro_blocks);
	}

}