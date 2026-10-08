<?php

namespace ShareMyPost\Settings;

use ShareMyPost\Helpers;

if(!defined('ABSPATH')){
	exit;
}

class UI{

	static function inline_content_settings() {
		$settings = \ShareMyPost\Util::get_settings();

		echo '<form method="post" action="options.php" class="sharemypost-settings-form">
		<input type="hidden" name="sharemypost_settings[section]" value="inline" />';
		settings_fields('sharemypost_settings_group');

		echo '<div class="sharemypost-wrap">
		<div class="sharemypost-hero-card">
			<div class="sharemypost-hero-info">
				<h2>' . esc_html__('Inline Content', 'sharemypost') . '</h2>
				<p>' . esc_html__('Configure how share buttons appear inside your post content.', 'sharemypost') . '</p>
			</div>
			<div class="sharemypost-hero-action">
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_settings[inline_enabled]" value="1" ' . checked(1, $settings['inline_enabled'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
		</div>';
		self::render_inline_network_selection_settings($settings,'sharemypost_settings');

		echo '<div class="sharemypost-dashboard-grid">';

		// --- CARD 1: Displays Rules ---
		echo '<div class="sharemypost-card">
			<h3><span class="dashicons dashicons-visibility"></span> ' . esc_html__('Display Rules', 'sharemypost') . '</h3>

			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Position', 'sharemypost') . '</span>
				<select class="sharemypost-large-input" name="sharemypost_settings[inline_position]">
					<option value="above" ' . selected($settings['inline_position'], 'above', false) . '>
						' . esc_html__('Above content', 'sharemypost') . '
					</option>
					<option value="below" ' . selected($settings['inline_position'], 'below', false) . '>
						' . esc_html__('Below content', 'sharemypost') . '
					</option>
					<option value="both" ' . selected($settings['inline_position'], 'both', false) . '>
						' . esc_html__('Both above and below', 'sharemypost') . '
					</option>
				</select>
			</div>

			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Display on Post Types', 'sharemypost') . '</span>
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

					foreach ( $ordered_types as $pt => $obj ) {
						$label   = isset( $obj->labels->singular_name ) ? $obj->labels->singular_name : ucfirst( $pt );
						$is_checked = in_array( $pt, (array) $settings['inline_post_types'], true );
						echo '<label class="sharemypost-pill">
						<input type="checkbox" name="sharemypost_settings[inline_post_types][]" value="' . esc_attr($pt) . '"' . checked($is_checked, true, false) . ' />
							<span>' . esc_html( $label ) . '</span>
						</label>';
					}
					echo '</div>
					</div>

			<div class="sharemypost-field-row">
				<span class="sharemypost-field-label">' . esc_html__('Show on Mobile', 'sharemypost') . '</span>
				<label class="sharemypost-toggle">
					<input type="checkbox" name="sharemypost_settings[mobile]" value="1"' . checked(1, $settings['mobile'], false) . ' />
					<span class="sharemypost-slider"></span>
				</label>
			</div>
		</div>';

		// Style Card
		do_action("sharemypost_inline_style_settings",$settings,'sharemypost_settings');

		self::render_extras_settings( $settings, 'sharemypost_settings', 'inline');

		echo '</div>';

		self::render_inline_ai_network_settings($settings, 'sharemypost_settings');

		echo '<div class="sharemypost-sticky-footer">
			<div class="sharemypost-footer-content">';
		submit_button(__('Save Settings', 'sharemypost'),'button-primary button-large','submit', false );

		echo '</div>
		</div>
		</div>
		</form>';
	}

	static function render_extras_settings($settings, $opt, $type = 'inline') {

			$show_labels = isset($settings['inline_show_labels'])? $settings['inline_show_labels']: (isset($settings['show_labels']) ? $settings['show_labels'] : 'icon_only');
			$color_type = isset($settings['inline_button_color_type'])? $settings['inline_button_color_type']: (isset($settings['button_color_type']) ? $settings['button_color_type'] : 'custom');
			$button_color = isset($settings['inline_button_color'])? $settings['inline_button_color']: (isset($settings['button_color']) ? $settings['button_color'] : '#1f75e1');

			echo '<div class="sharemypost-card">
				<h3><span class="dashicons dashicons-admin-appearance"></span> ' . esc_html__('Inline Button Appearance', 'sharemypost') . '</h3>
				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Show Labels', 'sharemypost') . '</span>
					<select class="sharemypost-large-input" name="' . esc_attr($opt) . '[inline_show_labels]">
						<option value="icon_only" ' . selected($show_labels, 'icon_only', false) . '>' . esc_html__('Icon Only', 'sharemypost') . '</option>
						<option value="label_only" ' . selected($show_labels, 'label_only', false) . '>' . esc_html__('Label Only', 'sharemypost') . '</option>
						<option value="both" ' . selected($show_labels, 'both', false) . '>' . esc_html__('Icon + Label', 'sharemypost') . '</option>
					</select>
				</div>

				<div class="sharemypost-field-row">
					<span class="sharemypost-field-label">' . esc_html__('Button Colors', 'sharemypost') . '</span>
					<select class="sharemypost-large-input sharemypost-preview-input" name="' . esc_attr($opt) . '[inline_button_color_type]" id="sharemypost_inline_color_type_select">
						<option value="original" ' . selected($color_type, 'original', false) . '>' . esc_html__('Original', 'sharemypost') . '</option>
						<option value="custom" ' . selected($color_type, 'custom', false) . '>' . esc_html__('Custom Color', 'sharemypost') . '</option>
					</select>
				</div>

				<div class="sharemypost-field-row" id="sharemypost_inline_custom_color_container" style="' . ($color_type === 'original' ? 'display:none;' : '') . '">
					<span class="sharemypost-field-label">' . esc_html__('Select Custom Color', 'sharemypost') . '</span>
					<input id="sharemypost_inline_button_color_input" class="sharemypost-preview-input" type="color" name="' . esc_attr($opt) . '[inline_button_color]" value="' . esc_attr($button_color) . '" />
				</div>
			</div>';

		do_action('sharemypost_inline_content_settings', $settings, $opt);
	}

	static function render_inline_network_selection_settings($settings, $opt){
		$enabled_list = (array) $settings['inline_enabled_networks'];
		$order = Helpers::normalize_network_order($settings['inline_network_order']);
		$inline_sortable = apply_filters('sharemypost_network_grid_sortable', true) ? ' sharemypost-core-sortable' : '';
		$inline_note = apply_filters('sharemypost_network_grid_note', esc_html__('Choose which networks should appear in share bars. Drag cards to reorder.', 'sharemypost'));
		
		echo '<div class="sharemypost-card sharemypost-network-selection-card" style="margin-top: 20px;">
			<h3><span class="dashicons dashicons-admin-post"></span> ' . esc_html__('Inline Networks', 'sharemypost') . '</h3>
			<p style="font-size: 13px; color: #666; margin-bottom: 15px;">' . esc_html($inline_note) . '</p>
			<div class="sharemypost-network-grid' . esc_attr($inline_sortable) . ' sharemypost-inline-network-grid" data-order-input="#sharemypost-inline-network-order">';

			static $allowed_svg = null;
			if(null === $allowed_svg){
				$allowed_svg = [
					'svg' => [
						'viewbox' => true,
						'viewBox' => true,
						'xmlns'   => true,
						'fill'    => true,
						'style'   => true,
					],
					'path' => [
						'd'     => true,
						'fill'  => true,
						'style' => true,
					],
				];
			}

			foreach(Helpers::get_networks_ordered('inline') as $network => $data){
				$is_checked = in_array($network, $enabled_list, true);
				$active_class = $is_checked ? 'is-active' : '';

				echo '<label class="sharemypost-network-item ' . esc_attr($active_class) . '" data-network="' . esc_attr($network) . '">
					<input type="checkbox" class="sharemypost-network-checkbox" style="display:none;" name="' . esc_attr($opt) . '[inline_enabled_networks][]" value="' . esc_attr($network) . '" ' . checked($is_checked, true, false) . ' />
					<div class="sharemypost-network-tile sharemypost-share-button sharemypost-network-' . esc_attr($network) . ' sharemypost-style-minimal">
						<span class="sharemypost-icon">' . wp_kses($data['icon'], $allowed_svg) . '</span>
						<span class="sharemypost-label">' . esc_html($data['label']) . '</span>
					</div>
				</label>';
			}
		echo '</div>
		<input type="hidden" id="sharemypost-inline-network-order" class="sharemypost-order-input" name="' . esc_attr($opt) . '[inline_network_order]" value="' . esc_attr(implode(',', $order)) . '" />
		</div>';
	}

	static function render_inline_ai_network_settings($settings, $opt) {
		do_action('sharemypost_inline_ai_network_settings', $settings, $opt);
	}

	static function configuration_settings(){
		do_action('sharemypost_configuration_settings');
	}

	static function click_to_tweet_settings(){
		do_action('sharemypost_click_to_x_settings');
	}

}