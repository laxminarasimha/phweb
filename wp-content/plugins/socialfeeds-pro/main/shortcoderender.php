<?php

namespace SocialFeedsPro;

if(!defined('ABSPATH')){
	exit;
}

class ShortcodeRender{

	static function instagram_feed($atts) {
		$options = get_option('socialfeeds_instagram_option', []);
		$atts = shortcode_atts([
			'feed' => '',
			'id' => '',
			'username' => '',
			// default empty so saved settings control limit unless shortcode sets it
			'limit' => '',
			'token' => isset($options['instagram_access_token']) ? $options['instagram_access_token'] : '',
			'type' => isset($options['instagram_default_type']) ? $options['instagram_default_type'] : 'all',
			'align' => '',
			'width' => '',
			'debug' => false,
		], $atts);

		// Load feed-specific settings if feed ID provided
		$feed_settings = [];
		$found_feed = null; 
		if (!empty($atts['feed']) || !empty($atts['id'])) {
			$feeds = isset($options['instagram_feeds']) ? $options['instagram_feeds'] : [];
			$feed_identifier = !empty($atts['feed']) ? $atts['feed'] : $atts['id'];
			
			// 1. Try finding by ID (String comparison)
			foreach ($feeds as $f) {
				if (isset($f['id']) && (string)$f['id'] === (string)$feed_identifier) {
					$found_feed = $f;
					break;
				}
			}

			// 2. Fallback to index if numeric feed attribute provided and not found by ID
			if (!$found_feed && !empty($atts['feed']) && is_numeric($atts['feed'])) {
				$index = (int)$atts['feed'] - 1;
				if (isset($feeds[$index])) {
					$found_feed = $feeds[$index];
				}
			}

			if ($found_feed) {
				$feed_settings = isset($found_feed['settings']) ? $found_feed['settings'] : [];
				if (empty($atts['username']) && isset($found_feed['input'])) {
					$atts['username'] = $found_feed['input'];
				}
			} else {
				return '<p class="socialfeeds-subtitle">'.esc_html__('Saved feed not found for ID or index: ', 'socialfeeds-pro').esc_html($feed_identifier) . esc_html__('.', 'socialfeeds-pro').'</p>';
			}
		}

		// Merge global and feed-specific settings
		$opts = array_merge($options, $feed_settings);
		$show_reels = !empty($opts['instagram_show_reels']);

		$feed_account_token = '';
        if (!empty($atts['feed']) || !empty($atts['id'])) {
            $feeds = isset($options['instagram_feeds']) ? $options['instagram_feeds'] : [];
            $feed_identifier = !empty($atts['feed']) ? $atts['feed'] : $atts['id'];
            foreach ($feeds as $f) {
                if (isset($f['id']) && (string)$f['id'] === (string)$feed_identifier) {
                    $feed_account_token = isset($f['account_token']) ? $f['account_token'] : '';
                    break;
                }
            }
            if (!$feed_account_token && is_numeric($feed_identifier)) {
                $idx = intval($feed_identifier) - 1;
                if (isset($feeds[$idx]['account_token'])) {
                    $feed_account_token = $feeds[$idx]['account_token'];
                }
            }
        }

        $token = !empty($feed_account_token) ? $feed_account_token : (!empty($atts['token']) ? $atts['token'] : (isset($options['instagram_access_token']) ? $options['instagram_access_token'] : ''));
        $username = $atts['username'];
		if (!empty($token) && !empty($username)) {
			$notice = '<p class="socialfeeds-subtitle">' . esc_html__('Note: Access token belongs to a specific Instagram account. To fetch media for "', 'socialfeeds-pro') . esc_html($username) . esc_html__('\" you must provide a token for that account or use the Instagram Business Discovery API.', 'socialfeeds-pro') . '</p>';
		}

		if(!empty($token)){
			$limit = intval($atts['limit']);

			// Resolve account_type and token_type from connected accounts for tagged posts
			$account_type = '';
			$token_type = isset($options['instagram_token_type']) ? $options['instagram_token_type'] : 'basic';
			$connected_accounts = isset($options['instagram_connected_accounts']) ? $options['instagram_connected_accounts'] : [];
			foreach($connected_accounts as $acct){
				if(!empty($acct['token']) && $acct['token'] === $token){
					$account_type = isset($acct['account_type']) ? $acct['account_type'] : '';
					$token_type = isset($acct['token_type']) ? $acct['token_type'] : $token_type;
					break;
				}
			}

			// If token_type is advanced, ensure account_type reflects BUSINESS
			if($token_type === 'advanced' && empty($account_type)){
				$account_type = 'BUSINESS';
			}

			$account_info = \SocialFeedsPro\Util::get_account_info($token, $token_type);	

			$per_page = 100; 
			if ($limit < 1) {
				$limit = intval(wp_is_mobile() ? 
					(isset($opts['instagram_number_posts_mobile']) ? $opts['instagram_number_posts_mobile'] : 6) : 
					(isset($opts['instagram_number_posts_desktop']) ? $opts['instagram_number_posts_desktop'] : 6)
				);
			}
			$fetch_target = $limit > 0 ? $limit : 100;

			$feed_type = isset($found_feed['type']) ? $found_feed['type'] : 'username';
			$source_input = isset($found_feed['input']) ? $found_feed['input'] : '';
			$user_id = isset($found_feed['account_id']) ? $found_feed['account_id'] : '';

			// Fetch paginated posts
			$data = \SocialFeedsPro\Util::fetch_feed_data($token, $fetch_target, $feed_type, $source_input, $user_id, false, $account_type);
		} elseif (!empty($username)) {
			$try_urls = [
				"https://www.instagram.com/" . rawurlencode($username) . "/?__a=1&__d=dis",
				"https://www.instagram.com/" . rawurlencode($username) . "/?__a=1",
			];
			$data = null;
			foreach ($try_urls as $try) {
				$resp = wp_remote_get($try);
				if (is_wp_error($resp))
					continue;
				$body = wp_remote_retrieve_body($resp);
				$json = json_decode($body, true);
				if (empty($json))
					continue;
				if (!empty($json['graphql']['user']['edge_owner_to_timeline_media']['edges'])) {
					$edges = $json['graphql']['user']['edge_owner_to_timeline_media']['edges'];
					$items = [];
					foreach ($edges as $edge) {
						$node = isset($edge['node']) ? $edge['node'] : null;
						if (!$node)
							continue;
						if (!$show_reels && !empty($node['is_video'])) {
							continue;
						}
						$items[] = [
							'media_type' => !empty($node['is_video']) ? 'VIDEO' : 'IMAGE',
							'media_url' => isset($node['display_url']) ? $node['display_url'] : (isset($node['thumbnail_src']) ? $node['thumbnail_src'] : ''),
							'permalink' => 'https://www.instagram.com/p/' . (isset($node['shortcode']) ? $node['shortcode'] : ''),
							'caption' => isset($node['edge_media_to_caption']['edges'][0]['node']['text']) ? $node['edge_media_to_caption']['edges'][0]['node']['text'] : '',
							'thumbnail_url' => isset($node['thumbnail_src']) ? $node['thumbnail_src'] : '',
						];
					}
					$data = ['data' => $items];
					break;
				}
				if (!empty($json['items'])) {
					$items = [];
					foreach ($json['items'] as $it) {
						$items[] = [
							'media_type' => strtoupper(isset($it['media_type']) ? $it['media_type'] : 'IMAGE'),
							'media_url' => isset($it['image_versions2']['candidates'][0]['url']) ? $it['image_versions2']['candidates'][0]['url'] : (isset($it['carousel_media'][0]['image_versions2']['candidates'][0]['url']) ? $it['carousel_media'][0]['image_versions2']['candidates'][0]['url'] : ''),
							'permalink' => 'https://www.instagram.com/p/' . (isset($it['code']) ? $it['code'] : ''),
							'caption' => isset($it['caption']) ? $it['caption'] : '',
							'thumbnail_url' => isset($it['image_versions2']['candidates'][0]['url']) ? $it['image_versions2']['candidates'][0]['url'] : '',
						];
					}
					$data = ['data' => $items];
					break;
				}
			}

			if(empty($data)){
				return '<p class="socialfeeds-subtitle">' . esc_html__('Could not fetch public Instagram profile for "', 'socialfeeds-pro') . esc_html($username) . esc_html__('". Instagram may block unauthenticated requests.', 'socialfeeds-pro') . '</p>';
			}

		} else {
			return '<p class="socialfeeds-subtitle">' . esc_html__('Instagram token is missing. Save it in settings or pass `token` attribute, or provide a `username` without token to attempt a public fetch.', 'socialfeeds-pro') . '</p>';
		}

		$width_attr = !empty($atts['width']) ? $atts['width'] : (isset($opts['instagram_width']) ? $opts['instagram_width'] : '');
		$align_attr = !empty($atts['align']) ? $atts['align'] : (isset($opts['instagram_align']) ? $opts['instagram_align'] : '');

		$width_class = !empty($width_attr) ? ' socialfeeds-width-' . str_replace('%', 'pct', esc_attr($width_attr)) : '';
		$align_class = !empty($align_attr) ? ' socialfeeds-align-' . esc_attr($align_attr) : '';

		$layout = isset($opts['instagram_layout']) ? $opts['instagram_layout'] : 'grid';
		$color_scheme = isset($opts['instagram_color_scheme']) ? $opts['instagram_color_scheme'] : 'light';
		$custom_color = isset($opts['instagram_custom_color']) ? $opts['instagram_custom_color'] : '#000000';

		$scheme_class = 'socialfeeds-scheme-' . $color_scheme;
		$scheme_style = '';
		
		// Text Colors
		$text_color = '#1d2327';
		$meta_color = '#606060';

		if($color_scheme === 'dark'){
			$text_color = '#ffffff';
			$meta_color = '#cccccc';
		} elseif($color_scheme === 'custom'){
			$scheme_style = 'background-color:' . esc_attr($custom_color) . ' !important;';
			
			// Calculate brightness
			$hex = str_replace('#', '', $custom_color);
			if(strlen($hex) == 3) {
				$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
			}
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
			$brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
			
			if($brightness < 128){
				$text_color = '#ffffff';
				$meta_color = '#cccccc';
			}
		}

		// Responsive columns	
		$cols_desktop = intval(isset($opts['instagram_columns_desktop'])? $opts['instagram_columns_desktop']: (isset($opts['instagram_grid_columns'])? $opts['instagram_grid_columns']: 3));	
		$cols_mobile = isset($opts['instagram_columns_mobile']) ? intval($opts['instagram_columns_mobile']) : 1;
		$padding = intval(isset($opts['instagram_padding'])? $opts['instagram_padding']: 16);
		$inner_style = 'gap:' . $padding . 'px;';
		if($layout === 'grid'){
			$inner_style .= ' display: grid; grid-template-columns: repeat(' . $cols_desktop . ', 1fr);';
		} elseif($layout === 'masonry'){
			$inner_style .= ' column-count: ' . $cols_desktop . '; column-gap: ' . $padding . 'px;';
		}

		// Also, add this media query for mobile responsiveness:
		$mobile_style = '';
		if($layout === 'grid'){
			$mobile_style = '<style>@media (max-width: 768px) {.socialfeeds-instagram-inner.layout-grid{grid-template-columns: repeat(' . $cols_mobile . ', 1fr) !important;}}</style>';
		} elseif($layout === 'masonry'){
			$mobile_style = '<style>@media (max-width: 768px) {.socialfeeds-instagram-inner.layout-masonry{column-count: ' . $cols_mobile . ' !important;}}</style>';
		}

		// Header settings
		$header_enabled = !empty($opts['instagram_header_enabled']);
		$header_show_bio = $header_enabled && !empty($opts['instagram_show_bio_text']) && !empty($account_info['biography']);
		$header_show_followers = $header_enabled && !empty($opts['instagram_show_followers']) && !empty($account_info['followers_count']);
		$media_count_enabled = $header_enabled && !empty($opts['instagram_media_count']) && !empty($account_info['media_count']);
		$header_style = isset($opts['instagram_header_style']) ? $opts['instagram_header_style'] : 'standard';
		$header_size_opt = isset($opts['instagram_header_size']) ? $opts['instagram_header_size'] : 'medium';

		switch ($header_size_opt) {
			case 'small': $header_size = 48; break;
			case 'large': $header_size = 96; break;
			case 'medium': default: $header_size = 72; break;
		}

		// Build header HTML
		$header_html = '';
		if($header_enabled && !empty($account_info)){
			$account_name = (isset($account_info['username']) && $account_info['username'] !== '') ? $account_info['username'] : (isset($account_info['name']) ? $account_info['name'] : '');
			$account_bio = isset($account_info['biography']) ? $account_info['biography'] : '';
			$account_followers = isset($account_info['followers_count']) ? number_format_i18n($account_info['followers_count']) : '';
			$account_media_count = isset($account_info['media_count']) ? number_format_i18n($account_info['media_count']) : '';
			$account_avatar = isset($account_info['profile_picture_url']) ? $account_info['profile_picture_url'] : '';
			
			// Custom Avatar Override
			if(!empty($opts['instagram_custom_avatar'])){
				$account_avatar = $opts['instagram_custom_avatar'];
			}

			if(!empty($account_avatar) || !empty($account_name)){
				$header_class = 'socialfeeds-instagram-header socialfeeds-instagram-header-' . esc_attr($header_style);
				
				// Use calculated text color
				$header_html = '<div class="' . $header_class . '" style="color:' . esc_attr($text_color) . ';">';

				if(!empty($account_avatar)){
					$header_html .= '<img src="' . esc_url($account_avatar) . '" alt="' . esc_attr($account_name) . '" class="socialfeeds-instagram-header-avatar" style="width:' . intval($header_size) . 'px; height:' . intval($header_size) . 'px;">';
				}

				$header_html .= '<div class="socialfeeds-instagram-header-info">';
				if (!empty($account_name)) $header_html .= '<h3 class="socialfeeds-instagram-header-name">' . esc_html($account_name) . '</h3>';
				if ($header_show_bio && !empty($account_bio)) $header_html .= '<p class="socialfeeds-instagram-header-bio">' . esc_html($account_bio) . '</p>';
				if ($header_show_followers && !empty($account_followers)) $header_html .= '<p class="socialfeeds-instagram-header-followers"><strong>' . esc_html($account_followers) . '</strong> ' . esc_html__('Followers', 'socialfeeds-pro') . '</p>';
				if ($media_count_enabled && !empty($account_media_count)) $header_html .= '<p class="socialfeeds-instagram-header-followers"><strong>' . esc_html($account_media_count) . '</strong> ' . esc_html__('Posts', 'socialfeeds-pro') . '</p>';
				$header_html .= '</div></div>';
			}
		}

		$play_mode = isset($opts['instagram_play_mode']) ? $opts['instagram_play_mode'] : 'newtab';
		$unique_id = 'socialfeeds-instagram-' . uniqid();

		// Main container
		$html = $mobile_style . '<div id="' . esc_attr($unique_id) . '" class="socialfeeds-instagram-feed ' . $scheme_class . ' ' . $align_class . ' ' . $width_class . '" style="' . $scheme_style . '" data-play-mode="' . esc_attr($play_mode) . '">';
		$html .= $header_html;
		$html .= '<div class="socialfeeds-instagram-inner layout-' . esc_attr($layout) . '" style="' . esc_attr($inner_style) . '">';

		// Posts processing
		$limit = intval($atts['limit']);
		if ($limit < 1) {
			$limit = intval( wp_is_mobile()? (isset($opts['instagram_number_posts_mobile']) ? $opts['instagram_number_posts_mobile'] : (isset($opts['instagram_number_posts_desktop']) ? $opts['instagram_number_posts_desktop'] : 6)) : (isset($opts['instagram_number_posts_desktop']) ? $opts['instagram_number_posts_desktop'] : 6) );
		}
		$sort_by = isset($opts['instagram_sort_by']) ? $opts['instagram_sort_by'] : 'newest';
			if (!empty($data['data'])) {
				$posts_to_display = [];
				$show_feed_posts = isset($opts['instagram_show_feed_posts']) ? !empty($opts['instagram_show_feed_posts']) : true;

				foreach ($data['data'] as $post) {
					$media_type = strtoupper(isset($post['media_type']) ? $post['media_type'] : '');
					$permalink = isset($post['permalink']) ? $post['permalink'] : '';

					// Identify Reel vs Post matches admin.js logic
					$is_reel = ($media_type === 'VIDEO' || $media_type === 'REEL' || strpos($permalink, '/reel/') !== false);
					$is_post = !$is_reel;

					if (!$show_reels && $is_reel) {
						continue;
					}
					if (!$show_feed_posts && $is_post) {
						continue;
					}

					$posts_to_display[] = $post;
				}
			if ($sort_by === 'likes') {
				usort($posts_to_display, function ($a, $b) {
        		$a_likes = isset($a['like_count']) ? intval($a['like_count']) : 0; $b_likes = isset($b['like_count']) ? intval($b['like_count']) : 0;
				return $b_likes - $a_likes; });
			} elseif ($sort_by === 'random') {
				shuffle($posts_to_display);
			} else {
				usort($posts_to_display, function ($a, $b) {
				$a_time = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0; $b_time = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
				return $b_time - $a_time;});
			}
			$count = 0;
			$aspect_ratio = isset($opts['instagram_aspect_ratio']) ? $opts['instagram_aspect_ratio'] : 'square';
			$hover_state  = isset($opts['instagram_hover_state'])  ? $opts['instagram_hover_state']  : 'overlay';
			$alignment   = isset($opts['instagram_alignment'])   ? $opts['instagram_alignment']   : 'left';

			foreach ($posts_to_display as $post) {
				if ($count >= $limit) break;

				$item_style = '';
				if ($layout === 'carousel') {
					$item_style = ' style="scroll-snap-align: start;"';
				} elseif ($layout === 'masonry') {
					$item_style = ' style="margin-bottom: ' . $padding . 'px;"';
				}

				$media_type = strtoupper(isset($post['media_type']) ? $post['media_type'] : 'IMAGE');
				$media_url  = isset($post['media_url']) ? esc_url($post['media_url']) : (isset($post['thumbnail_url']) ? esc_url($post['thumbnail_url']) : '');
				$permalink  = isset($post['permalink']) ? esc_url($post['permalink']) : '#';
				$caption    = isset($post['caption']) ? esc_html($post['caption']) : '';
				
				$item_aspect = ($aspect_ratio === 'instagram') ? (($media_type === 'VIDEO') ? 'portrait' : 'square') : $aspect_ratio;
				if ($layout === 'masonry') $item_aspect = 'auto';

				$html .= '<div class="socialfeeds-instagram-item hover-' . esc_attr($hover_state) . '"' . $item_style . '>';
				$html .= '<div class="socialfeeds-instagram-media aspect-' . esc_attr($item_aspect) . '">';
				
				if ($media_type === 'VIDEO') {
					$poster = esc_url(isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $media_url);
					
					if ($play_mode === 'inline') {
						$html .= '<video class="socialfeeds-video-player" controls preload="metadata" poster="' . esc_attr($poster) . '"><source src="' . $media_url . '" type="video/mp4"></video>';
					} else {
						$click_class = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
						$target = ($play_mode === 'newtab') ? 'target="_blank" rel="noopener noreferrer"' : '';
						$href = ($play_mode === 'newtab') ? $permalink : '#';
						
						$html .= '<a href="' . $href . '" ' . $target . ' class="' . $click_class . '" data-media="' . $media_url . '" data-type="VIDEO" data-permalink="' . $permalink . '">';
						
						if(strpos($poster, '.mp4') !== false){
							$html .= '<video src="' . $poster . '#t=0.001" preload="metadata" playsinline muted style="width:100%; height:100%; object-fit:cover; display:block; pointer-events:none;"></video>';
						} else {
							$html .= '<img src="' . $poster . '" alt="Instagram video">';
						}

						if (!isset($opts['instagram_show_play_icon']) || !empty($opts['instagram_show_play_icon'])) {
							$html .= '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
						}
						$html .= '</a>';
					}
				} else {
					$click_class = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
					$target = ($play_mode === 'newtab' || $play_mode === 'inline') ? 'target="_blank" rel="noopener noreferrer"' : ''; 
					$href = ($play_mode === 'newtab' || $play_mode === 'inline') ? $permalink : '#';

					$html .= '<a href="' . $href . '" ' . $target . ' class="' . $click_class . '" data-media="' . $media_url . '" data-type="IMAGE" data-permalink="' . $permalink . '" rel="noopener"><img src="' . $media_url . '" alt="Instagram post"></a>';
				}

				if ($hover_state === 'overlay') {
					$html .= '<div class="socialfeeds-hover-overlay"></div>';
				}
				$html .= '</div>'; // media

				// Stats
				$html .= '<div class="socialfeeds-instagram-stats" style="justify-content:' . ($alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start')) . ';">';
				if (!empty($opts['instagram_caption_enabled']) && !empty($caption)) {
					$html .= '<div class="caption" style="text-align:' . esc_attr($alignment) . ';">' . $caption . '</div>';
				}
				if (!empty($opts['instagram_likes']) && isset($post['like_count'])) {
					$html .= '<span class="likes"><span class="dashicons dashicons-heart socialfeeds-likes"></span> ' . number_format_i18n($post['like_count']) . '</span>';
				}
				if (!empty($opts['instagram_comments']) && isset($post['comments_count'])) {
					$html .= '<span class="comments"><span class="dashicons dashicons-admin-comments socialfeeds-comments"></span> ' . number_format_i18n($post['comments_count']) . '</span>';
				}
				$html .= '</div>'; // stats
				$html .= '</div>'; // item
				
				$count++;
			}
		}

		$html .= '</div>'; // inner

		// Footer Actions (Load More / Follow)
		$load_more_enabled = !empty($opts['instagram_load_more_enabled']);
		$follow_enabled = !empty($opts['instagram_follow_button_enabled']);

		if ($load_more_enabled || $follow_enabled) {
			$html .= '<div class="socialfeeds-load-more-container socialfeeds-instagram-actions">';
			if ($load_more_enabled) {
				$lm_text  = isset($opts['instagram_load_more_text']) ? $opts['instagram_load_more_text'] : 'Load More';
				$lm_bg    = isset($opts['instagram_load_more_bg_color']) ? $opts['instagram_load_more_bg_color'] : '#f0f0f0';
				$lm_color = isset($opts['instagram_load_more_text_color']) ? $opts['instagram_load_more_text_color'] : '#333333';
				$lm_hover = isset($opts['instagram_load_more_hover_color']) ? $opts['instagram_load_more_hover_color'] : '#fc0909';
				$lm_count = isset($opts['instagram_load_more_count']) ? $opts['instagram_load_more_count'] : 12;
				$html .= '<button type="button" class="socialfeeds-load-more-btn" 
				data-feed-type="instagram" 
				data-next-url="' . (isset($data['paging']['next']) ? esc_attr($data['paging']['next']) : '') . '"
				data-load-count="' . esc_attr($lm_count) . '" 
				data-layout="' . esc_attr($layout) . '" 
				data-padding="' . esc_attr($padding) . '" 
				data-cols="' . esc_attr($cols_desktop) . '" 
				data-aspect-ratio="' . esc_attr($aspect_ratio) . '" 
				data-caption-enabled="' . (!empty($opts['instagram_caption_enabled']) ? '1' : '0') . '"
				data-likes-enabled="' . (!empty($opts['instagram_likes']) ? '1' : '0') . '"
				data-comments-enabled="' . (!empty($opts['instagram_comments']) ? '1' : '0') . '"
				data-views-enabled="' . (!empty($opts['instagram_video_views']) ? '1' : '0') . '"
				data-show-reels="' . ($show_reels ? '1' : '0') . '"
				data-show-feed-posts="' . ($show_feed_posts ? '1' : '0') . '"
				data-hover-state="' . esc_attr($hover_state) . '"
				data-alignment="' . esc_attr($alignment) . '"
				data-play-mode="' . esc_attr($play_mode) . '"
				data-show-play-icon="' . (!isset($opts['instagram_show_play_icon']) || !empty($opts['instagram_show_play_icon']) ? '1' : '0') . '"
				style="background:' . esc_attr($lm_bg) . '; color:' . esc_attr($lm_color) . ';">'  . esc_html($lm_text) .  '</button>';				}
			if ($follow_enabled) {
				$fl_text = isset($opts['instagram_follow_button_text']) ? $opts['instagram_follow_button_text'] : 'Follow on Instagram';
				$fl_bg = isset($opts['instagram_follow_button_bg_color']) ? $opts['instagram_follow_button_bg_color'] : '#350ae1';
				$fl_color = isset($opts['instagram_follow_button_text_color']) ? $opts['instagram_follow_button_text_color'] : '#FFFFFF';
				$fl_hover = isset($opts['instagram_follow_button_hover_color']) ? $opts['instagram_follow_button_hover_color'] : '#3706e9';
				$ig_username = isset($account_info['username']) ? $account_info['username'] : '';
				$ig_url = $ig_username ? 'https://www.instagram.com/' . $ig_username . '/' : 'https://www.instagram.com/';
				$html .= '<a href="'.esc_url($ig_url).'" target="_blank" class="socialfeeds-follow-btn" style="background:'.esc_attr($fl_bg).'; color:'.esc_attr($fl_color).'; text-decoration:none;">';
				$html .= '<span class="dashicons dashicons-instagram" style="font-size:18px; width:18px; height:18px; line-height:1; margin-right:6px;"></span>' . esc_html($fl_text) . '</a>';
			}
		$html .= '<style>
			#' . esc_attr($unique_id) . ' .socialfeeds-load-more-btn:hover { background: ' . esc_attr($lm_hover) . ' !important; }
			#' . esc_attr($unique_id) . ' .socialfeeds-follow-btn:hover { background: ' . esc_attr($fl_hover) . ' !important; } </style>';
			$html .= '</div>';
		}

		$html .= '</div>'; // feed
		
		// Add Dynamic Styles for Text Colors
		$html .= '<style>
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-stats { color: ' . esc_attr($text_color) . '; }
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-stats .caption { color: ' . esc_attr($text_color) . '; }
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-stats .likes, 
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-stats .comments { color: ' . esc_attr($meta_color) . '; }
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-header-name { color: ' . esc_attr($text_color) . ' !important; }
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-header-bio, 
			#' . esc_attr($unique_id) . ' .socialfeeds-instagram-header-followers { color: ' . esc_attr($meta_color) . ' !important; }
		</style>';

		$html .= '<div id="socialfeeds-modal-root"></div>';
		return $html;
	}

	static function facebook_feed($atts) {
		$options = get_option('socialfeeds_facebook_option', []);
		$atts = shortcode_atts([
			'feed' => '',
			'id' => '',
			'limit' => '',
			'show_caption' => '',
			'width' => '',
			'align' => '',
		], $atts);

		$feed_settings = [];
		$found_feed = null;
		if (!empty($atts['feed']) || !empty($atts['id'])) {
			$feeds = isset($options['facebook_feeds']) ? $options['facebook_feeds'] : [];
			$feed_identifier = !empty($atts['feed']) ? $atts['feed'] : $atts['id'];
			foreach ($feeds as $f) {
				if (isset($f['id']) && (string)$f['id'] === (string)$feed_identifier) {
					$found_feed = $f;
					break;
				}
			}
		}

		// 2. Fallback to index if numeric feed attribute provided and not found by ID
		if (!$found_feed && !empty($atts['feed']) && is_numeric($atts['feed'])) {
			$index = (int)$atts['feed'] - 1;
			if (isset($feeds[$index])) {
				$found_feed = $feeds[$index];
			}
		}
		if (!$found_feed) {
			return '<p class="socialfeeds-subtitle">' . esc_html__('Facebook feed not found for ID or index: ', 'socialfeeds-pro') . esc_html($feed_identifier) . esc_html__('.', 'socialfeeds-pro') . '</p>';
		}

		$feed_settings = isset($found_feed['settings']) ? $found_feed['settings'] : [];
		$opts = array_merge($options, $feed_settings);
		
		$token = isset($found_feed['account_token']) ? $found_feed['account_token'] : (isset($options['facebook_access_token']) ? $options['facebook_access_token'] : '');
		$limit = !empty($atts['limit']) ? intval($atts['limit']) : (isset($opts['facebook_posts_per_page']) ? intval($opts['facebook_posts_per_page']) : (isset($opts['facebook_number_posts_desktop']) ? intval($opts['facebook_number_posts_desktop']) : 12));
		
		$data = apply_filters('socialfeeds_facebook_fetch_items', [], $found_feed, $token, $limit);


		$initial_after_cursor = \SocialFeedsPro\Facebook::$last_after_cursor;
		$initial_has_next = \SocialFeedsPro\Facebook::$last_has_next;

		if(empty($data)){
			return '<p class="socialfeeds-subtitle">' . esc_html__('No posts found for this Facebook feed.', 'socialfeeds-pro') . '</p>';
		}
		$layout = isset($opts['facebook_layout']) ? $opts['facebook_layout'] : 'grid';
		$padding = intval(isset($opts['facebook_padding']) ? $opts['facebook_padding'] : 8);
		$cols_desktop = intval(isset($opts['facebook_columns_desktop']) ? $opts['facebook_columns_desktop'] : 3);
		$aspect_ratio = isset($opts['facebook_aspect_ratio']) ? $opts['facebook_aspect_ratio'] : 'square';

		$width_attr = !empty($atts['width']) ? $atts['width'] : (isset($opts['facebook_width']) ? $opts['facebook_width'] : '');
		$align_attr = !empty($atts['align']) ? $atts['align'] : (isset($opts['facebook_align']) ? $opts['facebook_align'] : '');

		$width_class = !empty($width_attr) ? ' socialfeeds-width-' . str_replace('%', 'pct', esc_attr($width_attr)) : '';
		$align_class = !empty($align_attr) ? ' socialfeeds-align-' . esc_attr($align_attr) : '';

		$unique_id = 'socialfeeds-facebook-' . uniqid();
		$html = '';

		// Resolve color scheme and text colors
		$color_scheme = isset($opts['facebook_color_scheme']) ? $opts['facebook_color_scheme'] : 'light';
		$custom_color = isset($opts['facebook_custom_color']) ? $opts['facebook_custom_color'] : '#000000';
		$scheme_class = 'socialfeeds-scheme-' . $color_scheme;
		$scheme_style = '';
		$text_color = '#1d2327';
		$meta_color = '#65676b';

		if($color_scheme === 'dark'){
			$scheme_style = 'background: #0f0f0f; color: #fff; border: 1px solid #333; padding:15px; border-radius:8px;';
			$text_color = '#1d2327';
			$meta_color = '#1d2327';
		} elseif($color_scheme === 'custom'){
			$scheme_style = 'background-color:' . esc_attr($custom_color) . '; padding:15px; border-radius:8px;';
			
			if(self::is_dark($custom_color)){
				$text_color = '#1d2327';
				$meta_color = '#1d2327';
			}
		}

		// Header processing
		$header_html = '';
		if(!empty($opts['facebook_header_enabled'])){
			$page_id = isset($found_feed['input']) ? $found_feed['input'] : '';
			$account_info = \SocialFeedsPro\Util::get_facebook_account_info($token, $page_id);
			
			$name = isset($account_info['name']) ? $account_info['name'] : (isset($found_feed['name']) ? $found_feed['name'] : (isset($found_feed['input']) ? $found_feed['input'] : 'Facebook Page'));
			$about = isset($account_info['about']) ? $account_info['about'] : (isset($account_info['description']) ? $account_info['description'] : '');
			$fan_count = isset($account_info['fan_count']) ? number_format_i18n($account_info['fan_count']) : '0';
			$followers_count = isset($account_info['followers_count']) ? number_format_i18n($account_info['followers_count']) : '0';
			$profile_pic = isset($account_info['picture']['data']['url']) ? $account_info['picture']['data']['url'] : (isset($account_info['picture']['url']) ? $account_info['picture']['url'] : '');
			$cover_photo = isset($account_info['cover']['source']) ? $account_info['cover']['source'] : '';

			$header_class = 'socialfeeds-fb-header-v2';
			if(empty($opts['facebook_header_cover_enabled'])){
				$header_class .= ' no-cover';
			}
			$header_html .= '<div class="' . esc_attr($header_class) . '">';
			
			// Cover Section
			if(!empty($opts['facebook_header_cover_enabled'])){
				if(!empty($cover_photo)){
					$header_html .= '<div class="socialfeeds-fb-cover" style="background-image: url(\'' . esc_url($cover_photo) . '\');"></div>';
				} else {
					$header_html .= '<div class="socialfeeds-fb-cover no-image"></div>';
				}
			}

			$header_html .= '<div class="socialfeeds-fb-header-content">';
			
			// Profile Picture
			if(!empty($opts['facebook_header_avatar_enabled'])){
				$header_html .= '<div class="socialfeeds-fb-avatar-wrap">';
				if(!empty($profile_pic)){
					$header_html .= '<img src="' . esc_url($profile_pic) . '" alt="' . esc_attr($name) . '" class="socialfeeds-fb-avatar">';
				} else {
					$header_html .= '<div style="width:100%; height:100%; background:#1877f2; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-facebook" style="font-size:40px; width:40px; height:40px;"></span></div>';
				}
				$header_html .= '</div>';
			}

			$header_html .= '<div class="socialfeeds-fb-info-wrap">';
			$header_html .= '<h3 class="socialfeeds-fb-name" style="color:'.esc_attr($text_color).';">' . esc_html($name) . '</h3>';
			
			if(!empty($opts['facebook_header_caption_enabled']) && !empty($about)){
				$header_html .= '<div class="socialfeeds-fb-caption" style="color:'.esc_attr($meta_color).';">' . wp_kses_post($about) . '</div>';
			}

			if(!empty($opts['facebook_header_stats_enabled'])){
				$header_html .= '<div class="socialfeeds-fb-stats" style="color:'.esc_attr($meta_color).';">
					<span class="socialfeeds-fb-stat-item"><strong style="color:'.esc_attr($text_color).';">' . esc_html($fan_count) . '</strong> ' . esc_html__('likes', 'socialfeeds-pro') . '</span>
					<span class="socialfeeds-fb-stat-sep">•</span>
					<span class="socialfeeds-fb-stat-item"><strong style="color:'.esc_attr($text_color).';">' . esc_html($followers_count) . '</strong> ' . esc_html__('followers', 'socialfeeds-pro') . '</span>
				</div>';
			}
			
			$header_html .= '</div>'; // info-wrap
			$header_html .= '</div>'; // header-content
			$header_html .= '</div>'; // header-v2
		}

		$inner_style = 'gap:' . $padding . 'px;';
		if($layout === 'grid'){
			$inner_style .= ' display: grid; grid-template-columns: repeat(' . $cols_desktop . ', 1fr);';
		} elseif ($layout === 'list') {
			$inner_style .= ' display: flex; flex-direction: column;';
		} elseif ($layout === 'carousel') {
			$inner_style .= ' display: flex; overflow-x: hidden;';
		}

		// Responsive columns for mobile if grid
		if ($layout === 'grid') {
			$cols_mobile = isset($opts['facebook_columns_mobile']) ? intval($opts['facebook_columns_mobile']) : 1;
			$html .= '<style>@media (max-width: 768px) {#' . esc_attr($unique_id) . ' .socialfeeds-facebook-inner.layout-grid {grid-template-columns: repeat(' . $cols_mobile . ', 1fr) !important;}}</style>';
		}

		$play_mode = isset($opts['facebook_play_mode']) ? $opts['facebook_play_mode'] : 'newtab';
		$hover_state = isset($opts['facebook_hover_state']) ? $opts['facebook_hover_state'] : 'overlay';
		$show_caption = ($atts['show_caption'] !== '') ? filter_var($atts['show_caption'], FILTER_VALIDATE_BOOLEAN) : (isset($opts['facebook_show_caption']) ? (bool)$opts['facebook_show_caption'] : true);
		$show_likes = isset($opts['facebook_likes']) ? (bool)$opts['facebook_likes'] : true;
		$show_comments = isset($opts['facebook_comments']) ? (bool)$opts['facebook_comments'] : true;
		$sort_by = isset($opts['facebook_sort_by']) ? $opts['facebook_sort_by'] : 'newest';

		// Apply sorting
		$posts_to_sort = $data;
		if ($sort_by === 'most_liked') {
			usort($posts_to_sort, function ($a, $b) {
				$a_likes = isset($a['like_count']) ? intval($a['like_count']) : 0;
				$b_likes = isset($b['like_count']) ? intval($b['like_count']) : 0;
				return $b_likes - $a_likes;
			});
		} elseif ($sort_by === 'most_commented') {
			usort($posts_to_sort, function ($a, $b) {
				$a_comments = isset($a['comment_count']) ? intval($a['comment_count']) : 0;
				$b_comments = isset($b['comment_count']) ? intval($b['comment_count']) : 0;
				return $b_comments - $a_comments;
			});
		} elseif ($sort_by === 'random') {
			shuffle($posts_to_sort);
		} else {
			// Default: newest first
			usort($posts_to_sort, function ($a, $b) {
				$a_time = isset($a['created_time']) ? strtotime($a['created_time']) : 0;
				$b_time = isset($b['created_time']) ? strtotime($b['created_time']) : 0;
				return $b_time - $a_time;
			});
		}

		// Dynamic styles for the whole feed
		$html .= '<style>
			#' . esc_attr($unique_id) . ' .socialfeeds-facebook-message { color: ' . esc_attr($text_color) . '; }
			#' . esc_attr($unique_id) . ' .socialfeeds-fb-engagement { border-top-color: ' . (self::is_dark($custom_color) ? 'rgba(255,255,255,0.1)' : '#f0f0f0') . ' !important; }
		</style>';

		$html .= '<div id="' . esc_attr($unique_id) . '" class="socialfeeds-facebook-feed socialfeeds-facebook-feed-wrapper ' . esc_attr($scheme_class) . $width_class . $align_class . '" style="' . $scheme_style . '" data-play-mode="' . esc_attr($play_mode) . '">';
		$html .= $header_html;
		$html .= '<div class="socialfeeds-facebook-inner layout-' . esc_attr($layout) . '" style="' . esc_attr($inner_style) . '">';

		foreach ($posts_to_sort as $post) {
			$item_style = '';
			if ($layout === 'carousel') {
				$item_width = "calc((100% - " . (($cols_desktop - 1) * $padding) . "px) / " . $cols_desktop . ")";
				$item_style = ' style="flex: 0 0 ' . $item_width . '; max-width: ' . $item_width . ';"';
			} elseif ($layout === 'list') {
				$item_style = ' style="width: 100%;"';
			}

			$post_id = isset($post['id']) ? $post['id'] : '';
			$media_url = isset($post['full_picture']) ? $post['full_picture'] : '';
			$permalink = isset($post['permalink_url']) ? $post['permalink_url'] : '#';
			$message = isset($post['message']) ? $post['message'] : '';
			$type = isset($post['type']) ? $post['type'] : 'timeline';
			$media_type = ($type === 'video' || $type === 'reel') ? 'VIDEO' : 'IMAGE';

			$html .= '<div class="socialfeeds-facebook-item socialfeeds-fb-type-' . esc_attr($type) . ' hover-' . esc_attr($hover_state) . '" data-post-id="' . esc_attr($post_id) . '"' . $item_style . '>';
			
			$aspect_class = ($layout === 'list') ? 'auto' : $aspect_ratio;
			$html .= '<div class="socialfeeds-facebook-media aspect-' . esc_attr($aspect_class) . '" style="position:relative; overflow:hidden;">';
			
			if ($media_type === 'VIDEO' && $play_mode === 'inline') {
				$html .= '<video class="socialfeeds-video-player" controls preload="metadata" poster="' . esc_attr($media_url) . '"><source src="' . esc_url($media_url) . '" type="video/mp4"></video>';
			} else {
				$click_class = ($play_mode === 'lightbox') ? 'socialfeeds-open-modal-media' : '';
				$target = ($play_mode === 'newtab') ? 'target="_blank" rel="noopener noreferrer"' : '';
				$href = ($play_mode === 'newtab' || $play_mode === 'inline') ? $permalink : '#';
				
				$html .= '<a href="' . $href . '" ' . $target . ' class="' . $click_class . '" data-media="' . esc_url($media_url) . '" data-type="' . esc_attr($media_type) . '" data-permalink="' . esc_url($permalink) . '">';
				
				if(!empty($media_url)){
					$html .= '<img src="' . esc_url($media_url) . '" alt="Facebook ' . esc_attr($type) . '">';
				} else {
					$html .= '<div class="socialfeeds-video-placeholder">📹 No Media</div>';
				}

				if ($media_type === 'VIDEO') {
					$html .= '<span class="socialfeeds-play-overlay"><span class="dashicons dashicons-arrow-right"></span></span>';
				}
				$html .= '</a>';
			}

			if ($hover_state === 'overlay') {
				$html .= '<div class="socialfeeds-hover-overlay"></div>';
			}
			
			if($type === 'album' && !empty($post['count'])){
				$html .= '<div class="socialfeeds-fb-album-count"><span class="dashicons dashicons-images-alt2"></span> ' . intval($post['count']) . ' photo' . ($post['count'] > 1 ? 's' : '') . '</div>';
			}
			if($type === 'event' && !empty($post['start_time'])){
				$html .= '<div class="socialfeeds-fb-event-badge"><span class="dashicons dashicons-calendar-alt"></span> ' . date_i18n('M j', strtotime($post['start_time'])) . '</div>';
			}
			$html .= '</div>'; // media

			$html .= '<div class="socialfeeds-facebook-content">';
			if($show_caption && !empty($message)){
				$html .= '<div class="socialfeeds-facebook-message" style="font-weight:600; font-size:12px;">' . wp_trim_words(esc_html($message), 15) . '</div>';
			}
			
			if($type === 'event'){
				if(!empty($post['start_time'])){
					$html .= '<div class="socialfeeds-fb-event-time"><span class="dashicons dashicons-clock"></span> ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post['start_time'])) . '</div>';
				}
				if(!empty($post['place']['name'])){
					$html .= '<div class="socialfeeds-fb-event-place"><span class="dashicons dashicons-location"></span> ' . esc_html($post['place']['name']) . '</div>';
				}
			}
			
			if($type === 'album' && !empty($post['description'])){
				$html .= '<div class="socialfeeds-fb-album-desc">' . wp_trim_words(esc_html($post['description']), 20) . '</div>';
			}

			// Add engagement counts if enabled
			if ($show_likes || $show_comments) {
				$html .= '<div class="socialfeeds-fb-engagement">';
				if ($show_likes) {
					$like_count = isset($post['like_count']) ? $post['like_count'] : 0;
					$html .= '<span><span class="dashicons dashicons-heart"></span> ' . number_format_i18n($like_count) . '</span>';
				}
				if ($show_comments) {
					$comment_count = isset($post['comment_count']) ? $post['comment_count'] : 0;
					$html .= '<span><span class="dashicons dashicons-admin-comments"></span> ' . number_format_i18n($comment_count) . '</span>';
				}
				$html .= '</div>';
			}

			$html .= '</div>'; // content
			
			$html .= '</div>'; // item
		}

		$html .= '</div>'; // inner

		// Footer Actions (Load More / Follow)
		$load_more_enabled = !empty($opts['facebook_load_more_enabled']);
		$follow_enabled = !empty($opts['facebook_follow_button_enabled']);
		$after_cursor = isset($initial_after_cursor) ? $initial_after_cursor : '';

		if ($load_more_enabled || $follow_enabled) {
			$html .= '<div class="socialfeeds-load-more-container socialfeeds-facebook-actions" style="display:flex; justify-content:center; gap:15px; margin-top:25px; flex-wrap:wrap;">';
			if ($load_more_enabled) {
				$lm_text  = isset($opts['facebook_load_more_text']) ? $opts['facebook_load_more_text'] : 'Load More';
				$lm_bg    = isset($opts['facebook_load_more_bg_color']) ? $opts['facebook_load_more_bg_color'] : '#E74C3C';
				$lm_color = isset($opts['facebook_load_more_text_color']) ? $opts['facebook_load_more_text_color'] : '#FFFFFF';
				$lm_hover = isset($opts['facebook_load_more_hover_color']) ? $opts['facebook_load_more_hover_color'] : '#f76606';
				$lm_count = isset($opts['facebook_load_more_count']) ? intval($opts['facebook_load_more_count']) : 9;
				$lm_hidden = $initial_has_next ? '' : ' style="display:none;"';
				$html .= '<button type="button" class="socialfeeds-load-more-btn facebook-load-more-btn"' . $lm_hidden . '
				data-feed-type="facebook" 
				data-feed-id="' . esc_attr($found_feed['id']) . '"
				data-load-count="' . esc_attr($lm_count) . '"
				data-after-cursor="' . esc_attr($after_cursor) . '"
				style="background:' . esc_attr($lm_bg) . '; color:' . esc_attr($lm_color) . '; border:none; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer;">'  . esc_html($lm_text) .  '</button>';
			}
			if ($follow_enabled) {
				$fl_text = isset($opts['facebook_follow_button_text']) ? $opts['facebook_follow_button_text'] : 'Follow on Facebook';
				$fl_bg = isset($opts['facebook_follow_button_bg_color']) ? $opts['facebook_follow_button_bg_color'] : '#1877F2';
				$fl_color = isset($opts['facebook_follow_button_text_color']) ? $opts['facebook_follow_button_text_color'] : '#FFFFFF';
				$fl_hover = isset($opts['facebook_follow_button_hover_color']) ? $opts['facebook_follow_button_hover_color'] : '#0e5a9a';
				$fb_page_url = 'https://www.facebook.com/' . (isset($found_feed['input']) ? $found_feed['input'] : '');
				$html .= '<a href="'.esc_url($fb_page_url).'" target="_blank" class="socialfeeds-follow-btn facebook-follow-btn" style="background:'.esc_attr($fl_bg).'; color:'.esc_attr($fl_color).'; text-decoration:none; padding:10px 24px; border-radius:6px; font-weight:600; display:flex; align-items:center;">';
				$html .= '<span class="dashicons dashicons-facebook" style="font-size:18px; width:18px; height:18px; line-height:1; margin-right:6px;"></span>' . esc_html($fl_text) . '</a>';
			}

			$hover_styles = '';
			if(!empty($load_more_enabled)){
				$hover_styles .= '#' . esc_attr($unique_id) . ' .facebook-load-more-btn:hover { background: ' . esc_attr($lm_hover) . ' !important; }';
			}

			if(!empty($follow_enabled)){
				$hover_styles .= '#' . esc_attr($unique_id) . ' .facebook-follow-btn:hover { background: ' . esc_attr($fl_hover) . ' !important; }';
			}

			$html .= '<style>' . $hover_styles . '</style>';
			$html .= '</div>';
		}

		$html .= '</div>'; // feed
		return $html;
	}

	static function is_dark($color) {
		if (empty($color)) return false;
		$hex = str_replace('#', '', $color);
		if (strlen($hex) == 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
		if (strlen($hex) != 6) return false;
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
		$brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
		return $brightness < 128;
	}

	static function sort_google_reviews($a, $b, $type = 'newest') {

		$rating_a = isset($a['rating']) ? (int) $a['rating'] : 0;
		$rating_b = isset($b['rating']) ? (int) $b['rating'] : 0;
		$time_a = isset($a['time']) ? (int) $a['time'] : 0;
		$time_b = isset($b['time']) ? (int) $b['time'] : 0;

		if ($type === 'rating') {
			return $rating_b <=> $rating_a;
		}
		return $time_b <=> $time_a;
	}
	
	static function google_reviews($atts) {
		$options = get_option('socialfeeds_google_option', []);
		$atts = shortcode_atts([
			'feed' => '',
			'id' => '',
			'limit' => '',
			'width' => '',
			'align' => '',
		], $atts);

		$feed_settings = [];
		$found_feed = null;
		$feeds = isset($options['google_reviews_feeds']) ? $options['google_reviews_feeds'] : [];
		
		if(!empty($atts['feed']) || !empty($atts['id'])){
			$feed_identifier = !empty($atts['id']) ? $atts['id'] : $atts['feed'];
			foreach ($feeds as $f) {
				if(isset($f['id']) && (string)$f['id'] === (string)$feed_identifier){
					$found_feed = $f;
					break;
				}
			}

			// Fallback to index if numeric feed attribute provided and not found by ID
			if (!$found_feed && !empty($atts['feed']) && is_numeric($atts['feed'])) {
				$index = (int)$atts['feed'] - 1;
				if (isset($feeds[$index])) {
					$found_feed = $feeds[$index];
				}
			}

			if(!$found_feed){
				return '<p class="socialfeeds-subtitle">' . esc_html__('Google Reviews feed not found for ID or index: ', 'socialfeeds-pro') . esc_html($feed_identifier) . esc_html__('.', 'socialfeeds-pro') . '</p>';
			}
		}

		if(!$found_feed){
			return '<p class="socialfeeds-subtitle">' . esc_html__('Please provide a valid Google Reviews feed ID.', 'socialfeeds-pro') . '</p>';
		}

		$feed_settings = isset($found_feed['settings']) ? $found_feed['settings'] : [];
		$opts = array_merge($options, $feed_settings);

		$place_id = !empty($found_feed['account_id']) ? $found_feed['account_id'] : '';
		$connected_accounts = isset($options['google_connected_accounts']) ? $options['google_connected_accounts'] : [];
		if(empty($place_id) && !empty($connected_accounts)){
			$place_id = isset($connected_accounts[0]['place_id']) ? $connected_accounts[0]['place_id'] : '';
		}

		if(empty($place_id)){
			return '<p class="socialfeeds-subtitle">' . esc_html__('Google Location not configured.', 'socialfeeds-pro') . '</p>';
		}

		$api_key = isset($options['api_key']) ? $options['api_key'] : '';
		if(empty($api_key)){
			return '<p class="socialfeeds-subtitle">' . esc_html__('Google API key is required.', 'socialfeeds-pro') . '</p>';
		}

		$data = \SocialFeedsPro\GoogleReviews::fetch_google_place_reviews($place_id, $api_key);
		if(!empty($data['error'])){
			return '<p class="socialfeeds-subtitle">' . esc_html($data['error']) . '</p>';
		}

		if(empty($data['reviews'])){
			return '<p class="socialfeeds-subtitle">' . esc_html__('No reviews found.', 'socialfeeds-pro') . '</p>';
		}

		$reviews = isset($data['reviews']) ? $data['reviews'] : [];
		if(!is_array($reviews)){
			$reviews = [];
		}

		$min_rating = isset($opts['google_reviews_min_rating']) ? (int) $opts['google_reviews_min_rating'] : 1;
		$reviews = array_values(array_filter($reviews, function ($review) use ($min_rating) {
			$rating = isset($review['rating']) ? (int) $review['rating'] : 0;
			return $rating >= $min_rating;
		}));

		$sort_by = isset($opts['google_reviews_sort_by']) ? $opts['google_reviews_sort_by'] : 'newest';

		if ($sort_by === 'random') {
			shuffle($reviews);
		} else {
			usort($reviews, function ($a, $b) use ($sort_by) {
				return self::sort_google_reviews($a, $b, $sort_by);
			});
		}

		$layout = isset($opts['google_reviews_layout']) ? $opts['google_reviews_layout'] : 'grid';
		$padding = intval(isset($opts['google_reviews_padding']) ? $opts['google_reviews_padding'] : 16);
		$cols_desktop = intval(isset($opts['google_reviews_columns_desktop']) ? $opts['google_reviews_columns_desktop'] : 3);
		$cols_mobile = intval(isset($opts['google_reviews_columns_mobile']) ? $opts['google_reviews_columns_mobile'] : 1);
		$width_attr = !empty($atts['width']) ? $atts['width'] : (isset($opts['google_reviews_width']) ? $opts['google_reviews_width'] : '');
		$align_attr = !empty($atts['align']) ? $atts['align'] : (isset($opts['google_reviews_align']) ? $opts['google_reviews_align'] : '');
		$width_class = !empty($width_attr) ? ' socialfeeds-width-' . str_replace('%', 'pct', esc_attr($width_attr)) : '';
		$align_class = !empty($align_attr) ? ' socialfeeds-align-' . esc_attr($align_attr) : '';
		$unique_id = 'socialfeeds-google-reviews-' . uniqid();
		$html = '';

		$inner_style = 'gap:' . $padding . 'px;';
		if ($layout === 'grid') {
			$inner_style .= ' display: grid; grid-template-columns: repeat(' . $cols_desktop . ', 1fr);';
		} elseif ($layout === 'carousel') {
			$inner_style .= ' display: flex; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 10px;';
		} elseif ($layout === 'list') {
			$inner_style .= ' display: grid; grid-template-columns: 1fr;';
		}

		$mobile_style = '';
		if ($layout === 'grid') {
			$mobile_style = '<style>@media (max-width: 768px) {#' . $unique_id . ' .socialfeeds-google-reviews-inner.layout-grid{grid-template-columns: repeat(' . $cols_mobile . ', 1fr) !important;}}</style>';
		} elseif ($layout === 'carousel') {
			$desktop_width = "calc((100% - " . (($cols_desktop - 1) * $padding) . "px) / " . max(1, $cols_desktop) . ")";
			$mobile_width = "calc((100% - " . (($cols_mobile - 1) * $padding) . "px) / " . max(1, $cols_mobile) . ")";
			$mobile_style = '<style>#' . $unique_id . ' .socialfeeds-google-review-item { flex: 0 0 ' . $desktop_width . ' !important; max-width: ' . $desktop_width . ' !important; } @media (max-width: 768px) {#' . $unique_id . ' .socialfeeds-google-review-item { flex: 0 0 ' . $mobile_width . ' !important; max-width: ' . $mobile_width . ' !important; }}</style>';
		}

		$show_header = !empty($opts['google_reviews_header_enabled']);
		$header_html = '';
		if ($show_header) {
			$header_title = '';
			if (!empty($opts['google_custom_header_text'])) {
				$header_title = $opts['google_custom_header_text'];
			} elseif (!empty($data['place_name'])) {
				$header_title = $data['place_name'];
			} elseif (!empty($found_feed['name'])) {
				$header_title = $found_feed['name'];
			}

			if (!empty($opts['google_reviews_header_title']) && !empty($header_title)) {
				$header_html .= '<div class="socialfeeds-google-review-header-title" style="font-weight: bold; font-size: 20px;">' . esc_html($header_title) . '</div>';
			}

			if (!empty($opts['google_reviews_header_description'])) {
				$description_parts = [];
				if (!empty($data['address'])) {
					$description_parts[] = esc_html($data['address']);
				}
				if (isset($data['rating'])) {
				$review_count = isset($data['review_count']) ? (int) $data['review_count'] : 0;
				$description_parts[] = esc_html($data['rating']) . ' <span class="dashicons dashicons-star-filled"></span> (' . $review_count . ')';
			}
				if (!empty($data['url'])) {
					$description_parts[] = '<a href="' . esc_url($data['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('View on Google', 'socialfeeds-pro') . '</a>';
				}

				if (!empty($description_parts)) {
					$header_html .= '<div class="socialfeeds-google-review-header-description" style="font-weight:400; font-size:14px; color:#666; margin-bottom:16px;">' 
						. implode(' &#8226; ', $description_parts) . 
					'</div>';
				}
			}
		}

		$html = $mobile_style . '<div id="' . esc_attr($unique_id) . '" class="socialfeeds-google-reviews-feed ' . $align_class . ' ' . $width_class . '">';
		if (!empty($header_html)) {
			$html .= '<div class="socialfeeds-google-reviews-header">' . $header_html . '</div>';
		}
		$html .= '<div class="socialfeeds-google-reviews-inner layout-' . esc_attr($layout) . '" style="' . esc_attr($inner_style) . '">';
		$limit = !empty($atts['limit']) ? intval($atts['limit']) : (isset($opts['google_reviews_number_posts_desktop']) ? intval($opts['google_reviews_number_posts_desktop']) : 12);
		$count = 0;
		$show_author = !empty($opts['google_reviews_show_author']);
		$show_author_image = !empty($opts['google_reviews_show_author_image']);
		$show_text = !empty($opts['google_reviews_show_text']);
		$show_date = !empty($opts['google_reviews_show_date']);
		$show_rating = !empty($opts['rating_enabled']);

		$hover_state = isset($opts['google_reviews_hover_state']) ? $opts['google_reviews_hover_state'] : 'shadow';
		foreach ($reviews as $review) {
			if ($count >= $limit) break;

			$item_style = '';
			if ($layout === 'carousel') {
				$item_style = ' style="scroll-snap-align: start;"';
			}

			$author_name_raw = isset($review['author_name']) ? $review['author_name'] : __('Anonymous', 'socialfeeds-pro');
			$author_name_html = esc_html($author_name_raw);
			$author_name_attr = esc_attr($author_name_raw);
			$rating = isset($review['rating']) ? intval($review['rating']) : 0;
			$text = isset($review['text']) ? wp_kses_post($review['text']) : '';
			$time_text = isset($review['relative_time_description']) ? esc_html($review['relative_time_description']) : '';
			$profile_photo = isset($review['profile_photo_url']) ? esc_url($review['profile_photo_url']) : '';

			$html .= '<div class="socialfeeds-google-review-item hover-' . esc_attr($hover_state) . '"' . $item_style . '>';
			$html .= '<div class="socialfeeds-google-review-card">';

			if ($show_author || $show_author_image || $show_rating || $show_date) {
				$html .= '<div class="socialfeeds-google-review-header">';

				if ($show_author_image && !empty($profile_photo)) {
					$html .= '<img src="' . esc_url($profile_photo) . '" alt="' . $author_name_attr . '" class="socialfeeds-google-review-avatar">';
				}

				$html .= '<div class="socialfeeds-google-review-author">';
				if ($show_author) {
					$html .= '<h4 class="socialfeeds-google-review-name">' . $author_name_html . '</h4>';
				}

			$star_color = !empty($opts['rating_bg_color']) ? $opts['rating_bg_color'] : '#ffbe1a';
				if ($show_rating && $rating > 0) {
					$html .= '<div class="socialfeeds-google-review-rating">';		
					for ($i = 0; $i < 5; $i++) {
						if ($i < $rating) {
							$html .= '<span class="dashicons dashicons-star-filled" style="color:' . esc_attr($star_color) . ';"></span>';
						} else {
							$html .= '<span class="dashicons dashicons-star-outline" style="color:#ccc;"></span>';
						}
					}
					$html .= '</div>';
				}

				if ($show_date && !empty($time_text)) {
					$html .= '<p class="socialfeeds-google-review-time">' . $time_text . '</p>';
				}

				$html .= '</div>'; // author
				$html .= '</div>'; // header
			}

			if ($show_text && !empty($text)) {
				$html .= '<p class="socialfeeds-google-review-text">' . $text . '</p>';
			}

			$html .= '</div>'; // card

			if ($hover_state === 'overlay') {
				$html .= '<div class="socialfeeds-hover-overlay"></div>';
			}

			$html .= '</div>'; // item
			$count++;
		}

		$html .= '</div>'; 
		$html .= '</div>'; 
		return $html;
	}
}