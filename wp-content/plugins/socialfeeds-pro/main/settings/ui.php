<?php

namespace SocialFeedsPro\Settings;

if(!defined('ABSPATH')){
	exit;
}

class UI{

	static function instagram_connect_screen(){
		$feed_type = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : '';


		$preview_url = isset($_GET['preview_url']) ? esc_url_raw( wp_unslash( $_GET['preview_url'] ) ) : '';
		$message = isset($_GET['socialfeeds_msg']) ? sanitize_text_field( wp_unslash( $_GET['socialfeeds_msg'] ) ) : '';
		$opts = get_option('socialfeeds_instagram_option', []);
		
		// Editing existing feed
		$edit_id = isset($_GET['edit_id']) ? sanitize_text_field(wp_unslash($_GET['edit_id'])) : '';
		if(empty($edit_id) && isset($_GET['feed_id'])){
			$edit_id = sanitize_text_field(wp_unslash($_GET['feed_id']));
		}

		if(empty($edit_id) && isset($_GET['id'])){
			$edit_id = sanitize_text_field(wp_unslash($_GET['id']));
		}
		$feed_settings = [];
		$socialfeeds_input_value = '';
		$selected_account_index = null;
		
		$found_in_ig = false;
		if(!empty($edit_id)){
			if(!empty($opts['instagram_feeds'])){
				foreach($opts['instagram_feeds'] as $feed){
					if(isset($feed['id']) && (string) $feed['id'] === (string) $edit_id) {
						$found_in_ig = true;
						break;
					}
				}
			}

			if(empty($found_in_ig)){
				return;
			}
		}

		if ($edit_id && !empty($opts['instagram_feeds'])) {
			foreach ($opts['instagram_feeds'] as $feed) {
				if (isset($feed['id']) && (string) $feed['id'] === (string) $edit_id) {
					if (isset($feed['account_id'])) {
					$connected_accounts = isset($opts['instagram_connected_accounts']) ? $opts['instagram_connected_accounts'] : [];
						foreach ($connected_accounts as $idx => $account) {
							if (isset($account['id']) && (string) $account['id'] === (string) $feed['account_id']) {
							$selected_account_index = $idx;
							break;
							}
						}
					}
					break;
				}
			}
		}

		if(!empty($edit_id)){
			$feeds = isset($opts['instagram_feeds']) ? $opts['instagram_feeds'] : [];
			foreach($feeds as $f){
				if(isset($f['id']) && (string) $f['id'] === (string) $edit_id) {
					if(!empty($f['type'])){
						$feed_type = $f['type'];
					}
					if (isset($f['input'])) {
						$socialfeeds_input_value = rawurlencode($f['input']);
					}
					if (isset($f['preview']) && !$preview_url) {
						$preview_url = $f['preview'];
					}
					if (isset($f['settings']) && is_array($f['settings'])) {
						$feed_settings = $f['settings'];
					}
					break;
				}
			}
		}
		
		// Defaults
		$defaults = [
			// Layout
			'instagram_layout' => 'grid', // grid, carousel, masonry, highlight
			'instagram_aspect_ratio' => '',	 // px, optional
			'instagram_padding' => 8,
			'instagram_number_posts_desktop' => 12,
			'instagram_number_posts_mobile' => 6,
			'instagram_columns_desktop' => 3,
			'instagram_columns_tablet' => 2,
			'instagram_columns_mobile' => 1,
			// Color scheme
			'instagram_color_scheme' => 'light',
			'instagram_custom_color' => '#000000',
			// Header
			'instagram_header_enabled' => 1,
			'instagram_header_size' => 56,	// px
			'instagram_use_custom_avatar' => 0,
			'instagram_custom_avatar' => '', // URL
			'instagram_show_bio_text' => 1,
			'instagram_show_followers' => 0,
			'instagram_media_count' => 0,
			'instagram_header_style' => 'left', // standard, boxed, centered, left, middle, right
			// Stories
			'instagram_include_stories' => 0,
			'instagram_story_duration' => 5, // seconds
			// Media options
			'instagram_caption_enabled' => 1,
			'instagram_likes' => 0,
			'instagram_comments' => 0,
			'instagram_video_views' => 0,
			'instagram_hover_state' => 'overlay', // overlay, scale, none
			'instagram_alignment' => 'left', 
			// Load more & follow
			'instagram_load_more_enabled' => 1,
			'instagram_load_more_text' => 'Load More',
			'instagram_load_more_bg_color' => '#350ae1',
			'instagram_load_more_text_color' => '#FFFFFF',
			'instagram_load_more_hover_color' => '#160755',
			'instagram_infinite_scroll' => 0,
			'instagram_follow_button_enabled' => 1,
			'instagram_follow_button_text' => 'Follow on Instagram',
			'instagram_follow_button_bg_color' => '#FF3B30',
			'instagram_follow_button_text_color' => '#ffffff',
			'instagram_follow_button_hover_color' => '#740c06',
			// Lightbox
			'instagram_lightbox_enabled' => 1,
			// Source / API
			'instagram_source_type' => 'business', // personal, business, manual
			'instagram_account_id' => '',
			'instagram_access_token' => '',
			// Filters / moderation
			'instagram_filter_include_words' => '',
			'instagram_filter_exclude_words' => '',
			'instagram_show_photos' => 1,
			'instagram_show_feed_posts' => 1,
			'instagram_show_igtv' => 0,
			'instagram_show_reels' => 1,
			'instagram_post_offset' => 0,
			'instagram_sort_by' => 'newest', // newest, likes, random
			// Shoppable
			'instagram_shoppable_enabled' => 0,
			// Advanced
			'instagram_max_concurrent_requests' => 2,
			'instagram_custom_template_enabled' => 0,
			'instagram_custom_template_path' => '',
		];
		
		$settings = array_merge($defaults, $feed_settings);
		$admin_post = esc_url(admin_url('admin-ajax.php'));
		$admin_page = admin_url('admin.php?page=socialfeeds&tab=templates');
		
		echo '<div class="socialfeeds-wizard-container">';
		
		$connection_type = isset($_GET['connection_type']) ? sanitize_text_field(wp_unslash($_GET['connection_type'])) : '';
		
		if (empty($feed_type) && empty($edit_id)){
			self::render_instagram_feed_type_selection($connection_type, $opts);
		} else {
			if(empty($feed_type)){ 
				$feed_type = 'username'; 
			}
			self::render_instagram_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index, $socialfeeds_input_value);
			self::render_instagram_embed_modal();
		}
		
		echo '</div>';
	}

	static function render_instagram_feed_type_selection($connection_type, $opts) {
		$saved_token_type = isset($opts['instagram_token_type']) ? $opts['instagram_token_type'] : 'basic';

		if($connection_type === 'manual' && isset($_GET['step']) && 'token' === $_GET['step']){
			echo '<div class="socialfeeds-feed-main-card">
					<div class="socialfeeds-feed-main-header">
						<h2>'.esc_html__('Enter Access Token', 'socialfeeds-pro') . '</h2>
						<p>'.esc_html__('Paste a long-lived Instagram Graph API token to connect your account.', 'socialfeeds-pro').'</p>
					</div>
					<div class="socialfeeds-standalone-wrapper">
						
						<!-- Connection Type Selection -->
						<div class="socialfeeds-source-connection-type" style="margin-bottom:20px;">
							<label class="socialfeeds-connection-type-label">' . esc_html__('Select Connection Type', 'socialfeeds-pro') . '</label>
							
							<div class="socialfeeds-connection-type-cards">
								<label class="socialfeeds-connection-card ' . ($saved_token_type !== 'advanced' ? 'selected' : '') . '" data-type="basic">
									<input type="radio" name="instagram_source_token_type" value="basic" ' . checked($saved_token_type, 'basic', false) . ' ' . ($saved_token_type !== 'advanced' ? 'checked' : '') . '>
									<div class="socialfeeds-connection-card-inner">
										<span class="socialfeeds-connection-radio"></span>
										<div class="socialfeeds-connection-card-text">
											<strong>' . esc_html__('Business Basic', 'socialfeeds-pro') . '</strong>
											<span>' . esc_html__('Connect via Access Token', 'socialfeeds-pro') . '</span>
										</div>
									</div>
								</label>
								<label class="socialfeeds-connection-card ' . ($saved_token_type === 'advanced' ? 'selected' : '') . '" data-type="advanced">
									<input type="radio" name="instagram_source_token_type" value="advanced" ' . checked($saved_token_type, 'advanced', false) . '>
									<div class="socialfeeds-connection-card-inner">
										<span class="socialfeeds-connection-radio"></span>
										<div class="socialfeeds-connection-card-text">
											<strong>' . esc_html__('Business Advanced', 'socialfeeds-pro') . '</strong>
											<span>' . esc_html__('Connects via Facebook', 'socialfeeds-pro') . '</span>
										</div>
									</div>
								</label>
							</div>
						</div>

						<!-- Feature bullets for Basic -->
						<div class="socialfeeds-connection-features" id="socialfeeds-source-features-basic"' . ($saved_token_type === 'advanced' ? ' style="display:none;"' : '') . '>
							<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Requires Instagram Creator or Business account', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display profile info, avatars, and posts', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Does not require a Facebook page', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Hashtag feeds (from your account)', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item no"><span class="dashicons dashicons-dismiss"></span> ' . esc_html__('Does not display Tagged posts', 'socialfeeds-pro') . '</div>
						</div>

						<!-- Feature bullets for Advanced -->
						<div class="socialfeeds-connection-features" id="socialfeeds-source-features-advanced"' . ($saved_token_type !== 'advanced' ? ' style="display:none;"' : '') . '>
							<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Requires Facebook Page linked to Instagram Business account', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display profile info, avatars, and posts', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Hashtag feeds (from your account)', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Tagged posts', 'socialfeeds-pro') . '</div>
							<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Full Instagram API access', 'socialfeeds-pro') . '</div>
						</div>

						<textarea id="socialfeeds-standalone-token-input" class="socialfeeds-standalone-token-input"
							placeholder="' . esc_attr__('Paste long-lived access token here', 'socialfeeds-pro') . '" style="width:100%; min-height:100px; margin-bottom:15px; padding:12px; border-radius:8px; border:1px solid #e2e8f0;"></textarea>
						<div id="socialfeeds-standalone-ig-user-id-group"' . ($saved_token_type !== 'advanced' ? ' style="display:none; margin-bottom:15px;"' : ' style="margin-bottom:15px;"') . '>
							<label for="socialfeeds-standalone-ig-user-id" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('Instagram User ID', 'socialfeeds-pro') . '</label>
							<input type="text" id="socialfeeds-standalone-ig-user-id" class="socialfeeds-input-full" placeholder="' . esc_attr__('e.g. 17841400123456789', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
							<p class="description" style="margin-top:4px; font-size:12px; color:#64748b;">' . esc_html__('Required if auto-detection fails. Found in Meta Business Suite.', 'socialfeeds-pro') . '</p>
						</div>

						<!-- Instagram App Credentials (Advanced only) -->
						<div id="socialfeeds-modal-ig-app-group" ' . ($saved_token_type !== 'advanced' ? 'style="display:none;"' : '') . '>
							<div style="margin-bottom:15px;">
								<label for="socialfeeds-standalone-app-id" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App ID', 'socialfeeds-pro') . '</label>
								<input type="text" id="socialfeeds-standalone-app-id" class="socialfeeds-input-full" placeholder="' . esc_attr__('Enter App ID...', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
							</div>
							<div style="margin-bottom:15px;">
								<label for="socialfeeds-standalone-app-secret" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App Secret', 'socialfeeds-pro') . '</label>
								<input type="password" id="socialfeeds-standalone-app-secret" class="socialfeeds-input-full" placeholder="' . esc_attr__('Enter App Secret...', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
								<p class="description" style="margin-top:4px; font-size:12px; color:#64748b;">' . esc_html__('Providing App ID and Secret allows automatic token refresh and conversion to long-lived tokens.', 'socialfeeds-pro') . '</p>
							</div>
						</div>
						<p id="socialfeeds-standalone-token-message" class="socialfeeds-standalone-token-message"></p>
						<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
							<a href="'.esc_url(admin_url('admin.php?page=socialfeeds&connection_type=manual#instagram')).'" class="socialfeeds-btn-manage" style="width:auto; padding:10px 30px; border:none;"> ' . esc_html__('Back', 'socialfeeds-pro') . '</a>
							<button id="socialfeeds-ig-validate-btn" class="socialfeeds-btn-sync" style="padding:10px 40px;">
								' . esc_html__('Validate & Connect', 'socialfeeds-pro') . '
							</button>
						</div>
					</div>
				</div>';
		} else {
			echo '<div class="socialfeeds-feed-main-card">
					<div class="socialfeeds-feed-main-header">
						<span class="socialfeeds-section-title">'.esc_html__('Select Feed Type', 'socialfeeds-pro').'</span>
						<p>'.esc_html__('Choose the Instagram source type for this feed.', 'socialfeeds-pro').'</p>
					</div>
					
					<div class="socialfeeds-feed-type-v2">
						<!-- User Timeline -->
						<div class="socialfeeds-type-card selected" data-type="username">
							<div class="socialfeeds-p-card-icon instagram" style="background:#fdf2f8; color:#db2777; margin-bottom:15px;">
								<span class="dashicons dashicons-instagram"></span>
							</div>
							<div class="socialfeeds-card-content">
								<h3>'.esc_html__('User Timeline', 'socialfeeds-pro').'</h3>
								<p>'.esc_html__('Fetch posts from your Instagram profile.', 'socialfeeds-pro').'</p>
							</div>
						</div>

						<!-- Hashtag -->
						<div class="socialfeeds-type-card" data-type="hashtag">
							<div class="socialfeeds-p-card-icon" style="background:#fefce8; color:#ca8a04; margin-bottom:15px;">
								<span class="dashicons dashicons-hash">#</span>
							</div>
							<div class="socialfeeds-card-content">
								<h3>'.esc_html__('Hashtag Posts', 'socialfeeds-pro') . '</h3>
								<p>'.esc_html__('Fetch hashtag posts from your connected Instagram account.', 'socialfeeds-pro').'</p>
							</div>
						</div>

						<!-- Tagged -->
						<div class="socialfeeds-type-card" data-type="manual">
							<div class="socialfeeds-p-card-icon" style="background:#f0fdf4; color:#16a34a; margin-bottom:15px;">
								<span class="dashicons dashicons-admin-users"></span>
							</div>
							<div class="socialfeeds-card-content">
								<h3>'.esc_html__('Tagged Posts', 'socialfeeds-pro').'</h3>
								<p>'.esc_html__('Display posts your Instagram account has been tagged in.', 'socialfeeds-pro').'</p>
							</div>
						</div>

					</div>

					<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
					<a id="socialfeeds-select-type-btn-instagram" class="socialfeeds-btn-sync socialfeeds-disabled" href="#" style="padding:10px 40px;">'.esc_html__('Next', 'socialfeeds-pro').' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></a>
					</div>
					<input type="hidden" id="socialfeeds-connection-type-hidden" value="' . esc_attr($connection_type) . '">
				</div>';
		}
	}

	static function render_instagram_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index = null, $socialfeeds_input_value = '') {
		echo '<form method="post" action="' . esc_attr($admin_post) . '" id="socialfeeds-instagram-wizard-form" class="socialfeeds-wizard-form">';
			wp_nonce_field('socialfeeds_pro_nonce', 'nonce');
			echo '<input type="hidden" name="action" value="socialfeeds_pro_instagram_wizard_save">
			<input type="hidden" id="socialfeeds_stage" name="stage" value="customize">
			<input type="hidden" name="feed_type" id="socialfeeds-hidden-feed-type" value="' . esc_attr($feed_type) . '">
			<input type="hidden" id="preview_url_hidden" name="preview_url" value="' . esc_attr($preview_url) . '">';
			
			$source_active_class = empty($edit_id) ? 'active' : '';
			$customize_active_class = !empty($edit_id) ? 'active' : '';
			
			echo '<div class="socialfeeds-wizard-tabs">
				<div class="socialfeeds-wizard-tab ' .esc_attr($source_active_class). '" id="socialfeeds-instagram-tab-source" data-tab="source">
					<span class="socialfeeds-tab-number">1</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Source', 'socialfeeds-pro') . '</span>
				</div>
				<div class="socialfeeds-wizard-tab ' .esc_attr($customize_active_class). '" id="socialfeeds-instagram-tab-customize" data-tab="customize">
					<span class="socialfeeds-tab-number">2</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Customize', 'socialfeeds-pro') . '</span>
				</div>
			</div>
			<div class="socialfeeds-wizard-tab-content-wrapper">';
		
		// STEP 1: SOURCE INPUT
		$step1_style = empty($edit_id) ? '' : 'style="display:none;"';
		echo '<div class="socialfeeds-wizard-step ' . esc_attr($source_active_class) . '" id="socialfeeds-step-1" ' . $step1_style. '>';
		self::render_instagram_source_tab($feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
		echo '</div>';

		$step2_style = !empty($edit_id) ? '' : 'style="display:none;"';
		echo '<div class="socialfeeds-wizard-step ' . esc_attr($customize_active_class) . '" id="socialfeeds-step-2" ' . $step2_style . '>';
		// STEP 2: CUSTOMIZE & SETTINGS (Main Layout)
		self::render_instagram_customize_tab($settings, $preview_url, $edit_id, $opts, $feed_type, $selected_account_index, $socialfeeds_input_value);
		
		echo '</div>'; // close step 2
		
		echo '</div></form>';
	}

	static function render_instagram_source_tab($feed_type, $edit_id, $opts, $selected_account_index = null, $input_val = ''){
		echo '<div class="socialfeeds-source-card-v2">
				<div class="socialfeeds-feed-main-header" style="margin-bottom:30px;">
					<h2>'.esc_html__('Configure Source', 'socialfeeds-pro').'</h2>
					<p>'.esc_html__('Choose an account and provide the required information for your feed.', 'socialfeeds-pro').'</p>
				</div>';

		// Accounts block
		echo '<div class="socialfeeds-control-group" style="margin-bottom:20px;">
				<label class="socialfeeds-control-label">'.esc_html__('Select Account', 'socialfeeds-pro').'</label>
				<div class="socialfeeds-accounts-list" id="socialfeeds-accounts-list" style="display: grid; gap: 8px; margin-bottom: 10px;">';
		
		$connected_accounts = isset($opts['instagram_connected_accounts']) ? $opts['instagram_connected_accounts'] : [];
		if (empty($connected_accounts)) {
			echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
					<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No accounts connected.', 'socialfeeds-pro') . '</p>
				  </div>';
		} else {
			foreach ($connected_accounts as $idx => $account) {
				$checked = '';
			if ($edit_id && $selected_account_index !== null) {
				$checked = ($idx === $selected_account_index) ? ' checked' : '';
			} else {
				$checked = ($idx === 0) ? ' checked' : '';
			}
					echo '<label class="socialfeeds-account-item ' . ($checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:15px; border:2px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all 0.2s; position:relative;">
						<input type="radio" name="instagram_selected_account" id="socialfeeds-instagram-selected-account-'. esc_attr($idx) .'" value="' . esc_attr($idx) . '" style="margin-right:15px;" ' . esc_attr($checked) . '>
						<div style="width:48px; height:48px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:15px; display:flex; align-items:center; justify-content:center;">';
							if (!empty($account['profile_picture_url'])) {
								echo '<img src="' . esc_url($account['profile_picture_url']) . '" style="width:100%; height:100%; object-fit:cover;">';
							} else {
								echo '<span style="font-weight:600; color:#64748b;">' . esc_html(strtoupper(substr($account['username'], 0, 1))) . '</span>';
							}
						echo '</div>
					<div style="flex:1;">
						<strong style="display:block; font-size:15px; color:#1e293b;">' . esc_html($account['username']) . '</strong>
						<small style="color:#64748b;">' . esc_html(isset($account['account_type']) ? $account['account_type'] : 'PERSONAL') . ' Account</small>';
						$acct_token_type = isset($account['token_type']) ? $account['token_type'] : 'basic';
						if($acct_token_type === 'advanced'){
							echo ' <span style="display:inline-block; background:#dbeafe; color:#2563eb; font-size:10px; font-weight:600; padding:2px 6px; border-radius:4px; vertical-align:middle;">' . esc_html__('Advanced', 'socialfeeds-pro') . '</span>';
						} else {
							echo ' <span style="display:inline-block; background:#f0fdf4; color:#16a34a; font-size:10px; font-weight:600; padding:2px 6px; border-radius:4px; vertical-align:middle;">' . esc_html__('Basic', 'socialfeeds-pro') . '</span>';
						}
					echo '</div>
					<button type="button" class="socialfeeds-delete-account-btn" data-account-id="' . esc_attr($account['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; margin-left:10px; z-index:10; display: flex; align-items: center;" title="' . esc_attr__('Delete Account', 'socialfeeds-pro') . '">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</label>';
			}
		}
		echo '	</div>
			<div style="margin-top:20px;">
				<button type="button" id="socialfeeds-wizard-add-account-btn" class="socialfeeds-btn-manage" style="width:100%; justify-content:center;border-style:dashed; background:#fff;">
					<span class="dashicons dashicons-plus" style="margin-top:2px;"></span> ' . esc_html__('Add New Account', 'socialfeeds-pro') . '
				</button>
			</div>
				
				<div id="socialfeeds-wizard-token-form" style="display:none; margin-top:20px; padding:20px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
					<h4 style="margin:0 0 10px 0;">' . esc_html__('Enter Instagram Token', 'socialfeeds-pro') . '</h4>
					
					<div class="socialfeeds-connection-type-cards" style="margin-bottom:20px;">
						<label class="socialfeeds-connection-card selected" data-type="basic">
							<input type="radio" name="wizard_instagram_token_type" value="basic" checked>
							<div class="socialfeeds-connection-card-inner">
								<span class="socialfeeds-connection-radio"></span>
								<div class="socialfeeds-connection-card-text">
									<strong>' . esc_html__('Business Basic', 'socialfeeds-pro') . '</strong>
									<span>' . esc_html__('Connect via Access Token', 'socialfeeds-pro') . '</span>
								</div>
							</div>
						</label>
						<label class="socialfeeds-connection-card" data-type="advanced">
							<input type="radio" name="wizard_instagram_token_type" value="advanced">
							<div class="socialfeeds-connection-card-inner">
								<span class="socialfeeds-connection-radio"></span>
								<div class="socialfeeds-connection-card-text">
									<strong>' . esc_html__('Business Advanced', 'socialfeeds-pro') . '</strong>
									<span>' . esc_html__('Connects via Facebook', 'socialfeeds-pro') . '</span>
								</div>
							</div>
						</label>
					</div>

					<textarea id="socialfeeds-wizard-token-input" placeholder="' . esc_html__('Paste your Instagram access token here...', 'socialfeeds-pro') . '" style="width:100%; min-height:80px; padding:12px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:12px;"></textarea>
					<div id="socialfeeds-wizard-ig-user-id-group" style="display:none; margin-bottom:12px;">
						<label for="socialfeeds-wizard-ig-user-id" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('Instagram User ID', 'socialfeeds-pro') . '</label>
						<input type="text" id="socialfeeds-wizard-ig-user-id" class="socialfeeds-input-full" placeholder="' . esc_attr__('e.g. 17841400123456789', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
						<p class="description" style="margin-top:4px; font-size:12px; color:#64748b;">' . esc_html__('Required if auto-detection fails. Found in Meta Business Suite.', 'socialfeeds-pro') . '</p>
					</div>

					<!-- Instagram App Credentials (Advanced only) -->
					<div id="socialfeeds-wizard-ig-app-group" style="display:none;">
						<div style="margin-bottom:12px;">
							<label for="socialfeeds-wizard-app-id" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App ID', 'socialfeeds-pro') . '</label>
							<input type="text" id="socialfeeds-wizard-app-id" class="socialfeeds-input-full" placeholder="' . esc_attr__('Enter App ID...', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
						</div>
						<div style="margin-bottom:12px;">
							<label for="socialfeeds-wizard-app-secret" style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App Secret', 'socialfeeds-pro') . '</label>
							<input type="password" id="socialfeeds-wizard-app-secret" class="socialfeeds-input-full" placeholder="' . esc_attr__('Enter App Secret...', 'socialfeeds-pro') . '" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
							<p class="description" style="margin-top:4px; font-size:12px; color:#64748b;">' . esc_html__('Providing App ID and Secret allows automatic token refresh and conversion to long-lived tokens.', 'socialfeeds-pro') . '</p>
						</div>
					</div>
					<div id="socialfeeds-wizard-token-message"></div>
					<div style="display:flex; gap:10px; justify-content:flex-end;">
						<button type="button" id="socialfeeds-wizard-cancel-token-btn" class="socialfeeds-btn-manage" style="border:none;">' . esc_html__('Cancel', 'socialfeeds-pro') . '</button>
						<button type="button" id="socialfeeds-wizard-validate-token-btn" class="socialfeeds-btn-sync">' . esc_html__('Validate & Connect', 'socialfeeds-pro') . '</button>
					</div>
				</div>
			</div>';

		// Input for Hashtag
		$display_input = ($feed_type === 'hashtag') ? 'block' : 'none';
		$input_label = 'Hashtag';
		$input_placeholder = 'travel, photography...';

		echo '<div class="socialfeeds-control-group" id="socialfeeds-source-input-wrap" style="display:'.esc_attr($display_input).';">
				<label class="socialfeeds-control-label" id="socialfeeds-source-label">'.esc_html($input_label).'</label>
				<input type="text" id="socialfeeds-source-input-field" name="source_input" value="'.esc_attr(urldecode($input_val)).'" class="socialfeeds-input-full" placeholder="'.esc_attr($input_placeholder).'" oninput="document.getElementById(\'socialfeeds-source-input-field-sidebar\') ? document.getElementById(\'socialfeeds-source-input-field-sidebar\').value = this.value : null;">
				<p class="description" id="socialfeeds-source-help"></p>
			</div>';

		echo '<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
				<a href="' . esc_url(admin_url('admin.php?page=socialfeeds&action=create#instagram')) . '" class="socialfeeds-btn-manage" style="width:auto; padding:10px 30px; border:none;">' . esc_html__('Back', 'socialfeeds-pro') . '</a>
				<button type="button" class="socialfeeds-step-next-btn socialfeeds-btn-sync" data-next-step="2" id="socialfeeds-ig-next-btn" style="padding:10px 40px;">' . esc_html__('Next Step', 'socialfeeds-pro') . ' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></button>
			</div>
		</div>';
	}

	static function render_instagram_customize_tab($settings, $preview_url, $edit_id, $opts = [], $feed_type = 'username', $selected_account_index = null, $socialfeeds_input_value = '') {

		// Top Header Section (Title + Shortcode Controls)
		$platform_key = 'instagram';
		$shortcode_tag = $platform_key . '-feed';
		$feed_name = '';
		$display_id = '';
		$feeds = isset( $opts['instagram_feeds'] ) ? $opts['instagram_feeds'] : [];
		$found = false;
		if(!empty($edit_id)){
			foreach($feeds as $f){
				if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
					$feed_name = isset( $f['name'] ) ? $f['name'] : '';
					$display_id = $edit_id;
					$found = true;
					break;
				}
			}
		}

		if(empty($found)){
			$feed_type = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : 'username';
			$instagram_feeds = isset($opts['instagram_feeds']) ? $opts['instagram_feeds'] : [];
			
			$display_id = '';
			$same_type_count = 0;
			foreach($instagram_feeds as $f){
				if(isset($f['type'] ) && $f['type'] === $feed_type){
					$same_type_count++;
				}
			}

			$sequential_number = $same_type_count + 1;
			$feed_type_labels = [
				'channel' => 'Timeline',
				'manual' => 'Tagged',
			];

			$feed_type_label = isset($feed_type_labels[ $feed_type ]) ? $feed_type_labels[ $feed_type ] : ucfirst( $feed_type );
			$feed_name = 'Instagram Feed - ' . $feed_type_label . ' ' . $sequential_number;
		}

		echo '<div class="socialfeeds-customize-header" style="text-align:center;margin-bottom:30px;">
			<div class="socialfeeds-inline-name-wrapper" style="display:inline-flex;align-items:center;gap:10px;">
			<span class="socialfeeds-feed-name-text" style="font-size:15px;font-weight:600;">'.esc_html( $feed_name ) . '</span>
			<input type="text" class="socialfeeds-feed-name-input" value="' . esc_attr( $feed_name ) . '" style="display:none;font-size:15px;padding:4px 8px;" />';
			echo '<button type="button" class="socialfeeds-edit-name-btn" title="Edit" style="display:none;"><span class="dashicons dashicons-edit"></span></button>
			<button type="button" class="socialfeeds-save-name-btn" data-feed-id="' . esc_attr( $edit_id ) . '" data-platform="instagram" style="display:none;" title="Save"><span class="dashicons dashicons-yes"></span></button>';
		echo '</div></div>';
		
		echo '<div class="socialfeeds-customize-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
			<div class="socialfeeds-customize-header-left">
				<h2 class="socialfeeds-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">' . esc_html__('Customize Display', 'socialfeeds-pro') . '</h2>
				<p class="socialfeeds-desc" style="margin: 8px 0 0; color: #64748b; font-size: 13px;">' . esc_html__('Configure how your Instagram feed appears on your site.', 'socialfeeds-pro') . '</p>
			</div>
			<div class="socialfeeds-customize-header-right" style="display: flex; align-items: center; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 5px 10px; border-radius: 12px;">
				<div style="margin-right: 15px;">
					<code id="socialfeeds-top-shortcode" style="background: transparent; border: none; font-size: 14px; color: #475569; padding: 0; font-family: monospace; letter-spacing: 0.5px;">'.esc_html('[socialfeeds id="'.$display_id.'" platform="instagram"]').'</code>
				</div>
				<button type="button" class="socialfeeds-copy-shortcode" data-shortcode="' . esc_attr('[socialfeeds id="'.$display_id.'" platform="instagram"]') . '" style="display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 16px; cursor: pointer; color: #374151; font-weight: 500; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
					<span class="dashicons dashicons-admin-page" style="font-size: 18px; width: 18px; height: 18px; color: #64748b;"></span>
					' . esc_html__('Copy', 'socialfeeds-pro') . '
				</button>
				<button type="button" class="socialfeeds-fullscreen-btn" title="' . esc_attr__('Fullscreen', 'socialfeeds-pro') . '" style="display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px; cursor: pointer; width: 38px; height: 38px; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
					<span class="socialfeed-fullscreen dashicons dashicons-fullscreen-alt" style="font-size: 20px; width: 20px; height: 20px; color: #64748b;"></span>
				</button>
			</div>
		</div>';
		
		echo '<div class="socialfeeds-customize-columns">
			<div class="socialfeeds-customize-preview-column">
				<div class="socialfeeds-button-group" style="margin-bottom: 15px; margin-top: 0; display: flex; justify-content: flex-end; gap: 10px;">
					<a href="'.esc_url(admin_url('admin.php?page=socialfeeds#feeds')).'" class="button button-primary">'.esc_html__('All Feeds', 'socialfeeds-pro').'</a>
					<button type="submit" class="button button-primary" id="socialfeeds-save-btn">'.($edit_id ? esc_html__('Save', 'socialfeeds-pro') : esc_html__('Save', 'socialfeeds-pro')).'</button>
				</div>

				<div class="socialfeeds-customize-preview">
					<div class="socialfeeds-preview-header-bar">
						<span class="socialfeeds-preview-label">'.esc_html__('LIVE PREVIEW', 'socialfeeds-pro').'</span>
						<div class="socialfeeds-preview-device-toggles">
							<button type="button" class="socialfeeds-preview-device-btn active" data-width="100%" title="Desktop"><span class="dashicons dashicons-desktop"></span></button>
							<button type="button" class="socialfeeds-preview-device-btn" data-width="768" title="Tablet"><span class="dashicons dashicons-tablet"></span></button>
							<button type="button" class="socialfeeds-preview-device-btn" data-width="375" title="Mobile"><span class="dashicons dashicons-smartphone"></span></button>
						</div>
					</div>
					
					<div class="socialfeeds-preview-box-wrapper">
						<div class="socialfeeds-wizard-loader-overlay">
							<div class="socialfeeds-loader"></div>
						</div>
						<div class="socialfeeds-instagram-feed">
							<div id="socialfeeds-instagram-preview-header"></div>
							<div class="socialfeeds-preview-box" id="socialfeeds-instagram-preview-grid">
								<div class="socialfeeds-fetch-status-container" style="text-align:center; padding:10px; font-weight:500; color:#666;">
									<span id="socialfeeds-fetch-status"></span>
								</div>
							</div>
						</div>';
					
					echo '			
					</div>
					<div class="socialfeeds-load-more-wrap">
					</div>
				</div>
			</div>

			<div class="socialfeeds-customize-settings-sidebar">
				<div class="socialfeeds-sidebar-tabs">
					<button type="button" class="socialfeeds-sidebar-tab-btn active" data-target="socialfeeds-insta-tab-general">'.esc_html__('General', 'socialfeeds-pro').'</button>
					<button type="button" class="socialfeeds-sidebar-tab-btn" data-target="socialfeeds-insta-tab-style">'.esc_html__('Style', 'socialfeeds-pro').'</button>
				</div>

				<div class="socialfeeds-sidebar-content">';
					self::render_instagram_sidebar_general($settings, $feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
					self::render_instagram_sidebar_style($settings);
		echo '	</div> <!-- End Sidebar Content -->
			</div> <!-- End Sidebar -->

		</div>';
	}

	static function render_instagram_sidebar_general($settings, $feed_type = 'username', $edit_id = '', $opts = [], $selected_account_index = null, $socialfeeds_input_value = ''){

		echo '<div id="socialfeeds-insta-tab-general" class="socialfeeds-sidebar-tab-pane active">
			<div class="socialfeeds-sidebar-header">
				<h3><span class="dashicons dashicons-admin-settings"></span>'.esc_html__('General', 'socialfeeds-pro').'</h3>
			</div>

			<!-- Sources Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
							<span class="dashicons dashicons-database"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Sources', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group" style="margin-bottom:15px;">
						<label class="socialfeeds-control-label">'.esc_html__('Feed Type', 'socialfeeds-pro').'</label>
						<select id="socialfeeds-instagram-source-type-sidebar" name="instagram_source_type_sidebar" class="socialfeeds-select-full">
							<option value="username" '.selected($feed_type, 'username', false).'>User Timeline</option>
							<option value="hashtag" '.selected($feed_type, 'hashtag', false).'>Hashtag Posts</option>
							<option value="manual" '.selected($feed_type, 'manual', false).'>Tagged Posts</option>
						</select>
					</div>

					<!-- Accounts block -->
					<div class="socialfeeds-control-group" style="margin-bottom:20px;">
						<label class="socialfeeds-control-label">'.esc_html__('Select Account', 'socialfeeds-pro').'</label>
						<div class="socialfeeds-accounts-list" id="socialfeeds-accounts-list-sidebar" style="display: grid; gap: 8px; margin-bottom: 10px;">';
				
						$connected_accounts = isset($opts['instagram_connected_accounts']) ? $opts['instagram_connected_accounts'] : [];
						if(empty($connected_accounts)){
							echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
									<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No accounts connected.', 'socialfeeds-pro') . '</p>
								</div>';
						} else {
							foreach($connected_accounts as $idx => $account){
								$checked = ($edit_id && $selected_account_index !== null) ? ($idx === $selected_account_index ? ' checked' : '') : ($idx === 0 ? ' checked' : '');
								echo '<label class="socialfeeds-account-item ' . ($checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:10px; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; background:#fff; position:relative;">
									<input type="radio" name="instagram_selected_account" value="' . esc_attr($idx) . '" style="margin-right:10px;" ' . esc_attr($checked) . ' onchange="jQuery(\'input[name=\\\'instagram_selected_account\\\'][value=\\\'\' + this.value + \'\\\']\').prop(\'checked\', true).closest(\'.socialfeeds-account-item\').addClass(\'selected\').siblings().removeClass(\'selected\');">
									<div style="flex:1; min-width:0;">
										<strong style="display:block; font-size:13px; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . esc_html($account['username']) . '</strong>';
										$acct_token_type = isset($account['token_type']) ? $account['token_type'] : 'basic';
										if($acct_token_type === 'advanced'){
											echo '<span style="display:inline-block; background:#dbeafe; color:#2563eb; font-size:9px; font-weight:600; padding:1px 4px; border-radius:3px; vertical-align:middle;">' . esc_html__('Advanced', 'socialfeeds-pro') . '</span>';
										} else {
											echo '<span style="display:inline-block; background:#f0fdf4; color:#16a34a; font-size:9px; font-weight:600; padding:1px 4px; border-radius:3px; vertical-align:middle;">' . esc_html__('Basic', 'socialfeeds-pro') . '</span>';
										}
								echo '</div>
									<button type="button" class="socialfeeds-delete-account-btn" data-account-id="' . esc_attr($account['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:2px; margin-left:5px; z-index:10; display: flex; align-items: center;" title="' . esc_attr__('Delete Account', 'socialfeeds-pro') . '">
										<span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
									</button>
								</label>';
							}
						}
				echo '</div>
					</div>';
					
				$display_input = ($feed_type === 'hashtag') ? 'block' : 'none';
				$input_label = 'Hashtag';
				$input_placeholder = 'travel, photography...';

				echo '<div class="socialfeeds-control-group" id="socialfeeds-source-input-wrap-sidebar" style="display:'.esc_attr($display_input).';">
						<label class="socialfeeds-control-label" id="socialfeeds-source-label-sidebar">'.esc_html($input_label).'</label>
						<input type="text" name="source_input" id="socialfeeds-source-input-field-sidebar" value="'.esc_attr(urldecode($socialfeeds_input_value)).'" class="socialfeeds-input-full" placeholder="'.esc_attr($input_placeholder).'" oninput="document.getElementById(\'socialfeeds-source-input-field\') ? document.getElementById(\'socialfeeds-source-input-field\').value = this.value : null;">
						<p class="description" id="socialfeeds-source-help-sidebar"></p>
					</div>

					<div class="socialfeeds-control-group" style="margin-top:25px; border-top:1px solid #e2e8f0; padding-top:15px;">
						<button type="button" id="socialfeeds-fetch-preview-btn-sidebar" class="button button-primary" style="width:100%;">'.esc_html__('Fetch', 'socialfeeds-pro').'
						</button>
						<p class="description" style="margin-top:8px; font-size:11px; text-align:center; color:#64748b;">'.esc_html__('Click to fetch new posts with these source settings', 'socialfeeds-pro').'</p>
					</div>
				</div>
			</div>
			</div>

			<!-- Layout Accordion -->
			<div class="socialfeeds-accordion-wrapper">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
							<span class="dashicons dashicons-layout"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Layout', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('LAYOUT TYPE', 'socialfeeds-pro').'</label>
						<div class="socialfeeds-layout-selector">
							<label class="socialfeeds-layout-option">
								<input type="radio" name="instagram_layout" id="socialfeeds-instagram-layout-grid" value="grid" '. checked($settings['instagram_layout'], 'grid', false) .'>
								<div class="layout-box">
									<span class="dashicons dashicons-grid-view"></span>
									<span>'.esc_html__('Grid', 'socialfeeds-pro').'</span>
								</div>
							</label>
							<label class="socialfeeds-layout-option">
								<input type="radio" name="instagram_layout" id="socialfeeds-instagram-layout-carousel" value="carousel" '. checked($settings['instagram_layout'], 'carousel', false) .'>
								<div class="layout-box">
									<span class="dashicons dashicons-images-alt2"></span>
									<span>'.esc_html__('Carousel', 'socialfeeds-pro').'</span>
								</div>
							</label>
							<label class="socialfeeds-layout-option">
								<input type="radio" name="instagram_layout" id="socialfeeds-instagram-layout-masonry" value="masonry" '. checked($settings['instagram_layout'], 'masonry', false) .'>
								<div class="layout-box">
									<span class="dashicons dashicons-layout"></span>
									<span>'.esc_html__('Masonry', 'socialfeeds-pro').'</span>
								</div>
							</label>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('COLUMNS - DESKTOP', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">'.esc_html($settings['instagram_columns_desktop']).' Columns</span>
						</div>
						<div class="socialfeeds-range-slider">
							<span class="range-min">1</span>
							<input type="range" name="instagram_columns_desktop" id="socialfeeds-instagram-columns-desktop" min="1" max="6" step="1" value="'.esc_attr($settings['instagram_columns_desktop']).'">
							<span class="range-max">6</span>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('COLUMNS - MOBILE', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">'.esc_html($settings['instagram_columns_mobile']).' Columns</span>
						</div>
						<div class="socialfeeds-range-slider">
							<span class="range-min">1</span>
							<input type="range" name="instagram_columns_mobile" id="socialfeeds-instagram-columns-mobile" min="1" max="3" step="1" value="'.esc_attr($settings['instagram_columns_mobile']).'">
							<span class="range-max">3</span>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('SPACING', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">'.esc_html($settings['instagram_padding']).'px</span>
						</div>
						<div class="socialfeeds-range-slider">
							<span class="range-min">0</span>
							<input type="range" name="instagram_padding" id="socialfeeds-instagram-padding" min="0" max="100" step="1" value="'.esc_attr($settings['instagram_padding']).'">
							<span class="range-max">100</span>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">
							'.esc_html__('Aspect Ratio', 'socialfeeds-pro').'
						</label>

						<select name="instagram_aspect_ratio" id="socialfeeds-instagram-aspect-ratio" class="socialfeeds-input-full">
							<option value="square" '.selected($settings['instagram_aspect_ratio'], 'square', false).'>
								Square (1:1)
							</option>
							<option value="instagram" '.selected($settings['instagram_aspect_ratio'], 'instagram', false).'>
								Instagram Official (3.4)
							</option>
							<option value="portrait" '.selected($settings['instagram_aspect_ratio'], 'portrait', false).'>
								Portrait (4:5)
							</option>
						</select>
					</div>
				</div>
			</div>

			<!-- Content Limits Accordion -->
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #fff7ed; color: #f97316;">
							<span class="dashicons dashicons-filter"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Content Limits', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('NUMBER OF POSTS', 'socialfeeds-pro').'</label>
						</div>
						<input type="number" name="instagram_number_posts_desktop" id="socialfeeds-instagram-number-posts-desktop" value="'.esc_attr($settings['instagram_number_posts_desktop']).'" min="1" max="500" class="socialfeeds-input-full">
					</div>


				</div>
			</div>

				<!-- Header Accordion -->
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #fdf2f8; color: #db2777;">
								<span class="dashicons dashicons-align-center"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">'.esc_html__('Header', 'socialfeeds-pro').'</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Enable Header', 'socialfeeds-pro').'</span>
								<span class="socialfeeds-toggle-desc">'.esc_html__('Display feed header with profile info', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_header_enabled" id="socialfeeds-instagram-header-enabled" value="1" '. checked($settings['instagram_header_enabled'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-nested-options" style="margin-top:15px;">
							<div class="socialfeeds-control-group">
								<label class="socialfeeds-control-label">'.esc_html__('HEADER Position', 'socialfeeds-pro').'</label>
								<select name="instagram_header_style" id="socialfeeds-instagram-header-style" class="socialfeeds-select-full">
									<option value="left" '. selected($settings['instagram_header_style'], 'left', false) .'>Left</option>
									<option value="middle" '. selected($settings['instagram_header_style'], 'middle', false) .'>Middle</option>
									<option value="right" '. selected($settings['instagram_header_style'], 'right', false) .'>Right</option>
								</select>
							</div>
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('Header Size', 'socialfeeds-pro').'</label>
							<select name="instagram_header_size" id="socialfeeds-instagram-header-size" class="socialfeeds-input-full">
								<option value="small"  '. selected($settings['instagram_header_size'], 'small', false).'>Small</option>
								<option value="medium" '. selected($settings['instagram_header_size'], 'medium', false).'>Medium</option>
								<option value="large"  '. selected($settings['instagram_header_size'], 'large', false).'>Large</option>
							</select>
						</div>
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('Custom Avatar', 'socialfeeds-pro').'</label>
								<div class="socialfeeds-image-uploader">
									<input type="hidden" name="instagram_custom_avatar" id="socialfeeds-custom-avatar-url" value="'.esc_attr(isset($settings['instagram_custom_avatar']) ? $settings['instagram_custom_avatar'] : '').'">
									<div class="socialfeeds-avatar-preview-wrap" style="margin-bottom: 10px; '. (!empty($settings['instagram_custom_avatar']) ? 'display:block;' : 'display:none;') .'">
										<img src="'.esc_url(isset($settings['instagram_custom_avatar']) ? $settings['instagram_custom_avatar'] : '').'" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:1px solid #ddd;">
									</div>
									<div style="display:flex; gap:10px;">
										<button type="button" class="button socialfeeds-upload-avatar-btn">'.esc_html__('Upload Image', 'socialfeeds-pro').'</button>
										<button type="button" class="button socialfeeds-remove-avatar-btn" style="'. (!empty($settings['instagram_custom_avatar']) ? '' : 'display:none;') .'">'.esc_html__('Remove', 'socialfeeds-pro').'</button>
									</div>
								</div>
							</div>
							<div class="socialfeeds-toggle-row">
								<div class="socialfeeds-toggle-info">
									<span class="socialfeeds-toggle-title">'.esc_html__('Show Bio', 'socialfeeds-pro').'</span>
								</div>
								<label class="socialfeeds-switch">
									<input type="checkbox" name="instagram_show_bio_text" id="socialfeeds-instagram-show-bio-text" value="1" '. checked($settings['instagram_show_bio_text'], 1, false) .'>
									<span class="socialfeeds-slider"></span>
								</label>
							</div>
							<div class="socialfeeds-toggle-row">
								<div class="socialfeeds-toggle-info">
									<span class="socialfeeds-toggle-title">'.esc_html__('Show Followers', 'socialfeeds-pro').'</span>
								</div>
								<label class="socialfeeds-switch">
									<input type="checkbox" name="instagram_show_followers" id="socialfeeds-instagram-show-followers" value="1" '. checked($settings['instagram_show_followers'], 1, false) .'>
									<span class="socialfeeds-slider"></span>
								</label>
							</div>
							<div class="socialfeeds-toggle-row">
								<div class="socialfeeds-toggle-info">
									<span class="socialfeeds-toggle-title">'.esc_html__('Show Media Count', 'socialfeeds-pro').'</span>
								</div>
								<label class="socialfeeds-switch">
									<input type="checkbox" name="instagram_media_count" id="socialfeeds-instagram-media-count" value="1" '. checked($settings['instagram_media_count'], 1, false) .'>
									<span class="socialfeeds-slider"></span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- Post Experience Accordion -->
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
								<span class="dashicons dashicons-format-image"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">'.esc_html__('Post Experience', 'socialfeeds-pro').'</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('SORT BY', 'socialfeeds-pro').'</label>
							<select name="instagram_sort_by" id="socialfeeds-instagram-sort-by" class="socialfeeds-select-full">
								<option value="newest" '. selected($settings['instagram_sort_by'], 'newest', false) .'>Newest First</option>
								<option value="likes" '. selected($settings['instagram_sort_by'], 'likes', false) .'>Most Liked</option>
								<option value="random" '. selected($settings['instagram_sort_by'], 'random', false) .'>Random</option>
							</select>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Captions', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_caption_enabled" id="socialfeeds-instagram-caption-enabled" value="1" '. checked($settings['instagram_caption_enabled'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Likes', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_likes" id="socialfeeds-instagram-likes" value="1" '. checked($settings['instagram_likes'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Comments', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_comments" id="socialfeeds-instagram-comments" value="1" '. checked($settings['instagram_comments'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Post', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_show_feed_posts" id="socialfeeds-instagram-show-feed-posts" value="1" '. checked($settings['instagram_show_feed_posts'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Reels', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_show_reels" id="socialfeeds-instagram-show-reels" value="1" '. checked($settings['instagram_show_reels'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Show Play Icon', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_show_play_icon" id="socialfeeds-instagram-show-play-icon" value="1" '. checked(!isset($settings['instagram_show_play_icon']) || !empty($settings['instagram_show_play_icon']), 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">'.esc_html__('PLAY MODE', 'socialfeeds-pro').'</label>
							<select name="instagram_play_mode" id="socialfeeds-instagram-play-mode" class="socialfeeds-select-full">
								<option value="newtab" '. selected(isset($settings['instagram_play_mode']) ? $settings['instagram_play_mode'] : 'newtab', 'newtab', false) .'>Open in New Tab</option>
								<option value="lightbox" '. selected(isset($settings['instagram_play_mode']) ? $settings['instagram_play_mode'] : '', 'lightbox', false) .'>Open in Lightbox</option>
								<option value="inline" '. selected(isset($settings['instagram_play_mode']) ? $settings['instagram_play_mode'] : '', 'inline', false) .'>Play Inline</option>
							</select>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">'.esc_html__('HOVER STATE', 'socialfeeds-pro').'</label>
							<select name="instagram_hover_state" id="socialfeeds-instagram-hover-state" class="socialfeeds-select-full">
								<option value="overlay" '. selected($settings['instagram_hover_state'], 'overlay', false) .'>Overlay</option>
								<option value="scale" '. selected($settings['instagram_hover_state'], 'scale', false) .'>Scale</option>
								<option value="shadow" '. selected($settings['instagram_hover_state'], 'shadow', false) .'>Shadow</option>
								<option value="none" '. selected($settings['instagram_hover_state'], 'none', false) .'>None</option>
							</select>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">'.esc_html__('CONTENT ALIGNMENT', 'socialfeeds-pro').'</label>
							<select name="instagram_alignment" id="socialfeeds-instagram-alignment" class="socialfeeds-select-full">
								<option value="left" '. selected($settings['instagram_alignment'], 'left', false) .'>Left</option>
								<option value="center" '. selected($settings['instagram_alignment'], 'center', false) .'>Center</option>
								<option value="right" '. selected($settings['instagram_alignment'], 'right', false) .'>Right</option>
							</select>
						</div>
					</div>
				</div>

				<!-- Follow Button -->
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
								<span class="dashicons dashicons-rss"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">'.esc_html__('Follow Button', 'socialfeeds-pro').'</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Follow Button', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_follow_button_enabled" id="socialfeeds-instagram-follow-button-enabled" value="1" '. checked($settings['instagram_follow_button_enabled'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-nested-options" style="margin-top:15px;">
							<div class="socialfeeds-control-group">
								<label class="socialfeeds-control-label">'.esc_html__('BUTTON TEXT', 'socialfeeds-pro').'</label>
								<input type="text" name="instagram_follow_button_text" id="socialfeeds-instagram-follow-button-text" value="'.esc_attr($settings['instagram_follow_button_text']).'" class="socialfeeds-input-full">
							</div>
							<div class="socialfeeds-control-group">
								<label class="socialfeeds-control-label">'.esc_html__('BUTTON COLORS', 'socialfeeds-pro').'</label>
								<div style="display: flex; gap: 10px; margin-top: 5px;">
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('BG', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_follow_button_bg_color" id="socialfeeds-instagram-follow-button-bg-color" value="'.esc_attr($settings['instagram_follow_button_bg_color']).'" class="socialfeeds-color-input">
									</div>
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('TEXT', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_follow_button_text_color" id="socialfeeds-instagram-follow-button-text-color" value="'.esc_attr($settings['instagram_follow_button_text_color']).'" class="socialfeeds-color-input">
									</div>
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('HOVER', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_follow_button_hover_color" id="socialfeeds-instagram-follow-button-hover-color" value="'.esc_attr($settings['instagram_follow_button_hover_color']).'" class="socialfeeds-color-input">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Load More Accordion -->
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #faf5ff; color: #a855f7;">
								<span class="dashicons dashicons-plus-alt"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">'.esc_html__('Load More', 'socialfeeds-pro').'</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">'.esc_html__('Load More Button', 'socialfeeds-pro').'</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="instagram_load_more_enabled" id="socialfeeds-instagram-load-more-enabled" value="1" '. checked($settings['instagram_load_more_enabled'], 1, false) .'>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'. esc_html__('POSTS PER LOAD', 'socialfeeds-pro').'</label>
							<input type="number" name="instagram_load_more_count" id="socialfeeds-instagram-load-more-count" value="'.(!empty($settings['instagram_load_more_count']) ? esc_attr($settings['instagram_load_more_count']) : 12).'" min="1" class="socialfeeds-input-full">
							<small style="opacity:0.7;">How many posts load each time</small>
						</div>
						<div class="socialfeeds-nested-options" style="margin-top:15px;">
							<div class="socialfeeds-control-group">
								<label class="socialfeeds-control-label">'.esc_html__('BUTTON TEXT', 'socialfeeds-pro').'</label>
								<input type="text" name="instagram_load_more_text" id="socialfeeds-instagram-load-more-text" value="'.esc_attr($settings['instagram_load_more_text']).'" class="socialfeeds-input-full">
							</div>
							<div class="socialfeeds-control-group">
								<label class="socialfeeds-control-label">'.esc_html__('BUTTON COLORS', 'socialfeeds-pro').'</label>
								<div style="display: flex; gap: 10px; margin-top: 5px;">
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('BG', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_load_more_bg_color" id="socialfeeds-instagram-load-more-bg-color" value="'.esc_attr($settings['instagram_load_more_bg_color']).'" class="socialfeeds-color-input">
									</div>
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('TEXT', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_load_more_text_color" id="socialfeeds-instagram-load-more-text-color" value="'.esc_attr($settings['instagram_load_more_text_color']).'" class="socialfeeds-color-input">
									</div>
									<div style="flex: 1;">
										<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('HOVER', 'socialfeeds-pro').'</label>
										<input type="color" name="instagram_load_more_hover_color" id="socialfeeds-instagram-load-more-hover-color" value="'.esc_attr($settings['instagram_load_more_hover_color']).'" class="socialfeeds-color-input">
									</div>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div></div>';
	}

	static function render_instagram_sidebar_style($settings) {
	
		echo '<div id="socialfeeds-insta-tab-style" class="socialfeeds-sidebar-tab-pane">
			<div class="socialfeeds-sidebar-header">
				<h3>'. esc_html__('Style Settings', 'socialfeeds-pro').'</h3>
			</div>
			
			<!-- Color Scheme Accordion -->
			<div class="socialfeeds-accordion-wrapper">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #f0fdfa; color: #0d9488;">
							<span class="dashicons dashicons-art"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'. esc_html__('Color Scheme', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'. esc_html__('COLOR SCHEME', 'socialfeeds-pro') .'</label>
						<select name="instagram_color_scheme" id="socialfeeds-instagram-color-scheme" class="socialfeeds-select-full">
							<option value="light" '. selected($settings['instagram_color_scheme'], 'light', false) .'>'.esc_html__('Light', 'socialfeeds-pro').'</option>
							<option value="dark" '. selected($settings['instagram_color_scheme'], 'dark', false) .'>'.esc_html__('Dark', 'socialfeeds-pro').'</option>
							<option value="custom" '. selected($settings['instagram_color_scheme'], 'custom', false) .'>'.esc_html__('Custom', 'socialfeeds-pro').'</option>
						</select>
					</div>
					<div class="socialfeeds-control-group" id="socialfeeds-custom-color-group" style="'.('custom' !== $settings['instagram_color_scheme'] ? 'display:none;' : '').'">
						<label class="socialfeeds-control-label">'.esc_html__('CUSTOM BG COLOR', 'socialfeeds-pro').'</label>
						<input type="color" name="instagram_custom_color" id="socialfeeds-instagram-custom-color" value="'.esc_attr($settings['instagram_custom_color']).'" class="socialfeeds-color-input">
					</div>
				</div>
			</div>


			</div> <!-- End Accordion Wrapper -->
		</div>';
	}

	static function render_instagram_embed_modal() {
		echo '<div id="socialfeeds-embed-modal" class="socialfeeds-embed-modal">
			<div class="socialfeeds-embed-container" style="max-width: 800px; width: 90%; background: #fff; border-radius: 16px; position: relative;">
				<button id="socialfeeds-embed-close" class="socialfeeds-embed-close" style="position: absolute; right: 20px; top: 20px; border: none; background: none; font-size: 24px; cursor: pointer;">&times;</button>
				<div class="socialfeeds-embed-content" style="padding: 40px;">
					<h2 style="margin-top:0;">' . esc_html__('Embed Feed', 'socialfeeds-pro') . '</h2>
					<p style="color:#64748b; margin-bottom:25px;">' . esc_html__('Copy the shortcode and paste it into your page or post editor.', 'socialfeeds-pro') . '</p>
					
					<div class="socialfeeds-embed-shortcode-wrap" style="display:flex; gap:10px; margin-bottom:30px; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0;">
						<input type="text" id="socialfeeds-embed-shortcode" readonly value="" style="flex:1; background:transparent; border:none; font-family:monospace; font-size:16px; color:#1e293b;" />
						<button id="socialfeeds-embed-copy" class="socialfeeds-btn-sync" style="padding:8px 25px;">' . esc_html__('Copy', 'socialfeeds-pro') . '</button>
					</div>

					<div class="socialfeeds-preview-header-bar" style="border-radius:12px 12px 0 0;">
						<span class="socialfeeds-preview-label">'.esc_html__('EMBED PREVIEW', 'socialfeeds-pro').'</span>
						<div class="socialfeeds-preview-device-toggles">
							<button type="button" class="socialfeeds-preview-device active" data-width="100%"><span class="dashicons dashicons-desktop"></span></button>
							<button type="button" class="socialfeeds-preview-device" data-width="768"><span class="dashicons dashicons-tablet"></span></button>
						</div>
					</div>
					<div id="socialfeeds-embed-preview-wrap" style="background:#f1f5f9; padding:20px; border-radius:0 0 12px 12px; max-height:400px; overflow-y:auto;">
						<div id="socialfeeds-preview-grid-clone"></div>
					</div>
				</div>
			</div>
		</div>';
	}
	
	static function youtube_feed_type(){

		echo'<div class="socialfeeds-type-card" data-type="playlist" data-pro="1">
				<div class="socialfeeds-p-card-icon" style="background:#fef3c7; color:#d97706; margin-bottom:15px;">
					<span class="dashicons dashicons-playlist-video"></span>
				</div>
				<div class="socialfeeds-card-content">
					<h3>'.esc_html__('Playlist', 'socialfeeds-pro') . '</h3>
					<p>'.esc_html__('Display videos from any YouTube playlist.', 'socialfeeds-pro').'</p>
				</div>
			</div>

			<!-- Search -->
			<div class="socialfeeds-type-card" data-type="search" data-pro="1">
				<div class="socialfeeds-p-card-icon" style="background:#e0f2fe; color:#0284c7; margin-bottom:15px;">
					<span class="dashicons dashicons-search"></span>
				</div>
				<div class="socialfeeds-card-content">
					<h3>'.esc_html__('Search', 'socialfeeds-pro').'</h3>
					<p>'.esc_html__('Videos which match specific advanced search criteria.', 'socialfeeds-pro').'</p>
				</div>
			</div>

			<!-- Live Streams -->
			<div class="socialfeeds-type-card" data-type="live-streams" data-pro="1">
				<div class="socialfeeds-p-card-icon" style="background:#f0fdf4; color:#16a34a; margin-bottom:15px;">
					<span class="dashicons dashicons-media-video"></span>
				</div>
				<div class="socialfeeds-card-content">
					<h3>'.esc_html__('Live Streams', 'socialfeeds-pro').'</h3>
					<p>'.esc_html__('Upcoming and currently playing live stream videos.', 'socialfeeds-pro').'</p>
				</div>
			</div>

			<!-- Single Videos -->
			<div class="socialfeeds-type-card" data-type="single-videos">
				<div class="socialfeeds-p-card-icon" style="background:#fae8ff; color:#a21caf; margin-bottom:15px;">
					<span class="dashicons dashicons-controls-play"></span>
				</div>
				<div class="socialfeeds-card-content">
					<h3>'.esc_html__('Single Videos', 'socialfeeds-pro') . '</h3>
					<p>'.esc_html__('Display a curated list of single videos.', 'socialfeeds-pro').'</p>
				</div>
			</div>';
	}

	static function youtube_video_elements($settings){

		echo'<div class="socialfeeds-toggle-row">
			<div class="socialfeeds-toggle-info">
				<div class="socialfeeds-toggle-title">'.esc_html__('Show Duration', 'socialfeeds-pro').'</div>
				<div class="socialfeeds-toggle-desc">'.esc_html__('Video length', 'socialfeeds-pro').'</div>
			</div>
			<div class="socialfeeds-toggle-input">
				<label class="socialfeeds-switch">
					<input type="checkbox" name="youtube_show_duration" id="socialfeeds-youtube-show-duration" value="1" ' . checked(isset($settings['youtube_show_duration']) ? $settings['youtube_show_duration'] : 0, 1, false) . '>
					<span class="socialfeeds-slider"></span>
				</label>
			</div>
		</div>

		<div class="socialfeeds-toggle-row">
			<div class="socialfeeds-toggle-info">
				<div class="socialfeeds-toggle-title">'.esc_html__('Show Date', 'socialfeeds-pro').'</div>
				<div class="socialfeeds-toggle-desc">'.esc_html__('Publishing date', 'socialfeeds-pro').'</div>
			</div>
			<div class="socialfeeds-toggle-input">
				<label class="socialfeeds-switch">
					<input type="checkbox" name="youtube_show_date" id="socialfeeds-youtube-show-date" value="1" ' . checked($settings['youtube_show_date'], 1, false) . '>
					<span class="socialfeeds-slider"></span>
				</label>
			</div>
		</div>

		<div class="socialfeeds-toggle-row">
			<div class="socialfeeds-toggle-info">
				<div class="socialfeeds-toggle-title">'.esc_html__('Show Views', 'socialfeeds-pro').'</div>
				<div class="socialfeeds-toggle-desc">'.esc_html__('Total view count', 'socialfeeds-pro').'</div>
			</div>
			<div class="socialfeeds-toggle-input">
				<label class="socialfeeds-switch">
					<input type="checkbox" name="youtube_show_views" id="socialfeeds-youtube-show-views" value="1" ' . checked(isset($settings['youtube_show_views']) ? $settings['youtube_show_views'] : 0, 1, false) . '>
					<span class="socialfeeds-slider"></span>
				</label>
			</div>
		</div>

		<div class="socialfeeds-toggle-row">
			<div class="socialfeeds-toggle-info">
				<div class="socialfeeds-toggle-title">'.esc_html__('Show Likes', 'socialfeeds-pro').'</div>
				<div class="socialfeeds-toggle-desc">'.esc_html__('Engagement stats', 'socialfeeds-pro').'</div>
			</div>
			<div class="socialfeeds-toggle-input">
				<label class="socialfeeds-switch">
					<input type="checkbox" name="youtube_show_likes" id="socialfeeds-youtube-show-likes" value="1" ' . checked(isset($settings['youtube_show_likes']) ? $settings['youtube_show_likes'] : 0, 1, false) . '>
					<span class="socialfeeds-slider"></span>
				</label>
			</div>
		</div>

		<div class="socialfeeds-toggle-row">
			<div class="socialfeeds-toggle-info">
				<div class="socialfeeds-toggle-title">'.esc_html__('Show Comments', 'socialfeeds-pro').'</div>
				<div class="socialfeeds-toggle-desc">'.esc_html__('Comment count', 'socialfeeds-pro').'</div>
			</div>
			<div class="socialfeeds-toggle-input">
				<label class="socialfeeds-switch">
					<input type="checkbox" name="youtube_show_comments" id="socialfeeds-youtube-show-comments" value="1" ' . checked(isset($settings['youtube_show_comments']) ? $settings['youtube_show_comments'] : 0, 1, false) . '>
					<span class="socialfeeds-slider"></span>
				</label>
			</div>
		</div>
		
		<div class="socialfeeds-control-group" style="margin-top:15px;">
			<label class="socialfeeds-control-label">'.esc_html__('ON CLICK ACTION', 'socialfeeds-pro').'</label>
			<select name="youtube_click_action" id="socialfeeds-youtube-click-action" class="socialfeeds-select-full">
				<option value="newtab" ' . selected(isset($settings['youtube_click_action']) ? $settings['youtube_click_action'] : 'newtab', 'newtab', false) . '>' . esc_html__('Open in New Tab', 'socialfeeds-pro') . '</option>';

				echo '<option value="lightbox" '. selected(isset($settings['youtube_click_action']) ? $settings['youtube_click_action'] : '', 'lightbox', false).'>'.esc_html__('Open in Lightbox', 'socialfeeds-pro').'</option>
				<option value="inline" '. selected(isset($settings['youtube_click_action']) ? $settings['youtube_click_action'] : '', 'inline', false).'>'. esc_html__('Play Inline', 'socialfeeds-pro').'</option>
			</select>
		</div>';

		echo'<div class="socialfeeds-control-group" style="margin-top:15px;">
			<label class="socialfeeds-control-label">'.esc_html__('HOVER EFFECT', 'socialfeeds-pro') . '</label>
			<select name="youtube_hover_effect" id="socialfeeds-youtube-hover-effect" class="socialfeeds-select-full">
				<option value="overlay" '.selected($settings['youtube_hover_effect'], 'overlay', false) . '>' . esc_html__('Overlay', 'socialfeeds-pro') . '</option>
				<option value="scale" '.selected($settings['youtube_hover_effect'], 'scale', false) . '>' . esc_html__('Scale', 'socialfeeds-pro') . '</option>
				<option value="shadow" '.selected($settings['youtube_hover_effect'], 'shadow', false) . '>' . esc_html__('Shadow', 'socialfeeds-pro') . '</option>
				<option value="none" '.selected($settings['youtube_hover_effect'], 'none', false) . '>' . esc_html__('None', 'socialfeeds-pro') . '</option>
			</select>
		</div>';
	}
	
	static function instagram_dashbord_settings(){
		$insta_opts = get_option('socialfeeds_instagram_option', []);
		$connected_accounts = isset($insta_opts['instagram_connected_accounts']) ? $insta_opts['instagram_connected_accounts'] : [];
		$instagram_feeds = isset($insta_opts['instagram_feeds']) ? $insta_opts['instagram_feeds'] : [];

		echo'<div>
			<div class="socialfeeds-stat-label">'.esc_html__('Feeds', 'socialfeeds-pro').'</div>
			<div class="socialfeeds-stat-value">'.count($instagram_feeds).'</div>
		</div>
		<div>
			<div class="socialfeeds-stat-label">'.esc_html__('Accounts', 'socialfeeds-pro').'</div>
			<div class="socialfeeds-stat-value">'.count($connected_accounts).'</div>
		</div>';
	}

	static function youtube_layouts($settings){
		echo'<label class="socialfeeds-layout-option">
			<input type="radio" name="youtube_display_style" id="socialfeeds-youtube-display-style-list" value="list" '.checked($settings['youtube_display_style'], 'list', false).'>
			<div class="layout-box">
				<span class="dashicons dashicons-list-view"></span>
				<span>'.esc_html__('List', 'socialfeeds-pro').'</span>
			</div>
		</label>
		<label class="socialfeeds-layout-option">
			<input type="radio" name="youtube_display_style" id="socialfeeds-youtube-display-style-carousel" value="carousel" '.checked($settings['youtube_display_style'], 'carousel', false).'>
			<div class="layout-box">
				<span class="dashicons dashicons-images-alt2"></span>
				<span>'.esc_html__('Carousel', 'socialfeeds-pro').'</span>
			</div>
		</label>';
	}

	static function render_modals(){
		if(!is_admin()){
			return;
		}
		
		$screen = get_current_screen();
		if(empty($screen) || strpos($screen->id, 'socialfeeds') === false){
			return;
		}

		$insta_opts = get_option('socialfeeds_instagram_option', []);
		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$google_opts = get_option('socialfeeds_google_option', []);

		self::render_instagram_connection_modal($insta_opts);
		self::render_facebook_connection_modal($fb_opts);
		self::render_google_connection_modal($google_opts);
	}

	/**
	 * Render Instagram Connection Modal
	 */
	static function render_instagram_connection_modal($opts){
		// Mask existing token for security
		$existing_token = isset($opts['instagram_access_token']) ? $opts['instagram_access_token'] : '';
		$existing_token_type = isset($opts['instagram_token_type']) ? $opts['instagram_token_type'] : 'basic';

		echo '<div id="socialfeeds-ig-connection-modal" class="socialfeeds-modal-overlay">
			<div class="socialfeeds-modal-content socialfeeds-ig-modal-wide">
				<button type="button" class="socialfeeds-modal-close" data-modal="socialfeeds-ig-connection-modal">&times;</button>
				
				<h2>' . esc_html__('Instagram Settings', 'socialfeeds-pro') . '</h2>
				<p>' . esc_html__('Configure your Access Token and create feeds', 'socialfeeds-pro') . '</p>

				<form id="socialfeeds-ig-modal-token-form" method="post">

					<!-- Connection Type Selection -->
					<div class="socialfeeds-connection-type-section">
						<label class="socialfeeds-connection-type-label">' . esc_html__('Select Connection Type', 'socialfeeds-pro') . '</label>
						
						<div class="socialfeeds-connection-type-cards">
							<label class="socialfeeds-connection-card ' . ($existing_token_type !== 'advanced' ? 'selected' : '') . '" data-type="basic">
								<input type="radio" name="instagram_token_type" value="basic" ' . checked($existing_token_type, 'basic', false) . ' ' . ($existing_token_type !== 'advanced' ? 'checked' : '') . '>
								<div class="socialfeeds-connection-card-inner">
									<span class="socialfeeds-connection-radio"></span>
									<div class="socialfeeds-connection-card-text">
										<strong>' . esc_html__('Business Basic', 'socialfeeds-pro') . '</strong>
										<span>' . esc_html__('Connect via Access Token', 'socialfeeds-pro') . '</span>
									</div>
								</div>
							</label>
							<label class="socialfeeds-connection-card ' . ($existing_token_type === 'advanced' ? 'selected' : '') . '" data-type="advanced">
								<input type="radio" name="instagram_token_type" value="advanced" ' . checked($existing_token_type, 'advanced', false) . '>
								<div class="socialfeeds-connection-card-inner">
									<span class="socialfeeds-connection-radio"></span>
									<div class="socialfeeds-connection-card-text">
										<strong>' . esc_html__('Business Advanced', 'socialfeeds-pro') . '</strong>
										<span>' . esc_html__('Connects via Facebook', 'socialfeeds-pro') . '</span>
									</div>
								</div>
							</label>
						</div>
					</div>

					<!-- Feature bullets for Basic -->
					<div class="socialfeeds-connection-features" id="socialfeeds-features-basic"' . ($existing_token_type === 'advanced' ? ' style="display:none;"' : '') . '>
						<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Requires Instagram Creator or Business account', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display profile info, avatars, and posts', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Connect your Instagram account', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Does not require a Facebook page', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Hashtag feeds (from your account)', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item no"><span class="dashicons dashicons-dismiss"></span> ' . esc_html__('Does not display Tagged posts', 'socialfeeds-pro') . '</div>
					</div>

					<!-- Feature bullets for Advanced -->
					<div class="socialfeeds-connection-features" id="socialfeeds-features-advanced"' . ($existing_token_type !== 'advanced' ? ' style="display:none;"' : '') . '>
						<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Requires Facebook Page linked to Instagram Business account', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display profile info, avatars, and posts', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Connect your Instagram account', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Hashtag feeds (from your account)', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display Tagged posts', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Full Instagram API access', 'socialfeeds-pro') . '</div>
					</div>

					<div class="socialfeeds-modal-form-group">
						<label for="socialfeeds-modal-ig-token">' . esc_html__('Instagram Access Token', 'socialfeeds-pro') . '</label>
						<div class="socialfeeds-token-input-wrap">
							<span class="socialfeeds-token-icon dashicons dashicons-admin-network"></span>
							<input name="instagram_access_token" type="text" id="socialfeeds-modal-ig-token" placeholder="' . esc_attr__('Paste your token here...', 'socialfeeds-pro') . '" class="regular-text socialfeeds-api-input" data-has-token="' . ($existing_token ? 'true' : 'false') . '" />
						</div>
						<p class="description">' . esc_html__('Enter your long-lived Instagram Access Token. This token is saved and masked for security.', 'socialfeeds-pro') . ($existing_token ? ' ' . esc_html__('(Token is saved and masked for security)', 'socialfeeds-pro') : '') . '</p>
					</div>

					<!-- Instagram User ID (Advanced only) -->
					<div class="socialfeeds-modal-form-group" id="socialfeeds-modal-ig-user-id-group"' . ($existing_token_type !== 'advanced' ? ' style="display:none;"' : '') . '>
						<label for="socialfeeds-modal-ig-user-id">' . esc_html__('Instagram User ID', 'socialfeeds-pro') . '</label>
						<div class="socialfeeds-token-input-wrap">
							<span class="socialfeeds-token-icon dashicons dashicons-admin-users"></span>
							<input name="instagram_user_id" type="text" id="socialfeeds-modal-ig-user-id" value="" class="regular-text socialfeeds-api-input" placeholder="' . esc_attr__('e.g. 17841400123456789', 'socialfeeds-pro') . '" />
						</div>
						<p class="description">' . esc_html__('Your Instagram Business User ID. You can find this in your Meta Business Suite settings.', 'socialfeeds-pro') . '</p>
					</div>

					<!-- Instagram App Credentials (Advanced only) -->
					<div id="socialfeeds-modal-ig-app-group"' . ($existing_token_type !== 'advanced' ? ' style="display:none;"' : '') . '>
						<div class="socialfeeds-modal-form-group">
							<label for="socialfeeds-modal-ig-app-id">' . esc_html__('App ID', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-token-input-wrap">
								<span class="socialfeeds-token-icon dashicons dashicons-admin-generic"></span>
								<input name="instagram_app_id" type="text" id="socialfeeds-modal-ig-app-id" value="" class="regular-text socialfeeds-api-input" placeholder="' . esc_attr__('Enter App ID...', 'socialfeeds-pro') . '" />
							</div>
						</div>
						<div class="socialfeeds-modal-form-group">
							<label for="socialfeeds-modal-ig-app-secret">' . esc_html__('App Secret', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-token-input-wrap">
								<span class="socialfeeds-token-icon dashicons dashicons-lock"></span>
								<input name="instagram_app_secret" type="password" id="socialfeeds-modal-ig-app-secret" value="" class="regular-text socialfeeds-api-input" placeholder="' . esc_attr__('Enter App Secret...', 'socialfeeds-pro') . '" />
							</div>
							<p class="description">' . esc_html__('Providing App ID and Secret allows automatic token refresh and conversion to long-lived tokens.', 'socialfeeds-pro') . '</p>
						</div>
					</div>

					<div class="socialfeeds-modal-actions">
						<button type="submit" class="button socialfeeds-modal-btn-purple">' . esc_html__('Save Token', 'socialfeeds-pro') . '</button>
						<button type="button" id="socialfeeds-add-new-feed" class="button">' . esc_html__("+ Add New Feed", "socialfeeds-pro") . '</button>
					</div>
				</form>

				<!-- Connected Accounts Section -->
				<div class="socialfeeds-modal-connected-accounts" style="margin-top:30px; border-top:1px solid #f1f5f9; padding-top:20px;">
					<h3 style="margin-bottom:15px; font-size:16px;">' . esc_html__('Connected Accounts', 'socialfeeds-pro') . '</h3>
					<div class="socialfeeds-accounts-list" style="display: grid; gap: 8px;">';
						$connected = isset($opts['instagram_connected_accounts']) ? $opts['instagram_connected_accounts'] : [];
						if(empty($connected)){
							echo '<p style="color:#64748b; font-size:13px; font-style:italic;">' . esc_html__('No accounts connected yet.', 'socialfeeds-pro') . '</p>';
						} else {
							foreach($connected as $acct){
								echo '<div class="socialfeeds-account-item-static" style="display:flex; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc;">
									<div style="width:32px; height:32px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:12px; display:flex; align-items:center; justify-content:center;">';
										if (!empty($acct['profile_picture_url'])) {
											echo '<img src="' . esc_url($acct['profile_picture_url']) . '" style="width:100%; height:100%; object-fit:cover;">';
										} else {
											echo '<span style="font-weight:600; color:#64748b; font-size:12px;">' . esc_html(strtoupper(substr($acct['username'], 0, 1))) . '</span>';
										}
									echo '</div>
									<div style="flex:1;">
										<strong style="display:block; font-size:14px; color:#1e293b;">' . esc_html($acct['username']) . '</strong>
										<small style="color:#64748b; font-size:11px;">' . esc_html(isset($acct['account_type']) ? $acct['account_type'] : 'PERSONAL') . '</small>';
										$acct_token_type = isset($acct['token_type']) ? $acct['token_type'] : 'basic';
										if($acct_token_type === 'advanced'){
											echo ' <span style="display:inline-block; background:#dbeafe; color:#2563eb; font-size:9px; font-weight:600; padding:1px 4px; border-radius:3px; vertical-align:middle;">' . esc_html__('Advanced', 'socialfeeds-pro') . '</span>';
										} else {
											echo ' <span style="display:inline-block; background:#f0fdf4; color:#16a34a; font-size:9px; font-weight:600; padding:1px 4px; border-radius:3px; vertical-align:middle;">' . esc_html__('Basic', 'socialfeeds-pro') . '</span>';
										}
									echo '</div>
									<button type="button" class="socialfeeds-delete-account-btn" data-account-id="' . esc_attr($acct['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; display: flex; align-items: center;" title="' . esc_attr__('Delete Account', 'socialfeeds-pro') . '">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>';
							}
						}
					echo '</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Render Facebook Connection Modal
	 */
	static function render_facebook_connection_modal($opts){
		echo '<div id="socialfeeds-fb-connection-modal" class="socialfeeds-modal-overlay">
			<div class="socialfeeds-modal-content socialfeeds-fb-modal-wide">
				<button type="button" class="socialfeeds-modal-close" data-modal="socialfeeds-fb-connection-modal">&times;</button>
				
				<h2>' . esc_html__('Facebook Settings', 'socialfeeds-pro') . '</h2>
				<p>' . esc_html__('Configure your Facebook Page connection and create feeds', 'socialfeeds-pro') . '</p>

				<form id="socialfeeds-fb-modal-token-form" method="post">

					<input type="hidden" name="fb_token_type" value="advanced">

					<!-- Feature bullets for Advanced -->
					<div class="socialfeeds-connection-features" id="socialfeeds-fb-features-advanced">
						<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Full API access requires a Facebook App ID and Secret', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display all Page content types', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Official Facebook API integration', 'socialfeeds-pro') . '</div>
					</div>

					<div class="socialfeeds-modal-form-group">
						<label for="socialfeeds-modal-fb-page-id">' . esc_html__('Facebook Page ID', 'socialfeeds-pro') . '</label>
						<div class="socialfeeds-token-input-wrap">
							<span class="socialfeeds-token-icon dashicons dashicons-admin-users"></span>
							<input name="facebook_page_id" type="text" id="socialfeeds-modal-fb-page-id" placeholder="' . esc_attr__('e.g. 1029384756', 'socialfeeds-pro') . '" class="regular-text socialfeeds-api-input" />
						</div>
						<p class="description">' . esc_html__('Enter the ID of the Facebook Page you want to connect.', 'socialfeeds-pro') . '</p>
					</div>

					<div class="socialfeeds-modal-form-group">
						<label for="socialfeeds-modal-fb-token">' . esc_html__('Page Access Token', 'socialfeeds-pro') . '</label>
						<div class="socialfeeds-token-input-wrap">
							<span class="socialfeeds-token-icon dashicons dashicons-admin-network"></span>
							<input name="facebook_access_token" type="text" id="socialfeeds-modal-fb-token" placeholder="' . esc_attr__('Paste Page Access Token here...', 'socialfeeds-pro') . '" class="regular-text socialfeeds-api-input" />
						</div>
					</div>

					<!-- Facebook App Credentials (Show if Advanced or to support Long-lived in Basic) -->
					<div id="socialfeeds-modal-fb-app-group">
						<div class="socialfeeds-modal-form-group">
							<label for="socialfeeds-modal-fb-app-id">' . esc_html__('App ID', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-token-input-wrap">
								<span class="socialfeeds-token-icon dashicons dashicons-admin-generic"></span>
								<input name="facebook_app_id" type="text" id="socialfeeds-modal-fb-app-id" value="" class="regular-text socialfeeds-api-input" placeholder="' . esc_attr__('Enter App ID...', 'socialfeeds-pro') . '" />
							</div>
						</div>
						<div class="socialfeeds-modal-form-group">
							<label for="socialfeeds-modal-fb-app-secret">' . esc_html__('App Secret', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-token-input-wrap">
								<span class="socialfeeds-token-icon dashicons dashicons-lock"></span>
								<input name="facebook_app_secret" type="password" id="socialfeeds-modal-fb-app-secret" value="" class="regular-text socialfeeds-api-input" placeholder="' . esc_attr__('Enter App Secret...', 'socialfeeds-pro') . '" />
							</div>
							<p class="description">' . esc_html__('Providing App ID and Secret allows automatic token refresh.', 'socialfeeds-pro') . '</p>
						</div>
					</div>

					<div class="socialfeeds-modal-actions">
						<button type="submit" class="button socialfeeds-modal-btn-purple">' . esc_html__('Connect Page', 'socialfeeds-pro') . '</button>
						<button type="button" id="socialfeeds-fb-add-new-feed" class="button">' . esc_html__('+ Add New Feed', 'socialfeeds-pro') . '</button>
					</div>
				</form>

				<!-- Connected Accounts Section -->
				<div class="socialfeeds-modal-connected-accounts" style="margin-top:30px; border-top:1px solid #f1f5f9; padding-top:20px;">
					<h3 style="margin-bottom:15px; font-size:16px;">' . esc_html__('Connected Pages', 'socialfeeds-pro') . '</h3>
					<div class="socialfeeds-fb-accounts-list" style="display: grid; gap: 8px;">';
						$fb_connected = isset($opts['facebook_connected_accounts']) ? $opts['facebook_connected_accounts'] : [];
						if(empty($fb_connected)){
							echo '<p style="color:#64748b; font-size:13px; font-style:italic;">' . esc_html__('No pages connected yet.', 'socialfeeds-pro') . '</p>';
						} else {
							foreach($fb_connected as $acct){
								echo '<div class="socialfeeds-account-item-static" style="display:flex; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc;">
									<div style="width:32px; height:32px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:12px; display:flex; align-items:center; justify-content:center;">';
										if (!empty($acct['picture'])) {
											echo '<img src="' . esc_url($acct['picture']) . '" style="width:100%; height:100%; object-fit:cover;">';
										} else {
											echo '<span style="font-weight:600; color:#64748b; font-size:12px;">' . esc_html(strtoupper(substr($acct['name'], 0, 1))) . '</span>';
										}
									echo '</div>
									<div style="flex:1;">
										<strong style="display:block; font-size:14px; color:#1e293b;">' . esc_html($acct['name']) . '</strong>
										<small style="color:#64748b; font-size:11px;">' . esc_html__('PAGE', 'socialfeeds-pro') . '</small>
									</div>
									<button type="button" class="socialfeeds-delete-fb-account-btn" data-account-id="' . esc_attr($acct['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; display: flex; align-items: center;" title="' . esc_attr__('Delete Page', 'socialfeeds-pro') . '">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>';
							}
						}
					echo '</div>
				</div>
			</div>
		</div>';
	}

	static function facebook_connect_screen(){
		$feed_type = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : '';
		$preview_url = isset($_GET['preview_url']) ? esc_url_raw(wp_unslash($_GET['preview_url'])) : '';
		$opts = get_option('socialfeeds_facebook_option', []);
		$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
		if(empty($edit_id) && isset($_GET['feed_id'])) $edit_id = intval($_GET['feed_id']);

		$feed_settings = [];
		$socialfeeds_input_value = '';
		$selected_account_index = null;
		
		if ($edit_id && !empty($opts['facebook_feeds'])) {
			foreach ($opts['facebook_feeds'] as $feed) {
				if (isset($feed['id']) && (string) $feed['id'] === (string) $edit_id) {
					if (isset($feed['account_id'])) {
						$connected_accounts = isset($opts['facebook_connected_accounts']) ? $opts['facebook_connected_accounts'] : [];
						foreach ($connected_accounts as $idx => $account) {
							if (isset($account['id']) && (string) $account['id'] === (string) $feed['account_id']) {
								$selected_account_index = $idx;
								break;
							}
						}
					}
					$feed_type = isset($feed['type']) ? $feed['type'] : 'timeline';
					$socialfeeds_input_value = isset($feed['input']) ? $feed['input'] : '';
					$feed_settings = isset($feed['settings']) ? $feed['settings'] : [];
					break;
				}
			}
		}
		
		$defaults = [
			'facebook_layout' => 'grid',
			'facebook_aspect_ratio' => 'square',
			'facebook_padding' => 8,
			'facebook_posts_per_page' => 12,
			'facebook_number_posts_mobile' => 6,
			'facebook_columns_desktop' => 3,
			'facebook_columns_tablet' => 2,
			'facebook_columns_mobile' => 1,
			'facebook_color_scheme' => 'light',
			'facebook_custom_color' => '#000000',
			'facebook_header_enabled' => 1,
			'facebook_header_cover_enabled' => 1,
			'facebook_header_cover_image' => '',
			'facebook_header_cover_image_id' => '',
			'facebook_header_avatar_enabled' => 1,
			'facebook_use_custom_avatar' => 0,
			'facebook_custom_avatar' => '',
			'facebook_custom_avatar_id' => '',
			'facebook_header_caption_enabled' => 1,
			'facebook_header_stats_enabled' => 1,
			'facebook_show_caption' => 1,
			'facebook_likes' => 0,
			'facebook_comments' => 0,
			'facebook_sort_by' => 'newest',
			'facebook_hover_state' => 'overlay',
			'facebook_play_mode' => 'newtab',
			'facebook_load_more_enabled' => 0,
			'facebook_load_more_text' => 'Load More',
			'facebook_load_more_bg_color' => '#f1f5f9',
			'facebook_load_more_text_color' => '#1e293b',
			'facebook_load_more_hover_color' => '#e2e8f0',
			'facebook_load_more_count' => 9,
			'facebook_follow_button_enabled' => 0,
			'facebook_follow_button_text' => 'Follow on Facebook',
			'facebook_follow_button_bg_color' => '#1877f2',
			'facebook_follow_button_text_color' => '#ffffff',
			'facebook_follow_button_hover_color' => '#0a66c2',
		];
		
		$settings = array_merge($defaults, $feed_settings);
		$admin_post = esc_url(admin_url('admin-ajax.php'));
		
		echo '<div class="socialfeeds-wizard-container">';
		$connection_type = isset($_GET['connection_type']) ? sanitize_text_field(wp_unslash($_GET['connection_type'])) : '';
		
		if(empty($feed_type) && empty($edit_id)){
			self::render_facebook_feed_type_selection($connection_type, $opts);
		} else {
			self::render_facebook_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index, $socialfeeds_input_value);
		}
		echo '</div>';
	}

	static function render_facebook_feed_type_selection($connection_type, $opts){
		echo '<div class="socialfeeds-feed-main-card">
				<div class="socialfeeds-feed-main-header">
					<span class="socialfeeds-section-title">'.esc_html__('Select Feed Type', 'socialfeeds-pro').'</span>
					<p>'.esc_html__('Choose the source type for this Facebook feed.', 'socialfeeds-pro').'</p>
				</div>
				<div class="socialfeeds-feed-type-v2">
					<div class="socialfeeds-type-card selected" data-type="timeline">
						<div class="socialfeeds-p-card-icon" style="background:#eff6ff; color:#2563eb; margin-bottom:15px;">
							<span class="dashicons dashicons-facebook"></span>
						</div>
						<div class="socialfeeds-card-content">
							<h3>'.esc_html__('Timeline Posts', 'socialfeeds-pro').'</h3>
							<p>'.esc_html__('Fetch latest posts from a Facebook Page.', 'socialfeeds-pro').'</p>
						</div>
					</div>

					<div class="socialfeeds-type-card" data-type="albums">
						<div class="socialfeeds-p-card-icon" style="background:#fefce8; color:#ca8a04; margin-bottom:15px;">
							<span class="dashicons dashicons-images-alt2"></span>
						</div>
						<div class="socialfeeds-card-content">
							<h3>'.esc_html__('Albums', 'socialfeeds-pro').'</h3>
							<p>'.esc_html__('Display photo albums from your Facebook Page.', 'socialfeeds-pro').'</p>
						</div>
					</div>

					<div class="socialfeeds-type-card" data-type="events">
						<div class="socialfeeds-p-card-icon" style="background:#f0fdf4; color:#16a34a; margin-bottom:15px;">
							<span class="dashicons dashicons-calendar-alt"></span>
						</div>
						<div class="socialfeeds-card-content">
							<h3>'.esc_html__('Events', 'socialfeeds-pro').'</h3>
							<p>'.esc_html__('Show upcoming and past events from your Page.', 'socialfeeds-pro').'</p>
						</div>
					</div>
				</div>
				<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
					<a id="socialfeeds-select-type-btn-facebook" class="socialfeeds-btn-sync" href="#" style="padding:10px 40px;">'.esc_html__('Next', 'socialfeeds-pro').' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></a>
				</div>
			</div>';
	}

	static function render_facebook_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index = null, $socialfeeds_input_value = '') {
		echo '<form method="post" action="' . esc_attr($admin_post) . '" id="socialfeeds-facebook-wizard-form" class="socialfeeds-wizard-form">';
		wp_nonce_field('socialfeeds_pro_admin_nonce', 'nonce');
		echo '<input type="hidden" name="action" value="socialfeeds_pro_facebook_save_settings">
			<input type="hidden" name="feed_type" value="' . esc_attr($feed_type) . '">
			<input type="hidden" id="edit_id" name="edit_id" value="' . ($edit_id ? esc_attr($edit_id) : '') . '">';
			
		$source_active = empty($edit_id) ? 'active' : '';
		$customize_active = !empty($edit_id) ? 'active' : '';
		
		echo '<div class="socialfeeds-wizard-tabs">
				<div class="socialfeeds-wizard-tab ' . esc_attr($source_active) . '" data-tab="source">
					<span class="socialfeeds-tab-number">1</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Source', 'socialfeeds-pro') . '</span>
				</div>
				<div class="socialfeeds-wizard-tab ' . esc_attr($customize_active) . '" data-tab="customize">
					<span class="socialfeeds-tab-number">2</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Customize', 'socialfeeds-pro') . '</span>
				</div>
			</div>
			<div class="socialfeeds-wizard-tab-content-wrapper">';

		echo '<div class="socialfeeds-wizard-step ' . esc_attr($source_active) . '" id="socialfeeds-fb-step-1"' . (empty($edit_id) ? '' : ' style="display:none;"') . '>';
		self::render_facebook_source_tab($feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
		echo '</div>';

		echo '<div class="socialfeeds-wizard-step ' . esc_attr($customize_active) . '" id="socialfeeds-fb-step-2"' . (!empty($edit_id) ? '' : ' style="display:none;"') . '>';
		self::render_facebook_customize_tab($settings, $preview_url, $edit_id, $opts, $feed_type, $selected_account_index, $socialfeeds_input_value);
		echo '</div>';
		
		echo '</div></form>';
	}

	static function render_facebook_source_tab($feed_type, $edit_id, $opts, $selected_account_index = null, $input_val = ''){
		echo '<div class="socialfeeds-source-card-v2">
				<div class="socialfeeds-feed-main-header" style="margin-bottom:30px;">
					<h2>'.esc_html__('Configure Facebook Source', 'socialfeeds-pro').'</h2>
					<p>'.esc_html__('Choose a connected account and provide the required information for your feed.', 'socialfeeds-pro').'</p>
				</div>';

		// Accounts block
		echo '<div class="socialfeeds-control-group" style="margin-bottom:20px;">
				<label class="socialfeeds-control-label">'.esc_html__('Select Account', 'socialfeeds-pro').'</label>
				<div class="socialfeeds-accounts-list" id="socialfeeds-facebook-accounts-list" style="display: grid; gap: 8px; margin-bottom: 10px;">';
		
		$connected_accounts = isset($opts['facebook_connected_accounts']) ? $opts['facebook_connected_accounts'] : [];
		if (empty($connected_accounts)) {
			echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
					<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No accounts connected.', 'socialfeeds-pro') . '</p>
				</div>';
		} else {
			foreach ($connected_accounts as $idx => $account) {
				$checked = ($idx === $selected_account_index || ($selected_account_index === null && $idx === 0)) ? ' checked' : '';
				echo '<label class="socialfeeds-account-item ' . ($checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:15px; border:2px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all 0.2s; position:relative;">
						<input type="radio" name="facebook_selected_account" id="socialfeeds-facebook-selected-account-' . esc_attr($idx) . '" value="' . esc_attr($idx) . '" style="margin-right:15px;" class="socialfeeds-fb-trigger" ' .esc_attr($checked) . '>
						<div style="width:48px; height:48px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:15px; display:flex; align-items:center; justify-content:center;">';
							if (!empty($account['picture'])) {
								echo '<img src="' . esc_url($account['picture']) . '" style="width:100%; height:100%; object-fit:cover;">';
							} else {
								echo '<span style="font-weight:600; color:#64748b;">' . esc_html(strtoupper(substr($account['name'], 0, 1))) . '</span>';
							}
						echo '</div>';
						echo '<div style="flex:1;">
							<strong style="display:block; font-size:15px; color:#1e293b;">' . esc_html($account['name']) . '</strong>
							<small style="color:#64748b;">' . esc_html__('Facebook Page', 'socialfeeds-pro') . '</small>
						</div>
						<button type="button" class="socialfeeds-delete-facebook-account-btn" data-account-id="' . esc_attr($account['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; margin-left:10px; z-index:10; display: flex; align-items: center;" title="' . esc_attr__('Delete Account', 'socialfeeds-pro') . '">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</label>';
			}
		}
		echo '</div>
			<div style="margin-top:20px;">
				<button type="button" id="socialfeeds-facebook-add-account-btn" class="socialfeeds-btn-manage" style="width:100%; justify-content:center; border-style:dashed; background:#fff;">
					<span class="dashicons dashicons-plus" style="margin-top:2px;"></span> ' . esc_html__('Add New Facebook Page', 'socialfeeds-pro') . '
				</button>
			</div>

				<div id="socialfeeds-facebook-token-form" style="display:none; margin-top:20px; padding:20px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
					<h4 style="margin:0 0 10px 0;">' . esc_html__('Enter Facebook Page Info', 'socialfeeds-pro') . '</h4>
					
					<input type="hidden" name="fb_token_type" value="advanced">

					<div style="margin-bottom:12px;">
						<label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('Page ID', 'socialfeeds-pro') . '</label>
						<input type="text" id="socialfeeds-fb-page-id" placeholder="' . esc_attr__('Enter Page ID...', 'socialfeeds-pro') . '" class="socialfeeds-input-full" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
					</div>
					
					<div style="margin-bottom:12px;">
						<label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('Page Access Token', 'socialfeeds-pro') . '</label>
						<textarea id="socialfeeds-fb-token-input" placeholder="' . esc_attr__('Paste Page Access Token here...', 'socialfeeds-pro') . '" class="socialfeeds-input-full" style="width:100%; min-height:80px; padding:12px; border-radius:8px; border:1px solid #e2e8f0;"></textarea>
					</div>

					<!-- App Credentials (Advanced only) -->
					<div id="socialfeeds-fb-app-creds">
						<div style="margin-bottom:12px;">
							<label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App ID', 'socialfeeds-pro') . '</label>
							<input type="text" id="socialfeeds-fb-app-id" placeholder="' . esc_attr__('Enter App ID...', 'socialfeeds-pro') . '" class="socialfeeds-input-full" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
						</div>
						<div style="margin-bottom:12px;">
							<label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('App Secret', 'socialfeeds-pro') . '</label>
							<input type="text" id="socialfeeds-fb-app-secret" placeholder="' . esc_attr__('Enter App Secret...', 'socialfeeds-pro') . '" class="socialfeeds-input-full" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
						</div>
					</div>

					<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
						<button type="button" id="socialfeeds-fb-cancel-btn" class="socialfeeds-btn-manage" style="border:none;">' . esc_html__('Cancel', 'socialfeeds-pro') . '</button>
						<button type="button" id="socialfeeds-fb-validate-btn" class="socialfeeds-btn-sync">' . esc_html__('Connect Page', 'socialfeeds-pro') . '</button>
					</div>
				</div>
			</div>
			<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
				<a href="' . esc_url(admin_url('admin.php?page=socialfeeds&action=create#facebook')) . '" class="socialfeeds-btn-manage" style="width:auto; padding:10px 30px; border:none;">' . esc_html__('Back', 'socialfeeds-pro') . '</a>
				<button type="button" class="socialfeeds-fb-step-next-btn socialfeeds-btn-sync" data-next-step="2" style="padding:10px 40px;">' . esc_html__('Next Step', 'socialfeeds-pro') . ' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></button>
			</div>
		</div>';
	}

	static function render_facebook_customize_tab($settings, $preview_url, $edit_id, $opts = [], $feed_type = 'timeline', $selected_account_index = null, $socialfeeds_input_value = '') {
		
		$found = false;
		$feed_name = '';
		$display_id = '';
		$feeds = isset($opts['facebook_feeds']) ? $opts['facebook_feeds'] : [];
		
		if(!empty($edit_id)){
			foreach($feeds as $f){
				if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
					$feed_name = isset($f['name']) ? $f['name'] : '';
					$display_id = $edit_id;
					$found = true;
					break;
				}
			}
		}

		if(empty($found)){
			$display_id = '';
			$same_type_count = 0;
			foreach($feeds as $f){
				if(isset($f['type']) && $f['type'] === $feed_type){
					$same_type_count++;
				}
			}

			$sequential_number = $same_type_count + 1;
			$feed_name = 'Facebook Feed - ' . ucfirst($feed_type) . ' ' . $sequential_number;
		}

		echo '<div class="socialfeeds-customize-header" style="text-align:center;margin-bottom:30px;">
				<div class="socialfeeds-inline-name-wrapper" style="display:inline-flex;align-items:center;gap:10px;">
					<span class="socialfeeds-feed-name-text" style="font-size:15px;font-weight:600;">' . esc_html($feed_name) . '</span>
					<input type="text" class="socialfeeds-feed-name-input" value="' . esc_attr($feed_name) . '" style="display:none;font-size:15px;padding:4px 8px;" />
					<button type="button" class="socialfeeds-edit-name-btn" title="Edit" style="display:none;"><span class="dashicons dashicons-edit"></span></button>
					<button type="button" class="socialfeeds-save-name-btn" data-feed-id="' . esc_attr($edit_id) . '" data-platform="facebook" style="display:none;" title="Save"><span class="dashicons dashicons-yes"></span></button>
				</div>
			</div>';

		echo '<div class="socialfeeds-customize-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px;">
				<div class="socialfeeds-customize-header-left">
					<h2 class="socialfeeds-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">' . esc_html__('Customize Facebook Feed', 'socialfeeds-pro') . '</h2>
					<p class="socialfeeds-desc" style="margin: 8px 0 0; color: #64748b; font-size: 13px;">' . esc_html__('Configure how your Facebook feed appears on your site.', 'socialfeeds-pro') . '</p>
				</div>
				<div class="socialfeeds-header-right" style="display:flex; align-items:center; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; padding:5px 10px; border-radius:12px;">
					<div style="margin-right:15px;">
						<code id="socialfeeds-top-shortcode" style="background:transparent; border:none; font-size:14px; color:#475569; padding:0; font-family:monospace; letter-spacing:0.5px;">'.esc_attr('[socialfeeds id="'.$display_id.'" platform="facebook"]').'</code>
					</div>
					<button type="button" class="socialfeeds-copy-shortcode" data-shortcode="' . esc_attr('[socialfeeds id="'.$display_id.'" platform="facebook"]') . '"  style="display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #d1d5db; border-radius:8px; padding:8px 16px; cursor:pointer; color:#374151; font-weight:500; font-size:14px;">
						<span class="dashicons dashicons-admin-page" style="font-size:18px; width:18px; height:18px; color:#64748b;"></span>
						' . esc_html__('Copy', 'socialfeeds-pro') . '
					</button>
					<button type="button" class="socialfeeds-fullscreen-btn" title="' . esc_attr__('Fullscreen', 'socialfeeds-pro') . '" style="display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px; cursor: pointer; width: 38px; height: 38px; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
						<span class="dashicons dashicons-fullscreen-alt" style="font-size: 20px; width: 20px; height: 20px; color: #64748b;"></span>
					</button>
				</div>
			</div>';

		echo '<div class="socialfeeds-customize-columns">
				<div class="socialfeeds-customize-preview-column">
					<div class="socialfeeds-button-group" style="margin-bottom:15px; display:flex; justify-content:flex-end; gap:10px;">
						<a href="'.esc_url(admin_url('admin.php?page=socialfeeds#feeds')).'" class="button button-primary">' . esc_html__('All Feeds', 'socialfeeds-pro') . '</a>
						<button type="submit" class="button button-primary" id="socialfeeds-fb-save-btn">' . esc_html__('Save', 'socialfeeds-pro') . '</button>
					</div>
					<div class="socialfeeds-customize-preview">
						<div class="socialfeeds-preview-header-bar">
							<span class="socialfeeds-preview-label">' . esc_html__('LIVE PREVIEW', 'socialfeeds-pro') . '</span>
							<div class="socialfeeds-preview-device-toggles">
								<button type="button" class="socialfeeds-preview-device-btn active" data-width="100%" title="Desktop"><span class="dashicons dashicons-desktop"></span></button>
								<button type="button" class="socialfeeds-preview-device-btn" data-width="768" title="Tablet"><span class="dashicons dashicons-tablet"></span></button>
								<button type="button" class="socialfeeds-preview-device-btn" data-width="375" title="Mobile"><span class="dashicons dashicons-smartphone"></span></button>
							</div>
						</div>
						<div class="socialfeeds-preview-box-wrapper">
							<div class="socialfeeds-wizard-loader-overlay">
								<div class="socialfeeds-loader"></div>
							</div>
							<div id="socialfeeds-facebook-preview" class="socialfeeds-preview-box">
								<p style="text-align:center; color:#64748b; padding:20px;">' . esc_html__('(Facebook Feed Preview)', 'socialfeeds-pro') . '</p>
							</div>
						</div>
					</div>
				</div>
				<div class="socialfeeds-customize-settings-sidebar">
					<div class="socialfeeds-sidebar-tabs">
						<button type="button" class="socialfeeds-sidebar-tab-btn active" data-target="socialfeeds-fb-tab-general">'.esc_html__('General', 'socialfeeds-pro').'</button>
						<button type="button" class="socialfeeds-sidebar-tab-btn" data-target="socialfeeds-fb-tab-style">'.esc_html__('Style', 'socialfeeds-pro').'</button>
					</div>
					<div class="socialfeeds-sidebar-content">';
						self::render_facebook_sidebar_general($settings, $feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
						self::render_facebook_sidebar_style($settings);
		echo '		</div>
				</div>
			</div>';
	}

	static function render_facebook_sidebar_general($settings, $feed_type = 'timeline', $edit_id = '', $opts = [], $selected_account_index = null, $socialfeeds_input_value = ''){
		echo '<div id="socialfeeds-fb-tab-general" class="socialfeeds-sidebar-tab-pane active">
			<div class="socialfeeds-sidebar-header">
				<h3><span class="dashicons dashicons-admin-settings"></span>'.esc_html__('General', 'socialfeeds-pro').'</h3>
			</div>

			<!-- Sources Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
							<span class="dashicons dashicons-database"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Sources', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group" style="margin-bottom:15px;">
						<label class="socialfeeds-control-label">'.esc_html__('Feed Type', 'socialfeeds-pro').'</label>
						<select name="facebook_feed_type" class="socialfeeds-select-full socialfeeds-fb-trigger">
							<option value="timeline" '.selected($feed_type, 'timeline', false).'>Timeline Posts</option>
							<option value="albums" '.selected($feed_type, 'albums', false).'>Albums</option>
							<option value="events" '.selected($feed_type, 'events', false).'>Events</option>
						</select>
					</div>

					<div class="socialfeeds-control-group" style="margin-bottom:20px;">
						<label class="socialfeeds-control-label">'.esc_html__('Select Account', 'socialfeeds-pro').'</label>
						<div class="socialfeeds-accounts-list" style="display: grid; gap: 8px; margin-bottom: 10px;">';
				
						$connected_accounts = isset($opts['facebook_connected_accounts']) ? $opts['facebook_connected_accounts'] : [];
						if(empty($connected_accounts)){
							echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
									<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No accounts connected.', 'socialfeeds-pro') . '</p>
								</div>';
						} else {
							foreach($connected_accounts as $idx => $account){
								$checked = ($idx === $selected_account_index || ($selected_account_index === null && $idx === 0)) ? ' checked' : '';
								echo '<label class="socialfeeds-account-item ' . ($checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:10px; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; background:#fff; position:relative;">
									<input type="radio" name="facebook_selected_account" value="' . esc_attr($idx) . '" style="margin-right:10px;" '.esc_attr($checked). '>
									<div style="flex:1; min-width:0;">
										<strong style="display:block; font-size:13px; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . esc_html($account['name']) . '</strong>
									</div>
									<button type="button" class="socialfeeds-delete-facebook-account-btn" data-account-id="' . esc_attr($account['id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer;">
										<span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
									</button>
								</label>';
							}
						}
				echo '</div>
					</div>
				</div>
			</div>
			</div>

			<!-- Layout Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
							<span class="dashicons dashicons-layout"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Layout', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('LAYOUT TYPE', 'socialfeeds-pro').'</label>
						<div class="socialfeeds-layout-selector">
							<label class="socialfeeds-layout-option">
								<input type="radio" name="facebook_layout" id="socialfeeds-facebook-layout-grid" value="grid" '. checked($settings['facebook_layout'], 'grid', false) .' class="socialfeeds-fb-trigger">
								<div class="layout-box">
									<span class="dashicons dashicons-grid-view"></span>
									<span>'.esc_html__('Grid', 'socialfeeds-pro').'</span>
								</div>
							</label>
							<label class="socialfeeds-layout-option">
								<input type="radio" name="facebook_layout" id="socialfeeds-facebook-layout-list" value="list" '. checked($settings['facebook_layout'], 'list', false) .' class="socialfeeds-fb-trigger">
								<div class="layout-box">
									<span class="dashicons dashicons-list-view"></span>
									<span>'.esc_html__('List', 'socialfeeds-pro').'</span>
								</div>
							</label>
							<label class="socialfeeds-layout-option">
								<input type="radio" name="facebook_layout" id="socialfeeds-facebook-layout-carousel" value="carousel" '. checked($settings['facebook_layout'], 'carousel', false) .' class="socialfeeds-fb-trigger">
								<div class="layout-box">
									<span class="dashicons dashicons-images-alt2"></span>
									<span>'.esc_html__('Carousel', 'socialfeeds-pro').'</span>
								</div>
							</label>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('COLUMNS - DESKTOP', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">'.esc_html($settings['facebook_columns_desktop']).' Columns</span>
						</div>
						<div class="socialfeeds-range-slider">
							<span class="range-min">1</span>
							<input type="range" name="facebook_columns_desktop" min="1" max="6" step="1" value="'.esc_attr($settings['facebook_columns_desktop']).'" class="socialfeeds-fb-trigger">
							<span class="range-max">6</span>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('SPACING', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">'.esc_html($settings['facebook_padding']).'px</span>
						</div>
						<div class="socialfeeds-range-slider">
							<span class="range-min">0</span>
							<input type="range" name="facebook_padding" min="0" max="100" step="1" value="'.esc_attr($settings['facebook_padding']).'" class="socialfeeds-fb-trigger">
							<span class="range-max">100</span>
						</div>
					</div>

					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('ASPECT RATIO', 'socialfeeds-pro').'</label>
						<select name="facebook_aspect_ratio" class="socialfeeds-input-full socialfeeds-fb-trigger">
							<option value="square" '.selected($settings['facebook_aspect_ratio'], 'square', false).'>Square</option>
							<option value="portrait" '.selected($settings['facebook_aspect_ratio'], 'portrait', false).'>Portrait</option>
						</select>
					</div>
				</div>
			</div>
			</div>

			<!-- Content Limits Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #fff7ed; color: #f97316;">
							<span class="dashicons dashicons-filter"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Content Limits', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<div class="flex-title">
							<label class="socialfeeds-control-label">'.esc_html__('NUMBER OF POSTS', 'socialfeeds-pro').'</label>
							<span class="socialfeeds-value-display">Max 100</span>
						</div>
						<input type="number" name="facebook_posts_per_page" id="socialfeeds-facebook-posts-per-page" value="'.esc_attr($settings['facebook_posts_per_page']).'" min="1" max="100" class="socialfeeds-input-full socialfeeds-fb-trigger">
					</div>
				</div>
			</div>
			</div>

			<!-- Header Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #fdf2f8; color: #db2777;">
							<span class="dashicons dashicons-admin-users"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Header', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<!-- Enable Header -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Enable Header', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_header_enabled" value="1" '.checked($settings['facebook_header_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Enable Background Image -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Enable Background Image', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_header_cover_enabled" value="1" '.checked($settings['facebook_header_cover_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Enable Profile Picture -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Enable Profile Picture', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_header_avatar_enabled" value="1" '.checked($settings['facebook_header_avatar_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Show Caption -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Show Caption', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_header_caption_enabled" value="1" '.checked($settings['facebook_header_caption_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Show Stats -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Show Stats', 'socialfeeds-pro').'</div>
							<p class="description" style="margin-top:4px; font-size:11px;">'.esc_html__('Shows Likes and Followers count', 'socialfeeds-pro').'</p>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_header_stats_enabled" value="1" '.checked($settings['facebook_header_stats_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>
				</div>
			</div>
			</div>

			<!-- Post Experience -->
			
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #fdf2f8; color: #db2777;">
							<span class="dashicons dashicons-format-image"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Post Experience', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('Sort By', 'socialfeeds-pro').'</label>
						<select name="facebook_sort_by" class="socialfeeds-select-full socialfeeds-fb-trigger">
							<option value="newest" '.selected($settings['facebook_sort_by'], 'newest', false).'>Newest</option>
							<option value="most_liked" '.selected($settings['facebook_sort_by'], 'most_liked', false).'>Most Liked</option>
							<option value="most_commented" '.selected($settings['facebook_sort_by'], 'most_commented', false).'>Most Commented</option>
							<option value="random" '.selected($settings['facebook_sort_by'], 'random', false).'>Random</option>
							</select>
					</div>

					<!-- Show Caption -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Show Caption', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_show_caption" value="1" '.checked($settings['facebook_show_caption'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Show Likes -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Show Likes', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_likes" value="1" '.checked($settings['facebook_likes'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Show Comments -->
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Show Comments', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_comments" value="1" '.checked($settings['facebook_comments'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>

					<!-- Hover State -->
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('Hover State', 'socialfeeds-pro').'</label>
						<select name="facebook_hover_state" id="socialfeeds-facebook-hover-state" class="socialfeeds-select-full socialfeeds-fb-trigger">
							<option value="overlay" '. selected($settings['facebook_hover_state'], 'overlay', false) .'>Overlay</option>
							<option value="scale" '. selected($settings['facebook_hover_state'], 'scale', false) .'>Scale</option>
							<option value="shadow" '. selected($settings['facebook_hover_state'], 'shadow', false) .'>Shadow</option>
							<option value="none" '. selected($settings['facebook_hover_state'], 'none', false) .'>None</option>
						</select>
					</div>

					<!-- Play Mode -->
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'.esc_html__('Play Mode', 'socialfeeds-pro').'</label>
						<select name="facebook_play_mode" id="socialfeeds-facebook-play-mode" class="socialfeeds-select-full socialfeeds-fb-trigger">
							<option value="newtab" '. selected($settings['facebook_play_mode'], 'newtab', false) .'>Open in New Tab</option>
							<option value="lightbox" '. selected($settings['facebook_play_mode'], 'lightbox', false) .'>Open in Lightbox</option>
							<option value="inline" '. selected($settings['facebook_play_mode'], 'inline', false) .'>Play Inline</option>
						</select>
					</div>
				</div>
			</div>
			</div>

			<!-- Load More Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #fff7ed; color: #ea580c;">
							<span class="dashicons dashicons-plus-alt"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Load More', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Enable Load More', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_load_more_enabled" value="1" '.checked($settings['facebook_load_more_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>
					<div class="socialfeeds-nested-options" style="margin-top:15px;">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('BUTTON TEXT', 'socialfeeds-pro').'</label>
							<input type="text" name="facebook_load_more_text" id="socialfeeds-facebook-load-more-text" value="'.(isset($settings['facebook_load_more_text']) ? esc_attr($settings['facebook_load_more_text']) : 'Load More').'" class="socialfeeds-input-full socialfeeds-fb-trigger">
						</div>
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('Button Colors', 'socialfeeds-pro').'</label>
							<div style="display: flex; gap: 10px; margin-top: 5px;">
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('BG', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_load_more_bg_color" value="'.(isset($settings['facebook_load_more_bg_color']) ? esc_attr($settings['facebook_load_more_bg_color']) : '#E74C3C').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('Text', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_load_more_text_color" value="'.(isset($settings['facebook_load_more_text_color']) ? esc_attr($settings['facebook_load_more_text_color']) : '#FFFFFF').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('Hover', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_load_more_hover_color" value="'.(isset($settings['facebook_load_more_hover_color']) ? esc_attr($settings['facebook_load_more_hover_color']) : '#f76606').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
							</div>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<div class="flex-title">
								<label class="socialfeeds-control-label">'.esc_html__('VIDEOS TO LOAD', 'socialfeeds-pro').'</label>
							</div>
							<input type="number" name="facebook_load_more_count" value="'.(isset($settings['facebook_load_more_count']) ? esc_attr($settings['facebook_load_more_count']) : 9).'" min="1" max="50" class="socialfeeds-input-full socialfeeds-fb-trigger">
						</div>
					</div>
				</div>
			</div>
			</div>

			<!-- Follow Button Accordion -->
			<div class="socialfeeds-accordion-wrapper">
			<div class="socialfeeds-accordion-item">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #eff6ff; color: #1d4ed8;">
							<span class="dashicons dashicons-id"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'.esc_html__('Follow Button', 'socialfeeds-pro').'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-toggle-row">
						<div class="socialfeeds-toggle-info">
							<div class="socialfeeds-toggle-title">'.esc_html__('Enable Follow Button', 'socialfeeds-pro').'</div>
						</div>
						<div class="socialfeeds-toggle-input">
							<label class="socialfeeds-switch">
								<input type="checkbox" name="facebook_follow_button_enabled" value="1" '.checked($settings['facebook_follow_button_enabled'], 1, false).' class="socialfeeds-fb-trigger">
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>
					<div class="socialfeeds-nested-options" style="margin-top:15px;">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('Button Text', 'socialfeeds-pro').'</label>
							<input type="text" name="facebook_follow_button_text" value="'.(isset($settings['facebook_follow_button_text']) ? esc_attr($settings['facebook_follow_button_text']) : 'Follow on Facebook').'" class="socialfeeds-input-full socialfeeds-fb-trigger">
						</div>
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">'.esc_html__('Button Colors', 'socialfeeds-pro').'</label>
							<div style="display: flex; gap: 10px; margin-top: 5px;">
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('BG', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_follow_button_bg_color" value="'.(isset($settings['facebook_follow_button_bg_color']) ? esc_attr($settings['facebook_follow_button_bg_color']) : '#1877F2').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('Text', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_follow_button_text_color" value="'.(isset($settings['facebook_follow_button_text_color']) ? esc_attr($settings['facebook_follow_button_text_color']) : '#FFFFFF').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
								<div style="flex: 1;">
									<label style="font-size: 10px; opacity: 0.7; display: block; margin-bottom: 4px;">'.esc_html__('Hover', 'socialfeeds-pro').'</label>
									<input type="color" name="facebook_follow_button_hover_color" value="'.(isset($settings['facebook_follow_button_hover_color']) ? esc_attr($settings['facebook_follow_button_hover_color']) : '#0e5a9a').'" class="socialfeeds-color-input socialfeeds-fb-trigger">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>';
	}

	static function render_facebook_sidebar_style($settings) {
		echo '<div id="socialfeeds-fb-tab-style" class="socialfeeds-sidebar-tab-pane">
			<div class="socialfeeds-sidebar-header">
				<h3><span class="dashicons dashicons-art"></span>'. esc_html__('Style Settings', 'socialfeeds-pro').'</h3>
			</div>
			
			<div class="socialfeeds-accordion-wrapper">
			<div class="socialfeeds-accordion-item active">
				<div class="socialfeeds-accordion-header">
					<div class="socialfeeds-header-left">
						<div class="socialfeeds-icon-wrap" style="background: #f0fdfa; color: #0d9488;">
							<span class="dashicons dashicons-art"></span>
						</div>
						<div class="socialfeeds-title-wrap">
							<span class="socialfeeds-sidebar-title">'. esc_html__('Color Scheme', 'socialfeeds-pro') .'</span>
						</div>
					</div>
					<div class="socialfeeds-header-right">
						<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="socialfeeds-accordion-body">
					<div class="socialfeeds-control-group">
						<label class="socialfeeds-control-label">'. esc_html__('COLOR SCHEME', 'socialfeeds-pro') .'</label>
						<select name="facebook_color_scheme" class="socialfeeds-select-full socialfeeds-fb-trigger">
							<option value="light" '. selected($settings['facebook_color_scheme'], 'light', false) .'>'.esc_html__('Light', 'socialfeeds-pro').'</option>
							<option value="dark" '. selected($settings['facebook_color_scheme'], 'dark', false) .'>'.esc_html__('Dark', 'socialfeeds-pro').'</option>
							<option value="custom" '. selected($settings['facebook_color_scheme'], 'custom', false) .'>'.esc_html__('Custom', 'socialfeeds-pro').'</option>
						</select>
					</div>
					<div class="socialfeeds-control-group socialfeeds-fb-custom-color" style="'.('custom' !== $settings['facebook_color_scheme'] ? 'display:none;' : '').'">
						<label class="socialfeeds-control-label">'.esc_html__('CUSTOM BG COLOR', 'socialfeeds-pro').'</label>
						<input type="color" name="facebook_custom_color" value="'.esc_attr($settings['facebook_custom_color']).'" class="socialfeeds-color-input socialfeeds-fb-trigger">
					</div>
				</div>
			</div>
			</div>

		</div>';
	}

	/** Render Google Connection Modal**/
	static function render_google_connection_modal($opts){
		echo'<div id="socialfeeds-google-connection-modal" class="socialfeeds-modal-overlay">
			<div class="socialfeeds-modal-content socialfeeds-google-modal-wide">
				<button type="button" class="socialfeeds-modal-close" data-modal="socialfeeds-google-connection-modal">&times;</button>
				<h2>' . esc_html__('Google Reviews Settings', 'socialfeeds-pro') . '</h2>
				<p>' . esc_html__('Configure your Google Places API connection to display reviews', 'socialfeeds-pro') . '</p>

				<form id="socialfeeds-google-modal-token-form" method="post">
					<div class="socialfeeds-connection-features">
						<div class="socialfeeds-feature-item info"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__('Requires a Google Maps Platform API Key', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Display business ratings and reviews', 'socialfeeds-pro') . '</div>
						<div class="socialfeeds-feature-item yes"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Search for your business via Place ID', 'socialfeeds-pro') . '</div>
					</div>

					<div class="socialfeeds-modal-form-group">
						<label for="socialfeeds-modal-google-api-key">' . esc_html__('Google API Key', 'socialfeeds-pro') . '</label>
						<div class="socialfeeds-token-input-wrap">
							<span class="socialfeeds-token-icon dashicons dashicons-admin-network"></span>
							<input name="google_api_key" type="password" id="socialfeeds-modal-google-api-key" value="' . esc_attr(isset($opts['api_key']) ? $opts['api_key'] : '') . '" placeholder="' . esc_attr__('Enter your Google API Key...', 'socialfeeds-pro') . '" class="regular-text socialfeeds-api-input" />
						</div>
					</div>

					<div class="socialfeeds-modal-actions">
						<button type="submit" class="button socialfeeds-modal-btn-purple">' . esc_html__('Save Token', 'socialfeeds-pro') . '</button>
						<a href="' . esc_url(admin_url('admin.php?page=socialfeeds&action=create&type=reviews#google-reviews')) . '" id="socialfeeds-google-add-new-feed" class="button">' . esc_html__('+ Add New Feed', 'socialfeeds-pro') . '</a>
					</div>
				</form>

				<div class="socialfeeds-modal-connected-accounts" style="margin-top:30px; border-top:1px solid #f1f5f9; padding-top:20px;">
					<h3 style="margin-bottom:15px; font-size:16px;">' . esc_html__('Connected Locations', 'socialfeeds-pro') . '</h3>
					<div class="socialfeeds-google-accounts-list" style="display: grid; gap: 8px;">';
						$google_connected = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];
						if(empty($google_connected)){
							echo '<p style="color:#64748b; font-size:13px; font-style:italic;">' . esc_html__('No locations connected yet.', 'socialfeeds-pro') . '</p>';
						} else {
							foreach($google_connected as $loc){
							echo'<div class="socialfeeds-account-item-static" style="display:flex; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc;">
									<div style="width:32px; height:32px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:12px; display:flex; align-items:center; justify-content:center;">';
										echo '<span class="dashicons dashicons-location" style="color:#64748b;"></span>';
									echo '</div>
									<div style="flex:1;">
										<strong style="display:block; font-size:14px; color:#1e293b;">' . esc_html($loc['name']) . '</strong>
										<small style="color:#64748b; font-size:11px;">' . esc_html($loc['address']) . '</small>
									</div>
									<button type="button" class="socialfeeds-delete-google-account-btn" data-account-id="' . esc_attr($loc['place_id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; display: flex; align-items: center;" title="' . esc_attr__('Delete Location', 'socialfeeds-pro') . '">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>';
							}
						}
				echo'</div>
				</div>
			</div>
		</div>';
	}

	static function google_reviews_page() {

		$feed_type = isset($_GET['type'])? sanitize_text_field(wp_unslash($_GET['type'])): 'reviews';
		$preview_url = isset($_GET['preview_url']) ? esc_url_raw(wp_unslash($_GET['preview_url'])): '';
		$message = isset($_GET['socialfeeds_msg']) ? sanitize_text_field(wp_unslash($_GET['socialfeeds_msg'])): '';
		$opts = get_option('socialfeeds_google_option', []);
		$edit_id = isset($_GET['edit_id']) ? sanitize_text_field(wp_unslash($_GET['edit_id'])) : (isset($_GET['feed_id']) ? sanitize_text_field(wp_unslash($_GET['feed_id'])) : (isset($_GET['id'])? sanitize_text_field(wp_unslash($_GET['id'])): ''));

		$feed_settings = [];
		$socialfeeds_input_value = '';
		$selected_account_index  = null;
		$found_in_google = false;

		// Validate edit_id exists in Google feeds
		if (!empty($edit_id) && !empty($opts['google_reviews_feeds'])) {
			foreach ($opts['google_reviews_feeds'] as $feed) {
				if (isset($feed['id']) && (string) $feed['id'] === (string) $edit_id) {
					$found_in_google = true;
					break;
				}
			}

			if (!$found_in_google) {
				return;
			}
		}

		// Load feed data if editing
		if(!empty($edit_id) && !empty($opts['google_reviews_feeds'])) {
			foreach ($opts['google_reviews_feeds'] as $feed) {
				if(isset($feed['id']) && (string) $feed['id'] === (string) $edit_id) {

					// Match connected account index
					if(isset($feed['account_id']) && !empty($opts['google_connected_accounts'])) {
						foreach ($opts['google_connected_accounts'] as $idx => $account) {
							if(isset($account['place_id']) && (string) $account['place_id'] === (string) $feed['account_id']) {
								$selected_account_index = $idx;
								break;
							}
						}
					}

					// Fallback to matching place_id against saved input for older feeds
					if ($selected_account_index === null && !empty($feed['input']) && !empty($opts['google_connected_accounts'])) {
						$input_value = rawurldecode($feed['input']);
						foreach ($opts['google_connected_accounts'] as $idx => $account) {
							if (isset($account['place_id']) && (string) $account['place_id'] === (string) $input_value) {
								$selected_account_index = $idx;
								break;
							}
						}
					}

					// If only one location exists, select it by default on edit.
					if ($selected_account_index === null && !empty($opts['google_connected_accounts']) && count($opts['google_connected_accounts']) === 1) {
						$selected_account_index = 0;
					}

					// Feed type override
					if(!empty($feed['type'])) {
						$feed_type = $feed['type'];
					}

					// Input value
					if(isset($feed['input'])) {
						$socialfeeds_input_value = rawurlencode($feed['input']);
					}

					// Preview URL fallback
					if(!empty($feed['preview']) && empty($preview_url)) {
						$preview_url = $feed['preview'];
					}

					// Settings
					if(isset($feed['settings']) && is_array($feed['settings'])) {
						$feed_settings = $feed['settings'];
					}

					break;
				}
			}
		}

		// Default settings
		$defaults = [
			'google_reviews_layout' => 'grid',
			'google_reviews_padding' => 8,
			'google_reviews_columns_desktop' => 3,
			'google_reviews_columns_mobile' => 1,
			'google_reviews_header_enabled' => 1,
			'google_reviews_header_title' => 1,
			'google_reviews_header_description' => 1,
			'google_reviews_sort_by' => 'newest',
			'rating_enabled' => 1,
			'google_reviews_show_text' => 1,
			'google_reviews_show_author' => 1,
			'google_reviews_show_author_image' => 1,
			'google_reviews_show_date' => 1,
			'google_reviews_min_rating' => 1,
			'google_reviews_hover_state' => 'overlay',
			'google_reviews_color_scheme' => 'light',
			'rating_bg_color' => '#ffbe1a',
			'rating_hover_color' => '#e4f808',
			'google_custom_header_text' => '',
		];

		$settings = array_merge($defaults, $feed_settings);
		$admin_post = esc_url(admin_url('admin-ajax.php'));

		echo '<div class="socialfeeds-wizard-container">';

		if (empty($feed_type)) {
			$feed_type = 'reviews';
		}

		self::render_google_reviews_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index, $socialfeeds_input_value);
		self::render_google_reviews_embed_modal();

		echo '</div>';
	}

	static function render_google_reviews_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $admin_post, $selected_account_index = null, $socialfeeds_input_value = '') {
		$is_edit = !empty($edit_id);

		$source_active_class    = $is_edit ? '' : 'active';
		$customize_active_class = $is_edit ? 'active' : '';

		$step1_style = $is_edit ? 'style="display:none;"' : '';
		$step2_style = $is_edit ? '' : 'style="display:none;"';

		echo '<form method="post" action="' . esc_attr($admin_post) . '" id="socialfeeds-google-wizard-form" class="socialfeeds-wizard-form">';

		wp_nonce_field('socialfeeds_pro_admin_nonce', 'nonce');

		echo '<input type="hidden" name="action" value="socialfeeds_pro_google_reviews_save_settings">
			<input type="hidden" id="socialfeeds_stage" name="stage" value="customize">
			<input type="hidden" name="feed_type" id="socialfeeds-hidden-feed-type" value="' . esc_attr($feed_type) . '">
			<input type="hidden" name="edit_id" id="socialfeeds-edit-id" value="' . esc_attr($edit_id) . '">
			<input type="hidden" name="source_input" id="socialfeeds-source-input-hidden" value="' . esc_attr(urldecode($socialfeeds_input_value)) . '">
			<input type="hidden" id="preview_url_hidden" name="preview_url" value="' . esc_attr($preview_url) . '">

			<div class="socialfeeds-wizard-tabs">
				<div class="socialfeeds-wizard-tab ' . esc_attr($source_active_class) . '" id="socialfeeds-google-tab-source" data-tab="source">
					<span class="socialfeeds-tab-number">1</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Source', 'socialfeeds-pro') . '</span>
				</div>
				<div class="socialfeeds-wizard-tab ' . esc_attr($customize_active_class) . '" id="socialfeeds-google-tab-customize" data-tab="customize">
					<span class="socialfeeds-tab-number">2</span>
					<span class="socialfeeds-tab-label">' . esc_html__('Customize', 'socialfeeds-pro') . '</span>
				</div>
			</div>

			<div class="socialfeeds-wizard-tab-content-wrapper">
				<div class="socialfeeds-wizard-step ' . esc_attr($source_active_class) . '" id="socialfeeds-step-1" ' . $step1_style . '>';
					self::render_google_reviews_source_tab($feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
		echo '	</div>

				<div class="socialfeeds-wizard-step ' . esc_attr($customize_active_class) . '" id="socialfeeds-step-2" ' . $step2_style . '>';
					self::render_google_reviews_customize_tab($settings, $preview_url, $edit_id, $opts, $feed_type, $selected_account_index, $socialfeeds_input_value);
		echo '	</div>
			</div>
		</form>';
	}

	static function render_google_reviews_source_tab($feed_type, $edit_id, $opts, $selected_account_index = null, $input_val = '') {
		echo '<div class="socialfeeds-source-card-v2">
				<div class="socialfeeds-feed-main-header" style="margin-bottom:30px;">
					<h2>' . esc_html__('Select Location', 'socialfeeds-pro') . '</h2>
					<p>' . esc_html__('Choose a location to display reviews from.', 'socialfeeds-pro') . '</p>
				</div>';

		// Add Location Form
		echo '<div class="socialfeeds-control-group" style="margin-bottom:30px;">
				<label class="socialfeeds-control-label">' . esc_html__('Add New Location', 'socialfeeds-pro') . '</label>
				<div style="padding:20px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
					<div style="margin-bottom:15px;">
						<label style="display:block; margin-bottom:6px; font-weight:600; color:#1e293b;">' . esc_html__('Place ID', 'socialfeeds-pro') . '</label>
						<input type="text" id="socialfeeds-google-wizard-place-id" placeholder="' . esc_attr__('e.g., ChIJN1blzgBZwokR5LA6yadyYWQ', 'socialfeeds-pro') . '" class="socialfeeds-input-full" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px;" autocomplete="off">
						<strong>Note: To fetch Google reviews, go to the</strong>
						<a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank" rel="noopener noreferrer">Place ID Finder</a>
						</div>
						
					<div id="socialfeeds-google-wizard-token-message" style="margin-bottom:15px;"></div>
					<div style="display:flex; gap:10px; justify-content:flex-end;">
						<button type="button" id="socialfeeds-google-add-location-btn" class="socialfeeds-btn-sync" style="padding:8px 20px;">' . esc_html__('Add Location', 'socialfeeds-pro') . '</button>
					</div>
				</div>
		</div>';

		// Connected Locations List
		echo'<div class="socialfeeds-control-group" style="margin-bottom:20px;">
			<label class="socialfeeds-control-label">' . esc_html__('Connected Locations', 'socialfeeds-pro') . '</label>
			<div class="socialfeeds-accounts-list" id="socialfeeds-google-accounts-list" style="display: grid; gap: 8px; margin-bottom: 10px;">';

		$connected_accounts = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];
		if (empty($connected_accounts)) {
			echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
				<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No locations connected yet. Add one above.', 'socialfeeds-pro') . '</p>
				</div>';
		} else {
			foreach ($connected_accounts as $idx => $account) {
				$is_checked = ($edit_id && $selected_account_index !== null) ? ($idx === $selected_account_index) : ($idx === 0);
				
				echo '<label class="socialfeeds-account-item ' . ($is_checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:15px; border:2px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all 0.2s; position:relative;">
						<input type="radio" name="google_reviews_selected_account" id="socialfeeds-google-selected-account-' . esc_attr($idx) . '" value="' . esc_attr($idx) . '" style="margin-right:15px;" ' . checked($is_checked, true, false) . '>
						<div style="width:48px; height:48px; border-radius:50%; overflow:hidden; background:#f1f5f9; margin-right:15px; display:flex; align-items:center; justify-content:center;">
							<span class="dashicons dashicons-location" style="color:#64748b; font-size:28px; width:28px; height:28px;"></span>
						</div>
						<div style="flex:1;">
							<strong style="display:block; font-size:15px; color:#1e293b;">' . esc_html($account['name']) . '</strong>
							<small style="color:#64748b;">' . esc_html($account['address']) . '</small>
						</div>
						<button type="button" class="socialfeeds-delete-google-account-btn" data-account-id="' . esc_attr($account['place_id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:5px; margin-left:10px; z-index:10; display: flex; align-items: center;" title="' . esc_attr__('Delete Location', 'socialfeeds-pro') . '">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</label>';
			}
		}
		echo '	</div>
			</div>';

		echo '<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px; border-top:1px solid #f1f5f9; padding-top:20px;">
				<a href="' . esc_url(admin_url('admin.php?page=socialfeeds&action=create#google-reviews')) . '" class="socialfeeds-btn-manage" style="width:auto; padding:10px 30px; border:none;">' . esc_html__('Back', 'socialfeeds-pro') . '</a>
				<button type="button" class="socialfeeds-step-next-btn socialfeeds-btn-sync" data-next-step="2" id="socialfeeds-google-next-btn" style="padding:10px 40px;">' . esc_html__('Next', 'socialfeeds-pro') . ' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></button>
			</div>
		</div>';
	}

	static function render_google_reviews_customize_tab($settings, $preview_url, $edit_id, $opts = [], $feed_type = 'reviews', $selected_account_index = null, $socialfeeds_input_value = '') {
		$platform_key = 'google_reviews';
		$shortcode_tag = $platform_key . '-feed';
		$feed_name = '';
		$display_id = '';
		$feeds = isset($opts['google_reviews_feeds']) ? $opts['google_reviews_feeds'] : [];
		$found = false;

		if(!empty($edit_id)){
			foreach($feeds as $f){
				if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
					$feed_name = isset( $f['name'] ) ? $f['name'] : '';
					$display_id = $edit_id;
					$found = true;
					break;
				}
			}
		}

		if(empty($found)){
					$all_existing_ids = [];

					$display_id = '';
					$same_type_count = 0;
					foreach($feeds as $f){
						if(isset($f['type']) && $f['type'] === $feed_type){
							$same_type_count++;
						}
					}

					$sequential_number = $same_type_count + 1;
					$feed_name = 'Google Reviews Feed - ' . ucfirst($feed_type) . ' ' . $sequential_number;
		}

		echo '<div class="socialfeeds-customize-header" style="text-align:center;margin-bottom:30px;">
				<div class="socialfeeds-inline-name-wrapper" style="display:inline-flex;align-items:center;gap:10px;">
					<span class="socialfeeds-feed-name-text" style="font-size:15px;font-weight:600;">' . esc_html($feed_name) . '</span>
					<input type="text" class="socialfeeds-feed-name-input" value="' . esc_attr($feed_name) . '" style="display:none;font-size:15px;padding:4px 8px;" />
					<button type="button" class="socialfeeds-edit-name-btn" title="Edit" style="display:none;"><span class="dashicons dashicons-edit"></span></button>
					<button type="button" class="socialfeeds-save-name-btn" data-feed-id="' . esc_attr($edit_id) . '" data-platform="google_reviews" style="display:none;" title="' . esc_attr__('Save', 'socialfeeds-pro') . '"><span class="dashicons dashicons-yes"></span></button>
				</div>
			</div>';

		echo '<div class="socialfeeds-customize-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
				<div class="socialfeeds-customize-header-left">
					<h2 class="socialfeeds-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">' . esc_html__('Customize Display', 'socialfeeds-pro') . '</h2>
					<p class="socialfeeds-desc" style="margin: 8px 0 0; color: #64748b; font-size: 13px;">' . esc_html__('Configure how your Google Reviews feed appears on your site.', 'socialfeeds-pro') . '</p>
				</div>
				<div class="socialfeeds-customize-header-right" style="display: flex; align-items: center; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 5px 10px; border-radius: 12px;">
					<div style="margin-right: 15px;">
						<code id="socialfeeds-top-shortcode" style="background: transparent; border: none; font-size: 14px; color: #475569; padding: 0; font-family: monospace; letter-spacing: 0.5px;">' . esc_html('[socialfeeds id="' . $display_id . '" platform="google_reviews"]') . '</code>
					</div>
					<button type="button" class="socialfeeds-copy-shortcode" data-shortcode="' . esc_attr('[socialfeeds id="' . $display_id . '" platform="google_reviews"]') . '" style="display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 16px; cursor: pointer; color: #374151; font-weight: 500; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
						<span class="dashicons dashicons-admin-page" style="font-size: 18px; width: 18px; height: 18px; color: #64748b;"></span>
						' . esc_html__('Copy', 'socialfeeds-pro') . '
					</button>
					<button type="button" class="socialfeeds-fullscreen-btn" title="' . esc_attr__('Fullscreen', 'socialfeeds-pro') . '" style="display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px; cursor: pointer; width: 38px; height: 38px; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
						<span class="socialfeed-fullscreen dashicons dashicons-fullscreen-alt" style="font-size: 20px; width: 20px; height: 20px; color: #64748b;"></span>
					</button>
				</div>
			</div>';

		echo '<div class="socialfeeds-customize-columns">
				<div class="socialfeeds-customize-preview-column">
					<div class="socialfeeds-button-group" style="margin-bottom: 15px; margin-top: 0; display: flex; justify-content: flex-end; gap: 10px;">
						<a href="' . esc_url(admin_url('admin.php?page=socialfeeds#feeds')) . '" class="button button-primary">' . esc_html__('All Feeds', 'socialfeeds-pro') . '</a>
						<button type="submit" class="button button-primary" id="socialfeeds-save-btn">' . ($edit_id ? esc_html__('Save', 'socialfeeds-pro') : esc_html__('Save', 'socialfeeds-pro')) . '</button>
					</div>

					<div class="socialfeeds-customize-preview">
						<div class="socialfeeds-preview-header-bar">
							<span class="socialfeeds-preview-label">' . esc_html__('LIVE PREVIEW', 'socialfeeds-pro') . '</span>
							<div class="socialfeeds-preview-device-toggles">
								<button type="button" class="socialfeeds-preview-device-btn active" data-width="100%" title="Desktop"><span class="dashicons dashicons-desktop"></span></button>
								<button type="button" class="socialfeeds-preview-device-btn" data-width="768" title="Tablet"><span class="dashicons dashicons-tablet"></span></button>
								<button type="button" class="socialfeeds-preview-device-btn" data-width="375" title="Mobile"><span class="dashicons dashicons-smartphone"></span></button>
							</div>
						</div>
						
						<div class="socialfeeds-preview-box-wrapper">
							<div class="socialfeeds-wizard-loader-overlay">
								<div class="socialfeeds-loader"></div>
							</div>
							<div class="socialfeeds-google-feed">
								<div id="socialfeeds-google-preview-header"></div>
								<div class="socialfeeds-preview-box" id="socialfeeds-google-preview-grid">
									<div class="socialfeeds-fetch-status-container" style="text-align:center; padding:10px; font-weight:500; color:#666;">
										<span id="socialfeeds-fetch-status"></span>
									</div>
								</div>
								</div>
							</div>
						</div>
				</div>

				<div class="socialfeeds-customize-settings-sidebar">
					<div class="socialfeeds-sidebar-tabs">
						<button type="button" class="socialfeeds-sidebar-tab-btn active" data-target="socialfeeds-google-tab-general">' . esc_html__('General', 'socialfeeds-pro') . '</button>
						<button type="button" class="socialfeeds-sidebar-tab-btn" data-target="socialfeeds-google-tab-style">' . esc_html__('Style', 'socialfeeds-pro') . '</button>
					</div>

					<div class="socialfeeds-sidebar-content">';
						self::render_google_reviews_sidebar_general($settings, $feed_type, $edit_id, $opts, $selected_account_index, $socialfeeds_input_value);
						self::render_google_reviews_sidebar_style($settings);
		echo '		</div>
				</div>
			</div>';
	}

	static function render_google_reviews_sidebar_general($settings, $feed_type = 'reviews', $edit_id = '', $opts = [], $selected_account_index = null, $socialfeeds_input_value = '') {
		echo '<div id="socialfeeds-google-tab-general" class="socialfeeds-sidebar-tab-pane active">
			<div class="socialfeeds-sidebar-header">
				<h3><span class="dashicons dashicons-admin-settings"></span>' . esc_html__('General', 'socialfeeds-pro') . '</h3>
			</div>

			<!-- Sources Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
								<span class="dashicons dashicons-database"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">' . esc_html__('Sources', 'socialfeeds-pro') . '</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-control-group" style="margin-bottom:15px;">
							<label class="socialfeeds-control-label">' . esc_html__('Feed Type', 'socialfeeds-pro') . '</label>
							<select id="socialfeeds-google-source-type-sidebar" name="google_reviews_source_type_sidebar" class="socialfeeds-select-full">
								<option value="reviews" ' . selected($feed_type, 'reviews', false) . '>Google Reviews</option>
							</select>
						</div>

						<!-- Accounts block -->
						<div class="socialfeeds-control-group" style="margin-bottom:20px;">
							<label class="socialfeeds-control-label">' . esc_html__('Select Business Profile', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-accounts-list" id="socialfeeds-google-accounts-list-sidebar" style="display: grid; gap: 8px; margin-bottom: 10px;">';

						$connected_accounts = isset($opts['google_connected_accounts']) ? $opts['google_connected_accounts'] : [];
						if (empty($connected_accounts)) {
							echo '<div style="padding:15px; text-align:center; background:#f8fafc; border-radius:8px; border:1px dashed #e2e8f0;">
									<p style="color:#64748b; margin:0; font-size:12px;">' . esc_html__('No business profiles connected.', 'socialfeeds-pro') . '</p>
								</div>';
						} else {
							foreach ($connected_accounts as $idx => $account) {
								$is_checked = ($edit_id && $selected_account_index !== null) ? ($idx === $selected_account_index) : ($idx === 0);
								echo '<label class="socialfeeds-account-item ' . ($is_checked ? 'selected' : '') . '" style="display:flex; align-items:center; padding:10px; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; background:#fff; position:relative;">
									<input type="radio" name="google_reviews_selected_account" value="' . esc_attr($idx) . '" style="margin-right:10px;" ' . checked($is_checked, true, false) . ' onchange="jQuery(\'input[name=\\\'google_reviews_selected_account\\\'][value=\\\'\' + this.value + \'\\\']\').prop(\'checked\', true).closest(\'.socialfeeds-account-item\').addClass(\'selected\').siblings().removeClass(\'selected\');">
									<div style="flex:1; min-width:0;">
										<strong style="display:block; font-size:13px; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' . esc_html($account['name']) . '</strong>
										<small style="color:#64748b; font-size:11px;">' . esc_html($account['address']) . '</small>
									</div>
									<button type="button" class="socialfeeds-delete-google-account-btn" data-account-id="' . esc_attr($account['place_id']) . '" style="background:none; border:none; color:#ef4444; cursor:pointer;">
										<span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
									</button>
								</label>';
							}
						}
								echo '	</div>
						</div>';

					echo '<div class="socialfeeds-control-group" style="margin-top:25px; border-top:1px solid #e2e8f0; padding-top:15px;">
							<button type="button" id="socialfeeds-google-fetch-preview-btn-sidebar" class="button button-primary" style="width:100%;">' . esc_html__('Fetch', 'socialfeeds-pro') . '</button>
							<p class="description" style="margin-top:8px; font-size:11px; text-align:center; color:#64748b;">' . esc_html__('Click to fetch new reviews with these source settings', 'socialfeeds-pro') . '</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Layout Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #6366f1;">
								<span class="dashicons dashicons-layout"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">' . esc_html__('Layout', 'socialfeeds-pro') . '</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">' . esc_html__('LAYOUT TYPE', 'socialfeeds-pro') . '</label>
							<div class="socialfeeds-layout-selector">
								<label class="socialfeeds-layout-option">
									<input type="radio" name="google_reviews_layout" id="socialfeeds-google-layout-grid" value="grid" ' . checked($settings['google_reviews_layout'], 'grid', false) . '>
									<div class="layout-box">
										<span class="dashicons dashicons-grid-view"></span>
										<span>' . esc_html__('Grid', 'socialfeeds-pro') . '</span>
									</div>
								</label>
								<label class="socialfeeds-layout-option">
									<input type="radio" name="google_reviews_layout" id="socialfeeds-google-layout-list" value="list" ' . checked($settings['google_reviews_layout'], 'list', false) . '>
									<div class="layout-box">
										<span class="dashicons dashicons-layout"></span>
										<span>' . esc_html__('List', 'socialfeeds-pro') . '</span>
									</div>
								</label>
								<label class="socialfeeds-layout-option">
									<input type="radio" name="google_reviews_layout" id="socialfeeds-google-layout-carousel" value="carousel" ' . checked($settings['google_reviews_layout'], 'carousel', false) . '>
									<div class="layout-box">
										<span class="dashicons dashicons-images-alt2"></span>
										<span>' . esc_html__('Carousel', 'socialfeeds-pro') . '</span>
									</div>
								</label>
							</div>
						</div>

						<div class="socialfeeds-control-group">
							<div class="flex-title">
								<label class="socialfeeds-control-label">' . esc_html__('COLUMNS - DESKTOP', 'socialfeeds-pro') . '</label>
								<span class="socialfeeds-value-display">' . esc_html($settings['google_reviews_columns_desktop']) . ' Columns</span>
							</div>
							<div class="socialfeeds-range-slider">
								<span class="range-min">1</span>
								<input type="range" name="google_reviews_columns_desktop" id="socialfeeds-google-columns-desktop" min="1" max="6" step="1" value="' . esc_attr($settings['google_reviews_columns_desktop']) . '">
								<span class="range-max">6</span>
							</div>
						</div>

						<div class="socialfeeds-control-group">
							<div class="flex-title">
								<label class="socialfeeds-control-label">' . esc_html__('COLUMNS - MOBILE', 'socialfeeds-pro') . '</label>
								<span class="socialfeeds-value-display">' . esc_html($settings['google_reviews_columns_mobile']) . ' Columns</span>
							</div>
							<div class="socialfeeds-range-slider">
								<span class="range-min">1</span>
								<input type="range" name="google_reviews_columns_mobile" id="socialfeeds-google-columns-mobile" min="1" max="3" step="1" value="' . esc_attr($settings['google_reviews_columns_mobile']) . '">
								<span class="range-max">3</span>
							</div>
						</div>

						<div class="socialfeeds-control-group">
							<div class="flex-title">
								<label class="socialfeeds-control-label">' . esc_html__('SPACING', 'socialfeeds-pro') . '</label>
								<span class="socialfeeds-value-display">' . esc_html($settings['google_reviews_padding']) . 'px</span>
							</div>
							<div class="socialfeeds-range-slider">
								<span class="range-min">0</span>
								<input type="range" name="google_reviews_padding" id="socialfeeds-google-padding" min="0" max="100" step="1" value="' . esc_attr($settings['google_reviews_padding']) . '">
								<span class="range-max">100</span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Header Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #fdf2f8; color: #db2777;">
								<span class="dashicons dashicons-align-center"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">' . esc_html__('Header', 'socialfeeds-pro') . '</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Enable Header', 'socialfeeds-pro') . '</span>
								<span class="socialfeeds-toggle-desc">' . esc_html__('Display feed header with business info', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_header_enabled" id="socialfeeds-google-header-enabled" value="1" ' . checked($settings['google_reviews_header_enabled'], 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Enable Title', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_header_title" id="socialfeeds-google-header-title" value="1" ' . checked($settings['google_reviews_header_title'], 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:10px;">
							<label class="socialfeeds-control-label">' . esc_html__('Custom Header Text', 'socialfeeds-pro') . '</label>
							<textarea id="socialfeeds-google-custom-header-text" name="google_custom_header_text" class="socialfeeds-input-full" placeholder="' . esc_attr__('Optional text...', 'socialfeeds-pro') . '">' . esc_textarea(isset($settings['google_custom_header_text']) ? $settings['google_custom_header_text'] : '') . '</textarea>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Enable Description', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_header_description" id="socialfeeds-google-header-description" value="1" ' . checked($settings['google_reviews_header_description'], 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
					</div>
				</div>
			</div>

			<!-- Reviews Content Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #e0f2fe; color: #0284c7;">
								<span class="dashicons dashicons-format-image"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">' . esc_html__('Reviews', 'socialfeeds-pro') . '</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">' . esc_html__('SORT BY', 'socialfeeds-pro') . '</label>
							<select name="google_reviews_sort_by" id="socialfeeds-google-sort-by" class="socialfeeds-select-full">
								<option value="newest" ' . selected($settings['google_reviews_sort_by'], 'newest', false) . '>Newest First</option>
								<option value="rating" ' . selected($settings['google_reviews_sort_by'], 'rating', false) . '>Highest Rating</option>
								<option value="random" ' . selected($settings['google_reviews_sort_by'], 'random', false) . '>Random</option>
							</select>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<div class="socialfeeds-toggle-title">' . esc_html__('Enable Rating', 'socialfeeds-pro') . '</div>
								<div class="socialfeeds-toggle-desc">' . esc_html__('Show rating stars', 'socialfeeds-pro') . '</div>
							</div>
							<div class="socialfeeds-toggle-input">
								<label class="socialfeeds-switch">
									<input type="checkbox" name="rating_enabled" id="socialfeeds-google-rating-enabled" value="1" ' . checked($settings['rating_enabled'], 1, false) . '>
									<span class="socialfeeds-slider"></span>
								</label>
							</div>
						</div>

						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">' . esc_html__('Rating Colors', 'socialfeeds-pro') . '</label>
							<div style="display:flex; gap:10px; margin-top:5px;">
								<div style="flex:1;">
									<span style="font-size:11px; display:block; margin-bottom:3px;">Background</span>
									<input type="color" name="rating_bg_color" id="socialfeeds-google-rating-bg-color" value="' . esc_attr(isset($settings['rating_bg_color']) ? $settings['rating_bg_color'] : '#ffbe1a') . '" class="socialfeeds-color-input" style="width:100% !important;">
								</div>
								<div style="flex:1;">
									<span style="font-size:11px; display:block; margin-bottom:3px;">Hover</span>
									<input type="color" name="rating_hover_color" id="socialfeeds-google-rating-hover-color" value="' . esc_attr(isset($settings['rating_hover_color']) ? $settings['rating_hover_color'] : '#e4f808') . '" class="socialfeeds-color-input" style="width:100% !important;">
								</div>
							</div>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Show Review Text', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_show_text" id="socialfeeds-google-show-text" value="1" ' . checked(!isset($settings['google_reviews_show_text']) || !empty($settings['google_reviews_show_text']), 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Show Author Name', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_show_author" id="socialfeeds-google-show-author" value="1" ' . checked(!isset($settings['google_reviews_show_author']) || !empty($settings['google_reviews_show_author']), 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Show Author Image', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_show_author_image" id="socialfeeds-google-show-author-image" value="1" ' . checked(!isset($settings['google_reviews_show_author_image']) || !empty($settings['google_reviews_show_author_image']), 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-toggle-row">
							<div class="socialfeeds-toggle-info">
								<span class="socialfeeds-toggle-title">' . esc_html__('Show Date', 'socialfeeds-pro') . '</span>
							</div>
							<label class="socialfeeds-switch">
								<input type="checkbox" name="google_reviews_show_date" id="socialfeeds-google-show-date" value="1" ' . checked(!isset($settings['google_reviews_show_date']) || !empty($settings['google_reviews_show_date']), 1, false) . '>
								<span class="socialfeeds-slider"></span>
							</label>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">' . esc_html__('MINIMUM RATING', 'socialfeeds-pro') . '</label>
							<select name="google_reviews_min_rating" id="socialfeeds-google-min-rating" class="socialfeeds-select-full">
								<option value="1" ' . selected(isset($settings['google_reviews_min_rating']) ? $settings['google_reviews_min_rating'] : 1, 1, false) . '>1 Star & Above</option>
								<option value="2" ' . selected(isset($settings['google_reviews_min_rating']) ? $settings['google_reviews_min_rating'] : '', 2, false) . '>2 Stars & Above</option>
								<option value="3" ' . selected(isset($settings['google_reviews_min_rating']) ? $settings['google_reviews_min_rating'] : '', 3, false) . '>3 Stars & Above</option>
								<option value="4" ' . selected(isset($settings['google_reviews_min_rating']) ? $settings['google_reviews_min_rating'] : '', 4, false) . '>4 Stars & Above</option>
								<option value="5" ' . selected(isset($settings['google_reviews_min_rating']) ? $settings['google_reviews_min_rating'] : '', 5, false) . '>5 Stars Only</option>
							</select>
						</div>
						<div class="socialfeeds-control-group" style="margin-top:15px;">
							<label class="socialfeeds-control-label">' . esc_html__('HOVER STATE', 'socialfeeds-pro') . '</label>
							<select name="google_reviews_hover_state" id="socialfeeds-google-hover-state" class="socialfeeds-select-full">
								<option value="overlay" ' . selected($settings['google_reviews_hover_state'], 'overlay', false) . '>Overlay</option>
								<option value="scale" ' . selected($settings['google_reviews_hover_state'], 'scale', false) . '>Scale</option>
								<option value="shadow" ' . selected($settings['google_reviews_hover_state'], 'shadow', false) . '>Shadow</option>
								<option value="none" ' . selected($settings['google_reviews_hover_state'], 'none', false) . '>None</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}

	static function render_google_reviews_embed_modal() {
		echo '<div id="socialfeeds-embed-modal" class="socialfeeds-embed-modal">
			<div class="socialfeeds-embed-container" style="max-width: 800px; width: 90%; background: #fff; border-radius: 16px; position: relative;">
				<button id="socialfeeds-embed-close" class="socialfeeds-embed-close" style="position: absolute; right: 20px; top: 20px; border: none; background: none; font-size: 24px; cursor: pointer;">&times;</button>
				<div class="socialfeeds-embed-content" style="padding: 40px;">
					<h2 style="margin-top:0;">' . esc_html__('Embed Feed', 'socialfeeds-pro') . '</h2>
					<p style="color:#64748b; margin-bottom:25px;">' . esc_html__('Copy the shortcode and paste it into your page or post editor.', 'socialfeeds-pro') . '</p>
					
					<div class="socialfeeds-embed-shortcode-wrap" style="display:flex; gap:10px; margin-bottom:30px; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0;">
						<input type="text" id="socialfeeds-embed-shortcode" readonly value="" style="flex:1; background:transparent; border:none; font-family:monospace; font-size:16px; color:#1e293b;" />
						<button id="socialfeeds-embed-copy" class="socialfeeds-btn-sync" style="padding:8px 25px;">' . esc_html__('Copy', 'socialfeeds-pro') . '</button>
					</div>

					<div class="socialfeeds-preview-header-bar" style="border-radius:12px 12px 0 0;">
						<span class="socialfeeds-preview-label">'.esc_html__('EMBED PREVIEW', 'socialfeeds-pro').'</span>
						<div class="socialfeeds-preview-device-toggles">
							<button type="button" class="socialfeeds-preview-device active" data-width="100%"><span class="dashicons dashicons-desktop"></span></button>
							<button type="button" class="socialfeeds-preview-device" data-width="768"><span class="dashicons dashicons-tablet"></span></button>
						</div>
					</div>
					<div id="socialfeeds-embed-preview-wrap" style="background:#f1f5f9; padding:20px; border-radius:0 0 12px 12px; max-height:400px; overflow-y:auto;">
						<div id="socialfeeds-preview-grid-clone"></div>
					</div>
				</div>
			</div>
		</div>';
	}

	static function render_google_reviews_sidebar_style($settings) {
		echo '<div id="socialfeeds-google-tab-style" class="socialfeeds-sidebar-tab-pane">
			<div class="socialfeeds-sidebar-header">
				<h3><span class="dashicons dashicons-art"></span>' . esc_html__('Style Settings', 'socialfeeds-pro') . '</h3>
			</div>
			
			<!-- Color Scheme Accordion -->
			<div class="socialfeeds-accordion-wrapper" style="margin-bottom: 20px;">
				<div class="socialfeeds-accordion-item">
					<div class="socialfeeds-accordion-header">
						<div class="socialfeeds-header-left">
							<div class="socialfeeds-icon-wrap" style="background: #f0fdfa; color: #0d9488;">
								<span class="dashicons dashicons-art"></span>
							</div>
							<div class="socialfeeds-title-wrap">
								<span class="socialfeeds-sidebar-title">' . esc_html__('Color Scheme', 'socialfeeds-pro') . '</span>
							</div>
						</div>
						<div class="socialfeeds-header-right">
							<span class="socialfeeds-chevron dashicons dashicons-arrow-down-alt2"></span>
						</div>
					</div>
					<div class="socialfeeds-accordion-body">
						<div class="socialfeeds-control-group">
							<label class="socialfeeds-control-label">' . esc_html__('COLOR SCHEME', 'socialfeeds-pro') . '</label>
							<select name="google_reviews_color_scheme" id="socialfeeds-google-color-scheme" class="socialfeeds-select-full">
								<option value="light" ' . selected($settings['google_reviews_color_scheme'], 'light', false) . '>' . esc_html__('Light', 'socialfeeds-pro') . '</option>
								<option value="dark" ' . selected($settings['google_reviews_color_scheme'], 'dark', false) . '>' . esc_html__('Dark', 'socialfeeds-pro') . '</option>
								<option value="custom" ' . selected($settings['google_reviews_color_scheme'], 'custom', false) . '>' . esc_html__('Custom', 'socialfeeds-pro') . '</option>
							</select>
						</div>
						<div class="socialfeeds-control-group" id="socialfeeds-google-custom-color-group" style="' . ('custom' !== $settings['google_reviews_color_scheme'] ? 'display:none;' : '') . '">
							<label class="socialfeeds-control-label">' . esc_html__('CUSTOM BG COLOR', 'socialfeeds-pro') . '</label>
							<input type="color" name="google_reviews_custom_color" id="socialfeeds-google-custom-color" value="' . esc_attr($settings['google_reviews_custom_color'] ?? '') . '" class="socialfeeds-color-input" style="width:100% !important;">
						</div>
					</div>
				</div>
			</div> 
		</div>';
	}
}