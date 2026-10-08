<?php

namespace ShareMyPostPro\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class UI {

	static function append_custom_cta($html, $post_id, $is_floating, $settings) {
		if(empty($settings['custom_cta_enabled']) || empty($settings['custom_cta_text'])){
			return $html;
		}

		$cta_text = wp_kses_post($settings['custom_cta_text']);
		if(empty($cta_text)){
			return $html;
		}

		$cta = '<div class="sharemypost-pro-custom-cta"><div class="sharemypost-pro-custom-cta-text">' . $cta_text . '</div></div>';
		return $html . $cta;
	}

	static function render_inline_content_settings($settings, $opt) {
		
		echo '<div class="sharemypost-card">
			<h3><span class="dashicons dashicons-plus-alt"></span> ' . esc_html__('Extras', 'sharemypost-pro') . '</h3>
			
			<!-- Universal Share Button Toggle -->
			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Universal Share Button', 'sharemypost-pro') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" id="sharemypost_universal_toggle" name="' . esc_attr($opt) . '[inline_universal_enabled]" value="1" ' . checked(1, $settings['inline_universal_enabled'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>

			<!-- Universal Custom Color Input Row -->
			<div class="sharemypost-field-row" id="sharemypost_universal_color_container">
				<span class="sharemypost-field-label">' . esc_html__('Universal Button Color', 'sharemypost-pro') . '</span>
				<input class="sharemypost-preview-input" type="color" name="' . esc_attr($opt) . '[inline_universal_color]" value="' . esc_attr($settings['inline_universal_color']) . '" />
			</div>
		</div>';
	}

	static function render_floating_bar_settings() {
		if(!class_exists('\ShareMyPost\Util')){
			return;
		}
		$settings = \ShareMyPost\Util::get_settings();
		$opt = 'sharemypost_settings';
		echo '<form method="post" action="options.php" class="sharemypost-settings-form">
		<input type="hidden" name="sharemypost_settings[section]" value="floating" />';
		settings_fields('sharemypost_settings_group');

		echo '<div class="sharemypost-wrap">
		<div class="sharemypost-hero-card">
			<div class="sharemypost-hero-info">
				<h2>' . esc_html__('Floating Bar', 'sharemypost-pro') . '</h2>
				<p>' . esc_html__('Configure the sticky social bar on the side of your pages.', 'sharemypost-pro') . '</p>
			</div>
			<div class="sharemypost-hero-action">
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_settings[floating_enabled]" value="1" ' . checked(1, $settings['floating_enabled'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
		</div>';

		self::render_floating_network_selection_settings($settings, $opt);

		echo '<div class="sharemypost-dashboard-grid">
			<div class="sharemypost-card">
				<h3><span class="dashicons dashicons-visibility"></span> ' . esc_html__('Display Rules', 'sharemypost-pro') . '</h3>

				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Bar Position', 'sharemypost-pro') . '</span>
					<select class="sharemypost-large-input" name="sharemypost_settings[floating_position]">
						<option value="left" ' . selected($settings['floating_position'], 'left', false) . '>' . esc_html__('Left side', 'sharemypost-pro') . '</option>
						<option value="right" ' . selected($settings['floating_position'], 'right', false) . '>' . esc_html__('Right side', 'sharemypost-pro') . '</option>
					</select>
				</div>

				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Display on Post Types', 'sharemypost-pro') . '</span>
					<div class="sharemypost-pill-group">';

		$post_types = get_post_types(['public' => true], 'objects');
		unset($post_types['attachment']);
		$ordered_types = [];
		$core_slugs = ['post', 'page'];
		foreach($core_slugs as $slug){
			if(isset($post_types[$slug])){
				$ordered_types[$slug] = $post_types[$slug];
				unset($post_types[$slug]);
			}
		}
		foreach($post_types as $pt => $obj){
			$ordered_types[$pt] = $obj;
		}

		foreach($ordered_types as $pt => $obj){
			$label = isset($obj->labels->singular_name) ? $obj->labels->singular_name : ucfirst($pt);
			$is_checked = in_array($pt, (array) $settings['floating_post_types'], true);
			echo '<label class="sharemypost-pill">
				<input type="checkbox" name="sharemypost_settings[floating_post_types][]" value="' . esc_attr($pt) . '" ' . checked($is_checked, true, false) . ' />
				<span>' . esc_html($label) . '</span>
			</label>';
		}

		echo '</div>
				</div>

				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Hide on Mobile (< px)', 'sharemypost-pro') . '</span>
					<input type="number" class="sharemypost-large-input" name="sharemypost_settings[floating_mobile_breakpoint]" value="' . esc_attr($settings['floating_mobile_breakpoint']) . '" style="width:80px;" />
				</div>
			</div>';

		do_action('sharemypost_floating_style_settings', $settings, $opt);

		echo '<div class="sharemypost-card">
				<h3><span class="dashicons dashicons-admin-appearance"></span> ' . esc_html__('Floating Button Appearance', 'sharemypost-pro') . '</h3>
				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Show Labels', 'sharemypost-pro') . '</span>
					<select class="sharemypost-large-input" name="sharemypost_settings[floating_show_labels]">
						<option value="icon_only" ' . selected($settings['floating_show_labels'], 'icon_only', false) . '>' . esc_html__('Icon Only', 'sharemypost-pro') . '</option>
						<option value="label_only" ' . selected($settings['floating_show_labels'], 'label_only', false) . '>' . esc_html__('Label Only', 'sharemypost-pro') . '</option>
						<option value="both" ' . selected($settings['floating_show_labels'], 'both', false) . '>' . esc_html__('Icon + Label', 'sharemypost-pro') . '</option>
					</select>
				</div>
				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Button Colors', 'sharemypost-pro') . '</span>
					<select class="sharemypost-large-input" id="sharemypost_floating_color_type_select" name="sharemypost_settings[floating_button_color_type]">
						<option value="original" ' . selected($settings['floating_button_color_type'], 'original', false) . '>' . esc_html__('Original', 'sharemypost-pro') . '</option>
						<option value="custom" ' . selected($settings['floating_button_color_type'], 'custom', false) . '>' . esc_html__('Custom Color', 'sharemypost-pro') . '</option>
					</select>
				</div>
				<div class="sharemypost-field-row" id="sharemypost_floating_custom_color_container" style="' . ($settings['floating_button_color_type'] === 'original' ? 'display:none;' : '') . '">
					<span class="sharemypost-field-label">' . esc_html__('Select Custom Color', 'sharemypost-pro') . '</span>
					<input class="sharemypost-preview-input" type="color" name="sharemypost_settings[floating_button_color]" value="' . esc_attr($settings['floating_button_color']) . '" />
				</div>
			</div>';

		self::render_floating_extras_settings($settings, $opt);

		echo '</div>';

		echo '<div class="sharemypost-sticky-footer">
				<div class="sharemypost-footer-content">';
		submit_button(__('Save Settings', 'sharemypost-pro'), 'button-primary button-large', 'submit', false);
		echo '  </div>
		</div>
		</div>
		</form>';
	}

	static function render_floating_extras_settings($settings, $opt) {
		echo '<div class="sharemypost-card">
			<h3><span class="dashicons dashicons-plus-alt"></span> ' . esc_html__('Extras', 'sharemypost-pro') . '</h3>

			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Universal Share Button', 'sharemypost-pro') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" id="sharemypost_universal_toggle" name="' . esc_attr($opt) . '[floating_universal_enabled]" value="1" ' . checked(1, $settings['floating_universal_enabled'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>

			<div class="sharemypost-field-row" id="sharemypost_universal_color_container">
				<span class="sharemypost-field-label">' . esc_html__('Universal Button Color', 'sharemypost-pro') . '</span>
				<input class="sharemypost-preview-input" type="color" name="' . esc_attr($opt) . '[floating_universal_color]" value="' . esc_attr($settings['floating_universal_color']) . '" />
			</div>
		</div>';
	}

	static function render_floating_network_selection_settings($settings, $opt){
		if(!class_exists('\ShareMyPost\Helpers')){
			return;
		}
		$enabled_list = (array) $settings['floating_enabled_networks'];
		$order = \ShareMyPost\Helpers::normalize_network_order($settings['floating_network_order']);
		$floating_sortable = apply_filters('sharemypost_network_grid_sortable', false) ? ' sharemypost-core-sortable' : '';
		$floating_note = apply_filters('sharemypost_network_grid_note', esc_html__('Choose which networks should appear in floating share bars. Drag-and-drop reordering is available in ShareMyPost Pro.', 'sharemypost-pro'));

		echo '<div class="sharemypost-card sharemypost-network-selection-card" style="margin-top: 20px;">
			<h3><span class="dashicons dashicons-admin-site"></span> ' . esc_html__('Floating Networks', 'sharemypost-pro') . '</h3>
			<p style="font-size: 13px; color: #666; margin-bottom: 15px;">' . esc_html($floating_note) . '</p>
			<div class="sharemypost-network-grid' . esc_attr($floating_sortable) . ' sharemypost-floating-network-grid" data-order-input="#sharemypost-floating-network-order">';

		static $allowed_svg = null;
		if(null === $allowed_svg){
			$allowed_svg = [
				'svg'  => ['viewbox' => true, 'viewBox' => true, 'xmlns' => true, 'fill' => true, 'style' => true],
				'path' => ['d' => true, 'fill' => true, 'style' => true],
			];
		}

		$floating_networks = \ShareMyPost\Helpers::get_networks_ordered('floating');
		$floating_ai_keys = \ShareMyPostPro\Shortcode::get_ai_network_keys();
		$floating_networks = array_diff_key($floating_networks, array_flip($floating_ai_keys));

		foreach($floating_networks as $network => $data){
			$is_checked = in_array($network, $enabled_list, true);
			$active_class = $is_checked ? 'is-active' : '';

			echo '<label class="sharemypost-network-item ' . esc_attr($active_class) . '" data-network="' . esc_attr($network) . '">
				<input type="checkbox" class="sharemypost-network-checkbox" style="display:none;" name="' . esc_attr($opt) . '[floating_enabled_networks][]" value="' . esc_attr($network) . '" ' . checked($is_checked, true, false) . ' />
				<div class="sharemypost-network-tile sharemypost-share-button sharemypost-network-' . esc_attr($network) . ' sharemypost-style-minimal">
					<span class="sharemypost-icon">' . wp_kses($data['icon'], $allowed_svg) . '</span>
					<span class="sharemypost-label">' . esc_html($data['label']) . '</span>
				</div>
			</label>';
		}

		echo '</div>
		<input type="hidden" id="sharemypost-floating-network-order" class="sharemypost-order-input" name="' . esc_attr($opt) . '[floating_network_order]" value="' . esc_attr(implode(',', $order)) . '" />
		</div>';
	}

	static function click_to_tweet_settings() {
		$settings = \ShareMyPostPro\Shortcode::get_tweet_settings();
		
		echo '<div class="sharemypost-wrap">
		<div class="sharemypost-hero-card">
			<div class="sharemypost-hero-info">
				<h2>' . esc_html__('Click to X Settings', 'sharemypost-pro') . '</h2>
				<p>' . esc_html__('Configure how your Click to X boxes appear and behave. Use the [sharemypost_click_to_x] shortcode or the Click to X block in the editor.', 'sharemypost-pro') . '</p>
			</div>
		</div>';

		echo '<form method="post" action="options.php" style="display:flex;flex-direction:column;gap:20px;">';
		settings_fields('sharemypost_pro_tweet');
		echo '<div class="sharemypost-click-to-tweet-grid">';
		echo '<div class="sharemypost-card sharemypost-card-type2">
            <h3><span class="dashicons dashicons-admin-appearance"></span> ' . esc_html__('Default Theme', 'sharemypost-pro') . '</h3>
            <div class="sharemypost-field-row">
                <span class="sharemypost-field-label">' . esc_html__('Select Theme', 'sharemypost-pro') . '</span>
                <select name="sharemypost_pro_tweet_settings[tweet_theme]">
                    <option value="light" ' . selected($settings['tweet_theme'], 'light', false) . '>' . esc_html__('Light Theme', 'sharemypost-pro') . '</option>
                    <option value="dark" ' . selected($settings['tweet_theme'], 'dark', false) . '>' . esc_html__('Dark Theme', 'sharemypost-pro') . '</option>
                    <option value="gray" ' . selected($settings['tweet_theme'], 'gray', false) . '>' . esc_html__('Gray Theme', 'sharemypost-pro') . '</option>
                </select>
            </div>
        </div>';

		echo '<div class="sharemypost-card sharemypost-card-type2">
					<h3><span class="dashicons dashicons-admin-customizer"></span> ' . esc_html__('Accent Color', 'sharemypost-pro') . '</h3>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Choose Accent Color', 'sharemypost-pro') . '</span>
						<input type="color" name="sharemypost_pro_tweet_settings[tweet_accent_color]" value="' . esc_attr($settings['tweet_accent_color']) . '" />
					</div>
				</div>';

		echo '<div class="sharemypost-card sharemypost-card-type2">
					<h3><span class="dashicons dashicons-megaphone"></span> ' . esc_html__('Call to Action', 'sharemypost-pro') . '</h3>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('CTA Text', 'sharemypost-pro') . '</span>
						<input type="text" class="sharemypost-large-input" name="sharemypost_pro_tweet_settings[tweet_cta_text]" value="' . esc_attr($settings['tweet_cta_text']) . '" />
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('CTA Position', 'sharemypost-pro') . '</span>
						<select name="sharemypost_pro_tweet_settings[tweet_cta_position]">
							<option value="right" ' . selected($settings['tweet_cta_position'], 'right', false) . '>' . esc_html__('Right Position', 'sharemypost-pro') . '</option>
							<option value="left" ' . selected($settings['tweet_cta_position'], 'left', false) . '>' . esc_html__('Left Position', 'sharemypost-pro') . '</option>
						</select>
					</div>
				</div>';

		echo '<div class="sharemypost-card sharemypost-card-type2">
			<h3><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor" style="width:20px;height:20px;vertical-align:middle"><path d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z"/></svg> ' . esc_html__('Content Options', 'sharemypost-pro') . '</h3>
			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Remove URL  (Excludes post URL)', 'sharemypost-pro') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_pro_tweet_settings[tweet_remove_url]" value="1" ' . checked(1, $settings['tweet_remove_url'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Remove Username  (Excludes Username)', 'sharemypost-pro') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_pro_tweet_settings[tweet_remove_username]" value="1" ' . checked(1, $settings['tweet_remove_username'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Hide Hashtags  (Excludes Hashtags)', 'sharemypost-pro') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_pro_tweet_settings[tweet_hide_hashtags]" value="1" ' . checked(1, $settings['tweet_hide_hashtags'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
		</div>';
		echo '</div>';

		echo '<div class="sharemypost-card" style="background-color: #f9f9f9; border-left: 4px solid #1f75e1;">
			<h3><span class="dashicons dashicons-editor-help"></span> ' . esc_html__('How to Use', 'sharemypost-pro') . '</h3>
			<p><strong>' . esc_html__('Shortcode:', 'sharemypost-pro') . '</strong></p>
			<code style="display: block; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 3px; margin-bottom: 15px;">[sharemypost_click_to_x tweet="Example tweet content"]</code>
			
			<p><strong>' . esc_html__('Parameters:', 'sharemypost-pro') . '</strong></p>
			<ul style="margin-left: 20px;">
				<li><code>tweet</code> - ' . esc_html__('The text to tweet (required)', 'sharemypost-pro') . '</li>
				<li><code>cta_text</code> - ' . esc_html__('Button text (default: Click to Tweet)', 'sharemypost-pro') . '</li>
				<li><code>cta_position</code> - ' . esc_html__('right or left', 'sharemypost-pro') . '</li>
				<li><code>remove_url</code> - ' . esc_html__('true or false', 'sharemypost-pro') . '</li>
				<li><code>remove_username</code> - ' . esc_html__('true or false', 'sharemypost-pro') . '</li>
				<li><code>hide_hashtags</code> - ' . esc_html__('true or false', 'sharemypost-pro') . '</li>
				<li><code>accent_color</code> - ' . esc_html__('Hex color code (e.g., #FF0000)', 'sharemypost-pro') . '</li>
			</ul>
		</div>';

		echo '<div class="sharemypost-sticky-footer">
				<div class="sharemypost-footer-content">';
					submit_button(__('Save Changes', 'sharemypost-pro'), 'button-primary button-large', 'submit', false);
		echo '  </div>
			</div>';

		echo '</form>
		</div>';
	}

	static function render_configuration_settings() {
        $settings = \ShareMyPost\util::get_settings();
        echo '<form method="post" action="options.php" class="sharemypost-settings-form">
        <input type="hidden" name="sharemypost_settings[section]" value="configuration" />';
        settings_fields('sharemypost_settings_group');

        echo '<div class="sharemypost-wrap">
        <div class="sharemypost-card" style="margin-top: 24px;">
            
            <div class="sm-config-header-inline">
                <h2><span class="dashicons dashicons-admin-generic"></span> ' . esc_html__('Global Configuration', 'sharemypost-pro') . '</h2>
                <label class="sharemypost-toggle">
                    <input type="checkbox" name="sharemypost_settings[utm_tracking]" value="1" ' . checked(1, $settings['utm_tracking'], false) . ' />
                    <span class="sharemypost-slider"></span>
                </label>
            </div>

            <div class="sm-config-grid-2x2">
                <div class="sm-config-field-row">
                    <span class="sm-config-field-label">' . esc_html__('UTM Source', 'sharemypost-pro') . '</span>
                    <div class="sm-config-field-input">
                        <input type="text" class="sharemypost-large-input" name="sharemypost_settings[utm_source]" value="' . esc_attr($settings['utm_source']) . '" />
                        <p class="sm-config-description">' . esc_html__('Use {site_name} as placeholder', 'sharemypost-pro') . '</p>
                    </div>
                </div>

                <div class="sm-config-field-row">
                    <span class="sm-config-field-label">' . esc_html__('UTM Medium', 'sharemypost-pro') . '</span>
                    <div class="sm-config-field-input">
                        <input type="text" class="sharemypost-large-input" name="sharemypost_settings[utm_medium]" value="' . esc_attr($settings['utm_medium']) . '" />
                        <p class="sm-config-description">' . esc_html__('Usually use "social"', 'sharemypost-pro') . '</p>
                    </div>
                </div>

                <div class="sm-config-field-row">
                    <span class="sm-config-field-label">' . esc_html__('UTM Campaign', 'sharemypost-pro') . '</span>
                    <div class="sm-config-field-input">
                        <input type="text" class="sharemypost-large-input" name="sharemypost_settings[utm_campaign]" value="' . esc_attr($settings['utm_campaign']) . '" />
                        <p class="sm-config-description">' . esc_html__('Use {post_title} as placeholder', 'sharemypost-pro') . '</p>
                    </div>
                </div>

                <div class="sm-config-field-row">
                    <span class="sm-config-field-label">' . esc_html__('UTM Content', 'sharemypost-pro') . '</span>
                    <div class="sm-config-field-input">
                        <input type="text" class="sharemypost-large-input" name="sharemypost_settings[utm_content]" value="' . esc_attr($settings['utm_content']) . '" />
                        <p class="sm-config-description">' . esc_html__('Use {network} as placeholder', 'sharemypost-pro') . '</p>
                    </div>
                </div>
            </div>
        </div>';

    echo '<div class="sharemypost-card" style="margin-top: 20px;">
            <div class="sm-config-header-inline">
                <h2><span class="dashicons dashicons-chart-area"></span> ' . esc_html__('Google Analytics 4 (GA4)', 'sharemypost-pro') . '</h2>
                <label class="sharemypost-toggle">
                    <input type="checkbox" name="sharemypost_settings[ga4_enabled]" value="1" ' . checked(1, $settings['ga4_enabled'], false) . ' />
                    <span class="sharemypost-slider"></span>
                </label>
            </div>

            <div class="sm-config-field-row">
                <span class="sm-config-field-label">' . esc_html__('GA4 Measurement ID', 'sharemypost-pro') . '</span>
                <div class="sm-config-field-input">
                    <input type="text" class="sharemypost-large-input" name="sharemypost_settings[ga4_measurement_id]" value="' . esc_attr($settings['ga4_measurement_id']) . '" class="regular-text" placeholder="G-XXXXXXXXXX" />
                    <p class="sm-config-description">' . esc_html__('Your Google Analytics 4 Measurement ID (format: G-XXXXXXXXXX). Find it in GA4 Admin > Data Streams > Web', 'sharemypost-pro') . '</p>
                </div>
            </div>
        </div>';
        
        echo '<div class="sharemypost-sticky-footer">
                <div class="sharemypost-footer-content">';
                    submit_button(__('Save Changes', 'sharemypost-pro'), 'button-primary button-large', 'submit', false);
        echo '  </div>
            </div>
        </div></form>';
    }

	static function render_inline_ai_network_settings($settings, $opt) {
		$ai_networks = \ShareMyPostPro\Shortcode::get_ai_networks_ordered();
		$order = \ShareMyPost\Helpers::normalize_network_order(isset($settings['inline_ai_network_order']) ? $settings['inline_ai_network_order'] : '');
		if(empty($order)){
			$order = array_keys($ai_networks);
		}

		$enabled_list = (array) $settings['inline_ai_enabled_networks'];
		$ai_sortable = apply_filters('sharemypost_network_grid_sortable', false) ? ' sharemypost-core-sortable' : '';

		echo '<div class="sharemypost-card sharemypost-network-selection-card" style="margin-top: 20px;">
			<h3><span class="dashicons dashicons-admin-post"></span> ' . esc_html__('Ask AI', 'sharemypost-pro') . '</h3>
			<p style="font-size: 13px; color: #666; margin-bottom: 15px;">' . esc_html__('AI-powered networks are shortcode/block-based and display independently from standard share bars.', 'sharemypost-pro') . '</p>
			<div class="sharemypost-network-grid' . esc_attr($ai_sortable) . ' sharemypost-inline-ai-network-grid" data-order-input="#sharemypost-inline-ai-network-order">';

			static $allowed_svg_ai = null;
			if(null === $allowed_svg_ai){
				$allowed_svg_ai = [
					'svg' => [
						'viewbox' => true,
						'viewBox' => true,
						'xmlns'   => true,
						'width'   => true,
						'height'  => true,
						'fill'    => true,
						'style'   => true,
						'class'   => true,
					],
					'path' => [
						'd'     => true,
						'fill'  => true,
						'style' => true,
					],
					'circle' => [
						'cx' => true,
						'cy' => true,
						'r'  => true,
					],
					'rect' => [
						'x' => true,
						'y' => true,
						'width' => true,
						'height' => true,
					],
				];
			}

			foreach($ai_networks as $network => $data){
				$is_checked = in_array($network, $enabled_list, true);
				$active_class = $is_checked ? 'is-active' : '';

				echo '<label class="sharemypost-network-item ' . esc_attr($active_class) . '" data-network="' . esc_attr($network) . '">
					<input type="checkbox" class="sharemypost-network-checkbox" style="display:none;" name="' . esc_attr($opt) . '[inline_ai_enabled_networks][]" value="' . esc_attr($network) . '" ' . checked($is_checked, true, false) . ' />
					<div class="sharemypost-network-tile sharemypost-share-button sharemypost-network-' . esc_attr($network) . ' sharemypost-style-filled">
						<span class="sharemypost-icon">' . wp_kses($data['icon'], $allowed_svg_ai) . '</span>
						<span class="sharemypost-label">' . esc_html($data['label']) . '</span>
					</div>
				</label>';
			}

		echo '</div>';

		echo '<input type="hidden" id="sharemypost-inline-ai-network-order" class="sharemypost-order-input"
			name="' . esc_attr($opt) . '[inline_ai_network_order]"
			value="' . esc_attr(implode(',', $order)) . '" />';

		echo '<div style="border-top: 1px solid #e0e0e0; margin: 20px -20px 0; padding: 20px 20px 0;">
			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label" style="width:180px">' . esc_html__('Share Text Prefix', 'sharemypost-pro') . '</span>
				<div class="sharemypost-field-input">
					<input type="text" class="sharemypost-large-input" name="' . esc_attr($opt) . '[inline_ai_custom_text]" value="' . esc_attr(isset($settings['inline_ai_custom_text']) ? $settings['inline_ai_custom_text'] : '') . '" placeholder="' . esc_attr__('Enter custom text to display when sharing', 'sharemypost-pro') . '" />
					<p class="sm-config-description">' . esc_html__('This text will be displayed before the share link when users click to share with Ask AI.', 'sharemypost-pro') . '</p>
				</div>
			</div>
		</div>';

		echo '<div style="border-top: 1px solid #e0e0e0; margin: 20px -20px 0; padding: 20px 20px 0;">
			<h3><span class="dashicons dashicons-shortcode"></span> ' . esc_html__('Shortcode Usage', 'sharemypost-pro') . '</h3>
			<p>' . esc_html__('AI network share buttons can be placed anywhere using the following shortcode:', 'sharemypost-pro') . '</p>
			<code style="display:block;padding:10px;background:#f0f0f1;border-radius:4px;margin:10px 0;">[sharemypost_ai_networks]</code>
			</div>
		</div>';
	}

	static function render_inline_style_settings($settings, $opt) {
		$inline_button_shape = $settings['inline_button_shape'];
		$inline_button_size = $settings['inline_button_size'];
		$inline_space_between_icons = $settings['inline_space_between_icons'];
		$inline_button_style = $settings['inline_button_style'];
		echo '<div class="sharemypost-card">
			<h3><span class="dashicons dashicons-art"></span> ' . esc_html__('Button Style & Layout', 'sharemypost-pro') . '</h3>
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
				<div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Shape', 'sharemypost-pro') . '</span>
						<select class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="shape" name="' . esc_attr($opt) . '[inline_button_shape]">
							<option value="square" ' . selected($inline_button_shape, 'square', false) . '>' . esc_html__('Square', 'sharemypost-pro') . '</option>
							<option value="rounded" ' . selected($inline_button_shape, 'rounded', false) . '>' . esc_html__('Rounded', 'sharemypost-pro') . '</option>
							<option value="pill" ' . selected($inline_button_shape, 'pill', false) . '>' . esc_html__('Pill', 'sharemypost-pro') . '</option>
							<option value="circle" ' . selected($inline_button_shape, 'circle', false) . '>' . esc_html__('Circle', 'sharemypost-pro') . '</option>
							<option value="drop" ' . selected($inline_button_shape, 'drop', false) . '>' . esc_html__('Drop', 'sharemypost-pro') . '</option>
						</select>
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Size (px)', 'sharemypost-pro') . '</span>
						<input type="number" class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="size" min="6" max="120" step="1" name="' . esc_attr($opt) . '[inline_button_size]" value="' . esc_attr($inline_button_size) . '" />
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Space Between (px)', 'sharemypost-pro') . '</span>
						<input type="number" class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="space" min="0" max="60" step="1" name="' . esc_attr($opt) . '[inline_space_between_icons]" value="' . esc_attr($inline_space_between_icons) . '" />
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Style', 'sharemypost-pro') . '</span>
						<select class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="style" name="' . esc_attr($opt) . '[inline_button_style]">
							<option value="filled" ' . selected($inline_button_style, 'filled', false) . '>' . esc_html__('Filled', 'sharemypost-pro') . '</option>
							<option value="outline" ' . selected($inline_button_style, 'outline', false) . '>' . esc_html__('Outline', 'sharemypost-pro') . '</option>
							<option value="minimal" ' . selected($inline_button_style, 'minimal', false) . '>' . esc_html__('Minimal', 'sharemypost-pro') . '</option>
							<option value="dashed" ' . selected($inline_button_style, 'dashed', false) . '>' . esc_html__('Dashed', 'sharemypost-pro') . '</option>
							<option value="glass" ' . selected($inline_button_style, 'glass', false) . '>' . esc_html__('Glass', 'sharemypost-pro') . '</option>
						</select>
					</div>
				</div>

				<div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: #fafafa; min-height: 230px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
					<p style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">' . esc_html__('Live Preview', 'sharemypost-pro') . '</p>
						<div id="sharemypost-button-preview" class="sharemypost-share-bar sharemypost-inline-bar sharemypost-shape-' . esc_attr($inline_button_shape) . ' sharemypost-style-' . esc_attr($inline_button_style) . ' sharemypost-size-custom" style="display: flex; justify-content: center; align-items: center; min-height: 150px;
						--sharemypost-button-gap: ' . intval($inline_space_between_icons) . 'px;
						--sharemypost-button-size: ' . intval($inline_button_size) . 'px;
						--sharemypost-button-icon-size: ' . intval(max(20, round($inline_button_size * 0.70))) . 'px;
						--sharemypost-button-font-size: ' . intval(max(10, round($inline_button_size * 0.33))) . 'px;
						--sharemypost-button-padding: ' . intval(max(6, round($inline_button_size * 0.22))) . 'px ' . intval(max(10, round($inline_button_size * 0.35))) . 'px;">
						<div class="sharemypost-button-group" style="justify-content: center; flex-wrap: wrap; max-width: 100%;">
							<a href="javascript:void(0)" class="sharemypost-share-button sharemypost-network-facebook" style="--sharemypost-button-font-size: ' . intval(max(10, round($inline_button_size * 0.33))) . 'px; --sharemypost-button-padding: ' . intval(max(6, round($inline_button_size * 0.22))) . 'px ' . intval(max(10, round($inline_button_size * 0.35))) . 'px;">
								<span class="sharemypost-button-icon"><svg viewBox="0 0 24 24" fill="currentColor">
									<path d="M13.5 22v-8h3l.5-3.5h-3.5V7.8c0-1 .3-1.7 1.8-1.7H17V3.1a24.2 24.2 0 0 0-2.6-.1c-2.6 0-4.4 1.6-4.4 4.7V9.5H8V13h2.4v9h3.1z" fill="currentColor"/></svg></span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}

	static function render_floating_style_settings($settings, $opt) {
		$floating_button_shape = $settings['floating_button_shape'];
		$floating_button_size = $settings['floating_button_size'];
		$floating_space_between_icons = $settings['floating_space_between_icons'];
		$floating_button_style = $settings['floating_button_style'];
		echo '<div class="sharemypost-card">
			<h3><span class="dashicons dashicons-art"></span> ' . esc_html__('Button Style & Layout', 'sharemypost-pro') . '</h3>
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
				<div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Shape', 'sharemypost-pro') . '</span>
						<select class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="shape" name="' . esc_attr($opt) . '[floating_button_shape]">
							<option value="square" ' . selected($floating_button_shape, 'square', false) . '>' . esc_html__('Square', 'sharemypost-pro') . '</option>
							<option value="rounded" ' . selected($floating_button_shape, 'rounded', false) . '>' . esc_html__('Rounded', 'sharemypost-pro') . '</option>
							<option value="pill" ' . selected($floating_button_shape, 'pill', false) . '>' . esc_html__('Pill', 'sharemypost-pro') . '</option>
							<option value="circle" ' . selected($floating_button_shape, 'circle', false) . '>' . esc_html__('Circle', 'sharemypost-pro') . '</option>
							<option value="drop" ' . selected($floating_button_shape, 'drop', false) . '>' . esc_html__('Drop', 'sharemypost-pro') . '</option>
						</select>
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Size (px)', 'sharemypost-pro') . '</span>
						<input type="number" class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="size" min="6" max="120" step="1" name="' . esc_attr($opt) . '[floating_button_size]" value="' . esc_attr($floating_button_size) . '" />
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Space Between (px)', 'sharemypost-pro') . '</span>
						<input type="number" class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="space" min="0" max="60" step="1" name="' . esc_attr($opt) . '[floating_space_between_icons]" value="' . esc_attr($floating_space_between_icons) . '" />
					</div>
					<div class="sharemypost-field-row">
						<span class="sharemypost-field-label">' . esc_html__('Button Style', 'sharemypost-pro') . '</span>
						<select class="sharemypost-large-input sharemypost-preview-input" data-preview-attr="style" name="' . esc_attr($opt) . '[floating_button_style]">
							<option value="filled" ' . selected($floating_button_style, 'filled', false) . '>' . esc_html__('Filled', 'sharemypost-pro') . '</option>
							<option value="outline" ' . selected($floating_button_style, 'outline', false) . '>' . esc_html__('Outline', 'sharemypost-pro') . '</option>
							<option value="minimal" ' . selected($floating_button_style, 'minimal', false) . '>' . esc_html__('Minimal', 'sharemypost-pro') . '</option>
							<option value="dashed" ' . selected($floating_button_style, 'dashed', false) . '>' . esc_html__('Dashed', 'sharemypost-pro') . '</option>
							<option value="glass" ' . selected($floating_button_style, 'glass', false) . '>' . esc_html__('Glass', 'sharemypost-pro') . '</option>
						</select>
					</div>
				</div>

				<div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: #fafafa; min-height: 230px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
					<p style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">' . esc_html__('Live Preview', 'sharemypost-pro') . '</p>
						<div id="sharemypost-button-preview" class="sharemypost-share-bar sharemypost-floating-bar sharemypost-shape-' . esc_attr($floating_button_shape) . ' sharemypost-style-' . esc_attr($floating_button_style) . ' sharemypost-size-custom" style="display: flex; justify-content: center; align-items: center; min-height: 150px;
						--sharemypost-button-gap: ' . intval($floating_space_between_icons) . 'px;
						--sharemypost-button-size: ' . intval($floating_button_size) . 'px;
						--sharemypost-button-icon-size: ' . intval(max(20, round($floating_button_size * 0.70))) . 'px;
						--sharemypost-button-font-size: ' . intval(max(10, round($floating_button_size * 0.33))) . 'px;
						--sharemypost-button-padding: ' . intval(max(6, round($floating_button_size * 0.22))) . 'px ' . intval(max(10, round($floating_button_size * 0.35))) . 'px;">
						<div class="sharemypost-button-group" style="justify-content: center; flex-wrap: wrap; max-width: 100%;">
							<a href="javascript:void(0)" class="sharemypost-share-button sharemypost-network-facebook" style="--sharemypost-button-font-size: ' . intval(max(10, round($floating_button_size * 0.33))) . 'px; --sharemypost-button-padding: ' . intval(max(6, round($floating_button_size * 0.22))) . 'px ' . intval(max(10, round($floating_button_size * 0.35))) . 'px;">
								<span class="sharemypost-button-icon"><svg viewBox="0 0 24 24" fill="currentColor">
									<path d="M13.5 22v-8h3l.5-3.5h-3.5V7.8c0-1 .3-1.7 1.8-1.7H17V3.1a24.2 24.2 0 0 0-2.6-.1c-2.6 0-4.4 1.6-4.4 4.7V9.5H8V13h2.4v9h3.1z" fill="currentColor"/></svg></span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}
}
