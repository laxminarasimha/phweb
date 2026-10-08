<?php

namespace ShareMyPostPro;

if (!defined('ABSPATH')) {
    exit;
}

class CustomNetworks {

    static function init() {
        add_action('sharemypost_custom_networks_settings', '\ShareMyPostPro\CustomNetworks::render_settings');
    }

    static function get_all($force_refresh = false) {
        static $sorted_networks = null;
        if($sorted_networks !== null && !$force_refresh){
            return $sorted_networks;
        }

        $networks = get_option('sharemypost_custom_networks', []);

        if(!is_array($networks)){
            $sorted_networks = [];
            return [];
        }

        usort($networks, function ($a, $b) {
            $a_sort = isset($a['sort_order']) ? (int) $a['sort_order'] : 0;
            $b_sort = isset($b['sort_order']) ? (int) $b['sort_order'] : 0;
            return $a_sort - $b_sort;
        });

        $sorted_networks = $networks;
        return $networks;
    }

    static function get($id) {
        $networks = self::get_all();

        foreach ($networks as $network) {
            $network_id = isset($network['id']) ? (int) $network['id'] : 0;

            if($network_id === (int) $id){
                return $network;
            }
        }

        return null;
    }

    static function save($data) {
        $data = self::sanitize_network($data);
        $networks = self::get_all(true);

        if(!empty($data['id'])){
            $found = false;
            foreach ($networks as &$network) {
                if((isset($network['id']) ? (int) $network['id'] : 0) === (int) $data['id']){
                    $network = array_merge($network, $data);
                    $found = true;
                    break;
                }
            }
            unset($network);
            if($found){
                update_option('sharemypost_custom_networks', $networks);
                self::get_all(true);
                return (int)$data['id'];
            }
            return false;
        }

        $max_id = 0;
        foreach ($networks as $network) {
            if((isset($network['id']) ? (int) $network['id'] : 0) > $max_id){
                $max_id = (int) $network['id'];
            }
        }
        $data['id'] = $max_id + 1;
        $data['sort_order'] = count($networks);
        $networks[] = $data;
        update_option('sharemypost_custom_networks', $networks);
        self::get_all(true);
        return $data['id'];
    }

    static function delete($id) {
        $networks = self::get_all(true);
        foreach ($networks as $i => $network) {
            if((isset($network['id']) ? (int) $network['id'] : 0) === (int) $id){
                array_splice($networks, $i, 1);
                update_option('sharemypost_custom_networks', $networks);
                self::get_all(true);
                return true;
            }
        }
        return false;
    }

    static function sanitize_network($data) {
        $sanitized = [];

        if(isset($data['id'])){
            $sanitized['id'] = intval($data['id']);
        }

        $sanitized['name'] = sanitize_text_field(wp_unslash(isset($data['name']) ? $data['name'] : ''));
        $sanitized['slug'] = sanitize_key(wp_unslash(isset($data['slug']) ? $data['slug'] : ''));
        $sanitized['url'] = esc_url_raw(wp_unslash(isset($data['url']) ? $data['url'] : ''), ['http', 'https', 'mailto', 'sms']);
        $sanitized['icon'] = self::sanitize_svg(wp_unslash(isset($data['icon']) ? $data['icon'] : ''));
        $sanitized['color'] = sanitize_hex_color(isset($data['color']) ? $data['color'] : '') ?: '#1f75e1';
        if(isset($data['sort_order'])){
            $sanitized['sort_order'] = intval($data['sort_order']);
        }

        if(empty($sanitized['slug']) && !empty($sanitized['name'])){
            $sanitized['slug'] = sanitize_key($sanitized['name']);
        }

        if(empty($sanitized['slug'])){
            $sanitized['slug'] = 'custom-' . uniqid();
        }

        return $sanitized;
    }

    static function sanitize_svg($svg) {
        $allowed_tags = [
            'svg' => [
                'viewbox' => true,
                'viewBox' => true,
                'xmlns' => true,
                'width' => true,
                'height' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'style' => true,
                'class' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'style' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
            ],
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true,
                'ry' => true,
                'fill' => true,
            ],
            'g' => ['fill' => true, 'stroke' => true],
            'polygon' => ['points' => true, 'fill' => true],
            'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true],
            'polyline' => ['points' => true, 'fill' => true, 'stroke' => true],
            'ellipse' => ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true],
        ];

        return wp_kses($svg, $allowed_tags);
    }

    static function add_to_networks_filter($networks) {
        $custom = self::get_all();
        foreach ($custom as $network) {
            $slug = 'custom_' . $network['slug'];
            $networks[$slug] = [
                'label' => $network['name'],
                'url'   => $network['url'],
                'icon'  => $network['icon'],
            ];
        }
        return $networks;
    }

    static function render_settings() {
        $networks = self::get_all();
        $default_icon = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z" fill="currentColor"/></svg>';

        echo '<div class="sharemypost-wrap">
        <div class="sharemypost-hero-card">
            <div class="sharemypost-hero-info">
                <h2>' . esc_html__('Custom Sharing Networks', 'sharemypost-pro') . '</h2>
                <p>' . esc_html__('Add your own custom social networks with custom icons, colors, and share URLs.', 'sharemypost-pro') . '</p>
            </div>
        </div>

        <div class="sharemypost-dashboard-grid">
            <div class="sharemypost-card">
                <h3><span class="dashicons dashicons-plus-alt"></span> ' . esc_html__('Add New Custom Network', 'sharemypost-pro') . '</h3>
                <div class="sharemypost-custom-network-form" id="sharemypost-custom-network-form">
                    <input type="hidden" id="custom_network_id" value="" />

                    <div class="sharemypost-field-row">
                        <span class="sharemypost-field-label">' . esc_html__('Network Name', 'sharemypost-pro') . '</span>
                        <input type="text" id="custom_network_name" class="sharemypost-large-input" placeholder="e.g. My Network" />
                    </div>

                    <div class="sharemypost-field-row">
                        <span class="sharemypost-field-label">' . esc_html__('Share URL', 'sharemypost-pro') . '</span>
                        <input type="text" id="custom_network_url" class="sharemypost-large-input" placeholder="https://example.com/share?url=%s" />
                    </div>

                    <div class="sharemypost-field-row">
                        <span class="sharemypost-field-label">' . esc_html__('Custom Brand Color', 'sharemypost-pro') . '</span>
                        <input type="color" id="custom_network_color" value="#1f75e1" class="sharemypost-preview-input" style="height: 35px; width: 60px; padding: 2px; cursor: pointer;" />
                    </div>

                    <div class="sharemypost-field-row" style="flex-direction: column; align-items: stretch; gap: 8px;">
                        <span class="sharemypost-field-label">' . esc_html__('Custom SVG Icon', 'sharemypost-pro') . '</span>
                        <textarea id="custom_network_icon"  rows="4"  style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px;" placeholder="<svg viewBox=&quot;0 0 24 24&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;>...</svg>"></textarea>                    
			</div>

                    <div class="sharemypost-field-row" style="border-bottom: none; padding-top: 15px;">
                        <button type="button" class="button button-primary" id="sharemypost-save-custom-network">' . esc_html__('Save Network', 'sharemypost-pro') . '</button>
                        <button type="button" class="button" id="sharemypost-cancel-edit-custom-network" style="display:none;">' . esc_html__('Cancel', 'sharemypost-pro') . '</button>
                    </div>

                    <div id="sharemypost-custom-network-message" style="margin-top: 10px;"></div>
                </div>
            </div>

            <div class="sharemypost-card">
                <h3><span class="dashicons dashicons-visibility"></span> ' . esc_html__('Live Preview', 'sharemypost-pro') . '</h3>
                <div id="sharemypost-custom-preview-container" style="display: flex; justify-content: center; align-items: center; min-height: 150px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: #fafafa;">
                    <div id="sharemypost-custom-preview" class="sharemypost-share-bar sharemypost-inline-bar sharemypost-shape-rounded sharemypost-style-filled sharemypost-size-custom" style="--sharemypost-button-size: 38px; --sharemypost-button-gap: 10px; --sharemypost-button-font-size: 13px; --sharemypost-button-padding: 8px 13px;">
                        <div class="sharemypost-button-group" style="justify-content: center;">
                            <a href="javascript:void(0)" class="sharemypost-share-button sharemypost-network-custom-preview sharemypost-style-filled" style="--sharemypost-button-color: #1f75e1; background: #1f75e1; color: #fff;">
                                <span class="sharemypost-button-icon" id="sharemypost-custom-preview-icon">' . wp_kses_post($default_icon) . '</span>
                                <span class="sharemypost-button-label" id="sharemypost-custom-preview-label">' . esc_html__('My Network', 'sharemypost-pro') . '</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sharemypost-card" style="margin-top: 20px;">
            <h3><span class="dashicons dashicons-list-view"></span> ' . esc_html__('Saved Custom Networks', 'sharemypost-pro') . ' <span class="sharemypost-custom-count-badge">' . count($networks) . '</span></h3>
            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">' . esc_html__('Drag to reorder. These networks will automatically appear alongside built-in networks throughout ShareMyPost.', 'sharemypost-pro') . '</p>
            <div id="sharemypost-custom-networks-list" class="sharemypost-custom-networks-list">';

        if (empty($networks)) {
            echo '<p class="sharemypost-empty-state">' . esc_html__('No custom networks yet. Add one using the form above.', 'sharemypost-pro') . '</p>';
        } else {
            echo '<div class="sharemypost-custom-networks-grid sharemypost-sortable-networks" id="sharemypost-custom-networks-sortable">';
            foreach ($networks as $network) {
                self::render_network_card($network);
            }
            echo '</div>';
        }

        echo '</div>
        <input type="hidden" id="sharemypost-custom-network-order" value="' . esc_attr(implode(',', array_column($networks, 'id'))) . '" />
        </div>

        <div id="sharemypost-custom-network-delete-confirm" style="display:none;">
            <p>' . esc_html__('Are you sure you want to delete this custom network? This action cannot be undone.', 'sharemypost-pro') . '</p>
        </div>
        </div>';
    }

    static function render_network_card($network) {
        $display_slug = 'custom_' . $network['slug'];

        // Define allowed HTML tags and attributes specifically for SVGs
        $allowed_svg = array(
            'svg' => array(
                'class' => true,
                'aria-hidden' => true,
                'viewbox' => true,
                'xmlns' => true,
                'width' => true,
                'height' => true,
                'style' => true,
            ),
            'path' => array(
                'd' => true,
                'fill' => true,
            ),
            'g' => array(
                'fill' => true,
            ),
            'circle' => array(
                'cx' => true,
                'cy' => true,
                'r'  => true,
            )
        );

        echo '<label class="sharemypost-network-item is-active" data-network="' . esc_attr($display_slug) . '" data-custom-id="' . esc_attr($network['id']) . '">
            <div class="sharemypost-network-tile sharemypost-share-button sharemypost-network-' . esc_attr($display_slug) . ' sharemypost-style-filled" style="background-color: ' . esc_attr($network['color']) . '; color: #fff;">
                <span class="sharemypost-icon">' . wp_kses($network['icon'], $allowed_svg) . '</span>
                <span class="sharemypost-label">' . esc_html($network['name']) . '</span>
                <div class="sharemypost-custom-network-actions">
                    <span class="sharemypost-custom-network-edit dashicons dashicons-edit" data-id="' . esc_attr($network['id']) . '" title="' . esc_attr__('Edit', 'sharemypost-pro') . '" role="button" tabindex="0"></span>
                    <span class="sharemypost-custom-network-delete dashicons dashicons-trash" data-id="' . esc_attr($network['id']) . '" title="' . esc_attr__('Delete', 'sharemypost-pro') . '" role="button" tabindex="0"></span>
                </div>
            </div>
        </label>';
    }
}
