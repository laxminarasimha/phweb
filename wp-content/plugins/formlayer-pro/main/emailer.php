<?php
namespace FormLayerPro;

if(!defined('ABSPATH')){
	exit;
}

class Emailer {

	static function init(){
		add_action('formlayer_send_user_confirmation', '\FormLayerPro\Emailer::send_user_confirmation', 10, 2);
	}

	// Email Confirmation (End User)
	static function send_user_confirmation($form, $submitted_data){
		$form_id = $form->ID;
		$form_data = (class_exists('\FormLayer\Util') && method_exists('\FormLayer\Util', 'get_form_data')) ? \FormLayer\Util::get_form_data($form) : [];
		$settings = isset($form_data['settings']) ? $form_data['settings'] : [];

		$built = \FormLayer\Emailer::build_fields($form_id, $submitted_data);
		$fields_html = $built['fields_html'];
		$fields_text = $built['fields_text'];

		$conf_mail_sent = false;
		if (!empty($settings['email_confirmation']['enabled'])) {
			// Get recipient email field merge tag
			$conf_to_field = !empty($settings['email_confirmation']['to_email']) ? $settings['email_confirmation']['to_email'] : '';

			// Build plain merge tags to extract the email address
			$plain_merge_tags = \FormLayer\Emailer::build_merge_tags(false, $fields_html, $fields_text, $form, $submitted_data);
			$conf_to = \FormLayer\Emailer::strip_newlines(strtr($conf_to_field, $plain_merge_tags));

			if (is_email($conf_to)) {
				$conf_subject = !empty($settings['email_confirmation']['subject']) ? $settings['email_confirmation']['subject'] : 'Thank you for your submission!';
				$conf_message_body = !empty($settings['email_confirmation']['message']) ? $settings['email_confirmation']['message'] : "Thank you! We have received your submission:\n\n{all_fields}";

				$conf_is_html = false;
				$conf_format = isset($settings['email_confirmation']['format']) ? $settings['email_confirmation']['format'] : 'html';

				if($conf_format === 'html'){
					$conf_is_html = true;
				}

				$conf_merge_tags = \FormLayer\Emailer::build_merge_tags($conf_is_html, $fields_html, $fields_text, $form, $submitted_data);

				if($conf_is_html){
					$conf_message_body = wpautop($conf_message_body);
					$conf_message_body = str_replace('<p>{all_fields}</p>', '{all_fields}', $conf_message_body);
				} else {
					$conf_message_body = str_ireplace(['<br>', '<br/>', '<br />'], "\n", $conf_message_body);
					$conf_message_body = wp_strip_all_tags($conf_message_body);
				}

				$conf_subject = \FormLayer\Emailer::strip_newlines(strtr($conf_subject, $conf_merge_tags));
				$conf_message_body = strtr($conf_message_body, $conf_merge_tags);

				if($conf_is_html){
					$conf_branding = empty($settings['email_confirmation']['disable_branding']) ? '<div style="margin-top: 30px; text-align: center; font-size: 13px; color: #6b7280;">' . 'Powered by <a href="https://formlayer.net" style="color: #3b82f6; text-decoration: none;">FormLayer</a>' . '</div>' : '';

					$conf_message_body = '<div style="background-color: #f9fafb; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #374151; line-height: 1.6;">' . '<div style="max-width: 600px; margin: 0 auto;">' . $conf_message_body . $conf_branding . '</div>' .'</div>';
				}

				$conf_headers = [];
				$conf_from_name = !empty($settings['email_confirmation']['from_name']) ? \FormLayer\Emailer::strip_newlines(strtr($settings['email_confirmation']['from_name'], $conf_merge_tags)) : get_bloginfo('name');
				$conf_from_email = !empty($settings['email_confirmation']['from_email']) ? \FormLayer\Emailer::strip_newlines(strtr($settings['email_confirmation']['from_email'], $conf_merge_tags)) : get_option('admin_email');

				if($conf_from_email === '{admin_email}'){
					$conf_from_email = get_option('admin_email');
				}

				if(!is_email($conf_from_email)){
					$conf_from_email = get_option('admin_email');
				}

				$conf_headers[] = "From: " . $conf_from_name . " <" . $conf_from_email . ">";

				if(!empty($settings['email_confirmation']['reply_to'])){
					$reply_to = \FormLayer\Emailer::strip_newlines(strtr($settings['email_confirmation']['reply_to'], $conf_merge_tags));
					if(is_email($reply_to)){
						$conf_headers[] = "Reply-To: " . $reply_to;
					}
				}

				if(!empty($settings['email_confirmation']['bcc'])){
					$bcc_raw = \FormLayer\Emailer::strip_newlines(strtr($settings['email_confirmation']['bcc'], $conf_merge_tags));
					$bcc_parts = array_map('trim', explode(',', $bcc_raw));
					$bcc_valid = array_filter($bcc_parts, 'is_email');
					if(!empty($bcc_valid)){
						$conf_headers[] = "Bcc: " . implode(', ', $bcc_valid);
					}
				}

				$conf_content_type_filter = function() { return 'text/html'; };
				if($conf_is_html){
					add_filter('wp_mail_content_type', $conf_content_type_filter);
				}

				$conf_mail_sent = @wp_mail($conf_to, $conf_subject, $conf_message_body, $conf_headers);

				if($conf_is_html){
					remove_filter('wp_mail_content_type', $conf_content_type_filter);
				}
			}
		}

		return $conf_mail_sent;
	}
}
