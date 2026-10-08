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

class Frontend {

	static function init() {
		add_filter('formlayer_render_field_html', '\FormLayerPro\Frontend::render_field', 10, 2);
		add_filter('formlayer_pro_captcha_html', '\FormLayerPro\Frontend::render_captcha', 10, 3);
		add_filter('formlayer_pro_captcha_theme', '\FormLayerPro\Frontend::get_captcha_theme', 10, 2);
		add_filter('formlayer_pro_captcha_sitekey', '\FormLayerPro\Frontend::get_captcha_sitekey', 10, 2);
		add_action('formlayer_enqueue_captcha_scripts', '\FormLayerPro\Frontend::enqueue_captcha_scripts', 10, 1);
	}

	static function render_field($html, $field) {
		$type = isset($field['type']) ? $field['type'] : '';
		$label = isset($field['label']) ? $field['label'] : '';
		$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
		$required = !empty($field['required']) ? 'required' : '';
		$name = \FormLayer\Util::get_field_name($field);
		$req_mark = $required ? '<span class="required-indicator">*</span>' : '';

		$label_placement = isset($field['label_placement']) ? $field['label_placement'] : 'top';
		$container_class = isset($field['container_class']) ? $field['container_class'] : '';
		$element_class = isset($field['element_class']) ? $field['element_class'] : '';
		$help_text = isset($field['help_text']) ? $field['help_text'] : '';

		$label_color = isset($field['style_label_color']) ? $field['style_label_color'] : '';
		$border_radius = isset($field['style_border_radius']) ? $field['style_border_radius'] : '';

		$label_style = !empty($label_color) ? 'style="color:' . esc_attr($label_color) . ';"' : '';
		$input_style = !empty($border_radius) ? 'style="border-radius:' . intval($border_radius) . 'px;"' : '';

		$btn_bg = isset($field['file_btn_bg']) ? $field['file_btn_bg'] : '#5525d6';
		$btn_color = isset($field['file_btn_color']) ? $field['file_btn_color'] : '#ffffff';
		$btn_style = 'style="background:' . esc_attr($btn_bg) . '; color:' . esc_attr($btn_color) . ';"';

		$width_class = (!empty($field['field_width']) && intval($field['field_width']) === 50) ? ' formlayer-width-50' : '';
		$wrap_open = '<div class="formlayer-field-wrap label-' . esc_attr($label_placement) . ' ' . esc_attr($container_class) . $width_class . '">';
		$label_html = ($label && 'label-hidden' !== $label_placement && 'hidden' !== $label_placement) ? '<label class="formlayer-label" ' . $label_style . '>' . esc_html($label) . $req_mark . '</label>' : '';
		$help_html = !empty($help_text) ? '<div class="formlayer-help-text">' . esc_html($help_text) . '</div>' : '';

		switch($type){
			case 'image':
				return $wrap_open . $label_html . '
					<div class="formlayer-file-upload-box">
						<input type="file" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="formlayer-file-real ' . esc_attr($element_class) . '" accept="image/*" ' . $required . ' style="display:none;" onchange="this.nextElementSibling.nextElementSibling.innerText = this.files[0] ? this.files[0].name : \'No file chosen\'">
						<label for="' . esc_attr($name) . '" class="formlayer-file-fake-btn" ' . $btn_style . '>' . esc_html__('Choose File', 'formlayer-pro') . '</label>
						<span class="formlayer-file-chosen-name">' . esc_html__('No file chosen', 'formlayer-pro') . '</span>
					</div>' . $help_html . '
				</div>';
			case 'file':
				return $wrap_open . $label_html . '
					<div class="formlayer-file-upload-box">
						<input type="file" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="formlayer-file-real ' . esc_attr($element_class) . '" ' . $required . ' style="display:none;" onchange="this.nextElementSibling.nextElementSibling.innerText = this.files[0] ? this.files[0].name : \'No file chosen\'">
						<label for="' . esc_attr($name) . '" class="formlayer-file-fake-btn" ' . $btn_style . '>' . esc_html__('Choose File', 'formlayer-pro') . '</label>
						<span class="formlayer-file-chosen-name">' . esc_html__('No file chosen', 'formlayer-pro') . '</span>
					</div>' . $help_html . '
				</div>';
			case 'phone':
				$phone_placeholder = !empty($placeholder) ? $placeholder : __('Phone Number', 'formlayer-pro');

				return $wrap_open . $label_html . '
					<input type="tel" name="' . esc_attr($name) . '" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($phone_placeholder) . '" value="' . esc_attr($field['default_value'] ?? '') . '" ' . $required . ' ' . $input_style . '>' . $help_html . '
				</div>';
			case 'address':
				$showStreet = !isset($field['enable_street']) || $field['enable_street'] !== false;
				$showCity = !isset($field['enable_city']) || $field['enable_city'] !== false;
				$showState = !isset($field['enable_state']) || $field['enable_state'] !== false;
				$showZip = !isset($field['enable_zip']) || $field['enable_zip'] !== false;
				$showAddrCountry = !isset($field['enable_country']) || $field['enable_country'] !== false;

				$p_street = isset($field['placeholder_street']) && $field['placeholder_street'] !== '' ? $field['placeholder_street'] : __('Street Address', 'formlayer-pro');
				$p_city = isset($field['placeholder_city']) && $field['placeholder_city'] !== '' ? $field['placeholder_city'] : __('City', 'formlayer-pro');
				$p_state = isset($field['placeholder_state']) && $field['placeholder_state'] !== '' ? $field['placeholder_state'] : __('State / Province', 'formlayer-pro');
				$p_zip = isset($field['placeholder_zip']) && $field['placeholder_zip'] !== '' ? $field['placeholder_zip'] : __('Zip / Postal Code', 'formlayer-pro');
				$p_country = isset($field['placeholder_country']) && $field['placeholder_country'] !== '' ? $field['placeholder_country'] : __('Select Country', 'formlayer-pro');

				$l_street = isset($field['label_street']) && $field['label_street'] !== '' ? $field['label_street'] : __('Street Address', 'formlayer-pro');
				$l_city = isset($field['label_city']) && $field['label_city'] !== '' ? $field['label_city'] : __('City', 'formlayer-pro');
				$l_state = isset($field['label_state']) && $field['label_state'] !== '' ? $field['label_state'] : __('State / Province', 'formlayer-pro');
				$l_zip = isset($field['label_zip']) && $field['label_zip'] !== '' ? $field['label_zip'] : __('Zip / Postal Code', 'formlayer-pro');
				$l_country = isset($field['label_country']) && $field['label_country'] !== '' ? $field['label_country'] : __('Country', 'formlayer-pro');

				$html = $wrap_open . $label_html . '<div class="formlayer-grid formlayer-address-grid">';
				if($showStreet) {
					$html .= '<div class="formlayer-sub-field full-width">
						<input type="text" name="' . esc_attr($name) . '[address]" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($p_street) . '" ' . $input_style . '>
						<span class="formlayer-sub-label">' . esc_html($l_street) . '</span>
					</div>';
				}
				if($showCity) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[city]" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($p_city) . '" ' . $input_style . '>
						<span class="formlayer-sub-label">' . esc_html($l_city) . '</span>
					</div>';
				}
				if($showState) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[state]" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($p_state) . '" ' . $input_style . '>
						<span class="formlayer-sub-label">' . esc_html($l_state) . '</span>
					</div>';
				}
				if($showZip) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[zip]" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($p_zip) . '" ' . $input_style . '>
						<span class="formlayer-sub-label">' . esc_html($l_zip) . '</span>
					</div>';
				}
				if($showAddrCountry) {
					$addr_country_style = '';
					if (!empty($text_color)) $addr_country_style .= 'color:' . esc_attr($text_color) . ' !important;';
					if (!empty($border_radius)) $addr_country_style .= 'border-radius:' . intval($border_radius) . 'px;';
					$html .= '<div class="formlayer-sub-field">
						<div class="formlayer-select-wrap">
							<span class="dashicons dashicons-admin-site"></span>
							<select name="' . esc_attr($name) . '[country]" class="formlayer-input ' . esc_attr($element_class) . '" ' . $required . ' ' . (!empty($addr_country_style) ? 'style="' . esc_attr($addr_country_style) . '"' : '') . '>
								<option value="">' . esc_html($p_country) . '</option>';
								foreach(\FormLayer\Frontend::get_countries() as $code => $clabel) {
									$html .= '<option value="' . esc_attr($code) . '">' . esc_html($clabel) . '</option>';
								}
					$html .= '</select>
						</div>
						<span class="formlayer-sub-label">' . esc_html($l_country) . '</span>
					</div>';
				}
				$html .= '</div>' . $help_html . '</div>';
				return $html;
			case 'date':
				return $wrap_open . $label_html . '
					<div class="formlayer-date-wrap">
						<input type="date" name="' . esc_attr($name) . '" class="formlayer-input ' . esc_attr($element_class) . '" ' . $required . ' value="' . esc_attr($field['default_value'] ?? '') . '" ' . $input_style . '>
						<span class="dashicons dashicons-calendar-alt"></span>
					</div>' . $help_html . '
				</div>';
			case 'url':
				$extra_attrs = '';
				if (!empty($field['url_validation'])) $extra_attrs .= ' data-validate-url="1"';
				if (!empty($field['url_https_only'])) $extra_attrs .= ' data-https-only="1"';
				return $wrap_open . $label_html . '
					<input type="text" name="' . esc_attr($name) . '" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($field['default_value'] ?? '') . '" ' . $required . ' ' . $input_style . $extra_attrs . '>' . $help_html . '
				</div>';
			case 'password':
				return $wrap_open . $label_html . '
					<input type="password" name="' . esc_attr($name) . '" class="formlayer-input ' . esc_attr($element_class) . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($field['default_value'] ?? '') . '" ' . $required . ' ' . $input_style . '>' . $help_html . '
				</div>';
			case 'hidden':
				return '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($field['default_value'] ?? '') . '">';
			case 'camera':
				return $wrap_open . $label_html . '
					<div class="formlayer-file-upload-box">
						<input type="file" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="formlayer-file-real ' . esc_attr($element_class) . '" accept="image/*" capture="environment" ' . $required . ' style="display:none;" onchange="this.nextElementSibling.nextElementSibling.innerText = this.files[0] ? this.files[0].name : \'No file chosen\'">
						<label for="' . esc_attr($name) . '" class="formlayer-file-fake-btn" ' . $btn_style . '>' . esc_html__('Choose File', 'formlayer-pro') . '</label>
						<span class="formlayer-file-chosen-name">' . esc_html__('No file chosen', 'formlayer-pro') . '</span>
					</div>' . $help_html . '
				</div>';
			case 'richtext':
				return $wrap_open . $label_html . '
					<textarea name="' . esc_attr($name) . '" class="formlayer-input formlayer-richtext-editor ' . esc_attr($element_class) . '" placeholder="' . esc_attr($placeholder) . '" ' . $required . ' ' . $input_style . '></textarea>' . $help_html . '
				</div>';
			case 'recaptcha':
				return $wrap_open . '
					<div class="g-recaptcha" data-sitekey="' . esc_attr(self::get_site_key('recaptcha')) . '"></div>
				</div>';
			case 'hcaptcha':
				return $wrap_open . '
					<div class="h-captcha" data-sitekey="' . esc_attr(self::get_site_key('hcaptcha')) . '"></div>
				</div>';
			case 'turnstile':
				return $wrap_open . '
					<div class="cf-turnstile" data-sitekey="' . esc_attr(self::get_site_key('turnstile')) . '"></div>
				</div>';
		}

		return $html;
	}

	static function get_site_key($provider) {
		$settings = get_option('formlayer_settings', []);
		return isset($settings[$provider . '_site_key']) ? $settings[$provider . '_site_key'] : '';
	}

	static function render_captcha($html, $provider, $field){
		$settings = get_option('formlayer_settings', []);
		$site_key = '';
		$theme = 'light';

		if($provider === 'turnstile'){
			$site_key = isset($settings['captcha_t_site_key']) ? $settings['captcha_t_site_key'] : '';
			$theme = isset($field['captcha_theme']) ? $field['captcha_theme'] : (isset($settings['captcha_t_theme']) ? $settings['captcha_t_theme'] : 'light');
		} elseif($provider === 'recaptcha'){
			$site_key = isset($settings['captcha_r_site_key']) ? $settings['captcha_r_site_key'] : '';
			$theme = isset($field['captcha_theme']) ? $field['captcha_theme'] : (isset($settings['captcha_r_theme']) ? $settings['captcha_r_theme'] : 'light');
		}

		if(empty($site_key)){
			return '';
		}

		if($provider === 'turnstile'){
			$html = '<div class="formlayer-field-item formlayer-captcha-container" data-provider="turnstile" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($theme) . '">
				<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($theme) . '"></div>
			</div>';
		} elseif($provider === 'recaptcha'){
			$html = '<div class="formlayer-field-item formlayer-captcha-container" data-provider="recaptcha" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($theme) . '">
				<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '" data-theme="' . esc_attr($theme) . '"></div>
			</div>';
		}

		return $html;
	}

	static function get_captcha_theme($theme, $provider){
		$settings = get_option('formlayer_settings', []);

		if($provider === 'turnstile'){
			return isset($settings['captcha_t_theme']) ? $settings['captcha_t_theme'] : 'light';
		} elseif($provider === 'recaptcha'){
			return isset($settings['captcha_r_theme']) ? $settings['captcha_r_theme'] : 'light';
		}

		return $theme;
	}

	static function get_captcha_sitekey($sitekey, $provider){
		$settings = get_option('formlayer_settings', []);

		if($provider === 'turnstile'){
			return isset($settings['captcha_t_site_key']) ? $settings['captcha_t_site_key'] : '';
		} elseif($provider === 'recaptcha'){
			return isset($settings['captcha_r_site_key']) ? $settings['captcha_r_site_key'] : '';
		}

		return $sitekey;
	}

	static function enqueue_captcha_scripts($providers = []){
		$settings = get_option('formlayer_settings', []);

		if(empty($providers)){
			$providers = ['recaptcha', 'turnstile'];
		}

		if(in_array('turnstile', $providers, true) && !empty($settings['captcha_t_site_key'])){
			wp_enqueue_script('turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], FORMLAYER_PRO_VERSION, true);
		}

		if(in_array('recaptcha', $providers, true) && !empty($settings['captcha_r_site_key'])){
			wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', [], FORMLAYER_PRO_VERSION, true);
			wp_enqueue_script('recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($settings['captcha_r_site_key']), [], FORMLAYER_PRO_VERSION, true);
		}
	}

	static function verify_pro_captcha($captcha_provider, $post_data, $global_settings){
		$captcha_verified = false;
		$captcha_token = '';
		$secret_key = '';
		$verify_url = '';

		if($captcha_provider === 'turnstile'){
			$captcha_token = isset($post_data['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($post_data['cf-turnstile-response'])) : '';
			$secret_key = isset($global_settings['captcha_t_secret_key']) ? $global_settings['captcha_t_secret_key'] : '';
			$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
		} elseif($captcha_provider === 'recaptcha'){
			$captcha_token = isset($post_data['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($post_data['g-recaptcha-response'])) : '';
			$secret_key = isset($global_settings['captcha_r_secret_key']) ? $global_settings['captcha_r_secret_key'] : '';
			$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
		}

		if(!empty($captcha_token) && !empty($secret_key)){
			$response = wp_remote_post($verify_url, [
				'body' => [
					'secret' => $secret_key,
					'response' => $captcha_token
				]
			]);
			
			if(!is_wp_error($response)) {
				$body = json_decode(wp_remote_retrieve_body($response), true);
				if($body && !empty($body['success'])){
					$captcha_verified = true;
				}
			}
		}

		return $captcha_verified;
	}

}