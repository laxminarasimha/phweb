<?php

namespace SocialFeeds\Settings;

if(!defined('ABSPATH')){
	exit;
}

class UI{

	static function dashboard_tab(){
		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		$insta_opts = get_option('socialfeeds_instagram_option', []);
		$fb_opts = get_option('socialfeeds_facebook_option', []);
		$google_opts = get_option('socialfeeds_google_option', []);
		$cache_settings = get_option('socialfeeds_settings_option', []);
		
		$youtube_feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];
		$instagram_feeds = isset($insta_opts['instagram_feeds']) ? $insta_opts['instagram_feeds'] : [];
		$facebook_feeds = isset($fb_opts['facebook_feeds']) ? $fb_opts['facebook_feeds'] : [];
		$google_feeds = isset($google_opts['google_reviews_feeds']) ? $google_opts['google_reviews_feeds'] : [];

		$connected_accounts = isset($insta_opts['instagram_connected_accounts']) ? $insta_opts['instagram_connected_accounts'] : [];
		$facebook_connected_accounts = isset($fb_opts['facebook_connected_accounts']) ? $fb_opts['facebook_connected_accounts'] : [];
		$google_connected_accounts = isset($google_opts['google_connected_accounts']) ? $google_opts['google_connected_accounts'] : [];

		$total_feeds_count = count($youtube_feeds) + count($instagram_feeds) + count($facebook_feeds) + count($google_feeds);
		$cache_duration = isset($cache_settings['cache_duration']) ? $cache_settings['cache_duration'] : HOUR_IN_SECONDS;
		
		echo '<div class="socialfeeds-dashboard-overview" style="display: flex; gap: 30px; align-items: flex-start;">

				<div class="socialfeeds-dashboard-left" style="flex: 1; min-width: 0;">
					
					<!-- Platform Overview -->
					<div class="socialfeeds-section-title" style="margin-top: 0;">'.esc_html__('Platform Overview', 'socialfeeds').'</div>

					<div class="socialfeeds-platform-grid">
						<!-- YouTube Card -->
						<div class="socialfeeds-platform-card">
							<div class="socialfeeds-p-card-header">
								<div class="socialfeeds-p-card-icon youtube">
									<span class="dashicons dashicons-youtube"></span>
								</div>
								<div class="socialfeeds-status-pill ' . (empty($youtube_opts['youtube_api_key']) ? 'inactive' : '') . '">
									<span class="socialfeeds-status-dot"></span>
									' . (empty($youtube_opts['youtube_api_key']) ? esc_html__('Pending Setup', 'socialfeeds') : esc_html__('Integrated', 'socialfeeds')) . '
								</div>
							</div>
							<div class="socialfeeds-p-card-info">
								<h3>'.esc_html__('YouTube Feed', 'socialfeeds').'</h3>
								<p>'.esc_html__('Video Content', 'socialfeeds').'</p>
							</div>
							<div class="socialfeeds-p-card-stats">
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('Feeds', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.esc_html(count($youtube_feeds)).'</div>
								</div>
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('API Keys', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.(empty($youtube_opts['youtube_api_key']) ? '0' : '1').'</div>
								</div>
							</div>
							<button type="button" class="socialfeeds-btn-manage socialfeeds-settings-btn" data-page="socialfeeds-youtube">
								'.esc_html__('Manage Integration', 'socialfeeds').'
							</button>
						</div>

						<!-- Instagram Card -->
						<div class="socialfeeds-platform-card">
							<div class="socialfeeds-p-card-header">
								<div class="socialfeeds-p-card-icon instagram">
									<span class="dashicons dashicons-instagram"></span>
								</div>
								' . (defined('SOCIALFEEDS_PRO_VERSION') ? '
									<div class="socialfeeds-status-pill ' . (empty($connected_accounts) ? 'inactive' : '') . '">
										<span class="socialfeeds-status-dot"></span>
										' . (empty($connected_accounts) ? esc_html__('Pending Setup', 'socialfeeds') : esc_html__('Integrated', 'socialfeeds')) . '
									</div>
								' : '<span class="socialfeeds-pro-tag">PRO</span>') . '
							</div>
							<div class="socialfeeds-p-card-info">
								<h3>'.esc_html__('Instagram Feed', 'socialfeeds').'</h3>
								<p>'.esc_html__('Posts & Reels', 'socialfeeds').'</p>
							</div>
							<div class="socialfeeds-p-card-stats">';
								if(defined('SOCIALFEEDS_PRO_VERSION')){
									do_action('socialfeeds_insta_quick_settings');
								}
							echo'</div>';
							
							if(!defined('SOCIALFEEDS_PRO_VERSION')){
								echo '<a href="https://socialfeeds.org/pricing/" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><button type="button" style="width:100%;" class="socialfeeds-apikey-notice-btn">'.esc_html__('UPGRADE TO PRO', 'socialfeeds').'</button></a>';
							} else{
								echo'<button type="button" class="socialfeeds-btn-manage socialfeeds-instagram-settings">'.esc_html__('Manage Accounts', 'socialfeeds').'</button>';
							}
						echo'</div>

						<!-- Facebook Card -->
						<div class="socialfeeds-platform-card">
							<div class="socialfeeds-p-card-header">
								<div class="socialfeeds-p-card-icon facebook" style="background:#eff6ff; color:#2563eb;">
									<span class="dashicons dashicons-facebook"></span>
								</div>
								' . (defined('SOCIALFEEDS_PRO_VERSION') ? '
									<div class="socialfeeds-status-pill ' . (empty($facebook_connected_accounts) ? 'inactive' : '') . '">
										<span class="socialfeeds-status-dot"></span>
										' . (empty($facebook_connected_accounts) ? esc_html__('Pending Setup', 'socialfeeds') : esc_html__('Integrated', 'socialfeeds')) . '
									</div>
								' : '<span class="socialfeeds-pro-tag">PRO</span>') . '
							</div>
							<div class="socialfeeds-p-card-info">
								<h3>'.esc_html__('Facebook Feed', 'socialfeeds').'</h3>
								<p>'.esc_html__('Page Posts', 'socialfeeds').'</p>
							</div>
							<div class="socialfeeds-p-card-stats">
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('Feeds', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.esc_html(count($facebook_feeds)).'</div>
								</div>
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('Accounts', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.esc_html(count($facebook_connected_accounts)).'</div>
								</div>
							</div>';
							
							if(!defined('SOCIALFEEDS_PRO_VERSION')){
								echo '<a href="https://socialfeeds.org/pricing/" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><button type="button" style="width:100%;" class="socialfeeds-apikey-notice-btn">'.esc_html__('UPGRADE TO PRO', 'socialfeeds').'</button></a>';
							} else{
								echo'<button type="button" class="socialfeeds-btn-manage socialfeeds-facebook-settings">'.esc_html__('Manage Accounts', 'socialfeeds').'</button>';
							}
						echo'</div>

						<!-- Google Reviews Card -->
						<div class="socialfeeds-platform-card">
							<div class="socialfeeds-p-card-header">
								<div class="socialfeeds-p-card-icon google" style="background:#eff6ff; color:#2563eb;">
									<span class="dashicons dashicons-google"></span>
								</div>
								' . (defined('SOCIALFEEDS_PRO_VERSION') ? '
									<div class="socialfeeds-status-pill ' . (empty($google_connected_accounts) ? 'inactive' : '') . '">
										<span class="socialfeeds-status-dot"></span>
										' . (empty($google_connected_accounts) ? esc_html__('Pending Setup', 'socialfeeds') : esc_html__('Integrated', 'socialfeeds')) . '
									</div>
								' : '<span class="socialfeeds-pro-tag">PRO</span>') . '
							</div>
							<div class="socialfeeds-p-card-info">
								<h3>'.esc_html__('Google Reviews', 'socialfeeds').'</h3>
								<p>'.esc_html__('Business Reviews', 'socialfeeds').'</p>
							</div>
							<div class="socialfeeds-p-card-stats">
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('Feeds', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.esc_html(count($google_feeds)).'</div>
								</div>
								<div>
									<div class="socialfeeds-stat-label">'.esc_html__('Places', 'socialfeeds').'</div>
									<div class="socialfeeds-stat-value">'.esc_html(count($google_connected_accounts)).'</div>
								</div>
							</div>';
							
							if(!defined('SOCIALFEEDS_PRO_VERSION')){
								echo '<a href="https://socialfeeds.org/pricing/" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><button type="button" style="width:100%;" class="socialfeeds-apikey-notice-btn">'.esc_html__('UPGRADE TO PRO', 'socialfeeds').'</button></a>';
							} else{
								echo'<button type="button" class="socialfeeds-btn-manage socialfeeds-google-settings">'.esc_html__('Manage Places', 'socialfeeds').'</button>';
							}
						echo'</div>

					</div>

					<!-- Quick Overview (Metrics) -->
					<div class="socialfeeds-section-title" style="margin-top: 40px; display: flex; align-items: center; gap: 8px;">'.esc_html__('Quick Overview', 'socialfeeds').'</div>

					<div class="socialfeeds-metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; display: grid;">
						<div class="socialfeeds-metric-card" style="padding: 24px;">
							<div class="socialfeeds-metric-info">
								<div class="socialfeeds-stat-label">'.esc_html__('Total Feeds', 'socialfeeds').'</div>
								<div class="socialfeeds-stat-value">'.esc_html($total_feeds_count).'</div>
							</div>
							<div class="socialfeeds-metric-icon" style="background: #eef2ff; color: #6366f1;">
								<span class="dashicons dashicons-rss"></span>
							</div>
						</div>
						<div class="socialfeeds-metric-card" style="padding: 24px;">
							<div class="socialfeeds-metric-info">
								<div class="socialfeeds-stat-label">'.esc_html__('Connected Accounts', 'socialfeeds').'</div>
								<div class="socialfeeds-stat-value">'.esc_html(count($connected_accounts) + count($facebook_connected_accounts) + count($google_connected_accounts)).'</div>
							</div>
							<div class="socialfeeds-metric-icon" style="background: #ecfdf5; color: #10b981;">
								<span class="dashicons dashicons-admin-users"></span>
							</div>
						</div>
					</div>
				</div>

				<div class="socialfeeds-dashboard-right" style="width: 380px; flex-shrink: 0;">
					<div class="socialfeeds-section-title" style="display: flex; align-items: center; gap: 8px;">
						'.esc_html__('Quick Settings', 'socialfeeds').'
					</div>

					<div class="socialfeeds-quick-settings-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.01);">
						<form id="socialfeeds-cache-settings-form" method="post" class="socialfeeds-ajax-form">
							<input type="hidden" name="action" value="socialfeeds_save_cache_settings">

							<div style="margin-bottom: 20px;">
								<label style="display: block; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">'.esc_html__('Cache Lifespan', 'socialfeeds').'</label>
								<div style="position: relative;">
									<select name="cache_interval" style="width: 100%;  min-height: 42px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; padding: 0 12px;">
										<option value="'.esc_attr(30 * MINUTE_IN_SECONDS).'" '.selected($cache_duration, 30 * MINUTE_IN_SECONDS, false).'>'.esc_html__('Every 30 minutes', 'socialfeeds').'</option>
										<option value="'.esc_attr(HOUR_IN_SECONDS).'" '.selected($cache_duration, HOUR_IN_SECONDS, false).'>'.esc_html__('Every hour', 'socialfeeds').'</option>
										<option value="'.esc_attr(12 * HOUR_IN_SECONDS).'" '.selected($cache_duration, 12 * HOUR_IN_SECONDS, false).'>'.esc_html__('Every 12 hours', 'socialfeeds').'</option>
										<option value="'.esc_attr(DAY_IN_SECONDS).'" '.selected($cache_duration, DAY_IN_SECONDS, false).'>'.esc_html__('Every 24 hours', 'socialfeeds').'</option>
									</select>
								</div>
							</div>

		
							<button type="button" class="button socialfeeds-clear-cache" style="width: 100%; height: 44px; margin-bottom: 12px; background: transparent !important; color: #5525d9 !important; border: 1px solid #5525d9 !important; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
								<span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span>
								'.esc_html__('Clear All Caches', 'socialfeeds').'
							</button>

							<button type="submit" class="button button-primary socialfeeds-save-cache" style="width: 100%; height: 44px; background: #5525d9 !important; border: none !important; border-radius: 8px; font-weight: 600; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(85, 37, 217, 0.4);">'.esc_html__('Save Configuration', 'socialfeeds').'</button>
						</form>
					</div>
				</div>
			</div>';
	
		// Settings Modal
		echo '<div id="socialfeeds-settings-modal" class="socialfeeds-modal-overlay">
				<div class="socialfeeds-modal-content">
					<button type="button" class="socialfeeds-modal-close" data-modal="socialfeeds-settings-modal">&times;</button>
					<h2>' . esc_html__('YouTube Settings', 'socialfeeds') . '</h2>
					<p>' . esc_html__('Configure your API key and create feeds', 'socialfeeds') . '</p>

					<form id="socialfeeds-modal-api-form" method="post">
						<div class="socialfeeds-modal-form-group">
							<label for="socialfeeds-modal-api-key">' . esc_html__('YouTube API Key', 'socialfeeds') . '</label>
							<input name="youtube_api_key" type="text" id="socialfeeds-modal-api-key"
								value="' . esc_attr(\SocialFeeds\Util::get_youtube_api_key($youtube_opts)) . '"
								class="regular-text socialfeeds-api-input"
								placeholder="' . esc_attr__('Enter your YouTube API key', 'socialfeeds') . '" />
							<p class="description">' . esc_html__('Get your API key from Google Cloud Console', 'socialfeeds') . '</p>
						</div>

						<div class="socialfeeds-modal-actions">
							<button type="submit" class="button socialfeeds-modal-btn-purple">' . esc_html__('Save API Key', 'socialfeeds') . '</button>
							<a href="'.esc_url(admin_url('admin.php?page=socialfeeds&action=create#youtube&action=create')).'" id="socialfeeds-modal-add-new" class="button">' . esc_html__( '+ Add New Feed', 'socialfeeds' ) . '</a>
						</div>
					</form>
				</div>
			</div>';
	}

	static function settings_tab(){
		$youtube_opts = get_option('socialfeeds_youtube_option', []);
		$insta_opts = get_option('socialfeeds_instagram_option', []);
		$google_opts = get_option('socialfeeds_google_option', []);
		$youtube_feeds = isset($youtube_opts['youtube_feeds']) ? $youtube_opts['youtube_feeds'] : [];
		$instagram_feeds = isset($insta_opts['instagram_feeds']) ? $insta_opts['instagram_feeds'] : [];
		$google_feeds = isset($google_opts['google_reviews_feeds']) ? $google_opts['google_reviews_feeds'] : [];

		echo '<div class="socialfeeds-wizard-container">
		<div class="socialfeeds-settings-container">
				<div class="socialfeeds-section-title" style="margin-top: 0;">
					' . esc_html__('Feeds', 'socialfeeds') . '
				</div>

				<div class="socialfeeds-activity-card">
					<table class="socialfeeds-table">
						<thead>
							<tr>
								<th>' . esc_html__('Feed Name', 'socialfeeds') . '</th>
								<th>' . esc_html__('Platform', 'socialfeeds') . '</th>
								<th>' . esc_html__('Shortcode', 'socialfeeds') . '</th>
								<th style="text-align:right;">' . esc_html__('Actions', 'socialfeeds') . '</th>
							</tr>
						</thead>
						<tbody>';
						
						$processed_youtube = [];
						foreach($youtube_feeds as $f){
							$f['_platform_key'] = 'youtube';
							$f['_platform_label'] = 'YouTube';
							$processed_youtube[] = $f;
						}

						$processed_instagram = [];
						foreach($instagram_feeds as $f){
							$f['_platform_key'] = 'instagram';
							$f['_platform_label'] = 'Instagram';
							$processed_instagram[] = $f;
						}

						$processed_facebook = [];
						$fb_opts = get_option('socialfeeds_facebook_option', []);
						$facebook_feeds = isset($fb_opts['facebook_feeds']) ? $fb_opts['facebook_feeds'] : [];
						foreach($facebook_feeds as $f){
							$f['_platform_key'] = 'facebook';
							$f['_platform_label'] = 'Facebook';
							$processed_facebook[] = $f;
						}

						$processed_google = [];
						foreach($google_feeds as $f){
							$f['_platform_key'] = 'google_reviews';
							$f['_platform_label'] = 'Google Reviews';
							$processed_google[] = $f;
						}

						$all_feeds = array_merge($processed_youtube, $processed_instagram, $processed_facebook, $processed_google);
						
						//Sort by index to show recent ones first
						usort($all_feeds, ['\SocialFeeds\Settings\UI', 'sort_by_id']);
						$display_feeds = $all_feeds;

						if (empty($display_feeds)) {
							echo '<tr><td colspan="4" style="text-align:center; padding: 40px;">' . esc_html__('No history recorded yet. Create your first feed to get started.', 'socialfeeds') . '</td></tr>';
						} else {
							foreach ($display_feeds as $feed) {
								$platform_label = isset($feed['_platform_label']) ? $feed['_platform_label'] : 'YouTube';
								$platform_key = isset($feed['_platform_key']) ? $feed['_platform_key'] : 'youtube';
								$name = isset($feed['name']) ? $feed['name'] : '(no name)';
								
								// Use stored shortcode if available, otherwise generate it
								$shortcode = isset($feed['shortcode']) ? $feed['shortcode'] : '[' . $platform_key . '-feed id="' . esc_attr($feed['id']) . '"]';
								
								$edit_url = admin_url('admin.php?page=socialfeeds&action=edit&edit_id=' . rawurlencode($feed['id']) . '#' . $platform_key);

								echo '<tr data-feed-id="' . esc_attr($feed['id']) . '">
										<td>
											<div class="socialfeeds-inline-name-wrapper">
												<span class="socialfeeds-feed-name-text"> <strong>' . esc_html($name) . '</strong> </span>
												<input type="text" class="socialfeeds-feed-name-input" value="' . esc_attr($name) . '" style="display:none;" />
												<button type="button" class="socialfeeds-edit-name-btn" title="Edit"> <span class="dashicons dashicons-edit"></span></button>
												<button type="button" class="socialfeeds-save-name-btn" data-feed-id="' . esc_attr($feed['id']) . '" data-platform="' . esc_attr($platform_key) . '" style="display:none;" title="Save"><span class="dashicons dashicons-yes"></span></button>
											</div>
										</td>
										<td>
											<div class="socialfeeds-platform-tag">
												<span class="dashicons dashicons-' . ($platform_key === "youtube" ? "youtube" : ($platform_key === "facebook" ? "facebook" : ($platform_key === "google_reviews" ? "google" : "instagram"))) . '"></span>
												' . esc_html($platform_label) . '
											</div>
										</td>
										<td><code>' . esc_html($shortcode) . '</code></td>
										<td style="text-align:right;">
											<div class="socialfeeds-table-actions">
												<a href="' . esc_url($edit_url) . '" class="socialfeeds-action-btn" title="'.esc_attr__('Edit', 'socialfeeds').'"><span class="dashicons dashicons-edit"></span></a>
												<button type="button" class="socialfeeds-action-btn socialfeeds-copy-shortcode-feeds" data-shortcode="' . esc_attr($shortcode) . '" title="'.esc_attr__('Copy', 'socialfeeds').'"><span class="dashicons dashicons-admin-page"></span></button>
												<button type="button" class="socialfeeds-action-btn socialfeeds-delete-feed-btn" data-feed-id="' . esc_attr($feed['id']) . '" data-platform="' . esc_attr($platform_key) . '" title="'.esc_attr__('Delete', 'socialfeeds').'"><span class="dashicons dashicons-trash"></span></button>
											</div>
										</td>
									</tr>';
							}
						}

						echo '</tbody>
					</table>
				</div>
			</div></div>';
	}

	static function render_connect_screen(){
		$feed_type = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : '';
		$preview_url = isset($_GET['preview_url']) ? sanitize_url(wp_unslash($_GET['preview_url'])) : '';
		$message = isset($_GET['socialfeeds_msg']) ? sanitize_text_field(wp_unslash($_GET['socialfeeds_msg'])) : '';
		$opts = get_option('socialfeeds_youtube_option', []);
		
		// Editing existing feed
		$edit_id = isset($_GET['edit_id']) ? sanitize_text_field(wp_unslash($_GET['edit_id'])) : '';
		$feed_settings = [];
		$edit_input = isset($_GET['socialfeeds_input']) ? sanitize_text_field(wp_unslash($_GET['socialfeeds_input'])) : '';
		
		if(!empty($edit_id)){
			$feeds = isset($opts['youtube_feeds']) ? $opts['youtube_feeds'] : [];
			foreach($feeds as $f){
				if(isset($f['id']) && (string) $f['id'] === (string) $edit_id){
					
					if(!empty($f['type'])){
						$feed_type = $f['type'];
					}
					
					if(isset($f['input'])){
						$edit_input = $f['input'];
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
		
		// Merge feed-specific settings with global defaults
		$defaults = [
			'youtube_display_style' => 'grid',
			'youtube_grid_columns_desktop' => 3,
			'youtube_grid_columns_mobile' => 1,
			'youtube_color_scheme' => 'light',
			'youtube_custom_color' => '#000000',
			'youtube_header_enabled' => 1,
			'youtube_header_show_description' => 0,
			'youtube_load_more_enabled' => 1,
			'youtube_load_more_text' => 'Load More',
			'youtube_load_more_bg_color' => '#350ae1',
			'youtube_load_more_text_color' => '#FFFFFF',
			'youtube_load_more_hover_color' => '#200564',
			'youtube_thumb_size' => 'medium',
			'youtube_show_title' => 1,
			'youtube_show_desc' => 0,
			'youtube_show_play_icon' => 1,
			'youtube_show_duration' => 0,
			'youtube_show_date' => 0,
			'youtube_show_views' => 0,
			'youtube_show_likes' => 0,
			'youtube_show_comments' => 0,
			'youtube_videos_per_page' => 9,
			'youtube_spacing' => 16,
			'youtube_hover_effect' => 'overlay',
			'youtube_subscribe_button_enabled' => 0,
			'youtube_subscribe_text' => 'Subscribe',
			'youtube_subscribe_bg_color' => '#FF0000',
			'youtube_subscribe_text_color' => '#FFFFFF',
			'youtube_subscribe_hover_color' => '#CC0000',
		];
		
		$settings = array_merge($defaults, $feed_settings);
		
		echo '<div class="socialfeeds-wizard-container">';

		
		if (empty($feed_type)) {
			self::render_feed_type_selection();
		} else {
			self::render_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $edit_input);
		}
		
		echo '</div>';
		
	}

	static function render_feed_type_selection(){

		echo '<div class="socialfeeds-feed-main-card">
				<div class="socialfeeds-feed-main-header">
					<span class="socialfeeds-section-title">'.esc_html__('Select Feed Type', 'socialfeeds').'</span>
					<p>'.esc_html__('Select one or more feed types to display on your website. You can add or remove them later.', 'socialfeeds').'</p>
				</div>

				<div class="socialfeeds-feed-type-v2">
					<!-- Channel -->
					<div class="socialfeeds-type-card selected" data-type="channel">
						<div class="socialfeeds-p-card-icon youtube" style="background:#fee2e2;">
							<span class="dashicons dashicons-video-alt3"></span>
						</div>
						<div class="socialfeeds-card-content">
							<h3>'.esc_html__('Channel', 'socialfeeds').'</h3>
							<p>'.esc_html__('A feed of videos from any YouTube channel.', 'socialfeeds').'</p>
						</div>
					</div>';

					do_action('socialfeeds_youtube_feeds');

				echo'</div>

				<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px;">
					<a id="socialfeeds-select-type-btn" class="socialfeeds-btn-sync socialfeeds-disabled" href="#" style="padding:10px 40px;">'.esc_html__('Next', 'socialfeeds').' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></a>
				</div>
			</div>';
	}

	static function render_wizard_form($feed_type, $preview_url, $edit_id, $settings, $opts, $edit_input = ''){
		$feed_name   = '';
		$input_value = '';
		$is_edit_mode = false;
		$highest_number = 0;

		if(!empty($opts['youtube_feeds']) && is_array($opts['youtube_feeds'])){

			foreach($opts['youtube_feeds'] as $f){

				if(!is_array($f)){
					continue;
				}

				if(!empty($edit_id) && isset($f['id']) && $f['id'] == $edit_id){
					$feed_name   = isset($f['name']) ? $f['name'] : '';
					$input_value = isset($f['input']) ? $f['input'] : '';
					$is_edit_mode = true;
				}

				if(empty($is_edit_mode) && isset($f['type'], $f['name']) && $f['type'] === $feed_type && preg_match('/(\d+)$/', $f['name'], $m)){
					$highest_number = max($highest_number, (int) $m[1]);
				}
			}
		}

		if(empty($is_edit_mode)){
			$next_number = $highest_number + 1;

			$types = [
				'channel' => 'Channel',
				'playlist' => 'Playlist',
				'search' => 'Search',
				'single-videos' => 'Single Videos',
			];

			$label = isset($types[$feed_type]) ? $types[$feed_type] : '';

			$feed_name = 'YouTube Feed - ' . ($label ? $label . ' ' : '') . $next_number;
		}
		
		echo '<form id="socialfeeds-wizard-form">
		<input type="hidden" name="action" id="socialfeeds-form-action" value="socialfeeds_save_settings" class="socialfeeds-wizard-form">
			<input type="hidden" id="socialfeeds_stage" name="stage" value="">
			<input type="hidden" name="feed_type" id="socialfeeds-feed-type" value="'.esc_attr($feed_type).'">
			<input type="hidden" id="preview_url_hidden" name="preview_url" value="'.esc_attr($preview_url).'">
			<div class="socialfeeds-wizard-tabs">
				<div class="socialfeeds-wizard-tab active" id="tab-source" data-tab="source">
					<span class="socialfeeds-tab-number">1</span>
					<span class="socialfeeds-tab-label">'.esc_html__('Source', 'socialfeeds').'</span>
				</div>
				<div class="socialfeeds-wizard-tab" id="tab-customize" data-tab="customize">
					<span class="socialfeeds-tab-number">2</span>
					<span class="socialfeeds-tab-label">'.esc_html__('Customize', 'socialfeeds').'</span>
				</div>
			</div>
			<div class="socialfeeds-wizard-tab-content-wrapper">';
		
		// TAB 1: SOURCE INPUT
		echo '<div class="socialfeeds-wizard-tab-content active" id="socialfeeds-content-source">';
			self::render_source_tab($feed_type, $edit_id, $opts, $edit_input);
		echo '</div>';
		
		// TAB 2: CUSTOMIZE & SETTINGS
		echo '<div class="socialfeeds-wizard-tab-content" id="socialfeeds-content-customize">';
		
		// Centered editable feed name
		echo'<div class="socialfeeds-customize-header" style="text-align:center;margin-bottom:30px;">
		<div class="socialfeeds-inline-name-wrapper" style="display:inline-flex;align-items:center;gap:10px;">
		<span class="socialfeeds-feed-name-text" style="font-size:15px;font-weight:600;">'.esc_html($feed_name).'</span>
		<input type="text" class="socialfeeds-feed-name-input" value="'.esc_attr($feed_name).'" style="display:none;font-size:15px;padding:4px 8px;" />
		<button type="button" class="socialfeeds-edit-name-btn" title="Edit" style="display:none;"> <span class="dashicons dashicons-edit"></span></button>
		<button type="button" class="socialfeeds-save-name-btn" data-feed-id="'.esc_attr($edit_id).'" data-platform="youtube" style="display:none;" title="Save"> <span class="dashicons dashicons-yes"></span></button>';

		echo'</div></div>';

		self::render_customize_tab($settings, $preview_url, $edit_id, $feed_type, [], $feed_name);

		echo'</div>
		</div> 
		</form>';
	}

	static function render_source_tab($feed_type, $edit_id, $opts, $edit_input = ''){
		echo '<div class="socialfeeds-source-card-v2">
				<div class="socialfeeds-feed-main-header" style="margin-bottom:30px;">'.
					'<h2>'.esc_html__('Add Source', 'socialfeeds').'</h2>
					<p>'.esc_html__('Connect your YouTube channel or other sources to fetch videos.', 'socialfeeds').'</p>
				</div>';
		
		switch($feed_type){
			case 'channel':
				echo '<div class="socialfeeds-input-group-v2">
						<label class="socialfeeds-label-v2">'.esc_html__('Channel ID or Username', 'socialfeeds').'</label>
						<input type="text" id="socialfeeds-youtube-channel-input" class="socialfeeds-input-v2" placeholder="'.esc_attr__('e.g., @yourchannel or Uc1a2458ff29305kufo', 'socialfeeds').'" name="channel_id" value="'.esc_attr($edit_input).'">
						<div class="socialfeeds-help-v2">'.esc_html__('Enter any channel ID or username to display all of an account\'s latest videos. You can find the ID or User Name of your YouTube Channel from the URL.', 'socialfeeds').'</div>
					</div>';
				break;
				
			case 'playlist':
				echo '<div class="socialfeeds-input-group-v2">
						<label class="socialfeeds-label-v2">'.esc_html__('Playlist ID', 'socialfeeds').'</label>
						<input type="text" id="socialfeeds-youtube-playlist-input" class="socialfeeds-input-v2" placeholder="' . esc_attr__('e.g., PLBCF2DAC6FFB574DE', 'socialfeeds') . '" name="playlist_id" value="'.esc_attr($edit_input).'">
						<div class="socialfeeds-help-v2">'.esc_html__('Enter the ID of any YouTube playlist to display its videos. You can find the Playlist ID in the URL of the playlist page.', 'socialfeeds').'</div>
					</div>';
				break;
				
			case 'search':
				echo '<div class="socialfeeds-input-group-v2">
						<label class="socialfeeds-label-v2">'.esc_html__('Search Term', 'socialfeeds') . '</label>
						<input type="text" id="socialfeeds-youtube-search-input" class="socialfeeds-input-v2" placeholder="'.esc_attr__('e.g., web development tutorials', 'socialfeeds').'" name="search_term" value="'.esc_attr($edit_input).'">
						<div class="socialfeeds-help-v2">'.esc_html__('Enter keywords to search YouTube. Results display the most relevant videos.', 'socialfeeds').'</div>
					</div>';
				break;
				
			case 'single-videos':
				echo '<div class="socialfeeds-input-group-v2">
						<label class="socialfeeds-label-v2">'.esc_html__('One or more Video IDs', 'socialfeeds').'</label>
						<input type="text" id="socialfeeds-youtube-single-videos-input" class="socialfeeds-input-v2" placeholder="' . esc_attr__('e.g., k0JPrxa0_7U, dzYP01CPC6E', 'socialfeeds') . '" name="video_ids" value="'.esc_attr($edit_input).'">
						<div class="socialfeeds-help-v2">'.esc_html__('Displays individual videos sorted in order here. Display multiple single videos by using a comma separated list.', 'socialfeeds') . '</div>
					</div>';
				break;
				
			case 'live-streams':
				echo '<div class="socialfeeds-input-group-v2">
						<label class="socialfeeds-label-v2">'.esc_html__( 'Live Stream URL (for currently live streams) or Channel Name (to display all past live streams)', 'socialfeeds').'</label>
						<input type="text" id="socialfeeds-youtube-live-channel-input" class="socialfeeds-input-v2" placeholder="'.esc_attr__('e.g., softaculous', 'socialfeeds').'" name="channel_id" value="'.esc_attr($edit_input).'">
						<div class="socialfeeds-help-v2">'.esc_html__('Displays a single upcoming or currently playing live streaming video from a channel.', 'socialfeeds').'</div>
					</div>';
				break;
		}
		
		// PREVIEW SECTION & NEXT BUTTON
		if(!empty($edit_id)){
			echo '<input type="hidden" name="edit_id" id="socialfeeds-edit-id" value="' . esc_attr($edit_id) . '">';
		}
		
		echo '<div class="socialfeeds-modal-actions" style="flex-direction:row; justify-content:flex-end; gap:20px; margin-top:40px;">
				<a href="'.esc_url(admin_url('admin.php?page=socialfeeds&action=create#youtube')).'" class="socialfeeds-btn-manage" style="width:auto; padding:10px 30px; border:none;">' . esc_html__('Back', 'socialfeeds') . '</a>
				<button type="button" class="socialfeeds-btn-sync" id="socialfeeds-next-btn" style="padding:10px 40px;">' . esc_html__('Next Step', 'socialfeeds') . ' <span class="dashicons dashicons-arrow-right-alt2" style="margin-top:2px;"></span></button>
			</div>
		</div>';
	}

	static function render_customize_tab($settings, $preview_url, $edit_id, $feed_type = 'channel', $feed = []){

		$display_id = $edit_id ? $edit_id : '';

		// Top Header Section (Title + Shortcode Controls)
		echo '<div class="socialfeeds-customize-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
			<div class="socialfeeds-customize-header-left">
				<h2 class="socialfeeds-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">' . esc_html__('Customize Display', 'socialfeeds') . '</h2>
				<p class="socialfeeds-desc" style="margin: 8px 0 0; color: #64748b; font-size: 13px;">' . esc_html__('Configure how your YouTube feed appears on your site.', 'socialfeeds') . '</p>
			</div>
			<div class="socialfeeds-customize-header-right" style="display: flex; align-items: center; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 5px 10px; border-radius: 12px;">
				<div style="margin-right: 15px;">
					<code id="socialfeeds-top-shortcode" style="background: transparent; border: none; font-size: 14px; color: #475569; padding: 0; font-family: monospace; letter-spacing: 0.5px;">'.esc_html('[socialfeeds id="' . $display_id . '" platform="youtube"]').'</code>
				</div>
				<button type="button" class="socialfeeds-copy-shortcode" data-shortcode="' . esc_attr('[socialfeeds id="' . $display_id . '" platform="youtube"]') . '" style="display: flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 16px; cursor: pointer; color: #374151; font-weight: 500; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
					<span class="dashicons dashicons-admin-page" style="font-size: 18px; width: 18px; height: 18px; color: #64748b;"></span>
					' . esc_html__('Copy', 'socialfeeds') . '
				</button>
				<button type="button" class="socialfeeds-fullscreen-btn" title="' . esc_attr__('Fullscreen', 'socialfeeds') . '" style="display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px; cursor: pointer; width: 38px; height: 38px; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
					<span class="dashicons dashicons-fullscreen-alt" style="font-size: 20px; width: 20px; height: 20px; color: #64748b;"></span>
				</button>
			</div>
		</div>';
		echo '<div class="socialfeeds-customize-columns">
			<div class="socialfeeds-customize-preview-column">
				<div class="socialfeeds-button-group" style="margin-bottom: 15px; margin-top: 0; display: flex; justify-content: flex-end; gap: 10px;">
					<a href="'.esc_url(admin_url('admin.php?page=socialfeeds#feeds')).'" class="button button-primary">'.esc_html__('All Feeds', 'socialfeeds').'</a>
					<button type="submit" name="socialfeeds_save" class="button button-primary" id="socialfeeds-save-btn">'.esc_html__('Save', 'socialfeeds').'</button>
				</div>

				<div class="socialfeeds-customize-preview">
					<div class="socialfeeds-preview-header-bar">
						<span class="socialfeeds-preview-label">'.esc_html__('LIVE PREVIEW', 'socialfeeds').'</span>
						<div class="socialfeeds-preview-device-toggles">
							<button type="button" class="socialfeeds-preview-device-btn active" data-width="100%" title="'.esc_attr__('Desktop', 'socialfeeds').'"><span class="dashicons dashicons-desktop"></span></button>
							<button type="button" class="socialfeeds-preview-device-btn" data-width="768" title="'.esc_attr__('Tablet', 'socialfeeds').'"><span class="dashicons dashicons-tablet"></span></button>
							<button type="button" class="socialfeeds-preview-device-btn" data-width="375" title="'.esc_attr__('Mobile', 'socialfeeds').'"><span class="dashicons dashicons-smartphone"></span></button>
						</div>
					</div>
					
					<div class="socialfeeds-preview-box-wrapper">
						<div class="socialfeeds-wizard-loader-overlay">
							<div class="socialfeeds-loader"></div>
						</div>
						<div class="socialfeeds-preview-header" id="socialfeeds-preview-header"></div>
						<div class="socialfeeds-preview-box" id="socialfeeds-preview-grid"></div>
						
						<div class="socialfeeds-buttons">
							<div class="socialfeeds-load-more-wrap">
								<button type="button" id="socialfeeds-load-more-btn" class="button">Load More</button>
							</div>

							<div class="socialfeeds-subscribe-wrap" style="display:none;">
								<a id="socialfeeds-subscribe-btn" class="button" target="_blank">Subscribe</a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="socialfeeds-customize-settings-sidebar">
				<div class="socialfeeds-sidebar-tabs">
					<button type="button" class="socialfeeds-sidebar-tab-btn active" data-target="socialfeeds-tab-general">'.esc_html__('General', 'socialfeeds').'</button>
					<button type="button" class="socialfeeds-sidebar-tab-btn" data-target="socialfeeds-tab-style">'.esc_html__('Style', 'socialfeeds').'</button>
				</div>

				<div class="socialfeeds-sidebar-content">
					<!-- GENERAL TAB -->
					<div id="socialfeeds-tab-general" class="socialfeeds-sidebar-tab-pane active">
						
						<div class="socialfeeds-sidebar-header">
							<h3><span class="dashicons dashicons-admin-settings"></span> '.esc_html__('General', 'socialfeeds').'</h3>
						</div>

						<!-- Accordions -->
						<div class="socialfeeds-accordion-wrapper">

						<!-- Layout Accordion -->
						<div class="socialfeeds-accordion-item">
							<div class="socialfeeds-accordion-header">
								<div class="socialfeeds-header-left">
									<div class="socialfeeds-icon-wrap" style="background: #eef2ff; color: #4f46e5;">
										<span class="dashicons dashicons-grid-view"></span>
									</div>
									<div class="socialfeeds-title-wrap">
										<span class="socialfeeds-sidebar-title">'.esc_html__('Layout', 'socialfeeds').'</span>
									</div>
								</div>
								<div class="socialfeeds-header-right">
									<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
								</div>
							</div>
							<div class="socialfeeds-accordion-body">
								<div class="socialfeeds-control-group" style="margin-top: 15px;">
									<label class="socialfeeds-control-label">'.esc_html__('LAYOUT TYPE', 'socialfeeds').'</label>
									<div class="socialfeeds-layout-selector">
										<label class="socialfeeds-layout-option">
											<input type="radio" name="youtube_display_style" id="socialfeeds-youtube-display-style-grid" value="grid" '.checked($settings['youtube_display_style'], 'grid', false).'>
											<div class="layout-box">
												<span class="dashicons dashicons-grid-view"></span>
												<span>'.esc_html__('Grid', 'socialfeeds').'</span>
											</div>
										</label>';
										do_action('socialfeeds_pro_youtube_layouts', $settings);
									echo'</div>
								</div>

								<div class="socialfeeds-control-group">
									<div class="flex-title">
										<label class="socialfeeds-control-label">'.esc_html__('COLUMNS', 'socialfeeds').'</label>
										<span class="socialfeeds-value-display" id="columns-display">'.esc_html($settings['youtube_grid_columns_desktop']).' Columns</span>
									</div>
									<div class="socialfeeds-range-slider">
										<span class="range-min">1</span>
										<input type="range" name="youtube_grid_columns_desktop" id="socialfeeds-youtube-grid-columns-desktop" min="1" max="6" step="1" value="' . esc_attr($settings['youtube_grid_columns_desktop']) . '">
										<span class="range-max">6</span>
									</div>
								</div>

								<div class="socialfeeds-control-group">
									<div class="flex-title">
										<label class="socialfeeds-control-label">'.esc_html__('MOBILE COLUMNS', 'socialfeeds').'</label>
										<span class="socialfeeds-value-display" id="columns-mobile-display">'.esc_html($settings['youtube_grid_columns_mobile']).' Columns</span>
									</div>
									<div class="socialfeeds-range-slider">
										<span class="range-min">1</span>
										<input type="range" name="youtube_grid_columns_mobile" id="socialfeeds-youtube-grid-columns-mobile" min="1" max="3" step="1" value="'.esc_attr($settings['youtube_grid_columns_mobile']).'">
										<span class="range-max">3</span>
									</div>
								</div>

								<div class="socialfeeds-control-group">
									<div class="flex-title">
										<label class="socialfeeds-control-label">'.esc_html__('SPACING', 'socialfeeds').'</label>
										<span class="socialfeeds-value-display" id="spacing-display">'.esc_html($settings['youtube_spacing']).'px</span>
									</div>
									<div class="socialfeeds-range-slider">
										<span class="range-min">0</span>
										<input type="range" name="youtube_spacing" id="socialfeeds-youtube-spacing" min="0" max="100" step="1" value="' . esc_attr($settings['youtube_spacing']) . '">
										<span class="range-max">100</span>
									</div>
								</div>

							</div>
						</div>

						<!-- Content Limits Accordion -->
						<div class="socialfeeds-accordion-item">
							<div class="socialfeeds-accordion-header">
								<div class="socialfeeds-header-left">
									<div class="socialfeeds-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
										<span class="dashicons dashicons-filter"></span>
									</div>
									<div class="socialfeeds-title-wrap">
										<span class="socialfeeds-sidebar-title">'.esc_html__('Content Limits', 'socialfeeds').'</span>
									</div>
								</div>
								<div class="socialfeeds-header-right">
									<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
								</div>
							</div>
							<div class="socialfeeds-accordion-body">
								<div class="socialfeeds-control-group" style="margin-top: 15px;">
									<div class="flex-title">
										<label class="socialfeeds-control-label">'.esc_html__('NUMBER OF VIDEOS', 'socialfeeds').'</label>
										<span class="socialfeeds-value-display">Max 200</span>
									</div>
									<input type="number" name="youtube_videos_per_page" id="socialfeeds-youtube-videos-per-page" value="' . esc_attr($settings['youtube_videos_per_page']) . '" min="1" max="200" class="socialfeeds-input-full">
								</div>
							</div>
						</div>

							<!-- Video Elements Accordion -->
							<div class="socialfeeds-accordion-item">
								<div class="socialfeeds-accordion-header">
									<div class="socialfeeds-header-left">
										<div class="socialfeeds-icon-wrap" style="background: #f0fdf4; color: #16a34a;">
											<span class="dashicons dashicons-video-alt3"></span>
										</div>
										<div class="socialfeeds-title-wrap">
											<span class="socialfeeds-sidebar-title">'.esc_html__('Video Elements', 'socialfeeds').'</span>
										</div>
									</div>
									<div class="socialfeeds-header-right">
										<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
									</div>
								</div>
								<div class="socialfeeds-accordion-body">
									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Show Title', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Display video title', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_show_title" id="socialfeeds-youtube-show-title" value="1" ' . checked($settings['youtube_show_title'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>

									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Show Description', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Show video snippet', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_show_desc" id="socialfeeds-youtube-show-desc" value="1" ' . checked($settings['youtube_show_desc'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>

									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Show Play Icon', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Overlay on thumbnail', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_show_play_icon" id="socialfeeds-youtube-show-play-icon" value="1" ' . checked($settings['youtube_show_play_icon'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>
									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Lazy Load', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Lazy load thumbnails', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_lazy_load" id="socialfeeds-youtube-lazy-load" value="1" ' . checked(isset($settings['youtube_lazy_load']) ? $settings['youtube_lazy_load'] : 1, 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>';

									do_action('socialfeeds_video_elements', $settings);

								echo'</div>
							</div>

							<!-- Header Accordion -->
							<div class="socialfeeds-accordion-item">
								<div class="socialfeeds-accordion-header">
									<div class="socialfeeds-header-left">
										<div class="socialfeeds-icon-wrap" style="background: #f3e8ff; color: #7e22ce;">
											<span class="dashicons dashicons-align-center"></span>
										</div>
										<div class="socialfeeds-title-wrap">
											<span class="socialfeeds-sidebar-title">'.esc_html__('Header', 'socialfeeds').'</span>
										</div>
									</div>
									<div class="socialfeeds-header-right">
										<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
									</div>
								</div>
								<div class="socialfeeds-accordion-body">
									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Enable Header', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Show feed header', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_header_enabled" id="socialfeeds-youtube-header-enabled" value="1" ' . checked($settings['youtube_header_enabled'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>

									<div id="socialfeeds-header-options" class="socialfeeds-nested-options" style="' . (empty($settings['youtube_header_enabled']) ? 'display:none;' : '') . '">
										<div class="socialfeeds-toggle-row">
											<div class="socialfeeds-toggle-info">
												<div class="socialfeeds-toggle-title">'.esc_html__('Channel Name', 'socialfeeds').'</div>
											</div>
											<div class="socialfeeds-toggle-input">
												<label class="socialfeeds-switch">
													<input type="checkbox" name="youtube_header_show_channel_name" id="socialfeeds-youtube-header-show-channel-name" value="1" ' . checked(isset($settings['youtube_header_show_channel_name']) ? $settings['youtube_header_show_channel_name'] : 1, 1, false) . '>
													<span class="socialfeeds-slider"></span>
												</label>
											</div>
										</div>

										<div class="socialfeeds-toggle-row">
											<div class="socialfeeds-toggle-info">
												<div class="socialfeeds-toggle-title">'.esc_html__('Channel Logo', 'socialfeeds').'</div>
											</div>
											<div class="socialfeeds-toggle-input">
												<label class="socialfeeds-switch">
													<input type="checkbox" name="youtube_header_show_logo" id="socialfeeds-youtube-header-show-logo" value="1" ' . checked(isset($settings['youtube_header_show_logo']) ? $settings['youtube_header_show_logo'] : 1, 1, false) . '>
													<span class="socialfeeds-slider"></span>
												</label>
											</div>
										</div>

										<div class="socialfeeds-toggle-row">
											<div class="socialfeeds-toggle-info">
												<div class="socialfeeds-toggle-title">'.esc_html__('Channel Subscribers', 'socialfeeds').'</div>
											</div>
											<div class="socialfeeds-toggle-input">
												<label class="socialfeeds-switch">
													<input type="checkbox" name="youtube_header_show_subscribers" id="socialfeeds-youtube-header-show-subscribers" value="1" ' . checked(isset($settings['youtube_header_show_subscribers']) ? $settings['youtube_header_show_subscribers'] : 0, 1, false) . '>
													<span class="socialfeeds-slider"></span>
												</label>
											</div>
										</div>

										<div class="socialfeeds-toggle-row">
											<div class="socialfeeds-toggle-info">
												<div class="socialfeeds-toggle-title">'.esc_html__('Channel Description', 'socialfeeds').'</div>
											</div>
											<div class="socialfeeds-toggle-input">
												<label class="socialfeeds-switch">
													<input type="checkbox" name="youtube_header_show_description" id="socialfeeds-youtube-header-show-description" value="1" ' . checked(isset($settings['youtube_header_show_description']) ? $settings['youtube_header_show_description'] : 0, 1, false) . '>
													<span class="socialfeeds-slider"></span>
												</label>
											</div>
										</div>

										<div class="socialfeeds-control-group" style="margin-top:10px;">
											<label class="socialfeeds-control-label">'.esc_html__('Custom Channel Description', 'socialfeeds').'</label>
											<textarea id="socialfeeds-youtube-header-text" name="youtube_header_text" class="socialfeeds-input-full" placeholder="' . esc_attr__('Optional text...', 'socialfeeds') . '">'. esc_textarea(isset($settings['youtube_header_text']) ? $settings['youtube_header_text'] : '') .'</textarea>
										</div>
										<div class="socialfeeds-control-group" style="margin-top:15px; border-top: 1px solid #eee; padding-top: 15px;">
											<div class="socialfeeds-toggle-row">
												<div class="socialfeeds-toggle-info">
													<div class="socialfeeds-toggle-title">'.esc_html__('Show Banner', 'socialfeeds').'</div>
												</div>
												<div class="socialfeeds-toggle-input">
													<label class="socialfeeds-switch">
														<input type="checkbox" name="youtube_header_show_banner" id="socialfeeds-youtube-header-show-banner" value="1" ' . checked(isset($settings['youtube_header_show_banner']) ? $settings['youtube_header_show_banner'] : 0, 1, false) . '>
														<span class="socialfeeds-slider"></span>
													</label>
												</div>
											</div>
										</div>
										<div class="socialfeeds-control-group" id="socialfeeds-banner-url-group" style="margin-top:15px; border-top: 1px solid #eee; padding-top: 15px;">
											<label class="socialfeeds-control-label">' . esc_html__('CUSTOM BANNER IMAGE', 'socialfeeds') . '</label>
											<div style="display:flex; gap:8px;">
												<input type="text" id="socialfeeds-youtube-header-banner-url" name="youtube_header_banner_url" value="' . esc_attr(isset($settings['youtube_header_banner_url']) ? $settings['youtube_header_banner_url'] : '') . '" class="socialfeeds-input-full" placeholder="'.esc_attr__('Leave empty to fetch from channel...', 'socialfeeds').'">
												<button type="button" class="button socialfeeds-pick-image" data-target="#socialfeeds-youtube-header-banner-url">'.esc_html__('Select', 'socialfeeds').'</button>
											</div>
											<p class="socialfeeds-control-desc">' . esc_html__('Provide a custom banner URL or leave empty to use the channel\'s default banner.', 'socialfeeds') . '</p>
										</div>
									</div>
								</div>
							</div>

							<!-- Subscribe Button Accordion -->
							<div class="socialfeeds-accordion-item">
								<div class="socialfeeds-accordion-header">
									<div class="socialfeeds-header-left">
										<div class="socialfeeds-icon-wrap" style="background: #fee2e2; color: #dc2626;">
											<span class="dashicons dashicons-thumbs-up"></span>
										</div>
										<div class="socialfeeds-title-wrap">
											<span class="socialfeeds-sidebar-title">'.esc_html__('Subscribe Button', 'socialfeeds').'</span>
										</div>
									</div>
									<div class="socialfeeds-header-right">
										<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
									</div>
								</div>
								<div class="socialfeeds-accordion-body">
									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Enable Button', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Show subscribe link', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_subscribe_button_enabled" id="socialfeeds-youtube-subscribe-button-enabled" value="1" ' . checked($settings['youtube_subscribe_button_enabled'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>

									<div id="socialfeeds-subscribe-settings" class="socialfeeds-nested-options">
										<div class="socialfeeds-control-group">
											<label class="socialfeeds-control-label">' . esc_html__('BUTTON TEXT', 'socialfeeds') . '</label>
											<input type="text" name="youtube_subscribe_text" id="socialfeeds-youtube-subscribe-text" value="' . esc_attr(isset($settings['youtube_subscribe_text']) ? $settings['youtube_subscribe_text'] : 'Subscribe') . '" class="socialfeeds-input-full">
										</div>
									</div>

									<div class="socialfeeds-control-group" style="margin-top:15px;">
										<label class="socialfeeds-control-label">'.esc_html__('Colors', 'socialfeeds').'</label>
										<div style="display:flex; gap:10px; margin-top:5px;">
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">BG</span>
												<input type="color" name="youtube_subscribe_bg_color" id="socialfeeds-youtube-subscribe-bg-color" value="' . esc_attr(isset($settings['youtube_subscribe_bg_color']) ? $settings['youtube_subscribe_bg_color'] : '#FF0000') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">Text</span>
												<input type="color" name="youtube_subscribe_text_color" id="socialfeeds-youtube-subscribe-text-color" value="' . esc_attr(isset($settings['youtube_subscribe_text_color']) ? $settings['youtube_subscribe_text_color'] : '#FFFFFF') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">Hover</span>
												<input type="color" name="youtube_subscribe_hover_color" id="socialfeeds-youtube-subscribe-hover-color" value="' . esc_attr(isset($settings['youtube_subscribe_hover_color']) ? $settings['youtube_subscribe_hover_color'] : '#CC0000') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Load More Button Accordion -->
							<div class="socialfeeds-accordion-item">
								<div class="socialfeeds-accordion-header">
									<div class="socialfeeds-header-left">
										<div class="socialfeeds-icon-wrap" style="background: #fef3c7; color: #d97706;">
											<span class="dashicons dashicons-plus-alt"></span>
										</div>
										<div class="socialfeeds-title-wrap">
											<span class="socialfeeds-sidebar-title">'.esc_html__('Load More', 'socialfeeds').'</span>
										</div>
									</div>
									<div class="socialfeeds-header-right">
										<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
									</div>
								</div>
								<div class="socialfeeds-accordion-body">
									<div class="socialfeeds-toggle-row">
										<div class="socialfeeds-toggle-info">
											<div class="socialfeeds-toggle-title">'.esc_html__('Enable Load More', 'socialfeeds').'</div>
											<div class="socialfeeds-toggle-desc">'.esc_html__('Infinite scroll effect', 'socialfeeds').'</div>
										</div>
										<div class="socialfeeds-toggle-input">
											<label class="socialfeeds-switch">
												<input type="checkbox" name="youtube_load_more_enabled" id="socialfeeds-youtube-load-more-enabled" value="1" ' . checked($settings['youtube_load_more_enabled'], 1, false) . '>
												<span class="socialfeeds-slider"></span>
											</label>
										</div>
									</div>

									<div class="socialfeeds-control-group" style="margin-top:15px;">
										<label class="socialfeeds-control-label">' . esc_html__('BUTTON TEXT', 'socialfeeds') . '</label>
										<input type="text" name="youtube_load_more_text" id="socialfeeds-youtube-load-more-text" value="' . esc_attr(isset($settings['youtube_load_more_text']) ? $settings['youtube_load_more_text'] : 'Load More') . '" class="socialfeeds-input-full">
									</div>
									<div class="socialfeeds-control-group">
										<label class="socialfeeds-control-label">' . esc_html__('VIDEOS TO LOAD', 'socialfeeds') . '</label>
										<input type="number" name="youtube_load_more_count" id="socialfeeds-youtube-load-more-count" value="' . esc_attr(isset($settings['youtube_load_more_count']) ? $settings['youtube_load_more_count'] : 9) . '" class="socialfeeds-input-full" min="1" max="50">
									</div>
									
									<div class="socialfeeds-control-group" style="margin-top:15px;">
										<label class="socialfeeds-control-label">'.esc_html__('Colors', 'socialfeeds').'</label>
										<div style="display:flex; gap:10px; margin-top:5px;">
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">BG</span>
												<input type="color" name="youtube_load_more_bg_color" id="socialfeeds-youtube-load-more-bg-color" value="' . esc_attr(isset($settings['youtube_load_more_bg_color']) ? $settings['youtube_load_more_bg_color'] : '#350ae1') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">Text</span>
												<input type="color" name="youtube_load_more_text_color" id="socialfeeds-youtube-load-more-text-color" value="' . esc_attr(isset($settings['youtube_load_more_text_color']) ? $settings['youtube_load_more_text_color'] : '#FFFFFF') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
											<div style="flex:1;">
												<span style="font-size:11px; display:block; margin-bottom:3px;">Hover</span>
												<input type="color" name="youtube_load_more_hover_color" id="socialfeeds-youtube-load-more-hover-color" value="' . esc_attr(isset($settings['youtube_load_more_hover_color']) ? $settings['youtube_load_more_hover_color'] : '#4608e4') . '" class="socialfeeds-color-input" style="width:100% !important;">
											</div>
										</div>
									</div>						
								</div>
							</div>

						</div> <!-- End Accordion Wrapper -->
					</div> <!-- END GENERAL TAB -->



					<!-- STYLE TAB -->
					<div id="socialfeeds-tab-style" class="socialfeeds-sidebar-tab-pane">
						<div class="socialfeeds-sidebar-header">
							<h3>'.esc_html__('Style Settings', 'socialfeeds').'</h3>
						</div>
						
						<div class="socialfeeds-accordion-wrapper">
							<!-- Global Colors Accordion -->
							<div class="socialfeeds-accordion-item active">
								<div class="socialfeeds-accordion-header">
									<div class="socialfeeds-header-left">
										<div class="socialfeeds-icon-wrap" style="background: #f0f6fc; color: #2271b1;">
											<span class="accordion-icon dashicons dashicons-admin-appearance"></span>
										</div>
										<div class="socialfeeds-title-wrap">
											<span class="socialfeeds-sidebar-title">'.esc_html__('Color Scheme', 'socialfeeds').'</span>
										</div>
									</div>
									<div class="socialfeeds-header-right">
										<span class="dashicons dashicons-arrow-down-alt2 socialfeeds-chevron"></span>
									</div>
								</div>
								<div class="socialfeeds-accordion-body" style="display:block;">
									<div class="socialfeeds-control-group" style="margin-top:15px;">
										<label class="socialfeeds-control-label">'.esc_html__('COLOR SCHEME', 'socialfeeds').'</label>
										<select name="youtube_color_scheme" id="socialfeeds-youtube-color-scheme" class="socialfeeds-select-full">
											<option value="light"' . selected($settings['youtube_color_scheme'], 'light', false) . '>' . esc_html__('Light', 'socialfeeds') . '</option>
											<option value="dark"' . selected($settings['youtube_color_scheme'], 'dark', false) . '>' . esc_html__('Dark', 'socialfeeds') . '</option>
											<option value="custom"' . selected($settings['youtube_color_scheme'], 'custom', false) . '>' . esc_html__('Custom', 'socialfeeds') . '</option>
										</select>
									</div>
									<div class="socialfeeds-control-group" id="socialfeeds-custom-color-group" style="' . ('custom' !== $settings['youtube_color_scheme'] ? 'display:none;' : '') . '">
										<label class="socialfeeds-control-label">' . esc_html__('Custom BG Color', 'socialfeeds') . '</label>
										<input type="color" name="youtube_custom_color" id="socialfeeds-youtube-custom-color" value="' . esc_attr($settings['youtube_custom_color']) . '" class="socialfeeds-color-input">
									</div>
								</div>
							</div>
						</div>
					</div>

				</div> <!-- End Sidebar Content -->
			</div> <!-- End Sidebar -->

		</div>';
	}

	static function sort_by_id($a, $b){
		$id_a = isset($a['id']) ? intval($a['id']) : 0;
		$id_b = isset($b['id']) ? intval($b['id']) : 0;

		return $id_a - $id_b;
	}

}
