<?php

namespace ShareMyPostPro;

if(!defined('ABSPATH')){
    exit;
}

class Loader{

    static function init() {
        add_filter('sharemypost_share_bar_extra_buttons', '\ShareMyPostPro\Loader::render_universal_button', 10, 6);
        add_filter('sharemypost_share_bar_style_vars', '\ShareMyPostPro\Loader::add_universal_style_var', 10, 4);
        add_filter('sharemypost_share_url_permalink', '\ShareMyPostPro\Loader::add_ai_share_prefix', 9, 4);
        add_filter('sharemypost_share_url_permalink', '\ShareMyPostPro\Loader::add_utm_tracking', 10, 4);
        add_filter('sharemypost_network_grid_sortable', '__return_true');
        add_filter('sharemypost_network_grid_note', '\ShareMyPostPro\Loader::network_grid_note');
        add_filter('sharemypost_networks', '\ShareMyPostPro\Loader::register_networks');
        add_filter('sharemypost_enabled_networks_key', '\ShareMyPostPro\Loader::floating_settings_key', 10, 2);
        add_filter('sharemypost_network_order_key', '\ShareMyPostPro\Loader::floating_settings_key', 10, 2);
        add_filter('sharemypost_default_settings', '\ShareMyPostPro\Loader::add_pro_default_settings');
        add_filter('sharemypost_share_bar_wrapper_classes', '\ShareMyPostPro\Loader::apply_pro_bar_classes', 10, 4);
        add_filter('sharemypost_share_bar_style_vars', '\ShareMyPostPro\Loader::apply_pro_style_vars', 20, 4);
        add_filter('sharemypost_sanitize_settings', '\ShareMyPostPro\Loader::sanitize_pro_settings', 10, 3);
        add_filter('sharemypost_share_bar_buttons', '\ShareMyPostPro\Loader::filter_pro_share_buttons', 10, 4);
        add_filter('sharemypost_inline_networks_list', '\ShareMyPostPro\Loader::filter_pro_inline_networks_list');
    }

    static function add_ai_share_prefix($permalink, $post, $network, $settings) {
        $ai_networks = apply_filters('sharemypost_ai_network_keys', ['chatgpt', 'gemini', 'perplexity', 'google', 'claude', 'grok']);
        if(!empty($settings['inline_ai_custom_text']) && in_array($network, $ai_networks, true)){
            $permalink = $settings['inline_ai_custom_text'] . ' ' . $permalink;
        }
        return $permalink;
    }

    static function render_universal_button($html, $post_id, $is_floating, $settings, $show_icon, $show_label) {
        $universal_enabled = $is_floating ? $settings['floating_universal_enabled'] : $settings['inline_universal_enabled'];
        if(empty($universal_enabled)){
            return $html;
        }

        $html .= '<a href="javascript:void(0)"
            class="sharemypost-share-button sharemypost-network-universal">';

        if(!empty($show_icon)){
            $html .= '<span class="sharemypost-button-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
            </span>';
        }

        if(!empty($show_label)){
            $html .= '<span class="sharemypost-button-label">'
                . esc_html__('Share', 'sharemypost-pro') .
            '</span>';
        }

        $html .= '</a>';
        return $html;
    }

    static function add_universal_style_var($style_vars, $post_id, $is_floating, $settings) {
        $universal_color = $is_floating ? sanitize_hex_color($settings['floating_universal_color']) : sanitize_hex_color($settings['inline_universal_color']);
        if(!empty($universal_color)){
            $style_vars[] = '--sharemypost-universal-color:' . esc_attr($universal_color);
        }
        return $style_vars;
    }

    static function add_utm_tracking($permalink, $post, $network, $settings) {
        if(!empty($settings['utm_tracking'])){
            $source = str_replace('{site_name}', get_bloginfo('name'), $settings['utm_source']);
            $campaign = str_replace(['post_title', '{post_title}'], sanitize_title($post->post_title), $settings['utm_campaign']);
            $content = str_replace(['{network}'], $network, $settings['utm_content']);

            $permalink = add_query_arg([
                'utm_source' => sanitize_text_field(wp_unslash($source)),
                'utm_medium' => sanitize_text_field(wp_unslash($settings['utm_medium'])),
                'utm_campaign' => sanitize_text_field(wp_unslash($campaign)),
                'utm_content' => sanitize_text_field(wp_unslash($content)),
            ], $permalink);
        }
        return $permalink;
    }

    static function network_grid_note($note) {
        return esc_html__('Choose which networks should appear in share bars. Drag cards to reorder.', 'sharemypost-pro');
    }

    static function floating_settings_key($map, $type) {
        if($type === 'floating'){
            $inline_key = isset($map['inline']) ? $map['inline'] : '';
            $map['floating'] = $inline_key ? preg_replace('/^inline_/', 'floating_', $inline_key) : 'floating_enabled_networks';
        }
        return $map;
    }

    static function register_networks($networks) {
        $major = [
            'facebook' => [
                'label' => __('Facebook', 'sharemypost-pro'),
                'url' => 'https://www.facebook.com/sharer/sharer.php?u=%s',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></svg>',
            ],
            'whatsapp' => [
                'label' => __('WhatsApp', 'sharemypost-pro'),
                'url' => 'https://api.whatsapp.com/send?text=%s',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'sharemypost-pro'),
                'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=%s',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>',
            ],
            'telegram' => [
                'label' => __('Telegram', 'sharemypost-pro'),
                'url' => 'https://t.me/share/url?url=%s&text=%s',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telegram" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.287 5.906q-1.168.486-4.666 2.01-.567.225-.595.442c-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294q.39.01.868-.32 3.269-2.206 3.374-2.23c.05-.012.12-.026.166.016s.042.12.037.141c-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8 8 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629q.14.092.27.187c.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.4 1.4 0 0 0-.013-.315.34.34 0 0 0-.114-.217.53.53 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09"/></svg>',
            ],
            'messenger' => [
                'label' => __('Messenger', 'sharemypost-pro'),
                'url' => 'https://www.facebook.com/dialog/send?app_id=123456789&link=%s',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-messenger" viewBox="0 0 16 16"><path d="M0 7.76C0 3.301 3.493 0 8 0s8 3.301 8 7.76-3.493 7.76-8 7.76c-.81 0-1.586-.107-2.316-.307a.64.64 0 0 0-.427.03l-1.588.702a.64.64 0 0 1-.898-.566l-.044-1.423a.64.64 0 0 0-.215-.456C.956 12.108 0 10.092 0 7.76m5.546-1.459-2.35 3.728c-.225.358.214.761.551.506l2.525-1.916a.48.48 0 0 1 .578-.002l1.869 1.402a1.2 1.2 0 0 0 1.735-.32l2.35-3.728c.226-.358-.214-.761-.551-.506L9.728 7.381a.48.48 0 0 1-.578.002L7.281 5.98a1.2 1.2 0 0 0-1.735.32z"/></svg>',
            ],
            'bluesky' => [
		'label' => __('Bluesky', 'sharemypost-pro'),
		'url'   => 'https://bsky.app/intent/compose?text=%s',
		'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bluesky" viewBox="0 0 16 16"><path d="M3.468 1.948C5.303 3.325 7.276 6.118 8 7.616c.725-1.498 2.698-4.29 4.532-5.668C13.855.955 16 .186 16 2.632c0 .489-.28 4.105-.444 4.692-.572 2.04-2.653 2.561-4.504 2.246 3.236.551 4.06 2.375 2.281 4.2-3.376 3.464-4.852-.87-5.23-1.98-.07-.204-.103-.3-.103-.218 0-.081-.033.014-.102.218-.379 1.11-1.855 5.444-5.231 1.98-1.778-1.825-.955-3.65 2.28-4.2-1.85.315-3.932-.205-4.503-2.246C.28 6.737 0 3.12 0 2.632 0 .186 2.145.955 3.468 1.948"/></svg>',
	],
        ];
        return array_merge($networks, $major);
    }

    static function render_floating_bar() {
        if(!class_exists('\ShareMyPost\Util')) {
            return;
        }

        $settings = \ShareMyPost\Util::get_settings();

        if(empty($settings['floating_enabled'])){
            return;
        }

        if(empty($settings['mobile']) && wp_is_mobile()){
            return;
        }

        $post_type = get_post_type();
        $allowed_types = !empty($settings['floating_post_types']) ? (array) $settings['floating_post_types'] : ['post'];

        if(!in_array($post_type, $allowed_types, true)){
            return;
        }

        $html = self::get_floating_bar_html(get_the_ID());
        if(empty($html)){
            return;
        }

        static $allowed_html = null;

        if(null === $allowed_html){
            $allowed_html = [
                'div' => ['class' => true, 'style' => true, 'data-*' => true],
                'span' => ['class' => true, 'style' => true],
                'a' => ['href' => true, 'class' => true, 'target' => true, 'rel' => true, 'data-*' => true, 'style' => true],
                'svg' => ['viewbox' => true, 'viewBox' => true, 'xmlns' => true, 'class' => true, 'width' => true, 'height' => true, 'fill' => true, 'style' => true],
                'path' => ['d' => true, 'fill' => true, 'style' => true],
            ];
        }

        echo wp_kses($html, $allowed_html);
    }

    static function get_floating_bar_html($post_id) {
        if(!class_exists('\ShareMyPost\Util') || !class_exists('\ShareMyPost\Helpers')) {
            return '';
        }

        $settings = \ShareMyPost\Util::get_settings();
        $type = 'floating';
        $buttons = \ShareMyPost\Helpers::build_share_buttons($post_id, $type);

        $buttons = array_values(array_filter($buttons, function($btn){
            return !in_array($btn['network'], \ShareMyPostPro\Shortcode::get_ai_network_keys(), true);
        }));

        $color_type = $settings['floating_button_color_type'];
        $button_color = $settings['floating_button_color'];
        $button_size = $settings['floating_button_size'];
        $button_shape = $settings['floating_button_shape'];
        $button_style = $settings['floating_button_style'];
        $gap = $settings['floating_space_between_icons'];
        $show_labels_setting = $settings['floating_show_labels'];

        if(empty($buttons)){
            return '';
        }

        $floating_pos = $settings['floating_position'];

        $wrapper_classes = [
            'sharemypost-share-bar',
            'sharemypost-floating-bar',
            'sharemypost-color-type-' . esc_attr($color_type),
            'sharemypost-shape-' . esc_attr($button_shape),
            'sharemypost-style-' . esc_attr($button_style),
            is_numeric($button_size) ? 'sharemypost-size-custom' : 'sharemypost-size-' . esc_attr($button_size),
            'sharemypost-floating-' . esc_attr($floating_pos),
        ];

        $show_icon = in_array($show_labels_setting, ['icon_only', 'both'], true);
        $show_label = in_array($show_labels_setting, ['label_only', 'both'], true);
        $wrapper_classes[] = 'sharemypost-labels-' . esc_attr($show_labels_setting);

        $style_vars = [
            '--sharemypost-button-color:' . esc_attr($button_color),
        ];

        if(isset($gap) && is_numeric($gap)){
            $style_vars[] = '--sharemypost-button-gap:' . intval($gap) . 'px';
        }

        if(is_numeric($button_size)){
            $button_size = intval($button_size);
            $style_vars[] = '--sharemypost-button-size:' . $button_size . 'px';
            $style_vars[] = '--sharemypost-button-font-size:' . max(10, round($button_size * 0.33)) . 'px';
            $style_vars[] = '--sharemypost-button-padding:' . max(6, round($button_size * 0.22)) . 'px ' . max(10, round($button_size * 0.35)) . 'px';
            $style_vars[] = '--sharemypost-button-icon-size:' . max(20, round($button_size * 0.70)) . 'px';
        }

        $style_vars = apply_filters('sharemypost_share_bar_style_vars', $style_vars, $post_id, true, $settings);

        $mobile_breakpoint = absint($settings['floating_mobile_breakpoint']);

        $html = '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '" style="' . esc_attr(implode(';', $style_vars)) . '" data-mobile-breakpoint="' . esc_attr($mobile_breakpoint ?: 768) . '">';

        $html .= apply_filters('sharemypost_share_bar_before_buttons', '', $post_id, true, $settings, $type);

        $html .= '<div class="sharemypost-button-group">';

        foreach($buttons as $btn){
            $btn_classes = ['sharemypost-share-button', 'sharemypost-network-' . esc_attr($btn['network'])];
            $btn_classes = apply_filters('sharemypost_share_bar_button_classes', $btn_classes, $btn, $post_id, true, $settings);

            $share_url = $btn['share_url'];

            $html .= '<a href="' . esc_url($share_url) . '"
                class="' . esc_attr(implode(' ', $btn_classes)) . '"
                data-post-id="' . esc_attr($post_id) . '"
                data-network="' . esc_attr($btn['network']) . '">';

            if(!empty($show_icon)){
                static $allowed_svg = null;
                if(null === $allowed_svg){
                    $allowed_svg = [
                        'svg' => [
                            'viewbox' => true,
                            'viewBox' => true,
                            'xmlns' => true,
                            'fill' => true,
                            'stroke' => true,
                            'stroke-width' => true,
                            'stroke-linecap' => true,
                            'stroke-linejoin' => true,
                            'style' => true,
                            'class' => true,
                            'role' => true,
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
					'title' => [],
				];
                }
                $html .= '<span class="sharemypost-button-icon">'
                    . wp_kses($btn['icon'], $allowed_svg) .
                '</span>';
            }

            if(!empty($show_label)){
                $html .= '<span class="sharemypost-button-label">'
                    . esc_html($btn['label']) .
                '</span>';
            }

            $html = apply_filters('sharemypost_share_bar_button_inner', $html, $btn, $post_id, true, $settings);

            $html .= '</a>';
        }

        $html .= apply_filters('sharemypost_share_bar_extra_buttons', '', $post_id, true, $settings, $show_icon, $show_label);
        $html .= '</div>';
        $html .= apply_filters('sharemypost_share_bar_after_buttons', '', $post_id, true, $settings, $type);
        $html .= '</div>';
        $allowed_html = [
            'div' => [
                'class' => true,
                'style' => true,
                'id' => true,
                'data-mobile-breakpoint' => true,
            ],
            'p' => [
                'class' => true,
            ],
            'span' => [
                'class' => true,
                'style' => true,
            ],
            'a' => [
                'href' => true,
                'class' => true,
                'target' => true,
                'rel' => true,
                'data-post-id' => true,
                'data-network' => true,
                'data-click-id' => true,
                'style' => true,
            ],
            'svg' => [
                'viewbox' => true,
                'viewBox' => true,
                'xmlns' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'style' => true,
                'class' => true,
                'role' => true,
                'width' => true,
				'height' => true,
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
			'g' => [
				'fill' => true,
				'stroke' => true,
			],
			'polygon' => [
				'points' => true,
				'fill' => true,
			],
			'line' => [
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
				'stroke' => true,
			],
			'polyline' => [
				'points' => true,
				'fill' => true,
				'stroke' => true,
			],
			'ellipse' => [
				'cx' => true,
				'cy' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
				'stroke' => true,
			],
			'title' => [],
		];

        return wp_kses(apply_filters('sharemypost_share_bar_html', $html, $post_id, true, $settings), $allowed_html);
    }

    static function enqueue_frontend(){
        wp_enqueue_style('dashicons');
        wp_enqueue_style('sharemypost-pro-frontend', SHAREMYPOST_PRO_PLUGIN_URL . 'assets/css/frontend.css', [], SHAREMYPOST_PRO_VERSION);
        wp_enqueue_script('sharemypost-pro-frontend', SHAREMYPOST_PRO_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], SHAREMYPOST_PRO_VERSION, true);
        wp_localize_script('sharemypost-pro-frontend', 'sharemypost_pro_ajax',[
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('sharemypost-pro-nonce'),
		]);

        self::inject_custom_network_styles();
    }

    static function inject_custom_network_styles() {

        $custom_networks = \ShareMyPostPro\CustomNetworks::get_all();
        if(empty($custom_networks)){
            return;
        }

        $root_vars = '';
        $filled_rules = '';
        $outline_rules = '';
        $dashed_rules = '';
        $glass_rules = '';
        $minimal_rules = '';
        $modal_rules = '';

        foreach($custom_networks as $network){
            $slug = 'custom_' . $network['slug'];
            $color = !empty($network['color']) ? sanitize_hex_color($network['color']) : '#1f75e1';
            $root_vars .= "--sharemypost-{$slug}: {$color};";
            $filled_rules .= ".sharemypost-style-filled .sharemypost-share-button.sharemypost-network-{$slug} { background: var(--sharemypost-{$slug}); }";
            $outline_rules .= ".sharemypost-style-outline .sharemypost-share-button.sharemypost-network-{$slug} { border-color: var(--sharemypost-{$slug}); color: var(--sharemypost-{$slug}); }";
            $dashed_rules .= ".sharemypost-style-dashed .sharemypost-share-button.sharemypost-network-{$slug} { border-color: var(--sharemypost-{$slug}); color: var(--sharemypost-{$slug}); }";
            $glass_rules .= ".sharemypost-style-glass .sharemypost-share-button.sharemypost-network-{$slug} { color: var(--sharemypost-{$slug}); }";
            $minimal_rules .= ".sharemypost-style-minimal .sharemypost-share-button.sharemypost-network-{$slug} { color: var(--sharemypost-{$slug}); }";
            $modal_rules .= ".sharemypost-modal-item.sharemypost-network-{$slug} .sharemypost-modal-icon { color: var(--sharemypost-{$slug}); }";
        }

        $css = ":root{{$root_vars}}{$filled_rules}{$outline_rules}{$dashed_rules}{$glass_rules}{$minimal_rules}{$modal_rules}";

        wp_add_inline_style('sharemypost-pro-frontend', $css);
    }

    static function render_universal_modal() {
        $settings = \ShareMyPost\util::get_settings();

        if(empty($settings['inline_universal_enabled']) && empty($settings['floating_universal_enabled'])){
            return;
        }

        $networks = \ShareMyPost\Helpers::get_networks();
        $post_id  = get_the_ID();
        $post = get_post($post_id);

        if(!$post){
            return;
        }

        $html  = '<div id="sharemypost-modal-overlay" class="sharemypost-modal-overlay sharemypost-modal-hidden">';
        $html .= '<div class="sharemypost-modal-card">';
        $html .='<div class="sharemypost-modal-header">';
        $html .= '<h3>' . esc_html__('Share with', 'sharemypost-pro') . '</h3>';
        $html .= '<button type="button" class="sharemypost-modal-close">&times;</button>';
        $html .='</div>';
        $html .='<div class="sharemypost-modal-grid">';
        $ai_networks = \ShareMyPostPro\Shortcode::get_ai_network_keys();

        foreach($networks as $id => $data){
            if(in_array($id, $ai_networks, true)){
                continue;
            }
            $share_url = \ShareMyPost\Helpers::build_share_url($id, $post);
            $html .= '<a href="'.esc_url($share_url) . '" class="sharemypost-modal-item sharemypost-network-' . esc_attr($id) . '" data-network="' . esc_attr($id) . '" data-post-id="'.esc_attr($post->ID).'">';
            static $allowed_svg = null;
            if(null === $allowed_svg){
                $allowed_svg = [
                    'svg' => [
                        'viewbox' => true,
                        'viewBox' => true,
                        'xmlns' => true,
                        'fill' => true,
                        'stroke' => true,
                        'stroke-width' => true,
                        'stroke-linecap' => true,
                        'stroke-linejoin' => true,
                        'style' => true,
                        'class' => true,
                        'role' => true,
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
                    'title' => [],
                ];
            }
            $html .= '<span class="sharemypost-modal-icon">'.wp_kses($data['icon'], $allowed_svg).'</span>';
            $html .= '<span class="sharemypost-modal-label">'.esc_html($data['label']).'</span>';
            $html .= '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $allowed_html = [
            'div' => ['id' => true, 'class' => true],
            'h3' => [],
            'button' => ['type' => true, 'class' => true],
            'a' => ['href' => true, 'class' => true, 'data-network' => true, 'data-post-id' => true],
            'span' => ['class' => true],
            'svg' => ['viewbox' => true, 'viewBox' => true, 'xmlns' => true],
            'path' => ['d' => true, 'fill' => true],
        ];

        echo wp_kses($html, $allowed_html);
    }

    static function add_pro_default_settings($defaults) {
        return array_merge($defaults, [
            'inline_button_shape' => 'rounded',
            'inline_button_size' => 20,
            'inline_space_between_icons' => 10,
            'inline_button_style' => 'filled',
            'inline_universal_enabled' => 1,
            'inline_universal_color' => '#1f75e1',

            // Ask AI
            'inline_ai_enabled_networks' => [],
            'inline_ai_custom_text' => 'Review and summarize this URL:',

            // UTM
            'utm_tracking' => 0,
            'utm_source' => '{site_name}',
            'utm_medium' => 'social',
            'utm_campaign' => '{post_title}',
            'utm_content' => '{network}',

            // GA4
            'ga4_enabled' => 0,
            'ga4_measurement_id' => '',
            'ga4_api_secret' => '',

            // CTA
            'custom_cta_enabled' => 0,
            'custom_cta_text' => '',

            // Click To Tweet
            'click_to_tweet_text' => '',

            // Floating
            'floating_enabled' => 0,
            'floating_position' => 'left',
            'floating_post_types' => ['post'],
            'floating_enabled_networks' => ['twitter', 'pinterest', 'whatsapp', 'facebook'],
            'floating_network_order' => [],
            'floating_button_shape' => 'rounded',
            'floating_button_size' => 38,
            'floating_space_between_icons' => 10,
            'floating_button_style' => 'filled',
            'floating_button_color_type' => 'original',
            'floating_button_color' => '#1f75e1',
            'floating_show_labels' => 'icon_only',
            'floating_mobile_breakpoint' => 768,
            'floating_universal_enabled' => 1,
            'floating_universal_color' => '#1f75e1',
        ]);
    }

    static function apply_pro_bar_classes($classes, $post_id, $is_floating, $settings) {
        $type = $is_floating ? 'floating' : 'inline';
        $button_shape = isset($settings[$type . '_button_shape']) ? $settings[$type . '_button_shape'] : 'rounded';
        $button_style = isset($settings[$type . '_button_style']) ? $settings[$type . '_button_style'] : 'filled';
        $button_size = isset($settings[$type . '_button_size']) ? $settings[$type . '_button_size'] : ($is_floating ? 38 : 20);

        // Remove the default free classes if present
        $classes = array_diff($classes, [
            'sharemypost-shape-rounded',
            'sharemypost-style-filled',
            'sharemypost-size-normal',
            'sharemypost-size-custom'
        ]);

        $classes[] = 'sharemypost-shape-' . esc_attr($button_shape);
        $classes[] = 'sharemypost-style-' . esc_attr($button_style);
        $classes[] = is_numeric($button_size) ? 'sharemypost-size-custom' : 'sharemypost-size-' . esc_attr($button_size);

        return array_values($classes);
    }

    static function apply_pro_style_vars($style_vars, $post_id, $is_floating, $settings) {
        $type = $is_floating ? 'floating' : 'inline';
        $button_size = isset($settings[$type . '_button_size']) ? $settings[$type . '_button_size'] : ($is_floating ? 38 : 20);
        $gap = isset($settings[$type . '_space_between_icons']) ? $settings[$type . '_space_between_icons'] : 10;

        // Remove default free styles/gaps
        $style_vars = array_filter($style_vars, function($v) {
            return strpos($v, '--sharemypost-button-gap:') !== 0 &&
                   strpos($v, '--sharemypost-button-size:') !== 0 &&
                   strpos($v, '--sharemypost-button-font-size:') !== 0 &&
                   strpos($v, '--sharemypost-button-padding:') !== 0 &&
                   strpos($v, '--sharemypost-button-icon-size:') !== 0;
        });

        if(isset($gap) && is_numeric($gap)){
            $style_vars[] = '--sharemypost-button-gap:' . intval($gap) . 'px';
        }

        if(is_numeric($button_size)){
            $button_size = intval($button_size);
            $style_vars[] = '--sharemypost-button-size:' . $button_size . 'px';
            $style_vars[] = '--sharemypost-button-font-size:' . max(10, round($button_size * 0.33)) . 'px';
            $style_vars[] = '--sharemypost-button-padding:' . max(6, round($button_size * 0.22)) . 'px ' . max(10, round($button_size * 0.35)) . 'px';
            $style_vars[] = '--sharemypost-button-icon-size:' . max(20, round($button_size * 0.70)) . 'px';
        }

        return $style_vars;
    }

    static function sanitize_pro_settings($input, $defaults, $current) {
        $post_data = isset($_POST['sharemypost_settings']) && is_array($_POST['sharemypost_settings']) ? $_POST['sharemypost_settings'] : null;

        $section = '';
        if ($post_data !== null && isset($post_data['section'])) {
            $section = $post_data['section'];
        } elseif (isset($input['section'])) {
            $section = $input['section'];
        }

        $is_inline_tab = ($section !== '') ? ($section === 'inline') : isset($input['inline_position']);
        $is_floating_tab = ($section !== '') ? ($section === 'floating') : isset($input['floating_position']);
        $is_config_tab = ($section !== '') ? ($section === 'configuration') : isset($input['ga4_measurement_id']);

        $pro_checkboxes = [
            'inline_universal_enabled',
            'utm_tracking',
            'ga4_enabled',
            'custom_cta_enabled',
            'floating_enabled',
            'floating_universal_enabled'
        ];

        foreach($pro_checkboxes as $key){
            $is_set_in_form = false;
            if ($post_data !== null) {
                $is_set_in_form = isset($post_data[$key]);
            } else {
                $is_set_in_form = isset($input[$key]);
            }

            if(!$is_set_in_form){
                $belongs_to_active_tab = false;
                if ($is_inline_tab && $key === 'inline_universal_enabled') {
                    $belongs_to_active_tab = true;
                } elseif ($is_floating_tab && in_array($key, ['floating_enabled', 'floating_universal_enabled'], true)) {
                    $belongs_to_active_tab = true;
                } elseif ($is_config_tab && in_array($key, ['utm_tracking', 'ga4_enabled'], true)) {
                    $belongs_to_active_tab = true;
                }

                if ($belongs_to_active_tab) {
                    $input[$key] = 0;
                } else {
                    $input[$key] = isset($current[$key]) ? $current[$key] : (isset($defaults[$key]) ? $defaults[$key] : 0);
                }
            }
        }

        $pro_checkbox_arrays = [
            'inline_ai_enabled_networks',
            'floating_post_types',
            'floating_enabled_networks'
        ];

        foreach($pro_checkbox_arrays as $key){
            $is_set_in_form = false;
            if ($post_data !== null) {
                $is_set_in_form = isset($post_data[$key]);
            } else {
                $is_set_in_form = isset($input[$key]);
            }

            if(!$is_set_in_form){
                $belongs_to_active_tab = false;
                if ($is_inline_tab && $key === 'inline_ai_enabled_networks') {
                    $belongs_to_active_tab = true;
                } elseif ($is_floating_tab && in_array($key, ['floating_post_types', 'floating_enabled_networks'], true)) {
                    $belongs_to_active_tab = true;
                }

                if ($belongs_to_active_tab) {
                    $input[$key] = [];
                } else {
                    $input[$key] = isset($current[$key]) ? $current[$key] : (isset($defaults[$key]) ? $defaults[$key] : []);
                }
            }
        }

        $input['inline_button_size'] = isset($input['inline_button_size']) ? intval($input['inline_button_size']) : $defaults['inline_button_size'];
        if ($input['inline_button_size'] < 6 || $input['inline_button_size'] > 120) {
            $input['inline_button_size'] = $defaults['inline_button_size'];
        }

        $input['inline_space_between_icons'] = isset($input['inline_space_between_icons']) ? intval($input['inline_space_between_icons']) : $defaults['inline_space_between_icons'];
        if ($input['inline_space_between_icons'] < 0 || $input['inline_space_between_icons'] > 60) {
            $input['inline_space_between_icons'] = $defaults['inline_space_between_icons'];
        }

        $input['inline_button_shape'] = (isset($input['inline_button_shape']) && in_array($input['inline_button_shape'], ['square', 'rounded', 'pill', 'circle', 'drop'], true)) ? $input['inline_button_shape'] : $defaults['inline_button_shape'];

        $input['inline_button_style'] = (isset($input['inline_button_style']) && in_array($input['inline_button_style'], ['filled', 'outline', 'minimal', 'dashed', 'glass'], true)) ? $input['inline_button_style'] : $defaults['inline_button_style'];

        $input['inline_universal_enabled'] = !empty($input['inline_universal_enabled']) ? 1 : 0;
        $input['inline_universal_color'] = sanitize_hex_color(isset($input['inline_universal_color']) ? $input['inline_universal_color'] : '');
        if(empty($input['inline_universal_color'])){
            $input['inline_universal_color'] = $defaults['inline_universal_color'];
        }

        $networks = array_keys(\ShareMyPost\Helpers::get_networks());
        $input['inline_ai_enabled_networks'] = array_values(array_intersect($networks, (array)(isset($input['inline_ai_enabled_networks']) ? $input['inline_ai_enabled_networks'] : [])));
        $input['inline_ai_custom_text'] = isset($input['inline_ai_custom_text']) ? sanitize_text_field(wp_unslash($input['inline_ai_custom_text'])) : '';

        $input['utm_tracking'] = !empty($input['utm_tracking']) ? 1 : 0;
        $input['utm_source'] = isset($input['utm_source']) ? sanitize_text_field(wp_unslash($input['utm_source'])) : '';
        $input['utm_medium'] = isset($input['utm_medium']) ? sanitize_text_field(wp_unslash($input['utm_medium'])) : '';
        $input['utm_campaign'] = isset($input['utm_campaign']) ? sanitize_text_field(wp_unslash($input['utm_campaign'])) : '';
        $input['utm_content'] = isset($input['utm_content']) ? sanitize_text_field(wp_unslash($input['utm_content'])) : '';

        $input['ga4_enabled'] = !empty($input['ga4_enabled']) ? 1 : 0;
        $input['ga4_measurement_id'] = isset($input['ga4_measurement_id']) ? sanitize_text_field(wp_unslash($input['ga4_measurement_id'])) : '';
        if(!empty($input['ga4_measurement_id']) && !preg_match('/^G-[A-Za-z0-9]+$/', $input['ga4_measurement_id'])){
            $input['ga4_measurement_id'] = '';
        }
        $input['ga4_api_secret'] = isset($input['ga4_api_secret']) ? sanitize_text_field(wp_unslash($input['ga4_api_secret'])) : '';

        $input['custom_cta_enabled'] = !empty($input['custom_cta_enabled']) ? 1 : 0;
        $input['custom_cta_text'] = isset($input['custom_cta_text']) ? sanitize_textarea_field(wp_unslash($input['custom_cta_text'])) : '';

        $input['click_to_tweet_text'] = isset($input['click_to_tweet_text']) ? sanitize_textarea_field(wp_unslash($input['click_to_tweet_text'])) : '';

        $input['floating_enabled'] = !empty($input['floating_enabled']) ? 1 : 0;
        $input['floating_position'] = (isset($input['floating_position']) && in_array($input['floating_position'], ['left','right'], true)) ? $input['floating_position'] : $defaults['floating_position'];
        $input['floating_post_types'] = array_values(array_filter(array_map('sanitize_key', (array)(isset($input['floating_post_types']) ? $input['floating_post_types'] : []))));
        $input['floating_enabled_networks'] = array_values(array_intersect($networks, (array)(isset($input['floating_enabled_networks']) ? $input['floating_enabled_networks'] : [])));

        if(isset($input['floating_network_order'])){
            $order = \ShareMyPost\Helpers::normalize_network_order($input['floating_network_order']);
            $input['floating_network_order'] = !empty($order) ? $order : $defaults['floating_network_order'];
        }else{
            $input['floating_network_order'] = $defaults['floating_network_order'];
        }

        $input['floating_button_shape'] = (isset($input['floating_button_shape']) && in_array($input['floating_button_shape'], ['square', 'rounded', 'pill', 'circle', 'drop'], true)) ? $input['floating_button_shape'] : $defaults['floating_button_shape'];
        
        $input['floating_button_size'] = isset($input['floating_button_size']) ? intval($input['floating_button_size']) : $defaults['floating_button_size'];
        if ($input['floating_button_size'] < 6 || $input['floating_button_size'] > 120) {
            $input['floating_button_size'] = $defaults['floating_button_size'];
        }

        $input['floating_space_between_icons'] = isset($input['floating_space_between_icons']) ? intval($input['floating_space_between_icons']) : $defaults['floating_space_between_icons'];
        if ($input['floating_space_between_icons'] < 0 || $input['floating_space_between_icons'] > 60) {
            $input['floating_space_between_icons'] = $defaults['floating_space_between_icons'];
        }

        $input['floating_button_style'] = (isset($input['floating_button_style']) && in_array($input['floating_button_style'], ['filled', 'outline', 'minimal', 'dashed', 'glass'], true)) ? $input['floating_button_style'] : $defaults['floating_button_style'];
        $input['floating_button_color_type'] = (isset($input['floating_button_color_type']) && in_array($input['floating_button_color_type'], ['original', 'custom'], true)) ? $input['floating_button_color_type'] : $defaults['floating_button_color_type'];
        $input['floating_button_color'] = sanitize_hex_color(isset($input['floating_button_color']) ? $input['floating_button_color'] : '');
        if(empty($input['floating_button_color'])){
            $input['floating_button_color'] = $defaults['floating_button_color'];
        }

        $input['floating_show_labels'] = (isset($input['floating_show_labels']) && in_array($input['floating_show_labels'], ['icon_only', 'label_only', 'both'], true)) ? $input['floating_show_labels'] : $defaults['floating_show_labels'];
        $input['floating_mobile_breakpoint'] = isset($input['floating_mobile_breakpoint']) ? intval($input['floating_mobile_breakpoint']) : $defaults['floating_mobile_breakpoint'];

        $input['floating_universal_enabled'] = !empty($input['floating_universal_enabled']) ? 1 : 0;
        $input['floating_universal_color'] = sanitize_hex_color(isset($input['floating_universal_color']) ? $input['floating_universal_color'] : '');
        if(empty($input['floating_universal_color'])){
            $input['floating_universal_color'] = $defaults['floating_universal_color'];
        }

        return $input;
    }

    static function filter_pro_share_buttons($buttons, $post_id, $is_floating, $settings) {
        if (!$is_floating) {
            $ai_networks = \ShareMyPostPro\Shortcode::get_ai_network_keys();
            return array_values(array_filter($buttons, function($btn) use ($ai_networks) {
                return !in_array($btn['network'], $ai_networks, true);
            }));
        }
        return $buttons;
    }

    static function filter_pro_inline_networks_list($all_networks) {
        $ai_keys = \ShareMyPostPro\Shortcode::get_ai_network_keys();
        return array_diff_key($all_networks, array_flip($ai_keys));
    }
}
