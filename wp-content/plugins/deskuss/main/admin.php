<?php
/**
 * Deskuss Settings
 *
 * Admin menu, settings page, registration of settings/sections/fields,
 * license page handler, and related callbacks.
 *
 * @package Deskuss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Admin menu and settings page
add_action('admin_menu', 'deskuss_admin_menu');
function deskuss_admin_menu() {
	$capability = 'manage_options';
	$parent = DESKUSS_TICKET_URL .'/index.php';
	$page = '';
	
	$permalink_structure = get_option('permalink_structure');

	if(empty($permalink_structure)){
		$parent = 'deskuss';
		$page = 'deskuss_admin_landing';
	}

	add_menu_page(
		__('Deskuss', 'deskuss'),
		__('Deskuss', 'deskuss'),
		$capability,
		$parent,
		$page,
		'dashicons-tickets-alt',
		30
	);

	add_submenu_page(
		$parent,
		__('Deskuss', 'deskuss'),
		__('Deskuss', 'deskuss'),
		$capability,
		$parent,
		$page
	);

	add_submenu_page(
		$parent,
		__('Deskuss Settings', 'deskuss'),
		__('Settings', 'deskuss'),
		$capability,
		'deskuss-settings',
		'deskuss_settings_handler'
	);

	add_submenu_page(
		$parent,
		__('Deskuss License', 'deskuss'),
		__('License', 'deskuss'),
		$capability,
		'deskuss_license',
		'deskuss_license_handler'
	);
}

function deskuss_admin_landing() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$deskuss_url  = DESKUSS_TICKET_URL;
	$permalink_url = admin_url('options-permalink.php');
	?>
	<div class="wrap">
		<h1><?php echo esc_html__('Deskuss', 'deskuss'); ?></h1>
		<div class="notice notice-warning" style="max-width:640px;">
			<p><strong><?php echo esc_html__('Deskuss requires pretty permalinks to be enabled.', 'deskuss'); ?></strong></p>
			<p><?php echo esc_html__('Your WordPress permalink structure is currently set to "Plain", which prevents Deskuss from being accessible.', 'deskuss'); ?></p>
			<p><?php
				printf(
					wp_kses(
						__('Deskuss is available at <code>%s</code> but will not load until pretty permalinks are enabled. Please change your permalink settings, then return here to open Deskuss.', 'deskuss'),
						array('code' => array())
					),
					esc_html($deskuss_url)
				);
			?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url($permalink_url); ?>"><?php echo esc_html__('Change Permalink Settings', 'deskuss'); ?></a>
				&nbsp;
				<a class="button" href="<?php echo esc_url($deskuss_url); ?>"><?php echo esc_html__('Open Deskuss', 'deskuss'); ?></a>
			</p>
		</div>
	</div>
	<?php
}

function deskuss_settings_handler(){
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields('deskuss_settings_group');
			do_settings_sections('deskuss-settings');
			submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action('admin_init', 'deskuss_register_settings');
function deskuss_register_settings() {
	register_setting('deskuss_settings_group', 'deskuss_url_slug', array(
		'type'              => 'string',
		'default'           => 'deskuss',
		'sanitize_callback' => 'deskuss_sanitize_url_slug',
	));

	add_settings_section(
		'deskuss_url_section',
		__('URL Settings', 'deskuss'),
		'__return_empty_string',
		'deskuss-settings'
	);

	add_settings_field(
		'deskuss_url_slug',
		__('URL Slug', 'deskuss'),
		'deskuss_url_slug_field_callback',
		'deskuss-settings',
		'deskuss_url_section'
	);

	register_setting('deskuss_settings_group', 'deskuss_client_dev_mode', array(
		'type'    => 'boolean',
		'default' => false,
	));

	add_settings_section(
		'deskuss_dev_mode_section',
		__('Development Mode', 'deskuss'),
		'__return_empty_string',
		'deskuss-settings'
	);

	add_settings_field(
		'deskuss_client_dev_mode',
		__('Disable Client Pages', 'deskuss'),
		'deskuss_client_dev_mode_field_callback',
		'deskuss-settings',
		'deskuss_dev_mode_section'
	);
}

function deskuss_client_dev_mode_field_callback() {
	$checked = get_option('deskuss_client_dev_mode');
	?>
	<input type="checkbox" name="deskuss_client_dev_mode" value="1" <?php checked($checked, 1); ?> />
	<p class="description"><?php _e('When enabled, non-admin visitors (including staff) see a maintenance notice on all Deskuss pages. Administrators retain full access for development.', 'deskuss'); ?></p>
	<?php
}

function deskuss_sanitize_url_slug($value) {
	$value = sanitize_title($value);
	if (empty($value)) {
		$value = 'deskuss';
	}

	$old_value = get_option('deskuss_url_slug', 'deskuss');
	if ($old_value !== $value) {
		update_option('deskuss_flush_rewrite_rules', true);
	}

	return $value;
}

function deskuss_url_slug_field_callback() {
	$slug = get_option('deskuss_url_slug', 'deskuss');
	?>
	<input type="text" name="deskuss_url_slug" value="<?php echo esc_attr($slug); ?>" />
	<p class="description"><?php _e('The URL slug for Deskuss (e.g., "deskuss" or "tickets"). Change this to modify the base URL of the helpdesk.', 'deskuss'); ?></p>
	<?php
}

add_action('admin_init', 'deskuss_maybe_flush_rewrite_rules');
function deskuss_maybe_flush_rewrite_rules() {
	if (get_option('deskuss_flush_rewrite_rules')) {
		flush_rewrite_rules();
		delete_option('deskuss_flush_rewrite_rules');
	}
}

function deskuss_license_handler(){
	global $deskuss;
	
	include_once DESKUSS_DIR .'/main/license.php';
	deskuss_license();
}