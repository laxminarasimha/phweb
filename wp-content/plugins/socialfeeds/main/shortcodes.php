<?php

namespace SocialFeeds;

if(!defined('ABSPATH')){
	exit;
}

class Shortcodes{

	static function init(){
		add_shortcode('socialfeeds', '\SocialFeeds\Shortcodes::unified_feed');
	}

	static function unified_feed($atts){
		// Keep original atts for passthrough, but extract key identifying attributes
		$extracted = shortcode_atts(['id' => '', 'platform' => ''], $atts);
		
		$platform = strtolower(trim($extracted['platform']));
		$id = trim($extracted['id']);
		// Also support 'feed' as index/id alternative for consistency with YouTube behavior
		$feed_index = isset($atts['feed']) ? trim($atts['feed']) : '';
		
		if(empty($platform)){
			return '<p class="socialfeeds-subtitle">' . esc_html__('Platform attribute is required. Use platform="youtube", "instagram", "facebook" or "google_reviews".', 'socialfeeds') . '</p>';
		}
		
		if(empty($id) && empty($feed_index)){
			return '<p class="socialfeeds-subtitle">' . esc_html__('ID or Feed attribute is required.', 'socialfeeds') . '</p>';
		}
		
		// Route to appropriate platform handler, passing ALL attributes
		if($platform === 'youtube'){
			return self::youtube_feed_by_id($atts);
		} elseif($platform === 'instagram'){
			if(class_exists('\SocialFeedsPro\ShortcodeRender') && defined('SOCIALFEEDS_PRO_VERSION')){
				return \SocialFeedsPro\ShortcodeRender::instagram_feed($atts);
			}
		} elseif($platform === 'facebook'){
			if(class_exists('\SocialFeedsPro\ShortcodeRender') && defined('SOCIALFEEDS_PRO_VERSION')){
				return \SocialFeedsPro\ShortcodeRender::facebook_feed($atts);
			}
		} elseif($platform === 'google_reviews'){
			if(class_exists('\SocialFeedsPro\ShortcodeRender') && defined('SOCIALFEEDS_PRO_VERSION')){
				return \SocialFeedsPro\ShortcodeRender::google_reviews($atts);
			}
		} else {
			return '<p class="socialfeeds-subtitle">' . esc_html__('Invalid platform. Use platform="youtube", "instagram", "facebook" or "google_reviews".', 'socialfeeds') . '</p>';
		}
	}

	static function youtube_feed_by_id($atts){
		$opts_global = get_option('socialfeeds_youtube_option', []);
		$atts = shortcode_atts(['id' => '', 'feed' => '', 'limit' => 6, 'width' => '', 'align' => ''], $atts);
		$feeds = isset($opts_global['youtube_feeds']) ? $opts_global['youtube_feeds'] : [];
		$feed = null;
		$settings_opts = get_option('socialfeeds_settings_option', []);
		$cache_duration = !empty($settings_opts['cache']['duration']) ? absint($settings_opts['cache']['duration']) : '3600';

		if(isset($atts['feed']) && $atts['feed'] !== '' && is_numeric($atts['feed'])){
			$idx = intval($atts['feed']);
			if($idx >= 1){
				$zero_index = $idx - 1;
				if(isset($feeds[$zero_index])){
					$feed = $feeds[$zero_index];
				} else{
					return '<p class="socialfeeds-subtitle">'.esc_html__('Saved feed not found for index ', 'socialfeeds').esc_html($idx).esc_html__('.', 'socialfeeds').'</p>';
				}
			} else {
				return '<p class="socialfeeds-subtitle">'.esc_html__('Invalid feed index provided. Use a positive integer (1 = first saved feed).', 'socialfeeds').'</p>';
			}
		}

		if(empty($feed)){
			$feed_id = trim(isset($atts['id']) ? $atts['id'] : '');
			if(empty($feed_id))
				return '<p class="socialfeeds-subtitle">'.esc_html__('No feed id or index provided.', 'socialfeeds').'</p>';
			foreach($feeds as $f){
				if(isset($f['id']) && ((string) $f['id'] === (string) $feed_id || 'yt_' . (string) $f['id'] === (string) $feed_id || (string) $f['id'] === 'yt_' . (string) $feed_id)){
					$feed = $f;
					break;
				}
			}
			if(empty($feed))
				return '<p class="socialfeeds-subtitle">'.esc_html__('Saved feed not found for id ', 'socialfeeds').esc_html($feed_id) . esc_html__('.', 'socialfeeds').'</p>';
		}
		$feed_settings = isset($feed['settings']) ? $feed['settings'] : [];
		$opts = array_merge($opts_global, $feed_settings);
		$api_key = \SocialFeeds\Util::get_youtube_api_key($opts);
		
		if(empty($api_key))
			return '<p class="socialfeeds-subtitle">'.esc_html__('No YouTube API key configured.', 'socialfeeds') . '</p>';
		$limit = intval(isset($opts['youtube_videos_per_page']) ? $opts['youtube_videos_per_page'] : $atts['limit']);
		if($limit < 1)
			$limit = 6;

		$video_items = [];
		if(isset($feed['type']) && 'channel' === $feed['type']){
			// channel
			$channelId = $feed['input'];
			if(!preg_match('/^UC/', $channelId)){
				$cache_key_ch_res = 'socialfeeds_yt_channel' . md5($channelId);
				$ch_body = get_transient($cache_key_ch_res);
				if(false === $ch_body){
					$search_url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'type' => 'channel',
						'maxResults' => 1,
						'q' => $channelId,
					], 'https://www.googleapis.com/youtube/v3/search');

					$ch_resp = wp_remote_get($search_url);
					if(!is_wp_error($ch_resp) && 200 === wp_remote_retrieve_response_code($ch_resp)){
						$ch_body = json_decode(wp_remote_retrieve_body($ch_resp), true);
						set_transient($cache_key_ch_res, $ch_body, $cache_duration);
					}
				}

				if(!empty($ch_body['items'][0]['id']['channelId'])){
					$channelId = $ch_body['items'][0]['id']['channelId'];
				}
			}

			$url = add_query_arg([
				'key' => $api_key,
				'part' => 'snippet',
				'channelId' => $channelId,
				'order' => 'date',
				'type' => 'video',
				'maxResults' => $limit,
			], 'https://www.googleapis.com/youtube/v3/search');

			$cache_key = 'socialfeeds_yt_channel_name' . md5($url);
			$data = get_transient($cache_key);
			if(false === $data){
				$response = wp_remote_get($url);
				if(is_wp_error($response))
					return '<p>API request failed.</p>';
				$data = json_decode(wp_remote_retrieve_body($response), true);
				set_transient($cache_key, $data, $cache_duration);
			}
			$video_items = isset($data['items']) ? $data['items'] : [];
		} else {
			// PRO Features
			$video_items = apply_filters('socialfeeds_youtube_fetch_items', [], $feed, $api_key, $limit);
		}


		$display = isset($opts['youtube_display_style']) ? $opts['youtube_display_style'] : 'grid';
		$cols = intval(isset($opts['youtube_grid_columns_desktop']) ? $opts['youtube_grid_columns_desktop'] : (isset($opts['youtube_grid_columns']) ? $opts['youtube_grid_columns'] : 3));
		$cols_mobile = intval(isset($opts['youtube_grid_columns_mobile']) ? $opts['youtube_grid_columns_mobile'] : 1);
		
		$theme = isset($opts['youtube_theme']) ? $opts['youtube_theme'] : 'card';
		$show_title = !empty($opts['youtube_show_title']);
		$show_desc = !empty($opts['youtube_show_desc']);
		$show_play_icon = !empty($opts['youtube_show_play_icon']);
		$show_duration = !empty($opts['youtube_show_duration']);
		$show_date = !empty($opts['youtube_show_date']);
		$show_views = !empty($opts['youtube_show_views']);
		$show_likes = !empty($opts['youtube_show_likes']);
		$show_comments = !empty($opts['youtube_show_comments']);
		$show_subscribers = !empty($opts['youtube_header_show_subscribers']);

		$click_action = isset($opts['youtube_click_action']) ? $opts['youtube_click_action'] : 'newtab';
		$hover_effect = isset($opts['youtube_hover_effect']) ? $opts['youtube_hover_effect'] : 'overlay';
		$spacing = intval(isset($opts['youtube_spacing']) ? $opts['youtube_spacing'] : 16);
		$load_more_enabled = !empty($opts['youtube_load_more_enabled']);
		$scheme = isset($opts['youtube_color_scheme']) ? $opts['youtube_color_scheme'] : 'light';
		$custom_color = isset($opts['youtube_custom_color']) ? $opts['youtube_custom_color'] : '';
		$header_show_banner = !empty($opts['youtube_header_show_banner']);
		$header_banner_url = isset($opts['youtube_header_banner_url']) ? $opts['youtube_header_banner_url'] : '';
		$subscriber_count = isset($feed['subscriber_count']) ? intval($feed['subscriber_count']) : 0;
		
		// pro
		$allowed_actions = apply_filters('socialfeeds_allowed_click_actions', ['newtab']);
		if(!in_array($click_action, $allowed_actions)){
			$click_action = 'newtab';
		}

		$data_attrs = ' data-click="' . esc_attr($click_action) . '"';
		
		// Unique Render ID for Scoped CSS
		$render_id = 'sf-feed-' . uniqid();

		// Ensure we have a possible channel id variable for later
		$possible_channel_id = '';
		$stored_subscriber_count = $subscriber_count;
		$channel_url = '';
		$all_video_ids = [];
		if(!empty($video_items) && is_array($video_items)){
			foreach($video_items as $it){
				if(!empty($it['id']['videoId']))
					$all_video_ids[] = $it['id']['videoId'];
				elseif(!empty($it['id']))
					$all_video_ids[] = $it['id'];
			}
		}

		if(!empty($all_video_ids)){
			$video_items = apply_filters('socialfeeds_youtube_video_items_details', $video_items, $api_key, $limit);
		}

		// Ensure channel ID is determined for other uses (like Subscribe btn) even if header is disabled
		if(empty($possible_channel_id) && !empty($video_items) && is_array($video_items)){
			$first_tmp = reset($video_items);
			if(isset($first_tmp['snippet']['channelId'])){
				$possible_channel_id = $first_tmp['snippet']['channelId'];
			} elseif(isset($first_tmp['snippet']['videoOwnerChannelId'])){
				$possible_channel_id = $first_tmp['snippet']['videoOwnerChannelId'];
			}
		}

		// Calculate Channel URL for Subscribe button
		if (empty($channel_url)) {
			if (!empty($possible_channel_id)) {
				$channel_url = 'https://www.youtube.com/channel/' . $possible_channel_id;
			} elseif (isset($opts['youtube_url'])) {
				$channel_url = $opts['youtube_url'];
			}
			$channel_url = esc_url($channel_url);
		}

		// Calculate luminance for custom/dark early to application and header
		$is_dark_bg = ($scheme === 'dark');
		$custom_text_color = '';
		$custom_sub_color = '';

		if($scheme === 'custom' && !empty($custom_color)){
			$hex = str_replace('#', '', $custom_color);
			if(strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
			$overlay_color = "rgba($r,$g,$b,0.3)"; 
			$lum = ($r * 0.299 + $g * 0.587 + $b * 0.114);
			$is_dark_bg = ($lum < 128);
			$custom_text_color = $is_dark_bg ? '#ffffff' : '#222222';
			$custom_sub_color = $is_dark_bg ? '#cccccc' : '#666666';
		} elseif ($scheme === 'dark') {
			$custom_text_color = '#ffffff';
			$custom_sub_color = '#cccccc';
		}

		// Header rendering: build header HTML based on settings and available items
		$header_html = '';
		if(!empty($opts['youtube_header_enabled'])){
			$header_show_name = !empty($opts['youtube_header_show_channel_name']);
			$header_show_logo = !empty($opts['youtube_header_show_logo']);
			$header_show_subscribers = !empty($opts['youtube_header_show_subscribers']);
			$subscriber_text = '';
			$header_text = isset($opts['youtube_header_text']) ? $opts['youtube_header_text'] : '';
			$channel_name = '';
			$channel_logo = '';

			if(!empty($video_items) && is_array($video_items)){
				$first = reset($video_items);
				if(isset($first['snippet']['channelId'])){
					$possible_channel_id = $first['snippet']['channelId'];
				} elseif(isset($first['snippet']['videoOwnerChannelId'])){
					$possible_channel_id = $first['snippet']['videoOwnerChannelId'];
				} else{
					$possible_channel_id = '';
				}

				if (!empty($possible_channel_id) && !empty($api_key)){
					$ch_url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet,brandingSettings,statistics',
						'id' => $possible_channel_id,
					], 'https://www.googleapis.com/youtube/v3/channels');
					$cache_key_ch = 'socialfeeds_yt_headers' . md5($possible_channel_id);
					$ch_body = get_transient($cache_key_ch);
					
					if(false === $ch_body){
						$ch_resp = wp_remote_get($ch_url);
						if(!is_wp_error($ch_resp)){
							$ch_body = json_decode(wp_remote_retrieve_body($ch_resp), true);
							set_transient($cache_key_ch, $ch_body, $cache_duration);
						}
					}
					
					if(!empty($ch_body['items'][0]['snippet'])){
						$subscriber_count = 0;
						if (!empty($ch_body['items'][0]['statistics']['subscriberCount'])) {
							$subscriber_count = (int) $ch_body['items'][0]['statistics']['subscriberCount'];
						}
						$chn = $ch_body['items'][0]['snippet'];
						$channel_name = isset($chn['title']) ? $chn['title'] : $channel_name;
						if(isset($chn['thumbnails']['default']['url'])){
							$channel_logo = $chn['thumbnails']['default']['url'];
						}
					}

					if($header_show_banner && empty($header_banner_url) && !empty($ch_body['items'][0]['brandingSettings']['image']['bannerExternalUrl'])){
						$header_banner_url = $ch_body['items'][0]['brandingSettings']['image']['bannerExternalUrl'];
					}
				}

				if(isset($first['snippet']['channelTitle'])){
					$channel_name = $first['snippet']['channelTitle'];
				}
			}

			// Get channel description if enabled
			$channel_description = '';
			if(!empty($opts['youtube_header_show_description']) && !empty($possible_channel_id) && !empty($api_key)){
				$cache_key_desc = 'socialfeeds_yt_desc' . md5($possible_channel_id);
				$ch_desc_body = get_transient($cache_key_desc);

				if(false === $ch_desc_body){
					$ch_desc_url = add_query_arg([
						'key' => $api_key,
						'part' => 'snippet',
						'id' => $possible_channel_id,
					], 'https://www.googleapis.com/youtube/v3/channels');
					$ch_desc_resp = wp_remote_get($ch_desc_url);

					if(!is_wp_error($ch_desc_resp)){
						$ch_desc_body = json_decode(wp_remote_retrieve_body($ch_desc_resp), true);
						set_transient($cache_key_desc, $ch_desc_body, $cache_duration);
					}
				}

				if(!empty($ch_desc_body['items'][0]['snippet']['description'])){
					$channel_description = $ch_desc_body['items'][0]['snippet']['description'];
				}
			}

			if(!empty($header_show_banner) && !empty($header_banner_url)){
				$header_html .= '<div class="socialfeeds-header-banner" style="margin-bottom:15px; border-radius:8px; overflow:hidden; width:100%;">';
				$header_html .= '<img src="'.esc_url($header_banner_url).'" style="width:100%; height:auto; display:block; object-fit:cover; max-height:150px;" />';
				$header_html .= '</div>';
			}

			// Info row (Logo + Text)
			$header_html .= '<div class="socialfeeds-channel-info" style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">';
			
			if(!empty($header_show_logo) && !empty($channel_logo)){
				$header_html .= '<img class="socialfeeds-channel-logo" src="'.esc_url($channel_logo).'" alt="'.esc_attr($channel_name). '" style="width:48px; height:48px; border-radius:50%; object-fit:cover;" />';
			}

			$header_html .= '<div class="socialfeeds-channel-text" style="display: flex; flex-direction: column; justify-content: center;">';

			if(!empty($header_show_name) && !empty($channel_name)){
				$header_html .= '<div class="socialfeeds-channel-name" style="font-weight:700; font-size:18px; line-height:1.2; display:flex; align-items:center; gap:8px; ' . (!empty($custom_text_color) ? 'color: ' . $custom_text_color . ' !important;' : '') . '">'.esc_html($channel_name);
				if ($show_subscribers && !empty($subscriber_count)) {
					$header_html .= '<span style="font-size:14px; font-weight:400; color:' . (!empty($custom_sub_color) ? $custom_sub_color : '#666') . ' !important;">'.esc_html(self::format_count($subscriber_count)) . ' subscribers</span>';
				}
				$header_html .= '</div>';
			}
			
			if (!empty($header_text)) {
				$header_html .= '<small class="socialfeeds-channel-description" style="font-size:14px; color:' . (!empty($custom_sub_color) ? $custom_sub_color : '#666') . ' !important; margin-top:4px;">'.esc_html($header_text).'</small>';
			} elseif (!empty($opts['youtube_header_show_description']) && !empty($channel_description)) {
				$header_html .= '<small class="socialfeeds-channel-description" style="font-size:14px; color:' . (!empty($custom_sub_color) ? $custom_sub_color : '#666') . ' !important; margin-top:4px;">'.esc_html($channel_description).'</small>';
			}
			$header_html .= '</div>'; 
			$header_html .= '</div>'; 

			if(!empty($header_html)){
				$header_html = '<div class="socialfeeds-youtube-header">'.$header_html .'</div>';
			}
		}

		$wrap_style = '';
		if($scheme === 'custom' && !empty($custom_color)){
			$wrap_style = " style='background:" . esc_attr($custom_color) . " !important;'";
		}

		// Responsive Grid Style
		$dynamic_css = '';

		if( $display === 'grid' ){
			$dynamic_css .= "
			#".esc_attr($render_id)." .socialfeeds-youtube-grid {
				display: grid;
				grid-template-columns: repeat(" . esc_attr($cols) . ", 1fr);
				gap: " . esc_attr($spacing) . "px;
			}
			@media (max-width: 768px) {
				#".esc_attr($render_id)." .socialfeeds-youtube-grid {
					grid-template-columns: repeat(" . esc_attr($cols_mobile) . ", 1fr);
				}
			}";
		}

		$dynamic_css .= "
			/* Common */
			#".esc_attr($render_id)." .socialfeeds-video-item {
				transition: transform .25s ease, box-shadow .25s ease;
			}

			#".esc_attr($render_id)." .socialfeeds-card-media {
				position: relative;
				overflow: hidden;
			}

			#".esc_attr($render_id)." .socialfeeds-media-link {
				display: block;
				position: relative;
			}
			/* ------------------ OVERLAY ------------------- */
			#".esc_attr($render_id)." .hover-effect-overlay .socialfeeds-media-link::after {
				content: '';
				position: absolute;
				inset: 0;
				background: rgba(0,0,0,0.4);
				opacity: 0;
				transition: opacity .25s ease;
				pointer-events: none;
			}

			#".esc_attr($render_id)." .hover-effect-overlay .socialfeeds-media-link:hover::after {
				opacity: 1;
			}

			#".esc_attr($render_id)." .hover-effect-overlay:hover .socialfeeds-card-media::after {
				opacity: 1;
			}

			/* ------------------ SCALE ------------------- */
			#".esc_attr($render_id)." .hover-effect-scale img {
				transition: transform .25s ease;
			}

			#".esc_attr($render_id)." .hover-effect-scale:hover img {
				transform: scale(1.08);
			}

			/* ------------------ SHADOW ------------------- */
			#".esc_attr($render_id)." .hover-effect-shadow:hover {
				box-shadow: 0 12px 30px rgba(0,0,0,0.25);
				transform: translateY(-4px);
			}

			/* ------------------ NONE ------------------- */
			#".esc_attr($render_id)." .hover-effect-none:hover {
				box-shadow: none;
				transform: none;
			}";

		$width_attr = !empty($atts['width']) ? $atts['width'] : (isset($opts['youtube_width']) ? $opts['youtube_width'] : '');
		$align_attr = !empty($atts['align']) ? $atts['align'] : (isset($opts['youtube_align']) ? $opts['youtube_align'] : '');

		$width_class = !empty($width_attr) ? ' socialfeeds-width-' . str_replace('%', 'pct', esc_attr($width_attr)) : '';
		$align_class = !empty($align_attr) ? ' socialfeeds-align-' . esc_attr($align_attr) : '';

		$html = '<div id="'.esc_attr($render_id).'" class="socialfeeds-youtube-feed socialfeeds-container-' . esc_attr($display) . ' socialfeeds-scheme-' . esc_attr($scheme) . $width_class . $align_class . '"' . $wrap_style . $data_attrs . '>';
		$html .= $header_html;

		if('grid' === $display){
			// Remove inline styles for grid columns and gap as they are now in the scoped style block
			$html .= '<div class="socialfeeds-youtube-grid socialfeeds-theme-'.esc_attr($theme).' socialfeeds-card-grid">';
		} else {
			$autoplay_attr = '';
			if('carousel' === $display && !empty($opts['youtube_carousel_autoplay'])){
				$autoplay_attr = ' data-autoplay="1" data-autoplay-speed="3500"';
			}
			$html .= '<div class="socialfeeds-youtube-'.esc_attr($display).' socialfeeds-theme-' . esc_attr($theme) . '"' . $autoplay_attr . '>';
		}

		$rendered_count = !empty($video_items) ? count($video_items) : 0;

		if(!empty($video_items)){
		    $html .= self::render_items($video_items, $opts);
		}

		$html .= '</div>'; // End feed/grid/carousel/list div

		/* ------------------------------------------
		LOAD MORE + SUBSCRIBE BUTTON (INLINE ROW)
		-------------------------------------------*/

		if(!empty($load_more_enabled) && $rendered_count > 0 && (!isset($feed['type']) || $feed['type'] !== 'single-videos') && $rendered_count >= $limit){

			$feed_id = isset($feed['id']) ? $feed['id'] : '';
			$feed_type = isset($feed['type']) ? $feed['type'] : 'channel';
			$next_page_token = isset($data['nextPageToken']) ? $data['nextPageToken'] : '';
			$load_more_text = isset($opts['youtube_load_more_text']) ? $opts['youtube_load_more_text'] : 'Load More';
			$load_more_bg = isset($opts['youtube_load_more_bg_color']) ? $opts['youtube_load_more_bg_color'] : '#3f0cdaff';
			$load_more_text_color = isset($opts['youtube_load_more_text_color']) ? $opts['youtube_load_more_text_color'] : '#FFFFFF';
			$load_more_hover = isset($opts['youtube_load_more_hover_color']) ? $opts['youtube_load_more_hover_color'] : 'rgb(24, 3, 85)';
			$load_more_count = isset($opts['youtube_load_more_count']) ? intval($opts['youtube_load_more_count']) : 6;

			// START container (flex row)
			$html .= '<div class="socialfeeds-load-more-container socialfeeds-load-more-subscribe socialfeeds-load-more-inline-row">';

			// Load More Button
			$html .= '<button class="socialfeeds-load-more-btn" type="button"
				style="background:'.esc_attr($load_more_bg).'; color:'.esc_attr($load_more_text_color).' !important;"
				data-feed-id="'.esc_attr($feed_id).'"
				data-feed-type="'.esc_attr($feed_type).'"
				data-feed-input="'.esc_attr($feed['input']).'"
				data-limit="'.esc_attr($load_more_count).'"
				data-loaded="'.intval($rendered_count).'" '
				. ($next_page_token ? ' data-page-token="'.esc_attr($next_page_token).'"' : '').'
				data-load-text="'.esc_attr($load_more_text).'"
				data-loading-text="Loading..."
				data-no-more-text="No More Videos"
				>
				'.esc_html($load_more_text).'
			</button>';

			// Subscribe Button (if enabled)
			if(!empty($channel_url) && !empty($opts['youtube_subscribe_button_enabled'])){

				$subscribe_text = isset($opts['youtube_subscribe_text']) ? $opts['youtube_subscribe_text'] : 'Subscribe';
				$subscribe_bg = isset($opts['youtube_subscribe_bg_color']) ? $opts['youtube_subscribe_bg_color'] : '#FF0000';
				$subscribe_text_color = isset($opts['youtube_subscribe_text_color']) ? $opts['youtube_subscribe_text_color'] : '#FFFFFF';
				$sub_href = $channel_url.(strpos($channel_url, '?') !== false ? '&' : '?') . 'sub_confirmation=1';
				$subscribe_hover = isset($opts['youtube_subscribe_hover_color']) ? $opts['youtube_subscribe_hover_color'] : '#7a0707';

				$html .= "<a href='".esc_url($sub_href)."' target='_blank' rel='noopener' class='socialfeeds-btn-subscribe' style='background: ".esc_attr($subscribe_bg)."; color:".esc_attr($subscribe_text_color)." !important;'>" . esc_html($subscribe_text) . "</a>";
				// load more button hover
				if(!empty($load_more_enabled) && $rendered_count > 0 && ( ! isset( $feed['type'] ) || $feed['type'] !== 'single-videos' ) && $rendered_count >= $limit ){

					$load_more_hover = isset( $opts['youtube_load_more_hover_color']) ? $opts['youtube_load_more_hover_color'] : 'rgb(24, 3, 85)';

					$dynamic_css .= '#' . esc_attr( $render_id ) . ' .socialfeeds-load-more-btn:hover {' .'background: ' . esc_attr( $load_more_hover ) . ' !important;' .
						'}';
				}

				// subscribe button hover
				if(!empty($channel_url) && ! empty($opts['youtube_subscribe_button_enabled'])){

					$dynamic_css .= '#' . esc_attr( $render_id ) . ' .socialfeeds-btn-subscribe:hover {' .'background: ' . esc_attr( $subscribe_hover ) . ' !important;' .
						'}';
				}
			}
			
			$html .= '</div>';
		}
		
		$html .= '</div>'; // End socialfeeds-youtube-feed wrapper

		// Enqueue
		if(!empty($dynamic_css)){
			$handle = 'socialfeeds-dynamic-' . sanitize_key($render_id);
			wp_register_style($handle, false, [], SOCIALFEEDS_VERSION);
			wp_enqueue_style($handle);
			wp_add_inline_style($handle, wp_strip_all_tags($dynamic_css));
		}

		return $html;
	}

	static function format_count($num){
		$num = (int) $num;

		if ($num < 1000) {
			return (string) $num; }

		if ($num < 1000000) {
			$val = $num / 1000;
			return ($val >= 10 ? round($val) : round($val, 1)) . 'K'; }

		if ($num < 1000000000) {
			$val = $num / 1000000;
			return ($val >= 10 ? round($val) : round($val, 1)) . 'M'; }

		$val = $num / 1000000000;
		return round($val, 1) . 'B';
	}

	static function render_items($video_items, $opts){
		$html = '';
		$display = isset($opts['youtube_display_style']) ? $opts['youtube_display_style'] : 'grid';
		$show_title = !empty($opts['youtube_show_title']);
		$show_desc = !empty($opts['youtube_show_desc']);
		$show_play_icon = !empty($opts['youtube_show_play_icon']);
		$show_duration = !empty($opts['youtube_show_duration']);
		$show_date = !empty($opts['youtube_show_date']);
		$show_views = !empty($opts['youtube_show_views']);
		$show_likes = !empty($opts['youtube_show_likes']);
		$show_comments = !empty($opts['youtube_show_comments']);
		// thumb_size removed, always medium
		
		$hover_effect = isset($opts['youtube_hover_effect']) ? $opts['youtube_hover_effect'] : 'overlay';
		$scheme = isset($opts['youtube_color_scheme']) ? $opts['youtube_color_scheme'] : 'light';
		$custom_color = isset($opts['youtube_custom_color']) ? $opts['youtube_custom_color'] : '';

		// Color calc
		$custom_text_color = '';
		$custom_sub_color = '';
		if($scheme === 'custom' && !empty($custom_color)){
			$hex = str_replace('#', '', $custom_color);
			if(strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
			$lum = ($r * 0.299 + $g * 0.587 + $b * 0.114);
			$is_dark_bg = ($lum < 128);
			$custom_text_color = $is_dark_bg ? '#ffffff' : '#222222';
			$custom_sub_color = $is_dark_bg ? '#cccccc' : '#666666';
		} elseif ($scheme === 'dark') {
			$custom_text_color = '#ffffff';
			$custom_sub_color = '#cccccc';
		}

		foreach($video_items as $item){
			$video_id = '';
			
			if(isset($item['id']['videoId'])){
				$video_id = $item['id']['videoId'];
			} elseif(isset($item['id']) && is_string($item['id'])){
				$video_id = $item['id'];
			} elseif(isset($item['id']) && is_array($item['id']) && !empty($item['id'][0])){
				$video_id = $item['id'][0];
			} elseif(isset($item['videoId'])){
				$video_id = $item['videoId'];
			}
			
			if(empty($video_id))
				continue;

			$title = isset($item['snippet']['title']) ? $item['snippet']['title'] : (isset($item['title']) ? $item['title'] : '');
			$desc = isset($item['snippet']['description']) ? $item['snippet']['description'] : (isset($item['description']) ? $item['description'] : '');
			$thumb = isset($item['snippet']['thumbnails']['medium']['url']) ? $item['snippet']['thumbnails']['medium']['url'] : (isset($item['thumbnails']['medium']['url']) ? $item['thumbnails']['medium']['url'] : '');

			// Fallback for load more items structure which might differ slightly
			if(empty($thumb) && isset($item['thumbnails']['default']['url'])){
				$thumb = $item['thumbnails']['default']['url'];
			}

			$lazy_load = isset($opts['youtube_lazy_load']) ? $opts['youtube_lazy_load'] : 1;
			$watch_url = 'https://www.youtube.com/watch?v=' . $video_id;
			$short_desc = mb_substr($desc, 0, 60);
			if(mb_strlen($desc) > 60) $short_desc .= '...';

			
			// Metadata calculation
			$meta_pieces = [];
			
			$view_count = isset($item['statistics']['viewCount']) ? $item['statistics']['viewCount'] : '';
			$like_count = isset($item['statistics']['likeCount']) ? $item['statistics']['likeCount'] : '';
			$comment_count = isset($item['statistics']['commentCount']) ? $item['statistics']['commentCount'] : '';
			$published_at = isset($item['snippet']['publishedAt']) ? $item['snippet']['publishedAt'] : (isset($item['publishedAt']) ? $item['publishedAt'] : '');

			if ($show_views && $view_count !== '') {
				$meta_pieces[] = '<span class="socialfeeds-meta-item">' . 
					esc_html(self::format_count($view_count)) . ' ' . esc_html__('views', 'socialfeeds') . '</span>';
			}

			if ($show_likes && $like_count !== '') {
				$meta_pieces[] = '<span class="socialfeeds-meta-item">' . 
					esc_html(self::format_count($like_count)) . ' ' . esc_html__('likes', 'socialfeeds') . '</span>';
			}

			if ($show_comments && $comment_count !== '') {
				$meta_pieces[] = '<span class="socialfeeds-meta-item">' . 
					esc_html(self::format_count($comment_count)) . ' ' . esc_html__('comments', 'socialfeeds') . '</span>';
			}

			if ($show_date && !empty($published_at)) {
				$datef = date_i18n(get_option('date_format'), strtotime($published_at));
				$meta_pieces[] = '<span class="socialfeeds-meta-item">' . esc_html($datef) . '</span>';
			}


			$item_style = '';
			$text_color_style = '';
			$sub_color_style = '';
			
			if($scheme === 'custom' && !empty($custom_color)){
				$item_style = 'background:transparent !important; border:none !important; border-radius:0 !important; box-shadow:none !important;';
				if(!empty($custom_text_color)){
					$text_color_style = 'color: ' . $custom_text_color . ' !important;';
				}

				if(!empty($custom_sub_color)){
					$sub_color_style = 'color: ' . $custom_sub_color . ' !important;';
				}
			} elseif($scheme === 'dark') {
				$item_style = 'background:#0f0f0f !important; border:none !important; border-radius:0 !important; box-shadow:none !important;';
			}

			// Generate Item Inner Content
			$inner_content = "<div class='socialfeeds-card-media' style='position:relative; overflow:hidden;'>";
			$inner_content .= "<a href='" . esc_url($watch_url) . "' target='_blank' rel='noopener' class='socialfeeds-media-link' style='display:block; position:relative;'>";

			$thumb_tag = "<img src='".esc_url($thumb)."' alt='".esc_attr($title)."' style='width:100%; display:block;'";
			if(!empty($lazy_load)){
				$thumb_tag .= " loading='lazy'";
			}
			$inner_content .= $thumb_tag . "/>";

			$duration = isset($item['contentDetails']['duration']) ? $item['contentDetails']['duration'] : (isset($item['duration']) ? $item['duration'] : '');

			if(!empty($show_duration) && !empty($duration)){
				$dur = self::format_iso_duration($duration);
				$inner_content .= "<div class='socialfeeds-video-duration'>".esc_html($dur)."</div>";
			}

			if(!empty($opts['youtube_show_play_icon'])){
				$inner_content .= '<span class="socialfeeds-play-overlay"></span>';
			}
			$inner_content .= "</a>";

			if(!empty($show_title)){
				$title_tag = ('list' === $display) ? 'h3' : 'h5';

				$inner_content .= "<" . esc_html($title_tag) . " class='socialfeeds-video-title'" . (!empty($text_color_style) ? " style='" . esc_attr($text_color_style) . "'" : '') . ">" . esc_html($title) . "</" . esc_html($title_tag) . ">";
			}
			$inner_content .= "</div>";

			$inner_content .= "<div class='socialfeeds-card-actions'>";
			if(!empty($meta_pieces)){

				$inner_content .= '<div class="socialfeeds-video-meta-line socialfeeds-video-stats"'. (!empty($sub_color_style) ? ' style="' . esc_attr($sub_color_style) . '"' : ''). '>';
				$inner_content .= implode('', $meta_pieces);
				
				$inner_content .= '</div>';
			}
			$inner_content .= "</div>";

			if($show_desc && !empty($short_desc)){
				$inner_content .= "<p class='socialfeeds-card-desc socialfeeds-video-desc'" . (!empty($sub_color_style) ? ' style="'.esc_attr($sub_color_style) . '"' : ''). ">" . esc_html($short_desc)."</p>";
			}

			// Wrap based on display
			if('grid' === $display){
				$data_attrs = array(
					'data-video-id' => $video_id,
					'data-duration' => $duration,
					'data-date' => $published_at,
					'data-views' => $view_count,
					'data-likes' => $like_count,
					'data-comments' => $comment_count
				);

				$html .= "<div class='socialfeeds-video-item hover-effect-" . esc_attr($hover_effect) . "'";
				
				// Add item style if exists
				if(!empty($item_style)){
					$html .= " style='" . esc_attr($item_style) . "'";
				}
				
				// Add data attributes
				foreach($data_attrs as $attr => $value){
					if($value !== ''){ 
						$html .= " " . esc_html($attr) . "='" . esc_attr($value) . "'";
					}
				}
				
				$html .= ">";
				$html .= wp_kses_post($inner_content);
				$html .= "</div>";
			} elseif('list' === $display && defined('SOCIALFEEDS_PRO_VERSION')){
				$html .= "<div class='socialfeeds-video-item socialfeeds-list-item hover-effect-" . esc_attr($hover_effect) . "'";
				if(!empty($item_style)){
					$html .= " style='".esc_attr($item_style)."'";
				}
				$html .= ">";
				$html .= wp_kses_post($inner_content);
				$html .= "</div>";
			} elseif('carousel' === $display && defined('SOCIALFEEDS_PRO_VERSION')){
				$html .= "<div class='socialfeeds-video-item socialfeeds-carousel-item hover-effect-" . esc_attr($hover_effect) . "'";
				if(!empty($item_style)){
					$html .= " style='".esc_attr($item_style)."'";
				}
				$html .= ">";
				$html .= wp_kses_post($inner_content);
				$html .= "</div>";
			}
		}
		return wp_kses_post($html);
	}

	static function format_iso_duration($iso){
		if(empty($iso)){
			return '';
		}

		$interval = new \DateInterval($iso);

		$hours = $interval->h + ($interval->d * 24);
		$minutes = $interval->i;
		$seconds = $interval->s;

		if($hours > 0)
			return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
		return sprintf('%d:%02d', $minutes, $seconds);
	}

}

