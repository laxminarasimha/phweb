<?php

namespace ShareMyPostPro;

if(!defined('ABSPATH')){
    exit;
}

class Shortcode {

    static function init() {
        add_action('admin_init', '\ShareMyPostPro\Shortcode::register_tweet_settings');
        add_shortcode('sharemypost_ai_networks', '\ShareMyPostPro\Shortcode::render_ai_shortcode');
        add_filter('sharemypost_ai_networks','\ShareMyPostPro\Shortcode::register_pro_networks');
        add_filter('sharemypost_networks','\ShareMyPostPro\Shortcode::register_custom_networks');
    }

    static function register_tweet_settings() {
        register_setting('sharemypost_pro_tweet', 'sharemypost_pro_tweet_settings', [
            'type' => 'array',
            'sanitize_callback' => '\ShareMyPostPro\Shortcode::sanitize_tweet_settings',
        ]);

        add_settings_section('sharemypost_pro_tweet_section', esc_html__('Tweet Global Settings', 'sharemypost-pro'), '\ShareMyPostPro\Shortcode::render_tweet_settings_section','sharemypost_pro_tweet');
    }

    static function render_tweet_settings_section() {
        echo '<p>' . esc_html__('Configure global defaults for Click to Tweet boxes. These can be overridden on individual boxes.', 'sharemypost-pro') . '</p>';
    }

    static function get_default_tweet_settings() {
        return [
            'tweet_theme' => 'default',
            'tweet_accent_color' => '#1f75e1',
            'tweet_cta_text' => esc_html__('Click to Tweet', 'sharemypost-pro'),
            'tweet_cta_position' => 'right',
            'tweet_remove_url' => 0,
            'tweet_remove_username' => 0,
            'tweet_hide_hashtags' => 0,
        ];
    }

    static function get_tweet_settings() {
        return wp_parse_args(get_option('sharemypost_pro_tweet_settings', []), self::get_default_tweet_settings());
    }

    static function render_tweet($args = []) {
        $settings = self::get_tweet_settings();
        $box_args = array_merge([
			'tweet_text' => '',
			'theme' => $settings['tweet_theme'],
			'accent_color' => $settings['tweet_accent_color'],
			'cta_text' => $settings['tweet_cta_text'],
			'cta_position' => $settings['tweet_cta_position'],
			'remove_url' => (bool) $settings['tweet_remove_url'],
			'remove_username' => (bool) $settings['tweet_remove_username'],
			'hide_hashtags' => (bool) $settings['tweet_hide_hashtags'],
			],$args
		);

        $tweet_text = sanitize_text_field(wp_unslash($box_args['tweet_text']));
        $theme = sanitize_text_field(wp_unslash($box_args['theme']));

        if(!in_array($theme, ['default', 'simple', 'simple-alt', 'light', 'dark', 'gray'], true)){
            $theme = in_array($settings['tweet_theme'], ['default', 'simple', 'simple-alt', 'light', 'dark', 'gray'], true) ? $settings['tweet_theme'] : 'light';
        }

        $accent_color = sanitize_hex_color(wp_unslash($box_args['accent_color']));
        $cta_text = sanitize_text_field(wp_unslash($box_args['cta_text']));
        $cta_position = in_array($box_args['cta_position'], ['left', 'right']) ? $box_args['cta_position'] : 'right';
        $remove_url = (bool) $box_args['remove_url'];
        $remove_username = (bool) $box_args['remove_username'];
        $hide_hashtags = (bool) $box_args['hide_hashtags'];

        if(empty($tweet_text)){
            return '';
        }

        $tweet_text_full = $tweet_text;
        if (!$remove_url) {
            global $post;
            if ($post) {
                $tweet_text_full .= ' ' . get_permalink($post->ID);
            }
        }

        $twitter_username = self::get_twitter_username();
        if ($twitter_username && !$remove_username) {
            $tweet_text_full .= ' via @' . $twitter_username;
        }

        $tweet_text_display = $tweet_text;
        if ($hide_hashtags) {
            $tweet_text_display = trim(preg_replace('/#\w+/u', '', $tweet_text_display));
            $tweet_text_display = preg_replace('/\s+/', ' ', $tweet_text_display);
        }

        $twitter_url = self::build_twitter_share_url($tweet_text_full);
        $box_id = 'sharemypost-ctt-' . wp_generate_uuid4();
        $accent_style = !empty($accent_color) ? '--sharemypost-ctt-accent-color: ' . esc_attr($accent_color) . ';' : '';
        $position_class = 'cta-' . esc_attr($cta_position);

        $html = sprintf(
            '<div id="%s" class="sharemypost-click-to-tweet sharemypost-ctt-%s %s" style="%s" data-twitter-url="%s">',
            esc_attr($box_id),
            esc_attr($theme),
            esc_attr($position_class),
            esc_attr($accent_style),
            esc_attr($twitter_url)
        );
        $html .= '<div class="sharemypost-ctt-content">';
        $html .= '<p class="sharemypost-ctt-text">' . wp_kses_post($tweet_text_display) . '</p>';
        $html .= '</div>';
        $html .= sprintf(
            '<a href="%s" class="sharemypost-ctt-button" target="_blank" rel="noopener noreferrer" data-click-id="%s">%s</a>',
            esc_attr($twitter_url),
            esc_attr($box_id),
            esc_html($cta_text)
        );
        $html .= '</div>';

        $allowed_html = [
            'div' => [
                'id' => true,
                'class' => true,
                'style' => true,
                'data-twitter-url' => true,
            ],
            'p' => [
                'class' => true,
            ],
            'a' => [
                'href' => true,
                'class' => true,
                'target' => true,
                'rel' => true,
                'data-click-id' => true,
            ],
        ];

        return wp_kses($html, array_merge(wp_kses_allowed_html('post'), $allowed_html));
    }

    static function render_click_to_tweet($atts = [], $content = null) {
        $atts = shortcode_atts([
            'text' => '',
            'tweet' => '',
            'theme' => '',
            'cta_text' => '',
            'cta_position' => '',
            'remove_url' => 'false',
            'remove_username' => 'false',
            'hide_hashtags' => 'false',
            'accent_color' => '',
        ], $atts, 'sharemypost_click_to_x');


        $tweet_text = trim($atts['tweet'] ?: $atts['text']);
        if (empty($tweet_text) && !empty($content)) {
            $tweet_text = trim(wp_strip_all_tags(do_shortcode($content)));
        }

        if (empty($tweet_text)) {
            $tweet_text = get_the_title();
        }

        $args = [
            'tweet_text' => $tweet_text,
            'remove_url' => in_array($atts['remove_url'], ['true', '1', 'yes'], true),
            'remove_username' => in_array($atts['remove_username'], ['true', '1', 'yes'], true),
            'hide_hashtags' => in_array($atts['hide_hashtags'], ['true', '1', 'yes'], true),
        ];

		$keys = ['theme', 'accent_color', 'cta_text', 'cta_position'];

        foreach($keys as $key){
            if(!empty($atts[$key])){
                $args[$key] = $atts[$key];
            }
        }

        return self::render_tweet($args);
    }


    static function load_ga4_tracking() {
        $settings = \ShareMyPost\Util::get_settings();

        if(empty($settings['ga4_enabled']) || empty($settings['ga4_measurement_id'])){
            return;
        }

        $measurement_id = sanitize_text_field(wp_unslash($settings['ga4_measurement_id']));
        if(!preg_match('/^G-[A-Z0-9]+$/', $measurement_id)){
            return;
        }

        global $wp_version;
        wp_enqueue_script('ga4-gtag', 'https://www.googletagmanager.com/gtag/js?id=' . esc_attr($measurement_id), [], $wp_version, true);

        $inline_js = sprintf(
            "window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '%s', {'anonymize_ip': false, 'allow_google_signals': true, 'allow_ad_personalization_signals': true});",
            esc_js($measurement_id)
        );

        wp_add_inline_script('ga4-gtag', $inline_js, 'after');

        if (wp_script_is('sharemypost-pro-frontend', 'registered')) {
            wp_localize_script('sharemypost-pro-frontend', 'sharemypost_ga4', [
                'enabled' => true,
                'measurement_id' => $measurement_id,
            ]);
        }
    }

	static function get_twitter_username(){
        $settings = \ShareMyPost\util::get_settings();
        if(!empty($settings['twitter_username'])){
            $username = sanitize_text_field(wp_unslash($settings['twitter_username']));
            return ltrim($username, '@');
        }

        $username = get_option('sharemypost_twitter_username');
        if(!empty($username)){
            return ltrim(sanitize_text_field(wp_unslash($username)), '@');
        }

        return '';
    }

    static function extract_hashtags($text) {
        preg_match_all('/#[\w]+/', $text, $matches);
        return !empty($matches[0]) ? $matches[0] : [];
    }

    static function build_twitter_share_url($text) {
        return 'https://x.com/intent/tweet?text=' . rawurlencode($text);
    }

    static function get_ai_network_keys() {
        return ['chatgpt', 'gemini', 'perplexity', 'google', 'claude', 'grok'];
    }

    static function get_ai_networks_ordered(){
            $settings = \ShareMyPost\Util::get_settings();
            $ai_networks = apply_filters('sharemypost_ai_networks', []);

            $order = \ShareMyPost\Helpers::normalize_network_order(
                isset($settings['inline_ai_network_order']) ? $settings['inline_ai_network_order'] : '',
                array_keys($ai_networks)
            );
            
            $ordered = [];

            if(is_array($order) && !empty($order)){
                foreach($order as $network_key){
                    if(isset($ai_networks[$network_key])){
                        $ordered[$network_key] = $ai_networks[$network_key];
                    }
                }
            }

            foreach($ai_networks as $network_key => $data){
                if(!isset($ordered[$network_key])){
                    $ordered[$network_key] = $data;
                }
            }

            return $ordered;
    }

    static function render_ai_shortcode() {

        if(!class_exists('\ShareMyPost\Helpers')){
            return '';
        }

        $settings = \ShareMyPost\Util::get_settings();
        $post_id = get_the_ID();

        if(empty($post_id)){
            return '';
        }

        $ai_networks = self::get_ai_networks_ordered();

        $enabled = (array) $settings['inline_ai_enabled_networks'];
        if(empty($enabled)){
            $enabled = array_keys($ai_networks);
        }
        $ai_networks = array_intersect_key($ai_networks, array_flip($enabled));

        if(empty($ai_networks)){
            return '';
        }

        wp_enqueue_style('sharemypost-pro-frontend');
        wp_enqueue_script('sharemypost-pro-frontend');

        $color_type = isset($settings['inline_button_color_type']) ? $settings['inline_button_color_type'] : 'original';
        $button_color = isset($settings['inline_button_color']) ? $settings['inline_button_color'] : '#1f75e1';
        $button_size = isset($settings['inline_button_size']) ? $settings['inline_button_size'] : 20;
        $button_shape = isset($settings['inline_button_shape']) ? $settings['inline_button_shape'] : 'rounded';
        $button_style = isset($settings['inline_button_style']) ? $settings['inline_button_style'] : 'filled';
        $gap = isset($settings['inline_space_between_icons']) ? $settings['inline_space_between_icons'] : 10;
        $show_labels_setting = isset($settings['inline_show_labels']) ? $settings['inline_show_labels'] : 'icon_only';
        $wrapper_classes = [
            'sharemypost-share-bar',
            'sharemypost-ai-share-bar',
            'sharemypost-color-type-' . esc_attr($color_type),
            'sharemypost-shape-' . esc_attr($button_shape),
            'sharemypost-style-' . esc_attr($button_style),
            is_numeric($button_size) ? 'sharemypost-size-custom' : 'sharemypost-size-' . esc_attr($button_size),
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

            $html = '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '" style="' . esc_attr(implode(';', $style_vars)) . '">';
            $html .= '<div class="sharemypost-button-group">';

            foreach($ai_networks as $network => $data){
                $post = get_post($post_id);
                if(empty($post)){
                    continue;
                }
                $permalink = get_permalink($post);
                $permalink = apply_filters('sharemypost_share_url_permalink', $permalink, $post, $network, $settings);
                $share_url = sprintf($data['url'], rawurlencode($permalink));
                $icon = $data['icon'];
                $btn_classes = ['sharemypost-share-button', 'sharemypost-network-' . esc_attr($network)];

            $html .= '<a href="' . esc_url($share_url) . '"
                class="' . esc_attr(implode(' ', $btn_classes)) . '"
                data-post-id="' . esc_attr($post_id) . '"
                data-network="' . esc_attr($network) . '"
                target="_blank" rel="noopener noreferrer">';

            if($show_icon){
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
                $svg_icon = preg_replace('/\s(width|height)=["\']?\d*["\']?\s*/i', ' ', $icon);
                $svg_icon = preg_replace('/\s+style="width:\s*\d+px;\s*height:\s*\d+px;\s*"/i', ' ', $svg_icon);
                $html .= '<span class="sharemypost-button-icon">' . wp_kses($svg_icon, $allowed_svg) . '</span>';
            }

            if($show_label){
                $html .= '<span class="sharemypost-button-label">' . esc_html($data['label']) . '</span>';
            }

                $html .= '</a>';
            }

            $html .= '</div>';
            $html .= '</div>';

        $allowed_html = [
            'div' => [
                'class' => true,
                'style' => true,
                'id' => true,
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

        return wp_kses($html, $allowed_html);
    }

    static function sanitize_tweet_settings($input) {
        $sanitized = [];

        if (isset($input['tweet_theme'])) {
            $sanitized['tweet_theme'] = in_array($input['tweet_theme'], ['default', 'simple', 'simple-alt', 'light', 'dark', 'gray'], true ) ? $input['tweet_theme'] : 'light';
        }

        if (isset($input['tweet_accent_color'])) {
            $sanitized['tweet_accent_color'] = sanitize_hex_color(wp_unslash($input['tweet_accent_color'])) ?: '#1f75e1';
        }

        if (isset($input['tweet_cta_text'])) {
            $sanitized['tweet_cta_text'] = sanitize_text_field(wp_unslash($input['tweet_cta_text'])) ?: esc_html__('Click to Tweet', 'sharemypost-pro');
        }

        if (isset($input['tweet_cta_position'])) {
            $sanitized['tweet_cta_position'] = in_array($input['tweet_cta_position'], ['left', 'right'], true)? $input['tweet_cta_position']: 'right';
        }

        $sanitized['tweet_remove_url'] = isset($input['tweet_remove_url']) ? 1 : 0;
        $sanitized['tweet_remove_username'] = isset($input['tweet_remove_username']) ? 1 : 0;
        $sanitized['tweet_hide_hashtags'] = isset($input['tweet_hide_hashtags']) ? 1 : 0;

        return wp_parse_args($sanitized, self::get_default_tweet_settings());
    }

    static function register_pro_networks($networks) {

            $ai_networks = [
                'chatgpt' => [
                    'label' => __('ChatGPT', 'sharemypost-pro'),
                    'url'   => 'https://chatgpt.com/?q=%s',
                    'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-openai" viewBox="0 0 16 16"><path d="M14.949 6.547a3.94 3.94 0 0 0-.348-3.273 4.11 4.11 0 0 0-4.4-1.934A4.1 4.1 0 0 0 8.423.2 4.15 4.15 0 0 0 6.305.086a4.1 4.1 0 0 0-1.891.948 4.04 4.04 0 0 0-1.158 1.753 4.1 4.1 0 0 0-1.563.679A4 4 0 0 0 .554 4.72a3.99 3.99 0 0 0 .502 4.731 3.94 3.94 0 0 0 .346 3.274 4.11 4.11 0 0 0 4.402 1.933c.382.425.852.764 1.377.995.526.231 1.095.35 1.67.346 1.78.002 3.358-1.132 3.901-2.804a4.1 4.1 0 0 0 1.563-.68 4 4 0 0 0 1.14-1.253 3.99 3.99 0 0 0-.506-4.716m-6.097 8.406a3.05 3.05 0 0 1-1.945-.694l.096-.054 3.23-1.838a.53.53 0 0 0 .265-.455v-4.49l1.366.778q.02.011.025.035v3.722c-.003 1.653-1.361 2.992-3.037 2.996m-6.53-2.75a2.95 2.95 0 0 1-.36-2.01l.095.057L5.29 12.09a.53.53 0 0 0 .527 0l3.949-2.246v1.555a.05.05 0 0 1-.022.041L6.473 13.3c-1.454.826-3.311.335-4.15-1.098m-.85-6.94A3.02 3.02 0 0 1 3.07 3.949v3.785a.51.51 0 0 0 .262.451l3.93 2.237-1.366.779a.05.05 0 0 1-.048 0L2.585 9.342a2.98 2.98 0 0 1-1.113-4.094zm11.216 2.571L8.747 5.576l1.362-.776a.05.05 0 0 1 .048 0l3.265 1.86a3 3 0 0 1 1.173 1.207 2.96 2.96 0 0 1-.27 3.2 3.05 3.05 0 0 1-1.36.997V8.279a.52.52 0 0 0-.276-.445m1.36-2.015-.097-.057-3.226-1.855a.53.53 0 0 0-.53 0L6.249 6.153V4.598a.04.04 0 0 1 .019-.04L9.533 2.7a3.07 3.07 0 0 1 3.257.139c.474.325.843.778 1.066 1.303.223.526.289 1.103.191 1.664zM5.503 8.575 4.139 7.8a.05.05 0 0 1-.026-.037V4.049c0-.57.166-1.127.476-1.607s.752-.864 1.275-1.105a3.08 3.08 0 0 1 3.234.41l-.096.054-3.23 1.838a.53.53 0 0 0-.265.455zm.742-1.577 1.758-1 1.762 1v2l-1.755 1-1.762-1z"/></svg>',
                ],
                'gemini' => [
                    'label' => __('Gemini', 'sharemypost-pro'),
                    'url' => 'https://gemini.google.com/?q=%s',
                    'icon' => '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Google Gemini</title><path  fill="currentColor"  d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/></svg>',
                ],
                'claude' => [
                    'label' => __('Claude', 'sharemypost-pro'),
                    'url'   => 'https://claude.ai/new?q=%s',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-claude" viewBox="0 0 16 16"><path d="m3.127 10.604 3.135-1.76.053-.153-.053-.085H6.11l-.525-.032-1.791-.048-1.554-.065-1.505-.08-.38-.081L0 7.832l.036-.234.32-.214.455.04 1.009.069 1.513.105 1.097.064 1.626.17h.259l.036-.105-.089-.065-.068-.064-1.566-1.062-1.695-1.121-.887-.646-.48-.327-.243-.306-.104-.67.435-.48.585.04.15.04.593.456 1.267.981 1.654 1.218.242.202.097-.068.012-.049-.109-.181-.9-1.626-.96-1.655-.428-.686-.113-.411a2 2 0 0 1-.068-.484l.496-.674L4.446 0l.662.089.279.242.411.94.666 1.48 1.033 2.014.302.597.162.553.06.17h.105v-.097l.085-1.134.157-1.392.154-1.792.052-.504.25-.605.497-.327.387.186.319.456-.045.294-.19 1.23-.37 1.93-.243 1.29h.142l.161-.16.654-.868 1.097-1.372.484-.545.565-.601.363-.287h.686l.505.751-.226.775-.707.895-.585.759-.839 1.13-.524.904.048.072.125-.012 1.897-.403 1.024-.186 1.223-.21.553.258.06.263-.218.536-1.307.323-1.533.307-2.284.54-.028.02.032.04 1.029.098.44.024h1.077l2.005.15.525.346.315.424-.053.323-.807.411-3.631-.863-.872-.218h-.12v.073l.726.71 1.331 1.202 1.667 1.55.084.383-.214.302-.226-.032-1.464-1.101-.565-.497-1.28-1.077h-.084v.113l.295.432 1.557 2.34.08.718-.112.234-.404.141-.444-.08-.911-1.28-.94-1.44-.759-1.291-.093.053-.448 4.821-.21.246-.484.186-.403-.307-.214-.496.214-.98.258-1.28.21-1.016.19-1.263.112-.42-.008-.028-.092.012-.953 1.307-1.448 1.957-1.146 1.227-.274.109-.477-.247.045-.44.266-.39 1.586-2.018.956-1.25.617-.723-.004-.105h-.036l-4.212 2.736-.75.096-.324-.302.04-.496.154-.162 1.267-.871z"/></svg>',
                ],
                'google' => [
                    'label' => __('Google', 'sharemypost-pro'),
                    'url'   => 'https://www.google.com/search?q=%s&udm=50',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google" viewBox="0 0 16 16"><path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/></svg>',
                ],
                'perplexity' => [
                    'label' => __('Perplexity', 'sharemypost-pro'),
                    'url'   => 'https://www.perplexity.ai/search?q=%s',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-perplexity" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 .188a.5.5 0 0 1 .503.5V4.03l3.022-2.92.059-.048a.51.51 0 0 1 .49-.054.5.5 0 0 1 .306.46v3.247h1.117l.1.01a.5.5 0 0 1 .403.49v5.558a.5.5 0 0 1-.503.5H12.38v3.258a.5.5 0 0 1-.312.462.51.51 0 0 1-.55-.11l-3.016-3.018v3.448c0 .275-.225.5-.503.5a.5.5 0 0 1-.503-.5v-3.448l-3.018 3.019a.51.51 0 0 1-.548.11.5.5 0 0 1-.312-.463v-3.258H2.503a.5.5 0 0 1-.503-.5V5.215l.01-.1c.047-.229.25-.4.493-.4H3.62V1.469l.006-.074a.5.5 0 0 1 .302-.387.51.51 0 0 1 .547.102l3.023 2.92V.687c0-.276.225-.5.503-.5M4.626 9.333v3.984l2.87-2.872v-4.01zm3.877 1.113 2.871 2.871V9.333l-2.87-2.897zm3.733-1.668a.5.5 0 0 1 .145.35v1.145h.612V5.715H9.201zm-9.23 1.495h.613V9.13c0-.131.052-.257.145-.35l3.033-3.064h-3.79zm1.62-5.558H6.76L4.626 2.652zm4.613 0h2.134V2.652z"/></svg>',
                ],
                'grok' => [
                    'label' => __('Grok', 'sharemypost-pro'),
                    'url'   => 'https://grok.com/?q=%s',
                    'icon'  => '<svg viewBox="0 0 24 24" width="32" height="32" style="width: 32px !important; height: 32px !important; fill: currentColor !important;" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M9.27 15.29l7.978-5.897c.391-.29.95-.177 1.137.272.98 2.369.542 5.215-1.41 7.169-1.951 1.954-4.667 2.382-7.149 1.406l-2.711 1.257c3.889 2.661 8.611 2.003 11.562-.953 2.341-2.344 3.066-5.539 2.388-8.42l.006.007c-.983-4.232.242-5.924 2.75-9.383.06-.082.12-.164.179-.248l-3.301 3.305v-.01L9.267 15.292M7.623 16.723c-2.792-2.67-2.31-6.801.071-9.184 1.761-1.763 4.647-2.483 7.166-1.425l2.705-1.25a7.808 7.808 0 00-1.829-1A8.975 8.975 0 005.984 5.83c-2.533 2.536-3.33 6.436-1.962 9.764 1.022 2.487-.653 4.246-2.34 6.022-.599.63-1.199 1.259-1.682 1.925l7.62-6.815"/></svg>',
                ],
            ];

            return array_merge($networks, $ai_networks);
    }

	static function register_custom_networks($networks) {

	$custom_networks = \ShareMyPostPro\CustomNetworks::get_all();

    foreach($custom_networks as $custom){
        $slug = 'custom_' . $custom['slug'];

        if (!isset($networks[$slug])) {
            $networks[$slug] = [
                'label' => $custom['name'],
                'url'   => $custom['url'],
                'icon'  => $custom['icon'],
            ];
        }
    }

        return $networks;
    }
}