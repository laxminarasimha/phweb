<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class Ajax{

	static function hooks(){

		add_action('wp_ajax_socialfeeds_pro_insta_save_settings', '\SocialFeedsPro\Ajax::save_settings');
		add_action('wp_ajax_socialfeeds_pro_validate_instagram_token', '\SocialFeedsPro\Ajax::validate_instagram_token');
		add_action('wp_ajax_socialfeeds_pro_instagram_fetch_posts', '\SocialFeedsPro\Ajax::instagram_fetch_posts');
		add_action('wp_ajax_socialfeeds_pro_delete_instagram_account', '\SocialFeedsPro\Ajax::delete_instagram_account');
		add_action('wp_ajax_nopriv_socialfeeds_load_more_instagram', '\SocialFeedsPro\Ajax::load_more_instagram');
		add_action('wp_ajax_socialfeeds_load_more_instagram','\SocialFeedsPro\Ajax::load_more_instagram');
		add_action('wp_ajax_socialfeeds_pro_facebook_save_settings', '\SocialFeedsPro\Ajax::facebook_save_settings');
		add_action('wp_ajax_socialfeeds_pro_validate_facebook_token', '\SocialFeedsPro\Ajax::validate_facebook_token');
		add_action('wp_ajax_socialfeeds_pro_facebook_fetch_posts', '\SocialFeedsPro\Ajax::facebook_fetch_posts');
		add_action('wp_ajax_socialfeeds_pro_delete_facebook_account', '\SocialFeedsPro\Ajax::delete_facebook_account');
		add_action('wp_ajax_nopriv_socialfeeds_load_more_facebook', '\SocialFeedsPro\Ajax::load_more_facebook');
		add_action('wp_ajax_socialfeeds_load_more_facebook','\SocialFeedsPro\Ajax::load_more_facebook');
		add_action('wp_ajax_socialfeeds_pro_version_notice', '\SocialFeedsPro\Ajax::socialfeeds_pro_version_notice');
		add_action('wp_ajax_socialfeeds_pro_google_reviews_save_settings', '\SocialFeedsPro\Ajax::google_reviews_save_settings');
		add_action('wp_ajax_socialfeeds_pro_google_reviews_wizard_save', '\SocialFeedsPro\Ajax::google_reviews_save_settings');
		add_action('wp_ajax_socialfeeds_pro_save_google_api_key', '\SocialFeedsPro\Ajax::save_google_api_key');
		add_action('wp_ajax_socialfeeds_pro_add_google_place', '\SocialFeedsPro\Ajax::add_google_place');
		add_action('wp_ajax_socialfeeds_pro_delete_google_account', '\SocialFeedsPro\Ajax::delete_google_account');
		add_action('wp_ajax_socialfeeds_pro_fetch_google_reviews_preview', '\SocialFeedsPro\Ajax::fetch_google_reviews_preview');
	}

	static function save_settings(){

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'socialfeeds-pro'));
		}

		$insta_opts = get_option('socialfeeds_instagram_option', []);

		$insta_settings = get_option('socialfeeds_instagram_settings', []);

		// 1. Instagram Access Token
		if(isset($_POST['instagram_access_token'])){
			$insta_opts['instagram_access_token'] = !empty($_POST['instagram_access_token']) ? sanitize_text_field(wp_unslash($_POST['instagram_access_token'])) : '';
		}

		// 2. Wizard Feed Save
		if (isset($_POST['feed_type'])) {
			$feed_type = sanitize_text_field(wp_unslash($_POST['feed_type']));

			$feed_settings = [
				'instagram_layout' => isset($_POST['instagram_layout']) ? sanitize_text_field(wp_unslash($_POST['instagram_layout'])) : 'grid',
				'instagram_aspect_ratio' => isset($_POST['instagram_aspect_ratio']) ? sanitize_text_field(wp_unslash($_POST['instagram_aspect_ratio'])) : 'square',
				'instagram_padding' => isset($_POST['instagram_padding']) ? intval($_POST['instagram_padding']) : 8,
				'instagram_number_posts_desktop' => isset($_POST['instagram_number_posts_desktop']) ? intval($_POST['instagram_number_posts_desktop']) : 12,
				'instagram_number_posts_mobile' => isset($_POST['instagram_number_posts_mobile']) ? intval($_POST['instagram_number_posts_mobile']) : 6,
				'instagram_columns_desktop' => isset($_POST['instagram_columns_desktop']) ? intval($_POST['instagram_columns_desktop']) : 3,
				'instagram_columns_tablet' => isset($_POST['instagram_columns_tablet']) ? intval($_POST['instagram_columns_tablet']) : 2,
				'instagram_columns_mobile' => isset($_POST['instagram_columns_mobile']) ? intval($_POST['instagram_columns_mobile']) : 1,
				'instagram_color_scheme' => isset($_POST['instagram_color_scheme']) ? sanitize_text_field(wp_unslash($_POST['instagram_color_scheme'])) : 'light',
				'instagram_custom_color' => isset($_POST['instagram_custom_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_custom_color'])) : '#000000',
				'instagram_header_enabled' => isset($_POST['instagram_header_enabled']) ? 1 : 0,
				'instagram_header_size' => isset($_POST['instagram_header_size']) ? sanitize_text_field(wp_unslash($_POST['instagram_header_size'])) : 'medium',
				'instagram_custom_avatar' => isset($_POST['instagram_custom_avatar']) ? esc_url_raw(wp_unslash($_POST['instagram_custom_avatar'])) : '',
				'instagram_show_bio_text' => isset($_POST['instagram_show_bio_text']) ? 1 : 0,
				'instagram_show_followers' => isset($_POST['instagram_show_followers']) ? 1 : 0,
				'instagram_media_count' => isset($_POST['instagram_media_count']) ? 1 : 0,
				'instagram_header_style' => isset($_POST['instagram_header_style']) ? sanitize_text_field(wp_unslash($_POST['instagram_header_style'])) : 'left',
				'instagram_caption_enabled' => isset($_POST['instagram_caption_enabled']) ? 1 : 0,
				'instagram_likes' => !empty($_POST['instagram_likes']) ? 1 : 0,
				'instagram_comments' => !empty($_POST['instagram_comments']) ? 1 : 0,
				'instagram_video_views' => !empty($_POST['instagram_video_views']) ? 1 : 0,
				'instagram_show_feed_posts' => isset($_POST['instagram_show_feed_posts']) ? 1 : 0,
				'instagram_show_reels' => isset($_POST['instagram_show_reels']) ? 1 : 0,
				'instagram_play_mode' => isset($_POST['instagram_play_mode']) ? sanitize_text_field(wp_unslash($_POST['instagram_play_mode'])) : 'newtab',
				'instagram_show_play_icon' => isset($_POST['instagram_show_play_icon']) ? 1 : 0,
				'instagram_hover_state' => isset($_POST['instagram_hover_state']) ? sanitize_text_field(wp_unslash($_POST['instagram_hover_state'])) : 'overlay',
				'instagram_alignment' => isset($_POST['instagram_alignment']) ? sanitize_text_field(wp_unslash($_POST['instagram_alignment'])) : 'left',
				'instagram_sort_by' => isset($_POST['instagram_sort_by']) ? sanitize_text_field(wp_unslash($_POST['instagram_sort_by'])) : 'newest',
				'instagram_follow_button_enabled' => isset($_POST['instagram_follow_button_enabled']) ? 1 : 0,
				'instagram_follow_button_text' => isset($_POST['instagram_follow_button_text']) ? sanitize_text_field(wp_unslash($_POST['instagram_follow_button_text'])) : 'Follow on Instagram',
				'instagram_follow_button_bg_color' => isset($_POST['instagram_follow_button_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_follow_button_bg_color'])) : '#00376B',
				'instagram_follow_button_text_color' => isset($_POST['instagram_follow_button_text_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_follow_button_text_color'])) : '#FFFFFF',
				'instagram_follow_button_hover_color' => isset($_POST['instagram_follow_button_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_follow_button_hover_color'])) : '#4e0ee4',
				'instagram_load_more_enabled' => isset($_POST['instagram_load_more_enabled']) ? 1 : 0,
				'instagram_load_more_text' => isset($_POST['instagram_load_more_text']) ? sanitize_text_field(wp_unslash($_POST['instagram_load_more_text'])) : 'Load More...',
				'instagram_load_more_bg_color' => isset($_POST['instagram_load_more_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_load_more_bg_color'])) : '#E74C3C',
				'instagram_load_more_text_color' => isset($_POST['instagram_load_more_text_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_load_more_text_color'])) : '#FFFFFF',
				'instagram_load_more_hover_color' => isset($_POST['instagram_load_more_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['instagram_load_more_hover_color'])) : '#f76606',
				'instagram_load_more_count' => isset($_POST['instagram_load_more_count']) ? sanitize_text_field(wp_unslash($_POST['instagram_load_more_count'])) : 12,
			];

			$feeds = isset($insta_opts['instagram_feeds']) ? $insta_opts['instagram_feeds'] : [];
			$input_val = '';
			$account_id = '';
			$account_token = '';

			if(isset($_POST['instagram_selected_account'])){
				$selected_account_index = intval(wp_unslash($_POST['instagram_selected_account']));
				if(isset($_POST['source_input']) && in_array($feed_type, ['hashtag', 'manual'])) {
					$input_val = sanitize_text_field(wp_unslash($_POST['source_input']));
				} else {
					$input_val = sanitize_text_field(wp_unslash($_POST['instagram_selected_account']));
				}
				$accounts = isset($insta_opts['instagram_connected_accounts']) ? $insta_opts['instagram_connected_accounts'] : [];
				if (isset($accounts[$selected_account_index])) {
					$account_id = isset($accounts[$selected_account_index]['id']) ? $accounts[$selected_account_index]['id'] : '';
					$account_token = isset($accounts[$selected_account_index]['token']) ? $accounts[$selected_account_index]['token'] : '';
				}
			} elseif(isset($_POST['source_input'])){
				$input_val = sanitize_text_field(wp_unslash($_POST['source_input']));
			}

			if(isset($input_val)){
				$preview = isset($_POST['preview_url']) ? rawurldecode(sanitize_text_field(wp_unslash($_POST['preview_url']))) : '';
				$edit_id = isset($_POST['edit_id']) ? sanitize_text_field(wp_unslash($_POST['edit_id'])) : '';
				
				if ($edit_id) {
					$updated = false;
					foreach($feeds as $k => $f){
						if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
							$feeds[$k]['settings'] = $feed_settings;
							$feeds[$k]['input'] = $input_val;
							$feeds[$k]['preview'] = $preview;
							$feeds[$k]['account_id'] = $account_id;
							$feeds[$k]['account_token'] = $account_token;
							$feeds[$k]['type'] = $feed_type;
							$updated = true;
							break;
							}
					}
					if(!$updated) $edit_id = '';
				}

				if(!$edit_id){
				$same_type_count = 0;
				foreach($feeds as $f){
					if(isset($f['type']) && $f['type'] === $feed_type) $same_type_count++;
				}

				$index = $same_type_count + 1;
				$feed_type_labels = [
					'channel' => 'Timeline',
					'manual' => 'Tagged',
				];

				$feed_type_label = isset( $feed_type_labels[ $feed_type ]) ? $feed_type_labels[ $feed_type ] : ucfirst($feed_type);
				$name = 'Instagram Feed - ' . $feed_type_label . ' ' . $index;
				$feed_id = isset($_POST['client_feed_id']) ? sanitize_text_field(wp_unslash($_POST['client_feed_id'])) : '';
				
				if(empty($feed_id)){
					// Get global ID counter
					$global_counter = get_option('socialfeeds_global_id_counter', 0);
					
					// Collect all existing IDs from both YouTube, Instagram and Facebook
					$all_existing_ids = [];
					
					// Instagram IDs
					foreach ($feeds as $f) {
						if (isset($f['id'])) {
							$all_existing_ids[] = intval($f['id']);
						}
					}
					
					// YouTube IDs
					$youtube_opts = get_option('socialfeeds_youtube_option', []);
					$youtube_feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];
					foreach($youtube_feeds as $f){
						if(isset($f['id'])){
							$all_existing_ids[] = intval($f['id']);
						}
					}

					// Facebook IDs
					$facebook_opts = get_option('socialfeeds_facebook_option', []);
					$facebook_feeds = isset($facebook_opts['facebook_feeds']) ? $facebook_opts['facebook_feeds'] : [];
					foreach($facebook_feeds as $f){
						if(isset($f['id'])){
							$all_existing_ids[] = intval($f['id']);
						}
					}
					
					// Find next available ID
					// If no feeds exist, start from 1 and reset counter
					if (empty($all_existing_ids)) {
						$next_id = 1;
						// Reset global counter
						update_option('socialfeeds_global_id_counter', 2);
					} else {
						$next_id = max($global_counter, 1);
						while(in_array($next_id, $all_existing_ids)){
							$next_id++;
						}
						// Update global counter
						update_option('socialfeeds_global_id_counter', $next_id + 1);
					}
					
					$feed_id = $next_id;
				}
				
				$feeds[] = [
					'id' => $feed_id,
					'name' => $name,
					'shortcode' => '[socialfeeds id="' . $feed_id . '" platform="instagram"]',
					'type' => $feed_type,
					'input' => $input_val,
					'preview' => $preview,
					'settings' => $feed_settings,
					'account_id' => $account_id,
					'account_token' => $account_token,
					'created' => time(),	
				];
			}	
				$insta_opts['instagram_feeds'] = $feeds;
			}
		}

		update_option('socialfeeds_instagram_option', $insta_opts);

		$response = ['message' => esc_html__('Settings saved successfully.', 'socialfeeds-pro')];

		wp_send_json_success([
			'message' => esc_html__('Settings saved successfully.', 'socialfeeds-pro'),
			'feed_id' => isset($feed_id) ? $feed_id : $edit_id,
			'feed_name' => isset($name) ? $name : '',
		]);

	}

	static function load_more_instagram(){
		check_ajax_referer('socialfeeds_frontend_nonce', 'nonce');

		$next_url = isset($_POST['next_url']) ? esc_url_raw( wp_unslash($_POST['next_url']) )  : '';
		$load_count = isset($_POST['load_count']) ? intval($_POST['load_count']) : 12;
		if (!$next_url) {
			wp_send_json_error('Missing next URL');
		}

		// Security
		$parsed_url = wp_parse_url($next_url);
		$allowed_hosts = ['graph.instagram.com', 'graph.facebook.com'];
		if (empty($parsed_url['host']) || !in_array($parsed_url['host'], $allowed_hosts, true)) {
			wp_send_json_error('Invalid URL domain');
		}

		$response = wp_safe_remote_get($next_url, ['timeout' => 20, 'sslverify' => true]);

		if (is_wp_error($response)) {
			wp_send_json_error('Request failed');
		}

		$data = json_decode(wp_remote_retrieve_body($response), true);

		if (empty($data['data'])) {
			wp_send_json_error('No data');
		}

		$show_reels = !isset($_POST['show_reels']) || ($_POST['show_reels'] === '1');
		$show_feed_posts = !isset($_POST['show_feed_posts']) || ($_POST['show_feed_posts'] === '1');

		$valid_data = [];
		foreach ($data['data'] as $post) {
			$media_type = isset($post['media_type']) ? strtoupper($post['media_type']) : 'IMAGE';
			$permalink = isset($post['permalink']) ? $post['permalink'] : '';

			$is_reel = ($media_type === 'VIDEO' || $media_type === 'REEL' || strpos($permalink, '/reel/') !== false);
			$is_post = !$is_reel;

			if (!$show_reels && $is_reel) continue;
			if (!$show_feed_posts && $is_post) continue;

			$valid_data[] = $post;
		}

		$posts_to_load = array_slice($valid_data, 0, $load_count);

		ob_start();

		$layout = isset($_POST['layout']) ? sanitize_text_field(wp_unslash($_POST['layout'])) : 'grid';
		$cols_desktop = isset($_POST['cols']) ? intval($_POST['cols']) : 3;
		$padding = isset($_POST['padding']) ? intval($_POST['padding']) : 0;
		$aspect_ratio = isset($_POST['aspect_ratio']) ? sanitize_text_field(wp_unslash($_POST['aspect_ratio'])) : 'square';

		$caption_enabled = !empty($_POST['caption_enabled']) && $_POST['caption_enabled'] !== '0';
		$likes_enabled = !empty($_POST['likes_enabled']) && $_POST['likes_enabled'] !== '0';
		$comments_enabled = !empty($_POST['comments_enabled']) && $_POST['comments_enabled'] !== '0';
		$views_enabled = !empty($_POST['views_enabled']) && $_POST['views_enabled'] !== '0';
		$hover_state = isset($_POST['hover_state']) ? sanitize_text_field(wp_unslash($_POST['hover_state'])) : 'overlay';
		$alignment = isset($_POST['alignment']) ? sanitize_text_field(wp_unslash($_POST['alignment'])) : 'left';
		$play_mode = isset($_POST['play_mode']) ? sanitize_text_field(wp_unslash($_POST['play_mode'])) : 'newtab';
		$show_play_icon = !isset($_POST['show_play_icon']) || ($_POST['show_play_icon'] !== '0');

		foreach ($posts_to_load as $post) {
			$media_type = isset($post['media_type']) ? strtoupper($post['media_type']) : 'IMAGE';
			$media_url = isset($post['media_url']) ? esc_url($post['media_url']) : (isset($post['thumbnail_url']) ? esc_url($post['thumbnail_url']) : '');
			$permalink = isset($post['permalink']) ? esc_url($post['permalink']) : '#';
			$caption = isset($post['caption']) ? esc_html($post['caption']) : '';
			$thumb_url = isset($post['thumbnail_url']) ? esc_url($post['thumbnail_url']) : $media_url;
			$item_style = '';
			if ($layout === 'carousel') {
				// Avoid div by zero
				$cols_d = $cols_desktop > 0 ? $cols_desktop : 3;
				// Same calculation as in shortcoderender
				$item_width = "calc((100% - " . (($cols_d - 1) * $padding) . "px) / " . $cols_d . ")";
				$item_style = ' style="flex: 0 0 ' . $item_width . '; max-width: ' . $item_width . ';"';
			} elseif ($layout === 'masonry') {
				$item_style = ' style="margin-bottom: ' . $padding . 'px;"';
			}

			$item_aspect = ($aspect_ratio === 'instagram') ? (($media_type === 'VIDEO') ? 'portrait' : 'square') : $aspect_ratio;
			if ($layout === 'masonry') $item_aspect = 'auto';

			echo '<div class="socialfeeds-instagram-item hover-' . esc_attr($hover_state) . '" style="' . esc_attr($item_style) . '">';
			echo '  <div class="socialfeeds-instagram-media aspect-' . esc_attr($item_aspect) . '">';
			
			if ($media_type === 'VIDEO') {
				if ($play_mode === 'inline') {
					echo '<video class="socialfeeds-video-player" controls preload="metadata" poster="' . esc_attr($thumb_url) . '"> <source src="' . esc_url($media_url) . '" type="video/mp4"> </video>';

					} else {
					$click_class_attr = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
					$target_attr = ($play_mode === 'newtab') ? ' target="_blank" rel="noopener noreferrer"' : '';
					$href_attr = ($play_mode === 'newtab') ? $permalink : '#';
					
					echo '<a href="' . esc_url($href_attr) . '"' . ( $play_mode === 'newtab' || $play_mode === 'inline' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . ' class="' . esc_attr($click_class_attr) . '"' . ' data-media="' . esc_url($media_url) . '"'. ' data-type="VIDEO"'. ' data-permalink="' . esc_url($permalink) . '">';
					
					if(strpos($thumb_url, '.mp4') !== false){
						echo '<video src="'.esc_url($thumb_url).'#t=0.001" preload="metadata" playsinline muted style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>';
					} else {
						echo '<img src="' . esc_url($thumb_url) . '" alt="Instagram video">';
					}

					if ($show_play_icon) {
						echo '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
					}
					echo '</a>';
				}

			} else {
				
				$click_class_attr = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
				$target_attr = ($play_mode === 'newtab' || $play_mode === 'inline') ? ' target="_blank" rel="noopener noreferrer"' : '';
				$href_attr = ($play_mode === 'newtab' || $play_mode === 'inline') ? $permalink : '#';

				echo '<a href="' . esc_url($href_attr) . '"'. ( ($play_mode === 'newtab' || $play_mode === 'inline') ? ' target="_blank" rel="noopener noreferrer"' : '' ). ' class="' . esc_attr($click_class_attr) . '"'. ' data-media="' . esc_url($media_url) . '"'. ' data-type="IMAGE"'. ' data-permalink="' . esc_url($permalink) . '">'. '<img src="' . esc_url($media_url) . '" alt="Instagram post">'. '</a>';
				}

			if($hover_state === 'overlay'){
				echo '<div class="socialfeeds-hover-overlay"></div>';
			}
			echo '  </div>'; // media

			// Stats
			echo '<div class="socialfeeds-instagram-stats" style="justify-content:' . ($alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start')) . ';">';
	
			if ($caption_enabled && !empty($caption)) {
				echo '<div class="caption" style="text-align:' . esc_attr($alignment) . ';">' . esc_html($caption) . '</div>';
			}

			if ($likes_enabled && isset($post['like_count'])) {
				echo '<span class="likes">❤️ ' . esc_html( number_format_i18n( $post['like_count'] ) ) . '</span>';
			}

			if ($comments_enabled && isset($post['comments_count'])) {
				echo '<span class="comments">💬 ' . esc_html( number_format_i18n( $post['comments_count'] ) ) . '</span>';
			}

			if ($views_enabled && $media_type === 'VIDEO' && isset($post['video_views'])) {
				echo '<span class="views">👁️ ' . esc_html( number_format_i18n( $post['video_views'] ) ) . '</span>';
			}

			echo '</div>'; // stats

			echo '</div>'; // item
		}

		// Slice next URL if there are more posts left
		$remaining_posts = count($data['data']) - $load_count;
		$next = ($remaining_posts > 0) ? (isset($data['paging']['next']) ? $data['paging']['next'] : null) : null;

		wp_send_json_success([
			'html'  => ob_get_clean(),
			'count' => count($posts_to_load),
			'next'  => $next,
		]);
	}

	static function delete_instagram_account(){
		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('You do not have permission.', 'socialfeeds-pro')]);
		}

		$account_id = isset($_POST['account_id']) ? sanitize_text_field(wp_unslash($_POST['account_id'])) : '';

		if(empty($account_id)){
			wp_send_json_error(['message' => __('Account ID is missing.', 'socialfeeds-pro')]);
		}

		$options = get_option('socialfeeds_instagram_option', []);
		$connected = isset($options['instagram_connected_accounts']) ? $options['instagram_connected_accounts'] : [];
		$found = false;

		foreach($connected as $k => $acct){
			if(isset($acct['id']) && (string) $acct['id'] === (string) $account_id){
				unset($connected[$k]);
				$found = true;
				break;
			}
		}

		if($found){
			$options['instagram_connected_accounts'] = array_values($connected);
			update_option('socialfeeds_instagram_option', $options);
			wp_send_json_success(['message' => __('Account deleted successfully.', 'socialfeeds-pro')]);
		}

		wp_send_json_error(['message' => __('Account not found.', 'socialfeeds-pro')]);
	}

	static function instagram_fetch_posts(){

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have permission.', 'socialfeeds-pro'));
		}

		$feed_type = isset($_POST['feed_type']) ? sanitize_text_field(wp_unslash($_POST['feed_type'])) : '';
		$limit = isset($_POST['limit']) ? intval($_POST['limit']) : '';
		$token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';

		$options = get_option('socialfeeds_instagram_option', []);
		$accounts = isset($options['instagram_connected_accounts']) ? $options['instagram_connected_accounts'] : [];

		$account_type = '';
		$token_type = isset($options['instagram_token_type']) ? $options['instagram_token_type'] : 'basic';
		$api_id = '';
		if(empty($token) && !empty($options['instagram_access_token'])){
			$token = $options['instagram_access_token'];
		}

		if(isset($_POST['selected_account'])){
			$selected_index = isset( $_POST['selected_account'] ) ? absint( wp_unslash( $_POST['selected_account'] ) ) : 0;
			if (isset($accounts[$selected_index])) {
				$acct = $accounts[$selected_index];
				$api_id = isset($acct['id']) ? $acct['id'] : '';
				$token = isset($acct['token']) ? $acct['token'] : $token;
				$account_type = isset($acct['account_type']) ? $acct['account_type'] : '';
				// Use per-account token_type if available, otherwise fall back to global
				$token_type = isset($acct['token_type']) ? $acct['token_type'] : $token_type;
			}
		}

		// If token_type is advanced, ensure account_type reflects BUSINESS for proper API routing
		if($token_type === 'advanced' && empty($account_type)){
			$account_type = 'BUSINESS';
		}

		if(empty($token) || empty($feed_type)){
			wp_send_json_error(['message' => 'Missing required data']);
		}

		$source_input = isset($_POST['source_input']) ? sanitize_text_field(wp_unslash($_POST['source_input'])) : '';


		$posts = \SocialFeedsPro\InstagramSettings::fetch_posts($token, $feed_type, $api_id, $limit, $source_input, true, $account_type);

		$account_info = \SocialFeedsPro\Util::get_account_info($token, $token_type);

		wp_send_json_success([
			'account' => $account_info,
			'posts'   => isset($posts['data']) ? $posts['data'] : (is_array($posts) ? $posts : []),
			'error'   => isset($posts['error']) ? $posts['error'] : '',
		]);
	}	

	static function validate_instagram_token(){

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('You do not have required permission.', 'socialfeeds-pro')]);
		}

		$token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
		$token_type = isset($_POST['token_type']) ? sanitize_text_field(wp_unslash($_POST['token_type'])) : 'basic';

		// Validate token_type is one of the allowed values
		if(!in_array($token_type, ['basic', 'advanced'], true)){
			$token_type = 'basic';
		}

		if(empty($token)){
			wp_send_json_error(['message' => __('Access token is required.', 'socialfeeds-pro')]);
		}

		$app_id = isset($_POST['instagram_app_id']) ? sanitize_text_field(wp_unslash($_POST['instagram_app_id'])) : '';
		$app_secret = isset($_POST['instagram_app_secret']) ? sanitize_text_field(wp_unslash($_POST['instagram_app_secret'])) : '';
		$expires_at = 0;

		// Use the appropriate validation method based on token type
		if($token_type === 'advanced'){
			// Before validating, try to exchange for a long-lived token if app credentials are provided
			if(!empty($app_id) && !empty($app_secret)){
				$exchange_resp = \SocialFeedsPro\InstagramSettings::exchange_for_long_lived_token($token, $app_id, $app_secret);
				if(!empty($exchange_resp['access_token'])){
					$token = $exchange_resp['access_token'];
					if(!empty($exchange_resp['expires_in'])){
						$expires_at = time() + intval($exchange_resp['expires_in']);
					}
				} elseif (!empty($exchange_resp['error'])){
					/* translators: %s: Error message returned during token exchange. */
					wp_send_json_error(['message' => sprintf(__('Token exchange failed: %s', 'socialfeeds-pro'), $exchange_resp['error'])]);
				}
			}

			$instagram_user_id = isset($_POST['instagram_user_id']) ? sanitize_text_field(wp_unslash($_POST['instagram_user_id'])) : '';
			$info = \SocialFeedsPro\InstagramSettings::validate_token_advanced($token, $instagram_user_id);
		} else {
			$info = \SocialFeedsPro\InstagramSettings::validate_token($token);
		}

		// validate_token() / validate_token_advanced() will already send json error on failure
		if(empty($info) || !is_array($info)){
			wp_send_json_error(['message' => __('Invalid Instagram token.', 'socialfeeds-pro')]);
		}

		$options = get_option('socialfeeds_instagram_option', []);
		
		if(!is_array($options)){
			$options = [];
		}

		$connected = isset($options['instagram_connected_accounts']) ? $options['instagram_connected_accounts'] : [];

		$found = false;

		foreach($connected as $k => $acct){
			if(isset($acct['id']) && (string) $acct['id'] === (string) $info['id']){
				$connected[$k] = array_merge($acct, [
					'id' => $info['id'],
					'username' => $info['username'],
					'name' => $info['name'],
					'profile_picture_url' => $info['profile_picture_url'],
					'account_type' => $info['account_type'],
					'token' => $token,
					'token_type' => $token_type,
					'app_id' => $app_id,
					'app_secret' => $app_secret,
					'expires_at' => $expires_at,
					'last_refresh' => time(),
				]);
				$found = true;
				break;
			}
		}

		if(!$found){
			$connected[] = [
				'id' => $info['id'],
				'username' => $info['username'],
				'name' => $info['name'],
				'profile_picture_url' => $info['profile_picture_url'],
				'account_type' => $info['account_type'],
				'token' => $token,
				'token_type' => $token_type,
				'app_id' => $app_id,
				'app_secret' => $app_secret,
				'expires_at' => $expires_at,
				'last_refresh' => time(),
			];
		}

		$options['instagram_connected_accounts'] = $connected;
		$options['instagram_access_token'] = $token;
		$options['instagram_account_id'] = $info['id'];
		$options['instagram_token_type'] = $token_type;

		update_option('socialfeeds_instagram_option', $options);

		wp_send_json_success([
			'message' => esc_html__('Account connected successfully.', 'socialfeeds-pro'),
			'account' => $info,
			'token_type' => $token_type,
		]);
	}

	static function socialfeeds_pro_version_notice(){
		check_admin_referer('socialfeeds_version_notice', 'security');

		if(!current_user_can('activate_plugins')){
			wp_send_json_error(__('You do not have required access to do this action', 'socialfeeds-pro'));
		}
		
		$type = '';
		if(!empty($_REQUEST['type'])){
			$type = sanitize_text_field(wp_unslash($_REQUEST['type']));
		}

		if(empty($type)){
			wp_send_json_error(__('Unknow version difference type', 'socialfeeds-pro'));
		}
		
		update_option('socialfeeds_version_'. $type .'_nag', time() + WEEK_IN_SECONDS);
		wp_send_json_success();
	}

	static function facebook_save_settings(){
		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission.', 'socialfeeds-pro'));
		}

		$fb_opts = get_option('socialfeeds_facebook_option', []);
		if(isset($_POST['facebook_access_token'])){
			$fb_opts['facebook_access_token'] = !empty($_POST['facebook_access_token']) ? sanitize_text_field(wp_unslash($_POST['facebook_access_token'])) : '';
		}

		$feed_type = isset($_POST['facebook_feed_type']) ? sanitize_text_field(wp_unslash($_POST['facebook_feed_type'])) : (isset($_POST['feed_type']) ? sanitize_text_field(wp_unslash($_POST['feed_type'])) : 'timeline');
		
		if ($feed_type) {
			$feed_settings = [
				'facebook_layout' => isset($_POST['facebook_layout']) ? sanitize_text_field(wp_unslash($_POST['facebook_layout'])) : 'grid',
				'facebook_aspect_ratio' => isset($_POST['facebook_aspect_ratio']) ? sanitize_text_field(wp_unslash($_POST['facebook_aspect_ratio'])) : 'square',
				'facebook_padding' => isset($_POST['facebook_padding']) ? intval($_POST['facebook_padding']) : 8,
				'facebook_posts_per_page' => isset($_POST['facebook_posts_per_page']) ? intval($_POST['facebook_posts_per_page']) : 12,
				'facebook_number_posts_mobile' => isset($_POST['facebook_number_posts_mobile']) ? intval($_POST['facebook_number_posts_mobile']) : 6,
				'facebook_columns_desktop' => isset($_POST['facebook_columns_desktop']) ? intval($_POST['facebook_columns_desktop']) : 3,
				'facebook_columns_tablet' => isset($_POST['facebook_columns_tablet']) ? intval($_POST['facebook_columns_tablet']) : 2,
				'facebook_columns_mobile' => isset($_POST['facebook_columns_mobile']) ? intval($_POST['facebook_columns_mobile']) : 1,
				'facebook_color_scheme' => isset($_POST['facebook_color_scheme']) ? sanitize_text_field(wp_unslash($_POST['facebook_color_scheme'])) : 'light',
				'facebook_custom_color' => isset($_POST['facebook_custom_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_custom_color'])) : '#000000',
				'facebook_header_enabled' => isset($_POST['facebook_header_enabled']) ? 1 : 0,
				'facebook_header_cover_enabled' => isset($_POST['facebook_header_cover_enabled']) ? 1 : 0,
				'facebook_header_avatar_enabled' => isset($_POST['facebook_header_avatar_enabled']) ? 1 : 0,
				'facebook_header_caption_enabled' => isset($_POST['facebook_header_caption_enabled']) ? 1 : 0,
				'facebook_header_stats_enabled' => isset($_POST['facebook_header_stats_enabled']) ? 1 : 0,
				'facebook_header_style' => isset($_POST['facebook_header_style']) ? sanitize_text_field(wp_unslash($_POST['facebook_header_style'])) : 'standard',
				'facebook_load_more_enabled' => isset($_POST['facebook_load_more_enabled']) ? 1 : 0,
				'facebook_load_more_text' => isset($_POST['facebook_load_more_text']) ? sanitize_text_field(wp_unslash($_POST['facebook_load_more_text'])) : 'Load More',
				'facebook_load_more_bg_color' => isset($_POST['facebook_load_more_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_load_more_bg_color'])) : '#f1f5f9',
				'facebook_load_more_text_color' => isset($_POST['facebook_load_more_text_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_load_more_text_color'])) : '#1e293b',
				'facebook_load_more_hover_color' => isset($_POST['facebook_load_more_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_load_more_hover_color'])) : '#e2e8f0',
				'facebook_load_more_count' => isset($_POST['facebook_load_more_count']) ? intval($_POST['facebook_load_more_count']) : 9,
				'facebook_follow_button_enabled' => isset($_POST['facebook_follow_button_enabled']) ? 1 : 0,
				'facebook_follow_button_text' => isset($_POST['facebook_follow_button_text']) ? sanitize_text_field(wp_unslash($_POST['facebook_follow_button_text'])) : 'Follow on Facebook',
				'facebook_follow_button_bg_color' => isset($_POST['facebook_follow_button_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_follow_button_bg_color'])) : '#1877F2',
				'facebook_follow_button_text_color' => isset($_POST['facebook_follow_button_text_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_follow_button_text_color'])) : '#FFFFFF',
				'facebook_follow_button_hover_color' => isset($_POST['facebook_follow_button_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['facebook_follow_button_hover_color'])) : '#0e5a9a',
				'facebook_sort_by' => isset($_POST['facebook_sort_by']) ? sanitize_text_field(wp_unslash($_POST['facebook_sort_by'])) : 'default',
				'facebook_hover_state' => isset($_POST['facebook_hover_state']) ? sanitize_text_field(wp_unslash($_POST['facebook_hover_state'])) : 'none',
				'facebook_likes' => isset($_POST['facebook_likes']) ? 1 : 0,
				'facebook_comments' => isset($_POST['facebook_comments']) ? 1 : 0,
				'facebook_play_mode'=> isset($_POST['facebook_play_mode']) ? sanitize_text_field(wp_unslash($_POST['facebook_play_mode'])) : 'lightbox',
				];

			$edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
			$input_val = isset($_POST['source_input']) ? sanitize_text_field(wp_unslash($_POST['source_input'])) : '';
			$account_token = isset($_POST['account_token']) ? sanitize_text_field(wp_unslash($_POST['account_token'])) : '';
			$account_id = isset($_POST['account_id']) ? sanitize_text_field(wp_unslash($_POST['account_id'])) : '';

			$feeds = isset($fb_opts['facebook_feeds']) ? $fb_opts['facebook_feeds'] : [];
			$name = isset($_POST['feed_name']) ? sanitize_text_field(wp_unslash($_POST['feed_name'])) : '';

			if(empty($name) && $edit_id <= 0){
				$same_type_count = 0;
				foreach($feeds as $f){
					if(isset($f['type']) && $f['type'] === $feed_type) $same_type_count++;
				}
				$index = $same_type_count + 1;
				$feed_type_label = ucfirst($feed_type);
				$name = 'Facebook Feed - ' . $feed_type_label . ' ' . $index;
			}

			// For existing feeds with no name submitted, preserve the stored name
			if(empty($name) && $edit_id > 0){
				foreach($feeds as $f){
					if(isset($f['id']) && intval($f['id']) === $edit_id){
						$name = isset($f['name']) ? $f['name'] : '';
						break;
					}
				}
			}

			if(isset($_POST['selected_account'])){
				$selected_account_index = intval(wp_unslash($_POST['selected_account']));
				$accounts = isset($fb_opts['facebook_connected_accounts']) ? $fb_opts['facebook_connected_accounts'] : [];
				if (isset($accounts[$selected_account_index])) {
					$account_id = isset($accounts[$selected_account_index]['id']) ? $accounts[$selected_account_index]['id'] : '';
					$account_token = isset($accounts[$selected_account_index]['token']) ? $accounts[$selected_account_index]['token'] : '';
					
					if(empty($input_val)){
						$input_val = $account_id;
					}
				}
			}

			if($edit_id > 0){
				foreach ($feeds as $k => $f) {
					if (intval($f['id']) === $edit_id) {
						$feeds[$k]['settings'] = $feed_settings;
						$feeds[$k]['name'] = $name;
						$feeds[$k]['input'] = $input_val;
						$feeds[$k]['account_token'] = $account_token;
						$feeds[$k]['account_id'] = $account_id;
						$feeds[$k]['type'] = $feed_type;
						break;
					}
				}
			} else {
				$feed_id = isset($_POST['client_feed_id']) ? sanitize_text_field(wp_unslash($_POST['client_feed_id'])) : '';

				if(empty($feed_id)){
					$feed_id = \SocialFeedsPro\Util::get_next_feed_id(true);
				}

				$feeds[] = [
					'id' => $feed_id,
					'name' => $name,
					'shortcode' => '[socialfeeds id="' . $feed_id . '" platform="facebook"]',
					'type' => $feed_type,
					'input' => $input_val,
					'settings' => $feed_settings,
					'account_id' => $account_id,
					'account_token' => $account_token,
					'created' => time(),
				];
			}
			$fb_opts['facebook_feeds'] = $feeds;
		}

		update_option('socialfeeds_facebook_option', $fb_opts);
		wp_send_json_success([
			'message'   => __('Settings saved.', 'socialfeeds-pro'),
			'feed_id'   => isset($feed_id) ? $feed_id : $edit_id,
			'feed_name' => $name,
		]);
	}

	static function validate_facebook_token(){
		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('You do not have required permission.', 'socialfeeds-pro')]);
		}

		$token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
		$token_type = isset($_POST['token_type']) ? sanitize_text_field(wp_unslash($_POST['token_type'])) : 'advanced';
		$page_id = isset($_POST['facebook_page_id']) ? sanitize_text_field(wp_unslash($_POST['facebook_page_id'])) : '';

		if($token_type === 'advanced'){
			$app_id = isset($_POST['facebook_app_id']) ? sanitize_text_field(wp_unslash($_POST['facebook_app_id'])) : '';
			$app_secret = isset($_POST['facebook_app_secret']) ? sanitize_text_field(wp_unslash($_POST['facebook_app_secret'])) : '';
			$resp = \SocialFeedsPro\Facebook::exchange_for_long_lived_token($token, $app_id, $app_secret);
			if(!empty($resp['access_token'])){
				$token = $resp['access_token'];
			}
		}

		$data = \SocialFeedsPro\Facebook::validate_token($token, $page_id);

		if(!empty($data['error'])){
			wp_send_json_error(['message' => $data['error']]);
		}

		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$accounts = isset($fb_opts['facebook_connected_accounts']) ? $fb_opts['facebook_connected_accounts'] : [];
		
		$new_account = [
			'id' => $data['id'],
			'name' => $data['name'],
			'token' => $token,
			'token_type' => $token_type,
			'picture' => isset($data['picture']['data']['url']) ? $data['picture']['data']['url'] : '',
		];

		if($token_type === 'advanced'){
			$new_account['app_id'] = $app_id;
			$new_account['app_secret'] = $app_secret;
		}

		$found = false;
		foreach($accounts as $k => $acc){
			if($acc['id'] === $data['id']){
				$accounts[$k] = $new_account;
				$found = true;
				break;
			}
		}

		if(!$found) $accounts[] = $new_account;

		$fb_opts['facebook_connected_accounts'] = $accounts;
		$fb_opts['facebook_token_type'] = $token_type;
		update_option('socialfeeds_facebook_option', $fb_opts);

		wp_send_json_success(['account' => $new_account]);
	}

	static function facebook_fetch_posts(){
		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('You do not have required permission.', 'socialfeeds-pro')]);
		}

		$account_index = isset($_POST['selected_account']) ? intval($_POST['selected_account']) : -1;
		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$accounts = isset($fb_opts['facebook_connected_accounts']) ? $fb_opts['facebook_connected_accounts'] : [];

		if(!isset($accounts[$account_index])){
			wp_send_json_error(['error' => __('Invalid account selected', 'socialfeeds-pro')]);
		}

		$account = $accounts[$account_index];
		$token = $account['token'];
		$page_id = isset($_POST['source_input']) ? sanitize_text_field(wp_unslash($_POST['source_input'])) : $account['id'];
		$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;

		$feed_type = isset($_POST['feed_type']) ? sanitize_text_field(wp_unslash($_POST['feed_type'])) : 'timeline';
		$feed = ['type' => $feed_type, 'input' => $page_id];
		$posts = apply_filters('socialfeeds_facebook_fetch_items', [], $feed, $token, $limit);

		$account_info = \SocialFeedsPro\Util::get_facebook_account_info($token, $account['id']);

		wp_send_json_success(['posts' => $posts, 'account' => $account_info]);
	}

	static function delete_facebook_account(){
		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => __('You do not have required permission.', 'socialfeeds-pro')]);
		}

		$id = isset($_POST['account_id']) ? sanitize_text_field(wp_unslash($_POST['account_id'])) : '';
		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$accounts = isset($fb_opts['facebook_connected_accounts']) ? $fb_opts['facebook_connected_accounts'] : [];

		foreach($accounts as $k => $acc){
			if($acc['id'] === $id){
				unset($accounts[$k]);
				break;
			}
		}

		$fb_opts['facebook_connected_accounts'] = array_values($accounts);

		update_option('socialfeeds_facebook_option', $fb_opts);
		wp_send_json_success();
	}

	static function load_more_facebook(){
		check_ajax_referer('socialfeeds_frontend_nonce', 'nonce');

		$feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;
		$load_count = isset($_POST['load_count']) ? intval($_POST['load_count']) : 9;
		$after_cursor = isset($_POST['after_cursor']) ? sanitize_text_field(wp_unslash($_POST['after_cursor'])) : '';
		// IDs already visible on the page — filter these out to prevent duplicate posts
		$existing_ids = [];
		if(!empty($_POST['existing_ids']) && is_array($_POST['existing_ids'])){
			$existing_ids = array_map('sanitize_text_field', wp_unslash($_POST['existing_ids']));
		}

		if (empty($feed_id)) {
			wp_send_json_error('Missing feed ID');
		}

		$options = get_option('socialfeeds_facebook_option', []);
		$feeds = isset($options['facebook_feeds']) ? $options['facebook_feeds'] : [];

		$found_feed = null;
		foreach ($feeds as $feed) {
			if (isset($feed['id']) && intval($feed['id']) === $feed_id) {
				$found_feed = $feed;
				break;
			}
		}

		if (!$found_feed) {
			wp_send_json_error('Feed not found');
		}

		$token = isset($found_feed['account_token']) ? $found_feed['account_token'] : (isset($options['facebook_access_token']) ? $options['facebook_access_token'] : '');
		if (empty($token)) {
			wp_send_json_error('Missing token');
		}

		// Fetch the next page of posts using the cursor from the previous response
		$feed_obj = ['id' => $feed_id, 'type' => isset($found_feed['type']) ? $found_feed['type'] : 'timeline', 'input' => isset($found_feed['input']) ? $found_feed['input'] : ''];
		$posts = apply_filters('socialfeeds_facebook_fetch_items', [], $feed_obj, $token, $load_count, $after_cursor);

		// Remove any posts already displayed on the page
		if(!empty($existing_ids)){
		    $posts = array_filter($posts, function ($p) use ($existing_ids){
		        $id = isset($p['id']) ? (string) $p['id'] : '';
		        return !in_array($id, $existing_ids, true);
		    });
		    $posts = array_values($posts);
		}

		if (empty($posts)) {
			wp_send_json_error('No posts found');
		}

		// Get settings
		$feed_settings = isset($found_feed['settings']) ? $found_feed['settings'] : [];
		$opts = array_merge($options, $feed_settings);

		$layout = isset($opts['facebook_layout']) ? $opts['facebook_layout'] : 'grid';
		$padding = intval(isset($opts['facebook_padding']) ? $opts['facebook_padding'] : 8);
		$cols_desktop = intval(isset($opts['facebook_columns_desktop']) ? $opts['facebook_columns_desktop'] : 3);
		$aspect_ratio = isset($opts['facebook_aspect_ratio']) ? $opts['facebook_aspect_ratio'] : 'square';
		$play_mode = isset($opts['facebook_play_mode']) ? $opts['facebook_play_mode'] : 'newtab';
		$hover_state = isset($opts['facebook_hover_state']) ? $opts['facebook_hover_state'] : 'overlay';
		$show_likes = isset($opts['facebook_likes']) ? (bool)$opts['facebook_likes'] : true;
		$show_comments = isset($opts['facebook_comments']) ? (bool)$opts['facebook_comments'] : true;

		ob_start();

		foreach ($posts as $post) {
			$item_style = '';
			if ($layout === 'carousel') {
				$item_width = "calc((100% - " . (($cols_desktop - 1) * $padding) . "px) / " . $cols_desktop . ")";
				$item_style = ' style="flex: 0 0 ' . esc_attr($item_width) . '; max-width: ' . esc_attr($item_width) . ';"';
			} elseif ($layout === 'list') {
				$item_style = ' style="width: 100%;"';
			}

			$media_url = isset($post['full_picture']) ? $post['full_picture'] : '';
			$permalink = isset($post['permalink_url']) ? $post['permalink_url'] : '#';
			$message = isset($post['message']) ? $post['message'] : '';
			$type = isset($post['type']) ? $post['type'] : 'timeline';
			// so $media_type will properly be 'VIDEO' for video posts
			$media_type = ($type === 'video' || $type === 'reel') ? 'VIDEO' : 'IMAGE';

			$post_id_attr = isset($post['id']) ? ' data-post-id="' . esc_attr($post['id']) . '"' : '';
			echo '<div class="socialfeeds-facebook-item socialfeeds-fb-type-' . esc_attr($type) . ' hover-' . esc_attr($hover_state) . '"' . $post_id_attr . wp_kses_data($item_style) . '>';
			
			$aspect_class = ($layout === 'list') ? 'auto' : $aspect_ratio;
			echo '<div class="socialfeeds-facebook-media aspect-' . esc_attr($aspect_class) . '" style="position:relative; overflow:hidden;">';
			
			if ($media_type === 'VIDEO' && $play_mode === 'inline') {
				// For inline video: use full_picture as poster, link to permalink for actual playback
				// (Facebook Graph API does not expose raw .mp4 URLs to page tokens)
				echo '<a href="' . esc_url($permalink) . '" target="_blank" rel="noopener noreferrer">';
				if (!empty($media_url)) {
					echo '<img src="' . esc_url($media_url) . '" alt="Facebook video">';
				} else {
					echo '<div class="socialfeeds-video-placeholder">📹 Video</div>';
				}
				echo '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
				echo '</a>';
			} else {
				$click_class = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
				$target = ($play_mode === 'newtab') ? 'target="_blank" rel="noopener noreferrer"' : '';
				$href = ($play_mode === 'newtab' || $play_mode === 'inline') ? $permalink : '#';
				
				echo '<a href="' . esc_url($href) . '" ' . wp_kses_data($target) . ' class="' . esc_attr($click_class) . '" data-media="' . esc_url($media_url) . '" data-type="' . esc_attr($media_type) . '" data-permalink="' . esc_url($permalink) . '">';
				
				if (!empty($media_url)) {
					echo '<img src="' . esc_url($media_url) . '" alt="Facebook ' . esc_attr($type) . '">';
				} else {
					echo '<div class="socialfeeds-video-placeholder">📹 No Media</div>';
				}

				if ($media_type === 'VIDEO') {
					echo '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
				}
				echo '</a>';
			}

			if ($hover_state === 'overlay') {
				echo '<div class="socialfeeds-hover-overlay"></div>';
			}
			
			if ($type === 'album' && !empty($post['count'])) {
				echo '<div class="socialfeeds-fb-album-count"><span class="dashicons dashicons-images-alt2"></span> ' . intval($post['count']) . ' photo' . ($post['count'] > 1 ? 's' : '') . '</div>';
			}
			if ($type === 'event' && !empty($post['start_time'])) {
				echo '<div class="socialfeeds-fb-event-badge"><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html(date_i18n('M j', strtotime($post['start_time']))) . '</div>';
			}
			echo '</div>'; // media

			echo '<div class="socialfeeds-facebook-content">';
			if (!empty($message)) {
				echo '<div class="socialfeeds-facebook-message" style="font-weight:600; font-size:12px;">' . wp_kses_post(wp_trim_words(esc_html($message), 15)) . '</div>';
			}
			
			if ($type === 'event') {
				if (!empty($post['start_time'])) {
					echo '<div class="socialfeeds-fb-event-time"><span class="dashicons dashicons-clock"></span> ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post['start_time']))) . '</div>';
				}
				if (!empty($post['place']['name'])) {
					echo '<div class="socialfeeds-fb-event-place"><span class="dashicons dashicons-location"></span> ' . esc_html($post['place']['name']) . '</div>';
				}
			}
			
			if ($type === 'album' && !empty($post['description'])) {
				echo '<div class="socialfeeds-fb-album-desc">' . wp_kses_post(wp_trim_words(esc_html($post['description']), 20)) . '</div>';
			}

			// Add engagement counts if enabled
			if ($show_likes || $show_comments) {
				echo '<div class="socialfeeds-fb-engagement" style="display:flex; align-items:center; gap:12px; padding:8px 0; margin-top:8px;">';
				if ($show_likes) {
					$like_count = isset($post['like_count']) ? $post['like_count'] : 0;
					echo '<span style="display:flex; align-items:center; gap:4px; color:#999; font-size:11px;"><span class="dashicons dashicons-heart" style="font-size:14px; width:14px; height:14px;"></span> ' . esc_html(number_format_i18n($like_count)) . '</span>';
				}
				if ($show_comments) {
					$comment_count = isset($post['comment_count']) ? $post['comment_count'] : 0;
					echo '<span style="display:flex; align-items:center; gap:4px; color:#999; font-size:11px;"><span class="dashicons dashicons-admin-comments" style="font-size:14px; width:14px; height:14px;"></span> ' . esc_html(number_format_i18n($comment_count)) . '</span>';
				}
				echo '</div>';
			}

			echo '</div>'; // content
			echo '</div>'; // item
		}

		// Read cursor that facebook.php stored during the last fetch
		$next_cursor = \SocialFeedsPro\Facebook::$last_after_cursor;
		$has_next = \SocialFeedsPro\Facebook::$last_has_next;

		wp_send_json_success([
			'html' => ob_get_clean(),
			'has_more' => $has_next,
			'next_cursor' => $next_cursor,
		]);
	}
	
	static function get_all_feed_ids(){
		$all_existing_ids = [];

		$map = [
			'socialfeeds_instagram_option' => 'instagram_feeds',
			'socialfeeds_youtube_option' => 'youtube_feeds',
			'socialfeeds_facebook_option' => 'facebook_feeds',
			'socialfeeds_google_option' => 'google_reviews_feeds',
		];

		foreach($map as $option_key => $feed_key){

			$opts = get_option($option_key, []);

			if(!is_array($opts) || empty($opts[$feed_key])){
				continue;
			}

			foreach($opts[$feed_key] as $feed){
				if(!empty($feed['id'])){
					$all_existing_ids[] = (int) $feed['id'];
				}
			}
		}
		return array_values(array_unique($all_existing_ids));
	}

	static function get_next_feed_id(){

		$all_existing_ids = self::get_all_feed_ids();
		
		if(empty($all_existing_ids)){
			$next_id = 1;
			update_option('socialfeeds_global_id_counter', 2);
			return $next_id;
		}
		
		$global_counter = (int) get_option('socialfeeds_global_id_counter', 1);
		$max_existing_id = max($all_existing_ids);
		$next_id = max($max_existing_id + 1, $global_counter);

		update_option('socialfeeds_global_id_counter', $next_id + 1);

		return $next_id;
	}

	static function google_reviews_save_settings() {

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission.', 'socialfeeds-pro'));
		}

		$feed_settings = [
			'google_reviews_header_enabled' => isset($_POST['google_reviews_header_enabled']) ? 1 : 0,
			'google_reviews_header_title' => isset($_POST['google_reviews_header_title']) ? 1 : 0,
			'google_custom_header_text' => isset($_POST['google_custom_header_text']) ? sanitize_text_field(wp_unslash($_POST['google_custom_header_text'])) : '',
			'google_reviews_header_description' => isset($_POST['google_reviews_header_description']) ? 1 : 0,
			'google_reviews_layout' => isset($_POST['google_reviews_layout']) ? sanitize_text_field(wp_unslash($_POST['google_reviews_layout'])) : 'grid',
			'google_reviews_columns_desktop' => isset($_POST['google_reviews_columns_desktop']) ? intval($_POST['google_reviews_columns_desktop']) : 3,
			'google_reviews_columns_mobile' => isset($_POST['google_reviews_columns_mobile']) ? intval($_POST['google_reviews_columns_mobile']) : 1,
			'google_reviews_padding' => isset($_POST['google_reviews_padding']) ? intval($_POST['google_reviews_padding']) : 8,
			'google_reviews_sort_by' => isset($_POST['google_reviews_sort_by']) ? sanitize_text_field(wp_unslash($_POST['google_reviews_sort_by'])) : 'newest',
			'google_reviews_min_rating' => isset($_POST['google_reviews_min_rating']) ? intval($_POST['google_reviews_min_rating']) : 1,
			'google_reviews_hover_state' => isset($_POST['google_reviews_hover_state']) ? sanitize_text_field(wp_unslash($_POST['google_reviews_hover_state'])) : 'overlay',
			'rating_enabled' => isset($_POST['rating_enabled']) ? 1 : 0,
			'rating_bg_color' => isset($_POST['rating_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['rating_bg_color'])) : '#efad05',
			'rating_hover_color' => isset($_POST['rating_hover_color']) ? sanitize_hex_color(wp_unslash($_POST['rating_hover_color'])) : '#CC0000',
			'google_reviews_color_scheme' => isset($_POST['google_reviews_color_scheme']) ? sanitize_text_field(wp_unslash($_POST['google_reviews_color_scheme'])) : 'light',
			'google_reviews_custom_color' => isset($_POST['google_reviews_custom_color']) ? sanitize_hex_color(wp_unslash($_POST['google_reviews_custom_color'])) : '#000000',
			'google_reviews_show_text' => isset($_POST['google_reviews_show_text']) ? 1 : 0,
			'google_reviews_show_author' => isset($_POST['google_reviews_show_author']) ? 1 : 0,
			'google_reviews_show_author_image' => isset($_POST['google_reviews_show_author_image']) ? 1 : 0,
			'google_reviews_show_date' => isset($_POST['google_reviews_show_date']) ? 1 : 0,
		];
		// Get existing Google options
		$google_opts = get_option('socialfeeds_google_option', []);
		if(!is_array($google_opts)){
			$google_opts = [];
		}

		// Extract feeds + accounts
		$feeds = isset($google_opts['google_reviews_feeds']) ? $google_opts['google_reviews_feeds'] : [];
		if(!is_array($feeds)){
			$feeds = [];
		}

		$connected_accounts = isset($google_opts['google_connected_accounts']) ? $google_opts['google_connected_accounts'] : [];
		if(!is_array($connected_accounts)){
			$connected_accounts = [];
		}

		$input_val = sanitize_text_field(wp_unslash(isset($_POST['source_input']) ? $_POST['source_input'] : ''));

		$preview = '';
		if(isset($_POST['preview_url'])){
			$preview = rawurldecode(sanitize_text_field(wp_unslash($_POST['preview_url'])));
		}

		$edit_id = sanitize_text_field(wp_unslash(isset($_POST['edit_id']) ? $_POST['edit_id'] : ''));

		$selected_account_index = isset($_POST['google_reviews_selected_account'])? (int) $_POST['google_reviews_selected_account']: 0;

		$place_id = '';
		$place_name = '';

		if(isset($connected_accounts[$selected_account_index])){

			$acc = $connected_accounts[$selected_account_index];
			$place_id = isset($acc['place_id']) ? sanitize_text_field(wp_unslash($acc['place_id'])) : '';
			$place_name = isset($acc['name']) ? sanitize_text_field(wp_unslash($acc['name'])) : '';
		}

		if(empty($place_id)){
			wp_send_json_error([
				'message' => __('Please select a valid Google location.', 'socialfeeds-pro')
			]);
		}

		if(!empty($edit_id)){
			$feed_id = $edit_id;
		} else {

			$feed_id = isset($_POST['client_feed_id'])? sanitize_text_field(wp_unslash($_POST['client_feed_id'])): '';

			if (empty($feed_id)) {
				$feed_id = self::get_next_feed_id();
			}
		}

		if(!empty($edit_id)){

			$existing_feed = null;

			foreach ($feeds as $feed) {
				if(isset($feed['id']) && (string) $feed['id'] === (string) $edit_id){
					$existing_feed = $feed;
					break;
				}
			}

			if($existing_feed && isset($existing_feed['name']) && !empty($existing_feed['name'])){
				$feed_name = $existing_feed['name'];
			} else {
				$feed_name = 'Google Reviews Feed - Reviews 1';
			}

		} else {

			$same_type_count = 0;

			foreach ($feeds as $feed) {
				if(isset($feed['type']) && $feed['type'] === 'reviews'){
					$same_type_count++;
				}
			}

			$feed_name = 'Google Reviews Feed - Reviews ' . ($same_type_count + 1);
		}

		$feed_record = [
			'id' => $feed_id,
			'name' => $feed_name,
			'shortcode' => '[socialfeeds id="' . $feed_id . '" platform="google_reviews"]',
			'type' => sanitize_text_field(wp_unslash(isset($_POST['feed_type']) ? $_POST['feed_type'] : 'reviews')),
			'input' => $input_val,
			'preview' => $preview,
			'account_id' => $place_id,
			'account_name' => $place_name,
			'settings' => $feed_settings,
			'created' => time(),
		];

		if(!empty($edit_id)){

			$updated = false;

			foreach ($feeds as $index => $feed) {
				if(isset($feed['id']) && (string) $feed['id'] === (string) $edit_id){
					$feeds[$index] = array_merge($feed, $feed_record);
					$updated = true;
					break;
				}
			}

			if(!$updated){
				$feeds[] = $feed_record;
			}

		}else{
			$feeds[] = $feed_record;
		}

		$google_opts['google_reviews_feeds'] = $feeds;

		update_option('socialfeeds_google_option', $google_opts);

		wp_send_json_success([
			'message' => __('Google Reviews feed settings saved.', 'socialfeeds-pro'),
			'feed_id' => $feed_record['id'],
		]);
	}

	static function save_google_api_key() {

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission.', 'socialfeeds-pro'));
		}

		$api_key = isset($_POST['google_api_key']) ? sanitize_text_field(wp_unslash($_POST['google_api_key'])) : '';

		if(empty($api_key)){
			wp_send_json_error(['message' => __('Google API key cannot be blank.', 'socialfeeds-pro')]);
		}

		$opts = get_option('socialfeeds_google_option', []);

		if(!is_array($opts)){
			$opts = [];
		}

		$opts['api_key'] = $api_key;

		update_option('socialfeeds_google_option', $opts);

		wp_send_json_success([
			'message' => __('Google API key saved successfully.', 'socialfeeds-pro')
		]);
	}

	static function fetch_google_reviews_preview() {

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('Permission denied.', 'socialfeeds-pro'));
		}

		$opts = get_option('socialfeeds_google_option', []);

		if(!is_array($opts)){
			$opts = [];
		}

		$index = isset($_POST['selected_account_index']) ? (int) $_POST['selected_account_index'] : -1;
		$accounts = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];

		if(!is_array($accounts)){
			$accounts = [];
		}

		if(!isset($accounts[$index])){
			wp_send_json_error(['message' => __('Selected location not found.', 'socialfeeds-pro')]);
		}

		$account = $accounts[$index];
		$place_id = isset($account['place_id'])? sanitize_text_field(wp_unslash($account['place_id'])) : '';

		if(empty($place_id)){
			wp_send_json_error(['message' => __('Missing Place ID.', 'socialfeeds-pro')]);
		}

		$api_key = isset($opts['api_key']) ? $opts['api_key'] : '';

		if(empty($api_key)){
			wp_send_json_error([
				'message' => __('Google API key is required.', 'socialfeeds-pro')
			]);
		}

		$data = \SocialFeedsPro\GoogleReviews::fetch_google_place_reviews($place_id, $api_key);

		if(is_array($data) && isset($data['error']) && !empty($data['error'])){
			wp_send_json_error([
				'message' => $data['error']
			]);
		}

		wp_send_json_success($data);
	}

	static function add_google_place(){

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('Permission denied.', 'socialfeeds-pro'));
		}

		$place_id = isset($_POST['place_id']) ? sanitize_text_field(wp_unslash($_POST['place_id'])) : '';
		$place_name = isset($_POST['place_name']) ? sanitize_text_field(wp_unslash($_POST['place_name'])) : '';
		$place_address = isset($_POST['place_address']) ? sanitize_text_field(wp_unslash($_POST['place_address'])) : '';

		if(empty($place_id)){
			wp_send_json_error(['message' => __('Place ID is required.', 'socialfeeds-pro')]);
		}

		$opts = get_option('socialfeeds_google_option', []);
		if(!is_array($opts)){
			$opts = [];
		}

		$accounts = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];
		if(!is_array($accounts)){
			$accounts = [];
		}

		// Prevent duplicates
		foreach ($accounts as $acc) {
			if(!empty($acc['place_id']) && $acc['place_id'] === $place_id){
				wp_send_json_error(['message' => __('Place already exists.', 'socialfeeds-pro')]);
			}
		}

		// Auto-fetch details if needed
		if(empty($place_name) && !empty($opts['api_key'])){

			$details = \SocialFeedsPro\GoogleReviews::fetch_google_place_details($place_id, $opts['api_key']);

			if(is_array($details)){
				$place_name = isset($details['name']) ? $details['name'] : $place_id;
				$place_address = isset($details['address']) ? $details['address'] : '';
			}
		}

		$new_account = [
			'place_id' => $place_id,
			'name' => !empty($place_name) ? $place_name : $place_id,
			'address' => $place_address,
			'created' => time(),
		];

		$accounts[] = $new_account;
		$opts['google_connected_accounts'] = $accounts;

		update_option('socialfeeds_google_option', $opts);

		wp_send_json_success([
			'message' => __('Business profile added successfully.', 'socialfeeds-pro'),
			'account' => $new_account
		]);
	}

	static function delete_google_account() {

		check_ajax_referer('socialfeeds_pro_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('Permission denied.', 'socialfeeds-pro'));
		}

		$place_id = isset($_POST['account_id']) ? sanitize_text_field(wp_unslash($_POST['account_id'])) : '';

		if(empty($place_id)){
			wp_send_json_error(['message' => __('Place ID is required.', 'socialfeeds-pro')]);
		}

		$opts = get_option('socialfeeds_google_option', []);
		if(!is_array($opts)){
			$opts = [];
		}

		$accounts = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];
		if(!is_array($accounts)) {
			$accounts = [];
		}

		$accounts = array_values(array_filter($accounts, function ($acc) use ($place_id) {
			return !empty($acc['place_id']) && $acc['place_id'] !== $place_id;
		}));

		$opts['google_connected_accounts'] = $accounts;

		update_option('socialfeeds_google_option', $opts);

		wp_send_json_success([
			'message' => __('Location deleted successfully.', 'socialfeeds-pro')
		]);
	}
}