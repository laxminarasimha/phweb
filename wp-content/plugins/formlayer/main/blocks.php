<?php

namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Blocks{

	const CPT = 'formlayer_form';
	const SETTINGS_META = '_formlayer_form_settings';
	const DATA_META = '_formlayer_form_data';
	const BUILDER_META = '_formlayer_builder';

	static function init(){
		add_action('init', '\FormLayer\Blocks::register_cpt', 5);
		add_action('init', '\FormLayer\Blocks::register_meta', 6);
		add_action('init', '\FormLayer\Blocks::register_blocks', 8);
		add_action('init', '\FormLayer\Blocks::register_editor_styles', 7);
		add_action('enqueue_block_editor_assets', '\FormLayer\Blocks::enqueue_editor_assets');
		add_action('enqueue_block_assets', '\FormLayer\Blocks::enqueue_canvas_assets');
		add_filter('block_editor_settings_all', '\FormLayer\Blocks::inject_iframe_styles', 10, 2);
		add_filter('block_categories_all', '\FormLayer\Blocks::register_block_category', 10, 2);
		add_filter('allowed_block_types_all', '\FormLayer\Blocks::restrict_allowed_blocks', 10, 2);
		add_filter('use_block_editor_for_post_type', '\FormLayer\Blocks::enable_block_editor', 10, 2);
		add_action('save_post_' . self::CPT, '\FormLayer\Blocks::on_save_form', 20, 2);
		add_action('rest_insert_' . self::CPT, '\FormLayer\Blocks::on_rest_save_form', 20, 3);
		add_filter('rest_prepare_' . self::CPT, '\FormLayer\Blocks::rest_prepare_form', 10, 3);
		add_action('admin_init', '\FormLayer\Blocks::redirect_cpt_list');
		add_filter('enter_title_here', '\FormLayer\Blocks::enter_title_here', 10, 2);
		add_filter('admin_body_class', '\FormLayer\Blocks::admin_body_class');
	}

	static function register_cpt(){
		if(post_type_exists(self::CPT)){
			return;
		}

		register_post_type(self::CPT, [
			'labels' => [
				'name' => __('Forms', 'formlayer'),
				'singular_name' => __('Form', 'formlayer'),
				'add_new' => __('Add New Form', 'formlayer'),
				'add_new_item' => __('Add New Form', 'formlayer'),
				'edit_item' => __('Edit Form', 'formlayer'),
				'new_item' => __('New Form', 'formlayer'),
				'view_item' => __('View Form', 'formlayer'),
				'search_items' => __('Search Forms', 'formlayer'),
				'not_found' => __('No forms found.', 'formlayer'),
				'not_found_in_trash' => __('No forms found in Trash.', 'formlayer'),
				'item_published' => __('Form published.', 'formlayer'),
				'item_updated' => __('Form updated.', 'formlayer'),
			],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'show_in_rest' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'supports' => ['title', 'editor', 'custom-fields'],
			'capabilities' => [
				'edit_post' => 'manage_options',
				'read_post' => 'manage_options',
				'delete_post' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'publish_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'create_posts' => 'manage_options',
			],
		]);
	}

	static function register_meta(){
		register_post_meta(self::CPT, self::SETTINGS_META, [
			'type' => 'string',
			'single' => true,
			'show_in_rest' => true,
			'default' => '',
			'auth_callback' => function(){
				return current_user_can('manage_options');
			},
		]);

		register_post_meta(self::CPT, '_formlayer_display_id', [
			'type' => 'integer',
			'single' => true,
			'show_in_rest' => true,
			'auth_callback' => function(){
				return current_user_can('manage_options');
			},
		]);
	}

	static function common_attributes(){
		return [
			'fieldId' => ['type' => 'string', 'default' => ''],
			'label' => ['type' => 'string', 'default' => ''],
			'placeholder' => ['type' => 'string', 'default' => ''],
			'required' => ['type' => 'boolean', 'default' => false],
			'helpText' => ['type' => 'string', 'default' => ''],
			'defaultValue' => ['type' => 'string', 'default' => ''],
			'nameAttr' => ['type' => 'string', 'default' => ''],
			'labelPlacement' => ['type' => 'string', 'default' => 'top'],
			'containerClass' => ['type' => 'string', 'default' => ''],
			'elementClass' => ['type' => 'string', 'default' => ''],
			'styleLabelColor' => ['type' => 'string', 'default' => ''],
			'styleBorderRadius' => ['type' => 'string', 'default' => ''],
			'fieldWidth' => ['type' => 'number', 'default' => 100],
		];
	}

	static function extra_attributes($type){
		$options = [
			'options' => [
				'type' => 'array',
				'default' => [
					['label' => 'Option 1', 'value' => 'Option 1', 'default' => false],
					['label' => 'Option 2', 'value' => 'Option 2', 'default' => false],
					['label' => 'Option 3', 'value' => 'Option 3', 'default' => false],
				],
			],
		];

		switch($type){
			case 'name':
				return [
					'enableFirstName' => ['type' => 'boolean', 'default' => true],
					'enableMiddleName' => ['type' => 'boolean', 'default' => false],
					'enableLastName' => ['type' => 'boolean', 'default' => true],
					'labelFirst' => ['type' => 'string', 'default' => ''],
					'labelMiddle' => ['type' => 'string', 'default' => ''],
					'labelLast' => ['type' => 'string', 'default' => ''],
					'placeholderFirst' => ['type' => 'string', 'default' => ''],
					'placeholderMiddle' => ['type' => 'string', 'default' => ''],
					'placeholderLast' => ['type' => 'string', 'default' => ''],
				];
			case 'address':
				return [
					'enableStreet' => ['type' => 'boolean', 'default' => true],
					'enableCity' => ['type' => 'boolean', 'default' => true],
					'enableState' => ['type' => 'boolean', 'default' => true],
					'enableZip' => ['type' => 'boolean', 'default' => true],
					'enableCountry' => ['type' => 'boolean', 'default' => true],
				];
			case 'number':
				return [
					'min' => ['type' => 'string', 'default' => ''],
					'max' => ['type' => 'string', 'default' => ''],
					'digitLimit' => ['type' => 'string', 'default' => ''],
				];
			case 'textarea':
			case 'richtext':
				return [
					'rows' => ['type' => 'number', 'default' => 4],
				];
			case 'date':
				return [
					'dateFormat' => ['type' => 'string', 'default' => 'Y-m-d'],
				];
			case 'captcha':
				return [
					'captchaProvider' => ['type' => 'string', 'default' => 'hcaptcha'],
					'captchaTheme' => ['type' => 'string', 'default' => 'light'],
				];
			case 'submit':
				return [
					'btnAlign' => ['type' => 'string', 'default' => 'left'],
					'btnSize' => ['type' => 'string', 'default' => 'md'],
					'btnBgColor' => ['type' => 'string', 'default' => ''],
					'btnTextColor' => ['type' => 'string', 'default' => ''],
					'btnBgHover' => ['type' => 'string', 'default' => ''],
					'btnTextHover' => ['type' => 'string', 'default' => ''],
				];
			case 'gdpr':
				return [
					'gdprLabel' => ['type' => 'string', 'default' => ''],
					'gdprDescription' => ['type' => 'string', 'default' => ''],
				];
			case 'terms':
				return [
					'termsLabel' => ['type' => 'string', 'default' => ''],
				];
			case 'dropdown':
			case 'radio':
			case 'checkbox':
			case 'multiple':
				return $options;
			case 'url':
				return [
					'urlValidation' => ['type' => 'boolean', 'default' => false],
					'urlHttpsOnly' => ['type' => 'boolean', 'default' => false],
				];
			case 'file':
			case 'image':
			case 'camera':
				return [
					'fileBtnBg' => ['type' => 'string', 'default' => '#5525d6'],
					'fileBtnColor' => ['type' => 'string', 'default' => '#ffffff'],
				];
			default:
				return [];
		}
	}

	static function get_block_definitions(){
		$blocks = [
			'name' => [
				'type' => 'name',
				'name' => 'formlayer/name-field',
				'title' => __('Name Fields', 'formlayer'),
				'icon' => 'admin-users',
				'category' => 'general',
				'keywords' => ['name', 'first', 'last'],
				'preview' => 'name',
			],
			'email' => [
				'type' => 'email',
				'name' => 'formlayer/email-field',
				'title' => __('Email', 'formlayer'),
				'icon' => 'email',
				'category' => 'general',
				'keywords' => ['email', 'mail'],
				'preview' => 'email',
			],
			'text' => [
				'type' => 'text',
				'name' => 'formlayer/text-field',
				'title' => __('Simple Text', 'formlayer'),
				'icon' => 'edit',
				'category' => 'general',
				'keywords' => ['text', 'input'],
				'preview' => 'text',
			],
			'mask' => [
				'type' => 'mask',
				'name' => 'formlayer/mask-field',
				'title' => __('Mask Input', 'formlayer'),
				'icon' => 'shield',
				'category' => 'general',
				'keywords' => ['mask', 'phone'],
				'preview' => 'text',
			],
			'textarea' => [
				'type' => 'textarea',
				'name' => 'formlayer/textarea-field',
				'title' => __('Text Area', 'formlayer'),
				'icon' => 'editor-alignleft',
				'category' => 'general',
				'keywords' => ['textarea', 'message'],
				'preview' => 'textarea',
			],
			'country' => [
				'type' => 'country',
				'name' => 'formlayer/country-field',
				'title' => __('Country List', 'formlayer'),
				'icon' => 'flag',
				'category' => 'general',
				'keywords' => ['country', 'select'],
				'preview' => 'select',
			],
			'number' => [
				'type' => 'number',
				'name' => 'formlayer/number-field',
				'title' => __('Numeric Field', 'formlayer'),
				'icon' => 'editor-ol',
				'category' => 'general',
				'keywords' => ['number', 'numeric'],
				'preview' => 'number',
			],
			'dropdown' => [
				'type' => 'dropdown',
				'name' => 'formlayer/dropdown-field',
				'title' => __('Dropdown', 'formlayer'),
				'icon' => 'arrow-down-alt2',
				'category' => 'general',
				'keywords' => ['select', 'dropdown'],
				'preview' => 'select',
			],
			'radio' => [
				'type' => 'radio',
				'name' => 'formlayer/radio-field',
				'title' => __('Radio Field', 'formlayer'),
				'icon' => 'marker',
				'category' => 'general',
				'keywords' => ['radio', 'choice'],
				'preview' => 'radio',
			],
			'checkbox' => [
				'type' => 'checkbox',
				'name' => 'formlayer/checkbox-field',
				'title' => __('Checkbox', 'formlayer'),
				'icon' => 'yes',
				'category' => 'general',
				'keywords' => ['checkbox'],
				'preview' => 'checkbox',
			],
			'multiple' => [
				'type' => 'multiple',
				'name' => 'formlayer/multiple-field',
				'title' => __('Multiple Choice', 'formlayer'),
				'icon' => 'list-view',
				'category' => 'general',
				'keywords' => ['multiple', 'choices'],
				'preview' => 'checkbox',
			],
			'section' => [
				'type' => 'section',
				'name' => 'formlayer/section-field',
				'title' => __('Section Break', 'formlayer'),
				'icon' => 'minus',
				'category' => 'general',
				'keywords' => ['section', 'heading'],
				'preview' => 'section',
			],
			'rating' => [
				'type' => 'rating',
				'name' => 'formlayer/rating-field',
				'title' => __('Ratings', 'formlayer'),
				'icon' => 'star-filled',
				'category' => 'advanced',
				'keywords' => ['rating', 'stars'],
				'preview' => 'rating',
			],
			'terms' => [
				'type' => 'terms',
				'name' => 'formlayer/terms-field',
				'title' => __('Terms & Conditions', 'formlayer'),
				'icon' => 'media-text',
				'category' => 'advanced',
				'keywords' => ['terms'],
				'preview' => 'terms',
			],
			'gdpr' => [
				'type' => 'gdpr',
				'name' => 'formlayer/gdpr-field',
				'title' => __('GDPR Agreement', 'formlayer'),
				'icon' => 'shield',
				'category' => 'advanced',
				'keywords' => ['gdpr', 'privacy'],
				'preview' => 'gdpr',
			],
			'captcha' => [
				'type' => 'captcha',
				'name' => 'formlayer/captcha-field',
				'title' => __('Captcha Protection', 'formlayer'),
				'icon' => 'shield-alt',
				'category' => 'advanced',
				'keywords' => ['captcha', 'spam'],
				'preview' => 'captcha',
			],
			'submit' => [
				'type' => 'submit',
				'name' => 'formlayer/submit-button',
				'title' => __('Submit Button', 'formlayer'),
				'icon' => 'plus-alt',
				'category' => 'advanced',
				'keywords' => ['submit', 'button'],
				'preview' => 'submit',
			],
		];

		$blocks = apply_filters('formlayer_gutenberg_blocks', $blocks);

		foreach($blocks as $type => &$block){
			if(empty($block['type'])){
				$block['type'] = $type;
			}
			if(empty($block['name'])){
				$block['name'] = 'formlayer/' . $type . '-field';
			}
			$block['attributes'] = array_merge(self::common_attributes(), self::extra_attributes($block['type']));
		}
		unset($block);

		return $blocks;
	}

	static function register_blocks(){
		foreach(self::get_block_definitions() as $block){
			register_block_type($block['name'], [
				'api_version' => 3,
				'title' => $block['title'],
				'category' => 'formlayer-fields',
				'attributes' => $block['attributes'],
				'supports' => [
					'html' => false,
					'reusable' => false,
					'customClassName' => true,
				],
				'render_callback' => '\FormLayer\Blocks::render_field_block',
			]);
		}
	}

	static function render_field_block($attributes, $content = '', $block = null){
		$block_name = '';
		if(is_object($block) && !empty($block->name)){
			$block_name = $block->name;
		} elseif(is_array($block) && !empty($block['blockName'])){
			$block_name = $block['blockName'];
		}

		$field = self::attributes_to_field($attributes, $block_name);
		return Frontend::render_field_html($field);
	}

	static function attr_field_map(){
		return [
			'fieldId' => 'id',
			'label' => 'label',
			'placeholder' => 'placeholder',
			'required' => 'required',
			'helpText' => 'help_text',
			'defaultValue' => 'default_value',
			'nameAttr' => 'name_attr',
			'labelPlacement' => 'label_placement',
			'containerClass' => 'container_class',
			'elementClass' => 'element_class',
			'styleLabelColor' => 'style_label_color',
			'styleBorderRadius' => 'style_border_radius',
			'fieldWidth' => 'field_width',
			'options' => 'options',
			'enableFirstName' => 'enable_first_name',
			'enableMiddleName' => 'enable_middle_name',
			'enableLastName' => 'enable_last_name',
			'labelFirst' => 'label_first',
			'labelMiddle' => 'label_middle',
			'labelLast' => 'label_last',
			'placeholderFirst' => 'placeholder_first',
			'placeholderMiddle' => 'placeholder_middle',
			'placeholderLast' => 'placeholder_last',
			'enableStreet' => 'enable_street',
			'enableCity' => 'enable_city',
			'enableState' => 'enable_state',
			'enableZip' => 'enable_zip',
			'enableCountry' => 'enable_country',
			'min' => 'min',
			'max' => 'max',
			'digitLimit' => 'digit_limit',
			'rows' => 'rows',
			'dateFormat' => 'date_format',
			'captchaProvider' => 'captcha_provider',
			'captchaTheme' => 'captcha_theme',
			'btnAlign' => 'btn_align',
			'btnSize' => 'btn_size',
			'btnBgColor' => 'btn_bg_color',
			'btnTextColor' => 'btn_text_color',
			'btnBgHover' => 'btn_bg_hover',
			'btnTextHover' => 'btn_text_hover',
			'gdprLabel' => 'gdpr_label',
			'gdprDescription' => 'gdpr_description',
			'termsLabel' => 'terms_label',
			'urlValidation' => 'url_validation',
			'urlHttpsOnly' => 'url_https_only',
			'fileBtnBg' => 'file_btn_bg',
			'fileBtnColor' => 'file_btn_color',
		];
	}

	static function type_from_block_name($block_name){
		$definitions = self::get_block_definitions();
		foreach($definitions as $def){
			if($def['name'] === $block_name){
				return $def['type'];
			}
		}

		$name = str_replace(['formlayer/', '-field', '-button'], '', (string)$block_name);
		return $name === 'submit' ? 'submit' : $name;
	}

	static function block_name_from_type($type){
		$definitions = self::get_block_definitions();
		if(isset($definitions[$type]['name'])){
			return $definitions[$type]['name'];
		}
		if($type === 'submit'){
			return 'formlayer/submit-button';
		}
		return 'formlayer/' . $type . '-field';
	}

	static function attributes_to_field($attrs, $block_name = ''){
		$attrs = is_array($attrs) ? $attrs : [];
		$type = self::type_from_block_name($block_name);
		if(empty($type) && !empty($attrs['type'])){
			$type = $attrs['type'];
		}

		$definitions = self::get_block_definitions();
		$def_attrs = [];
		if(!empty($type) && isset($definitions[$type]['attributes'])){
			foreach($definitions[$type]['attributes'] as $k => $attr_def){
				if(isset($attr_def['default'])){
					$def_attrs[$k] = $attr_def['default'];
				}
			}
		}
		$attrs = array_merge($def_attrs, $attrs);

		$field = [
			'type' => $type,
		];

		foreach(self::attr_field_map() as $attr_key => $field_key){
			if(array_key_exists($attr_key, $attrs)){
				$field[$field_key] = $attrs[$attr_key];
			}
		}

		if(empty($field['label']) && $type === 'email'){
			$field['label'] = __('Email', 'formlayer');
		}

		if(empty($field['id'])){
			$field['id'] = 'f' . substr(md5(wp_json_encode($attrs) . $type), 0, 10);
		}

		return $field;
	}

	static function field_to_attributes($field){
		$field = is_array($field) ? $field : [];
		$attrs = [];

		foreach(self::attr_field_map() as $attr_key => $field_key){
			if(array_key_exists($field_key, $field)){
				$attrs[$attr_key] = $field[$field_key];
			}
		}

		if(empty($attrs['fieldId']) && !empty($field['id'])){
			$attrs['fieldId'] = $field['id'];
		}

		if(isset($field['value']) && empty($attrs['defaultValue'])){
			$attrs['defaultValue'] = $field['value'];
		}

		return $attrs;
	}

	static function blocks_to_fields($blocks){
		$fields = [];
		if(empty($blocks) || !is_array($blocks)){
			return $fields;
		}

		foreach($blocks as $block){
			if(empty($block['blockName'])){
				continue;
			}

			if(strpos($block['blockName'], 'formlayer/') !== 0){
				if(!empty($block['innerBlocks'])){
					$fields = array_merge($fields, self::blocks_to_fields($block['innerBlocks']));
				}
				continue;
			}

			$fields[] = self::attributes_to_field(isset($block['attrs']) ? $block['attrs'] : [], $block['blockName']);

			if(!empty($block['innerBlocks'])){
				$fields = array_merge($fields, self::blocks_to_fields($block['innerBlocks']));
			}
		}

		return $fields;
	}

	static function fields_to_blocks($fields){
		$blocks = [];
		if(empty($fields) || !is_array($fields)){
			return $blocks;
		}

		foreach($fields as $field){
			$type = isset($field['type']) ? $field['type'] : 'text';
			$blocks[] = [
				'blockName' => self::block_name_from_type($type),
				'attrs' => self::field_to_attributes($field),
				'innerBlocks' => [],
				'innerHTML' => '',
				'innerContent' => [],
			];
		}

		return $blocks;
	}

	static function default_settings(){
		return [
			'notifications' => [
				'enabled' => true,
				'to_email' => '{admin_email}',
				'reply_to' => '',
				'from_name' => 'FormLayer',
				'from_email' => '{admin_email}',
				'bcc' => '',
				'subject' => 'New Form Submission',
				'message' => "You have a new submission:\n\n{all_fields}",
				'format' => 'html',
			],
			'email_confirmation' => [
				'enabled' => false,
				'to_email' => '',
				'reply_to' => '{admin_email}',
				'from_name' => 'FormLayer',
				'from_email' => '{admin_email}',
				'bcc' => '',
				'subject' => 'Thank you for your submission!',
				'message' => "Thank you! We have received your submission:\n\n{all_fields}",
				'format' => 'html',
			],
			'confirmations' => [
				'type' => 'message',
				'message' => 'Thank you for your submission!',
				'redirect_url' => '',
				'hide_form' => true,
			],
			'integrations' => [],
			'custom_css' => '',
		];
	}

	static function on_rest_save_form($post, $request, $creating){
		if(!is_object($post) || empty($post->ID)){
			return;
		}
		self::on_save_form($post->ID, $post);
	}

	static function on_save_form($post_id, $post){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return;
		}

		if(wp_is_post_revision($post_id)){
			return;
		}

		if(is_object($post) && in_array($post->post_status, ['auto-draft', 'trash', 'inherit'], true)){
			return;
		}

		if(!current_user_can('manage_options')){
			return;
		}

		Util::get_display_id($post_id);

		$content = isset($post->post_content) ? $post->post_content : '';
		if($content === '' || !has_blocks($content)){
			return;
		}

		$fields = self::blocks_to_fields(parse_blocks($content));
		$settings_raw = get_post_meta($post_id, self::SETTINGS_META, true);
		$settings = is_string($settings_raw) && $settings_raw !== '' ? json_decode($settings_raw, true) : (is_array($settings_raw) ? $settings_raw : null);
		if(!is_array($settings) || empty($settings)){
			$settings = self::default_settings();
			update_post_meta($post_id, self::SETTINGS_META, wp_json_encode($settings));
		}

		update_post_meta($post_id, self::DATA_META, [
			'title' => $post->post_title,
			'fields' => $fields,
			'settings' => $settings,
		]);
		update_post_meta($post_id, self::BUILDER_META, 'gutenberg');
	}

	static function rest_prepare_form($response, $post, $request){
		$context = $request->get_param('context');
		if($context !== 'edit'){
			return $response;
		}

		$existing = get_post_meta($post->ID, self::SETTINGS_META, true);
		if($existing !== '' && $existing !== false){
			if(empty($response->data['meta']) || !is_array($response->data['meta'])){
				$response->data['meta'] = [];
			}
			$response->data['meta'][self::SETTINGS_META] = is_string($existing) ? $existing : wp_json_encode($existing);
		}

		$data = json_decode($post->post_content, true);
		if(!is_array($data) || empty($data['fields']) || has_blocks($post->post_content)){
			return $response;
		}

		$serialized = serialize_blocks(self::fields_to_blocks($data['fields']));
		if(!empty($response->data['content']) && is_array($response->data['content'])){
			$response->data['content']['raw'] = $serialized;
		}

		if(!empty($data['settings'])){
			if(empty($response->data['meta']) || !is_array($response->data['meta'])){
				$response->data['meta'] = [];
			}
			if($existing === '' || $existing === false){
				$response->data['meta'][self::SETTINGS_META] = wp_json_encode($data['settings']);
			}
		}

		return $response;
	}

	static function register_block_category($categories, $context){
		$post_type = '';
		if(is_object($context) && !empty($context->post) && !empty($context->post->post_type)){
			$post_type = $context->post->post_type;
		}

		if($post_type !== self::CPT){
			return $categories;
		}

		return array_merge([
			[
				'slug' => 'formlayer-fields',
				'title' => __('FormLayer Fields', 'formlayer'),
			],
		], $categories);
	}

	static function restrict_allowed_blocks($allowed, $context){
		$post_type = '';
		if(is_object($context) && !empty($context->post) && !empty($context->post->post_type)){
			$post_type = $context->post->post_type;
		}

		if($post_type !== self::CPT){
			return $allowed;
		}

		$names = [];
		foreach(self::get_block_definitions() as $block){
			$names[] = $block['name'];
		}

		return $names;
	}

	static function enable_block_editor($use, $post_type){
		if($post_type === self::CPT){
			return true;
		}
		return $use;
	}

	static function redirect_cpt_list(){
		global $pagenow;
		if($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === self::CPT){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect(admin_url('admin.php?page=formlayer'));
			exit;
		}
	}

	static function enter_title_here($title, $post){
		if(is_object($post) && isset($post->post_type) && $post->post_type === self::CPT){
			return __('Form title', 'formlayer');
		}
		return $title;
	}

	static function admin_body_class($classes){
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if($screen && $screen->post_type === self::CPT){
			$classes .= ' formlayer-form-editor';
		}
		return $classes;
	}

	static function is_form_editor_screen(){
		if(!is_admin()){
			return false;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if($screen && $screen->post_type === self::CPT){
			return true;
		}

		if(isset($_GET['post_type']) && $_GET['post_type'] === self::CPT){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if(!empty($_GET['post'])){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return get_post_type((int) $_GET['post']) === self::CPT;
		}

		global $post;
		if(is_object($post) && isset($post->post_type) && $post->post_type === self::CPT){
			return true;
		}

		return false;
	}

	static function register_editor_styles(){
		wp_register_style('formlayer-frontend', FORMLAYER_PLUGIN_URL . 'assets/css/frontend.css', [], FORMLAYER_VERSION);
		wp_register_style('formlayer-form-editor', FORMLAYER_PLUGIN_URL . 'assets/css/form-editor.css', ['dashicons', 'formlayer-frontend'], FORMLAYER_VERSION);
	}

	static function enqueue_canvas_assets(){
		if(!self::is_form_editor_screen()){
			return;
		}

		wp_enqueue_style('dashicons');
		wp_enqueue_style('formlayer-frontend');
		wp_enqueue_style('formlayer-form-editor');
	}

	static function inject_iframe_styles($settings, $context = null){
		$post_type = '';
		if(is_object($context) && !empty($context->post) && !empty($context->post->post_type)){
			$post_type = $context->post->post_type;
		} elseif(self::is_form_editor_screen()){
			$post_type = self::CPT;
		}

		if($post_type !== self::CPT){
			return $settings;
		}

		$files = [
			FORMLAYER_PLUGIN_DIR . 'assets/css/frontend.css',
			FORMLAYER_PLUGIN_DIR . 'assets/css/form-editor.css',
		];

		if(!isset($settings['styles']) || !is_array($settings['styles'])){
			$settings['styles'] = [];
		}

		foreach($files as $file){
			if(!file_exists($file)){
				continue;
			}
			$css = file_get_contents($file);
			if($css === false || $css === ''){
				continue;
			}
			$settings['styles'][] = [
				'css' => $css,
				'__unstableType' => 'plugin',
				'isGlobalStyles' => false,
			];
		}

		return $settings;
	}

	static function enqueue_editor_assets(){
		if(!self::is_form_editor_screen()){
			return;
		}

		wp_enqueue_style('dashicons');
		wp_enqueue_style('formlayer-frontend');
		wp_enqueue_style('formlayer-form-editor');

		wp_enqueue_script('formlayer-form-editor', FORMLAYER_PLUGIN_URL . 'assets/js/form-editor.js', ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-editor', 'wp-i18n', 'wp-core-data', 'wp-compose'], FORMLAYER_VERSION, true);

		$post_id = 0;
		$display_id = 0;
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if($screen && !empty($screen->post_id)){
			$post_id = (int) $screen->post_id;
		} elseif(!empty($_GET['post'])){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = (int) $_GET['post']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if($post_id){
			$display_id = (int) get_post_meta($post_id, '_formlayer_display_id', true);
		}

		$categories = [
			['id' => 'general', 'label' => __('General Fields', 'formlayer')],
			['id' => 'advanced', 'label' => __('Advanced Fields', 'formlayer')],
		];
		$categories = apply_filters('formlayer_builder_categories', $categories);

		$teasers = [];
		if(!defined('FORMLAYER_PRO_VERSION')){
			$categories[] = ['id' => 'pro', 'label' => __('Premium Fields', 'formlayer')];
			$teasers = [
				['type' => 'address', 'title' => __('Address Fields', 'formlayer'), 'icon' => 'location', 'category' => 'pro'],
				['type' => 'date', 'title' => __('Date / Time Picker', 'formlayer'), 'icon' => 'calendar-alt', 'category' => 'pro'],
				['type' => 'url', 'title' => __('Website / URL', 'formlayer'), 'icon' => 'admin-site', 'category' => 'pro'],
				['type' => 'phone', 'title' => __('Phone Number', 'formlayer'), 'icon' => 'phone', 'category' => 'pro'],
				['type' => 'file', 'title' => __('File Upload', 'formlayer'), 'icon' => 'upload', 'category' => 'pro'],
				['type' => 'image', 'title' => __('Image Upload', 'formlayer'), 'icon' => 'format-image', 'category' => 'pro'],
			];
		}

		wp_localize_script('formlayer-form-editor', 'formlayer_form_editor', [
			'blocks' => array_values(self::get_block_definitions()),
			'categories' => $categories,
			'teasers' => $teasers,
			'is_pro' => defined('FORMLAYER_PRO_VERSION'),
			'pro_assets_url' => defined('FORMLAYER_PRO_ASSETS_URL') ? FORMLAYER_PRO_ASSETS_URL : '',
			'post_id' => $post_id,
			'display_id' => $display_id,
			'shortcode' => $display_id ? '[formlayer id="' . $display_id . '"]' : '',
			'forms_url' => admin_url('admin.php?page=formlayer'),
			'default_settings' => self::default_settings(),
			'admin_email' => get_option('admin_email'),
			'logo_url' => FORMLAYER_ASSETS_URL . '/img/formlayer-logo.png',
			'logo_icon_url' => FORMLAYER_ASSETS_URL . '/img/formlayer-logo-30.png',
			'style_urls' => [
				[
					'id' => 'formlayer-frontend-canvas',
					'href' => FORMLAYER_PLUGIN_URL . 'assets/css/frontend.css?ver=' . FORMLAYER_VERSION,
				],
				[
					'id' => 'formlayer-form-editor-canvas',
					'href' => FORMLAYER_PLUGIN_URL . 'assets/css/form-editor.css?ver=' . FORMLAYER_VERSION,
				],
				[
					'id' => 'formlayer-dashicons-canvas',
					'href' => includes_url('css/dashicons.min.css'),
				],
			],
		]);
	}
}
