<?php

namespace ShareMyPost;

if(!defined('ABSPATH')){
	exit;
}

class Admin {

	static function init() {
		add_action('admin_menu', '\ShareMyPost\Admin::add_admin_menu');
		add_action('admin_enqueue_scripts', '\ShareMyPost\Admin::enqueue_admin_assets');
		add_action('admin_init', '\ShareMyPost\Admin::register_settings');
	}

	static function register_settings() {
		register_setting('sharemypost_settings_group', 'sharemypost_settings', [
			'type' => 'array',
			'sanitize_callback' => ['\ShareMyPost\Helpers', 'sanitize_settings'],
			'default' => [],
		]);
	}

	static function add_admin_menu() {
		
		$icon = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIj48c3R5bGU+LnMwIHsgZmlsbDogIzYzNjZmMSB9IC5zMSB7IGZpbGw6ICNmZmZmZmYgfTwvc3R5bGU+PGcgaWQ9IkxheWVyIDEiPjxwYXRoIGNsYXNzPSJzMCIgZD0ibTE2MyAwaDY5OGM5MC4wMiAwIDE2MyA3Mi45OCAxNjMgMTYzdjY5OGMwIDkwLjAyLTcyLjk4IDE2My0xNjMgMTYzaC02OThjLTkwLjAyIDAtMTYzLTcyLjk4LTE2My0xNjN2LTY5OGMwLTkwLjAyIDcyLjk4LTE2MyAxNjMtMTYzeiIvPjxnPjxwYXRoIGlkPSJtYXJrIiBjbGFzcz0iczEiIGQ9Im02MjAuNyAxNDYuNmgxNDguOWM2LjggMCAxMS44IDUuMiAxMS44IDExLjh2MTU1LjZjMCA1LjUtMi45IDguMi03LjQgNy45bC01MC41LTQ5LjljLTIzLjUgMjMtNTUuNSA0NC05Ny41IDUwaC0xOTFjLTE3LjM3IDAtMzQuMDMgNi45LTQ2LjMyIDE5LjE4LTEyLjI4IDEyLjI5LTE5LjE4IDI4Ljk1LTE5LjE4IDQ2LjMyIDAgMTcuMzcgNi45IDM0LjAzIDE5LjE4IDQ2LjMyIDEyLjI5IDEyLjI4IDI4Ljk1IDE5LjE4IDQ2LjMyIDE5LjE4aDE3OC4yYzQyLjY5IDEwLjk4IDgwLjIgMzYuNTQgMTA2LjA0IDcyLjI1IDI1Ljg0IDM1LjcxIDM4LjM4IDc5LjMyIDM1LjQ2IDEyMy4zMS0yLjkyIDQzLjk4LTIxLjExIDg1LjU2LTUxLjQ1IDExNy41NC0zMC4zMyAzMS45OC03MC44OCA1Mi4zNi0xMTQuNjUgNTcuNmgtMzIyLjZjLTcuNS0wLjItMTIuMy0zLjItMTIuMS04LjcgOC4xLTQzIDQyLjEtOTkgMTE4LjEtMTEyLjVoMTk0YzE3LjY0IDAgMzQuNTUtNy4wMSA0Ny4wMi0xOS40OCAxMi40Ny0xMi40NyAxOS40OC0yOS4zOCAxOS40OC00Ny4wMiAwLTE3LjY0LTcuMDEtMzQuNTUtMTkuNDgtNDcuMDItMTIuNDctMTIuNDctMjkuMzgtMTkuNDgtNDcuMDItMTkuNDhoLTEzMWMtNDguMjcgMC05NC41Ni0xOS4xNy0xMjguNjktNTMuMzEtMzQuMTQtMzQuMTMtNTMuMzEtODAuNDItNTMuMzEtMTI4LjY5IDAtNDguMjcgMTkuMTctOTQuNTYgNTMuMzEtMTI4LjY5IDM0LjEzLTM0LjE0IDgwLjQyLTUzLjMxIDEyOC42OS01My4zMWgxOTVjNy0xLjUgMTYtNi41IDIxLjctMTEuM2wtMzMuNC0zNi42Yy0zLTMtMi4zLTguMSAyLjQtMTF6Ii8+PC9nPjwvZz48L3N2Zz4=';
		
		add_menu_page( 'ShareMyPost', 'ShareMyPost', 'manage_options', 'sharemypost', '\ShareMyPost\Admin::dispatcher', $icon);
	}

	static function dispatcher() {
		echo '<div class="sharemypost-wrap">';

		self::render_header();
		echo '<div class="sharemypost-main-content">
		<div id="sharemypost-options" class="sharemypost-tab-content active">';
		self::render_options_layout();
		echo '</div>';

		echo '</div>
		</main></div>
		</div>'; 
	}

	static function render_header() {

		if (defined('SHAREMYPOST_PRO_VERSION')) {
			$sub_tabs = [
				'inline_content' => ['label' => 'Inline Content', 'icon' => 'menu'],
				'floating_bar' => ['label' => 'Floating Bar', 'icon' => 'align-pull-left', 'pro' => true],
				'custom_networks' => ['label' => 'Custom Networks', 'icon' => 'networking', 'pro' => true],
				'click_to_tweet' => ['label' => 'Click to X', 'pro' => true],
				'configuration' => ['label' => 'Configuration', 'icon' => 'admin-settings', 'pro' => true],
				'support' => ['label' => 'Support', 'icon' => 'sos'],
				'license' => ['label' => 'License', 'icon' => 'admin-network'],
			];
		} else {
			$sub_tabs = [
				'inline_content' => ['label' => 'Inline Content', 'icon' => 'menu'],
				'support' => ['label' => 'Support', 'icon' => 'sos'],
				'go_pro' => ['label' => 'Go Pro', 'icon' => 'lock'],
			];
		}

		echo '<div class="sharemypost-admin-wrapper">
		<header class="sharemypost-admin-header main-header-sticky">
			<div class="sharemypost-header-left">

				<div class="sharemypost-logo">
					<img alt="' . esc_attr__('ShareMyPost Logo', 'sharemypost') . '" src="' . esc_url(SHAREMYPOST_ASSETS_URL) . '/img/sharemypost-logo.svg" height="38"/>
				</div>
				<nav class="sharemypost-nav-tabs glass-tabs" id="sharemypost-main-nav"></nav>
				<div class="sharemypost-sub-tabs-wrapper">';
				$icon = '';
				foreach($sub_tabs as $slug => $tab){
						
					if ($slug === 'click_to_tweet') {
						$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor" style="width:20px;height:20px;vertical-align:middle"><path d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z"/></svg> ';
					} elseif (!empty($tab['icon'])) {
						$icon = '<span class="dashicons dashicons-' . esc_attr($tab['icon']) . '"></span> ';
					}
					
					$pro_tag = (!defined('SHAREMYPOST_PRO_VERSION') && !empty($tab['pro'])) ? '<span class="sharemypost-pro-tag">PRO</span>' : '';
					echo '<button type="button" class="sharemypost-sub-tab" data-subtab="' . esc_attr($slug) . '">' . wp_kses_post($icon) . esc_html($tab['label']) . wp_kses_post($pro_tag) . '</button>';
						}
					echo '</div>

			</div> <div class="sharemypost-header-right">
				<div class="sharemypost-version-badge">
					v' . esc_html(SHAREMYPOST_VERSION) . '
				</div>
			</div>
		</header>
		<main class="sharemypost-admin-main-content">';
	}

	static function render_options_layout() {
		if (defined('SHAREMYPOST_PRO_VERSION')) {
			$sub_tabs = [
				'inline_content' => ['label' => 'Inline Content'],
				'floating_bar' => ['label' => 'Floating Bar', 'pro' => true],
				'custom_networks' => ['label' => 'Custom Networks', 'pro' => true],
				'click_to_tweet' => ['label' => 'Click to X', 'pro' => true],
				'configuration' => ['label' => 'Configuration', 'pro' => true],
				'support' => ['label' => 'Support'],
				'license' => ['label' => 'License', 'pro' => true],
			];
		} else {
			$sub_tabs = [
				'inline_content' => ['label' => 'Inline Content'],
				'support' => ['label' => 'Support'],
				'go_pro' => ['label' => 'Go Pro'],
			];
		}

		echo '<div class="sharemypost-options-container">';
			foreach ($sub_tabs as $slug => $tab) {
				$style = ($slug === 'inline_content') ? '' : 'display:none;';
				echo '<div id="subtab-' . esc_attr($slug) . '" class="sharemypost-sub-panel" style="' . esc_attr($style) . '">';
					if($slug === 'custom_networks'){
						do_action('sharemypost_custom_networks_settings');
					} elseif($slug === 'floating_bar'){
							do_action('sharemypost_floating_bar_settings');

					} elseif($slug === 'license'){
							do_action('sharemypost_render_license_page');
					} elseif($slug === 'support'){
						self::support_page();
					} elseif($slug === 'go_pro'){
						self::go_pro_page();
					} else {
						$method = $slug . '_settings';
						if(method_exists('\ShareMyPost\Settings\UI', $method)){
							\ShareMyPost\Settings\UI::$method();
						}
					}
				echo '</div>';
			}
		echo '</div>';
	}

	static function enqueue_admin_assets($hook) {
		if(false === strpos($hook, 'sharemypost')){
			return;
		}

		wp_enqueue_style('sharemypost-admin', SHAREMYPOST_PLUGIN_URL . 'assets/css/admin.css', [], SHAREMYPOST_VERSION);
		wp_enqueue_media();
		wp_enqueue_script('sharemypost-admin', SHAREMYPOST_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], SHAREMYPOST_VERSION, true);
		wp_localize_script('sharemypost-admin', 'sharemypost_data', [
			'nonce' => wp_create_nonce('sharemypost_admin_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'is_pro' => defined('SHAREMYPOST_PRO_VERSION')
		]);
	}

	static function support_page(){
		echo '<div class="sharemypost-support-page" style="margin-top: 24px">
			<div class="sharemypost-logo-name" style="margin: 0 auto 50px;">
				<img alt="' . esc_html('sharemypost logo', 'sharemypost') . '" width="300px" src="' . esc_url(SHAREMYPOST_ASSETS_URL) . '/img/sharemypost-logo-name.png' . '"/>	
			</div>
			<h2>' . esc_html__('Help & Support', 'sharemypost') . '</h2>
			<p>' . esc_html__('You can contact the ShareMyPost team via email at ', 'sharemypost') . '<a href="mailto:support@sharemypost.net">support@sharemypost.net</a> ' . esc_html__('or through our Support Ticket System.', 'sharemypost') . '</p>
			<p>' . esc_html__('You can also check the documentation here:', 'sharemypost') . ' <a href="https://sharemypost.net/docs" target="_blank" rel="noopener noreferrer">https://sharemypost.net/docs</a></p>
		</div>';
	}

	static function go_pro_page(){
		echo '<div class="sharemypost-gopro-container">
			<div class="sharemypost-gopro-hero">
				<h2 class="sharemypost-gopro-title">' . esc_html__('Upgrade to ShareMyPost PRO', 'sharemypost') . '</h2>
				<p class="sharemypost-gopro-description">' . esc_html__('Unlock advanced sharing features, deep tracking analytics, AI tools, and premium support.', 'sharemypost') . '</p>
				<a href="https://sharemypost.net" target="_blank" class="button button-primary button-large sharemypost-gopro-button">' . esc_html__('Get ShareMyPost Pro Now', 'sharemypost') . '</a>
			</div>

			<div class="sharemypost-features-grid">
				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-chart-bar sharemypost-feature-icon"></span>
						' . esc_html__('Advanced Analytics & Tracking', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Seamless Google Analytics 4 (GA4) integration and customizable dynamic UTM tracking parameters to measure your viral loops.', 'sharemypost') . '</p>
				</div>

				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-admin-links sharemypost-feature-icon"></span>
						' . esc_html__('Universal Modal Sharing', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Add a single universal share button that triggers a stunning modal listing all 30+ networks, keeping your layout clean.', 'sharemypost') . '</p>
				</div>

				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-edit sharemypost-feature-icon"></span>
						' . esc_html__('Click to X (Twitter) Shortcode', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Create high-converting callout boxes within your content using customizable themes, accents, and custom pre-written tweets.', 'sharemypost') . '</p>
				</div>

				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-admin-generic sharemypost-feature-icon"></span>
						' . esc_html__('AI Sharing Integration', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Enable one-click sharing/prompting to popular AI assistants like ChatGPT, Gemini, Claude, Perplexity, and Grok.', 'sharemypost') . '</p>
				</div>

				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-art sharemypost-feature-icon"></span>
						' . esc_html__('Advanced Customization', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Unlock 6 button shapes, 5 button styles, adjustable custom sizes, margins, custom colors, and custom network icons.', 'sharemypost') . '</p>
				</div>

				<div class="sharemypost-feature-card">
					<h3 class="sharemypost-feature-title">
						<span class="dashicons dashicons-align-left sharemypost-feature-icon"></span>
						' . esc_html__('Floating Share Bars', 'sharemypost') . '
					</h3>
					<p class="sharemypost-feature-description">' . esc_html__('Add sticky side bars that follow visitors as they scroll, fully customizable in behavior and device visibility.', 'sharemypost') . '</p>
				</div>
			</div>

			<div class="sharemypost-gopro-footer">
				' . esc_html__('Need help? Feel free to contact our support team at support@sharemypost.net', 'sharemypost') . '
			</div>
		</div>';
	}
}