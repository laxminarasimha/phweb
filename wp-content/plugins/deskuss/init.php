<?php
/*
* Deskuss
* https://deskuss.com
* (c) Softaculous Team
*/

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

define('DESKUSS_BASE', plugin_basename(DESKUSS_FILE));
define('DESKUSS_PRO_BASE', 'deskuss-pro/deskuss-pro.php');
define('DESKUSS_VERSION', '1.0.7');
define('DESKUSS_DIR', dirname(DESKUSS_FILE));
define('DESKUSS_PRO_DIR', DESKUSS_DIR .'/main/premium');
define('DESKUSS_SLUG', 'deskuss');
define('DESKUSS_URL', plugins_url('', DESKUSS_FILE));
define('DESKUSS_CSS', DESKUSS_URL.'/assets/css');
define('DESKUSS_JS', DESKUSS_URL.'/assets/js');
define('DESKUSS_PRO_URL', 'https://deskuss.com/pricing?from=plugin');
define('DESKUSS_WWW_URL', 'https://deskuss.com/');
define('DESKUSS_DOCS', 'https://deskuss.com/docs/');
define('DESKUSS_API', 'https://a.softaculous.com/deskuss/');
define('DESKUSS_TICKET_URL', get_site_url() . '/' . get_option('deskuss_url_slug', 'deskuss') . '/admin/');
define('DESKUSS_DB_PREFIX', 'deskuss_');
define('DESKUSS_MEDIA_URL', DESKUSS_URL.'/assets');
define('DESKUSS_VIEWS_DIR', DESKUSS_DIR . '/views/');
define('DESKUSS_CONFIG_DIR', DESKUSS_DIR . '/config/');
define('DESKUSS_DB_DIR', DESKUSS_DIR . '/db/');
define('INCLUDE_DIR', DESKUSS_DIR .'/packages/include/' );
define('STAFFINC_DIR', DESKUSS_VIEWS_DIR . 'staff/');
define('CLIENTINC_DIR', DESKUSS_VIEWS_DIR . 'client/');
include_once(DESKUSS_DIR.'/main/functions.php');

require_once DESKUSS_DIR . '/src/Core/Autoloader.php';
\Deskuss\Core\Autoloader::register();

spl_autoload_register('deskuss_autoload_register');
function deskuss_autoload_register($class){
	
	if(!preg_match('/DESKUSS\\\\/', $class)){
		return;
	}
	
	$file = strtolower(str_replace( array('DESKUSS', '\\'), array('', DIRECTORY_SEPARATOR), $class)); 
	$file = trim(strtolower($file), '/').'.php';

	// For Free
	if(file_exists(DESKUSS_DIR.'/main/'.$file)){
		include_once(DESKUSS_DIR.'/main/'.$file);
	}
	
	// For Pro
	if(file_exists(DESKUSS_PRO_DIR.'/'.$file)){
		include_once(DESKUSS_PRO_DIR.'/'.$file);
	}
	
}

if(is_admin()){
	include_once(DESKUSS_DIR.'/main/admin.php');
}

/* function deskuss_died(){
	print_r(error_get_last());
}
//register_shutdown_function('deskuss_died');

function deskuss_error_handler($errno, $errstr, $errfile, $errline) {
	if ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_DEPRECATED || $errno == E_USER_DEPRECATED || $errno == E_WARNING || $errno == E_USER_WARNING) {
		return true;
	}
	return false;
}
set_error_handler('deskuss_error_handler'); */

// Ok so we are now ready to go
register_activation_hook(DESKUSS_FILE, 'deskuss_activation');

// Is called when the ADMIN enables the plugin
function deskuss_activation(){
	global $wpdb;

	$sql = array();
	
	// Create databases
	deskuss_create_tables();
	deskuss_create_threads_table();
	deskuss_create_notifications_table();
	add_option('deskuss_version', DESKUSS_VERSION);

	// Run DB migrations for the auto-close feature and any later schema bumps.
	require_once DESKUSS_DIR . '/main/db-upgrader.php';
	deskuss_run_db_upgrades();

	// Ensure schema_signature matches core.sig after table creation
	deskuss_ensure_schema_signature();

	// Auto-sync existing WP administrators as Deskuss super admins
	deskuss_sync_all_wp_admins();

	// Flush rewrite rules so /deskuss/ routes are registered
	flush_rewrite_rules();
}

// Auto-sync all WP users with the administrator role as Deskuss super admins
function deskuss_sync_all_wp_admins(){
	if(get_option('deskuss_initial_admins_synced')){
		return;
	}

	deskuss_sync_all_wp_admins_with_count();
	update_option('deskuss_initial_admins_synced', time());
}

// Manual variant: returns the number of admins newly synced (for the staffmembers button).
// Does NOT set the deskuss_initial_admins_synced option, so it can be re-run.
function deskuss_sync_all_wp_admins_with_count(){
	$admins = get_users(array(
		'role'    => 'administrator',
		'fields'  => array('ID'),
	));

	if(empty($admins)){
		return 0;
	}

	$count = 0;
	foreach($admins as $admin){
		$result = deskuss_sync_wp_user_to_staff($admin->ID, true);
		if($result){
			$count++;
		}
	}

	return $count;
}

// Sync a single WP user to the Deskuss dk_staff table.
// If $force_admin is true, the staff is created/updated with isadmin=1.
// Otherwise the WP role is checked: administrator => isadmin=1, else isadmin=0.
function deskuss_sync_wp_user_to_staff($wp_user_id, $force_admin = false){
	global $wpdb;

	$wp_user = get_userdata($wp_user_id);
	if(!$wp_user || !$wp_user->ID){
		return false;
	}

	$is_admin = $force_admin || user_can($wp_user->ID, 'administrator');
	// $is_staff = user_can($wp_user->ID, 'deskuss_staff') || user_can($wp_user->ID, 'administrator');

	// Only sync if user has deskuss_staff capability OR is admin
	if(!$is_admin){
		return false;
	}

	$staff_table = $wpdb->prefix . 'dk_staff';
	$now = current_time('mysql', true);
	$admin_permissions = deskuss_admin_permissions_json();
	$firstname = $wp_user->first_name ?: $wp_user->user_login;
	$lastname  = $wp_user->last_name ?: '';
	// Admins authenticate via WordPress, so no Deskuss-direct password hash is set.
	$passwd = '';

	// Check if a staff row already exists (by username or email)
	$existing = $wpdb->get_row($wpdb->prepare(
		"SELECT staff_id, permissions FROM {$staff_table} WHERE username = %s OR email = %s LIMIT 1",
		$wp_user->user_login,
		$wp_user->user_email
	));

	if($existing){
		// Update isadmin flag based on current WP role, and backfill admin
		// permissions / blank password if they are missing.
		$update_data = array('isadmin' => $is_admin ? 1 : 0);
		$update_format = array('%d');

		if($is_admin && empty($existing->permissions)){
			$update_data['permissions'] = $admin_permissions;
			$update_data['passwd']      = $passwd;
			$update_data['isactive']    = 1;
			$update_data['isvisible']   = 1;
			$update_format[] = '%s';
			$update_format[] = '%s';
			$update_format[] = '%d';
			$update_format[] = '%d';
		}

		$wpdb->update(
			$staff_table,
			$update_data,
			array('staff_id' => $existing->staff_id),
			$update_format,
			array('%d')
		);
		return (int)$existing->staff_id;
	}

	// Create a new staff row matching the default admin template
	// (33 columns, all 34 table fields except auto-increment staff_id).
	$result = $wpdb->query($wpdb->prepare(
		"INSERT INTO {$staff_table}
			(dept_id, role_id, username, firstname, lastname, email, isadmin, max_page_size, permissions, created, updated)
			VALUES
			(%d, %d, %s, %s, %s, %s, %d, %d, %s, %s, %s)",
		1,                              // dept_id
		1,                              // role_id
		$wp_user->user_login,           // username
		$firstname,                     // firstname
		$lastname,                      // lastname
		$wp_user->user_email,           // email
		$is_admin ? 1 : 0,              // isadmin
		25,                             // max_page_size
		$is_admin ? $admin_permissions : null, // permissions (admin JSON for admins)
		$now,                           // created
		$now                            // updated
	));

	if($result){
		return (int)$wpdb->insert_id;
	}

	return false;
}

add_action('user_register', 'deskuss_sync_user_on_register', 10, 1);
function deskuss_sync_user_on_register($user_id){
	deskuss_sync_wp_user_to_staff($user_id, false);
}

add_action('set_user_role', 'deskuss_sync_user_on_role_change', 10, 3);
function deskuss_sync_user_on_role_change($user_id, $new_role, $old_roles){
	deskuss_sync_wp_user_to_staff($user_id, false);
}

add_action('profile_update', 'deskuss_sync_user_on_profile_update', 10, 2);
function deskuss_sync_user_on_profile_update($user_id, $old_user_data){
	deskuss_sync_wp_user_to_staff($user_id, false);
}

// Checks if we are to update ?
function deskuss_update_check(){
	global $wpdb;

	$sql = array();
	$current_version = get_option('deskuss_version');
	$version = (int) str_replace('.', '', $current_version);

	// No update required
	if($current_version == DESKUSS_VERSION){
		// Even if version matches, verify schema_signature is correct
		// This handles edge cases where tables exist but signature is missing
		deskuss_ensure_schema_signature();
		return true;
	}

	// Is it first run ?
	if(empty($current_version)){

		// Reinstall
		deskuss_activation();

		// Trick the following if conditions to not run
		$version = (int) str_replace('.', '', DESKUSS_VERSION);

	}

	// Run any pending DB migrations (idempotent — safe to run on every load).
	require_once DESKUSS_DIR . '/main/db-upgrader.php';
	deskuss_run_db_upgrades();

	// Save the new Version
	update_option('deskuss_version', DESKUSS_VERSION);
	
}

// Add action to load Deskuss
add_action('plugins_loaded', 'deskuss_load_plugin');
// Function to check the page and load Deskuss assets
function deskuss_load_plugin() {
	global $deskuss;
	if (empty($deskuss)) {
		$deskuss = new stdClass();
	}

	// Load license
	deskuss_load_license();

	// Check if the installed version is outdated
	deskuss_update_check();

	// Get plugin options
	$options = get_option('deskuss_options', array());
	$deskuss->options = empty($options) ? array() : $options;
}

// Update checker
add_action('plugins_loaded', 'deskuss_init_update_checker', 20);
function deskuss_init_update_checker(){
	
	if(!current_user_can('activate_plugins')){
		return;
	}
	
	include_once(DESKUSS_DIR.'/main/plugin-update-checker.php');
	$deskuss_updater = Deskuss_PucFactory::buildUpdateChecker(deskuss_pro_api_url().'updates.php?version='.DESKUSS_VERSION, DESKUSS_FILE);
	
	// Add the license key to query arguments
	$deskuss_updater->addQueryArgFilter('deskuss_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-deskuss', 'deskuss_pro_updater_check_link', 10, 1);
}

// Show expiry notice if license is expired
add_action('admin_notices', 'deskuss_expiry_notice');
function deskuss_expiry_notice(){
	global $deskuss;
	
	if(!current_user_can('activate_plugins')){
		return;
	}

	if(!empty($deskuss->license) && empty($deskuss->license['active']) && strpos($deskuss->license['license'], 'SOFTWP') !== FALSE){
		add_filter('softaculous_expired_licenses', 'deskuss_plugins_expired');
		
		$dismissed_at = get_option('softaculous_expired_licenses', 0);
		$expired_plugins = apply_filters('softaculous_expired_licenses', []);
		if(
			!empty($expired_plugins) && 
			is_array($expired_plugins) && 
			!defined('DESKUSS_EXPIRY_LICENSES') && 
			(empty($dismissed_at) || ($dismissed_at + WEEK_IN_SECONDS) < time())
		){
			define('DESKUSS_EXPIRY_LICENSES', true);
			echo '<div class="notice notice-error is-dismissible" id="deskuss-expiry-notice">
					<p>'.sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please renew it to continue receiving uninterrupted updates and support for %3$s.', 'deskuss'),
					'<font style="color:red;"><b>',
					'</b></font>',
					esc_html(implode(', ', $expired_plugins))
					). '</p>
				</div>';

			wp_register_script('deskuss-expiry-notice', '', ['jquery'], DESKUSS_VERSION, true);
			wp_enqueue_script('deskuss-expiry-notice');
			wp_add_inline_script('deskuss-expiry-notice', '
			jQuery(document).ready(function(){
				jQuery("#deskuss-expiry-notice").on("click", ".notice-dismiss", function(e){
					e.preventDefault();
					let target = jQuery(e.target);
					let jEle = target.closest("#deskuss-expiry-notice");
					jEle.slideUp();
					jQuery.post("'.admin_url('admin-ajax.php').'", {
						security : "'.wp_create_nonce('deskuss_expiry_notice').'",
						action: "deskuss_dismiss_expired_licenses",
					}, function(res){
						if(!res["success"]){
							alert(res["data"]);
						}
					}).fail(function(data){
						alert("There seems to be some issue dismissing this alert");
					});
				});
			})');
		}
	}
}

function deskuss_plugins_expired($plugins){
	$plugins[] = 'Deskuss';
	return $plugins;
}

add_action('init', 'deskuss_custom_add_user_role');
function deskuss_custom_add_user_role() {
	if ( ! get_role( 'deskuss_staff' ) ) {
		add_role(
			'deskuss_staff',
			__( 'Deskuss Staff', 'deskuss' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'upload_files' => true,
			)
		);
	}
	if ( ! get_role( 'support_client' ) ) {
		add_role(
			'support_client',
			__( 'Support Client', 'deskuss' ),
			array(
				'read' => true,
			)
		);
	}
}

add_action('admin_init', 'deskuss_block_support_client_admin');
function deskuss_block_support_client_admin() {
	if (!is_user_logged_in() || wp_doing_ajax()) {
		return;
	}
	$user = wp_get_current_user();
	if (in_array('support_client', (array) $user->roles)
		&& !user_can($user->ID, 'deskuss_staff')
		&& !user_can($user->ID, 'administrator')) {
		$slug = get_option('deskuss_url_slug', 'deskuss');
		wp_redirect(home_url('/' . $slug . '/'));
		exit;
	}
}

add_action('after_setup_theme', 'deskuss_hide_admin_bar_for_support_clients');
function deskuss_hide_admin_bar_for_support_clients() {
	$user = wp_get_current_user();
	if (in_array('support_client', (array) $user->roles)
		&& !user_can($user->ID, 'deskuss_staff')
		&& !user_can($user->ID, 'administrator')) {
		show_admin_bar(false);
	}
}

// Hook to add custom capabilities
add_action('admin_init', 'deskuss_add_custom_capabilities');
function deskuss_add_custom_capabilities() {
	// Only run once per version to avoid repeated DB writes.
	$cap_version = get_option( 'deskuss_caps_version', '' );
	if ( DESKUSS_VERSION === $cap_version ) {
		return;
	}

	$role   = get_role( 'administrator' );
	$editor = get_role( 'editor' );
	if ( $role ) {
		$role->add_cap( 'deskuss_staff' );
		$role->add_cap( 'support_client' );
	}
	if ( $editor ) {
		$editor->add_cap( 'deskuss_staff' );
		$editor->add_cap( 'support_client' );
	}

	update_option( 'deskuss_caps_version', DESKUSS_VERSION );
}

// Register WordPress rewrite rules to replace .htaccess routing
add_action('init', 'deskuss_add_rewrite_rules');
function deskuss_add_rewrite_rules() {
	$slug = get_option('deskuss_url_slug', 'deskuss');
	// Top-level /{slug}/ pages and files (e.g. /deskuss/index.php, /deskuss/login.php, /deskuss/open.php)
	add_rewrite_rule('^' . $slug . '/(.*)?$', 'index.php?deskuss_route=$matches[1]', 'top');
}

// Ensure the Deskuss rewrite rule is present in WP's rewrite array.
// On some installs the rule is never registered/flushed (e.g. permalinks
// never saved, or activation ran before the rule was added), so the legacy
// REQUEST_URI fallback in deskuss_parse_request is the only thing keeping
// routing alive. Force a one-time flush if the rule is missing.
add_action('plugins_loaded', 'deskuss_maybe_schedule_rewrite_flush', 20);
function deskuss_maybe_schedule_rewrite_flush() {
	if (!get_option('deskuss_flush_rewrite_rules')) {
		global $wp_rewrite;
		$rules = is_array($wp_rewrite->rules ?? null) ? $wp_rewrite->rules : array();
		$has_deskuss = false;
		foreach (array_keys($rules) as $pattern) {
			if (strpos($pattern, 'deskuss/') !== false) {
				$has_deskuss = true;
				break;
			}
		}
		if (!$has_deskuss) {
			update_option('deskuss_flush_rewrite_rules', 1);
		}
	}
}

// Register query vars for Deskuss routing
add_filter('query_vars', 'deskuss_query_vars');
function deskuss_query_vars($vars) {
	$vars[] = 'deskuss_route';
	return $vars;
}

// Flush rewrite rules on activation and deactivation
register_deactivation_hook(DESKUSS_FILE, 'deskuss_flush_rewrite_rules_on_deactivate');
function deskuss_flush_rewrite_rules_on_deactivate() {
	flush_rewrite_rules();
}

// Render a generic maintenance notice and stop execution.
function deskuss_show_maintenance_notice() {
	wp_die(
		'<div style="max-width:640px;margin:40px auto;text-align:center;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">'
		. '<h2>' . esc_html__('Service Temporarily Unavailable', 'deskuss') . '</h2>'
		. '<p>' . esc_html__('The support center is temporarily unavailable. Please contact the site administrator for assistance.', 'deskuss') . '</p>'
		. '</div>',
		esc_html__('Service Unavailable', 'deskuss'),
		array('response' => 503)
	);
}

// Route Deskuss requests through WordPress instead of .htaccess
add_action('parse_request', 'deskuss_parse_request', 1);
function deskuss_parse_request($wp) {
	// Make key Deskuss globals available in this function scope
	// so included view files can access them without global declarations.
	global $cfg, $dsk, $session;
	$slug = get_option('deskuss_url_slug', 'deskuss');

	// Also catch when WordPress matches /{slug}/ as a page name
	if (!isset($wp->query_vars['deskuss_route'])) {
		// Check if this looks like a deskuss route
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		// Strip query string before processing
		$uri_path = strtok($uri, '?');
		$home_path = rtrim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
		$path = $uri_path;
		if ($home_path && strpos($uri_path, $home_path) === 0) {
			$path = substr($uri_path, strlen($home_path));
		}
		$path = ltrim($path, '/');

		if ($path === $slug) {
			$wp->query_vars['deskuss_route'] = '';
		} elseif (strpos($path, $slug . '/') === 0) {
			// This is a deskuss route that WordPress misrouted as a page
			$wp->query_vars['deskuss_route'] = substr($path, strlen($slug) + 1);
		} else {
			return;
		}
	}
	$route = $wp->query_vars['deskuss_route'];
	$public_html = DESKUSS_DIR . '/packages';
	$assets_dir = DESKUSS_DIR . '/assets';
	$site_url = home_url();
	$site_url_path = rtrim(parse_url($site_url, PHP_URL_PATH) ?? '', '/');
	if (empty($site_url_path)) {
		$site_url_path = '';
	}

	// Handle static assets (CSS, JS, images, fonts, etc.) directly from assets/
	// ONLY when the route starts with `assets/` — otherwise URLs like
	// `admin/ajax.php/.../something.json` (AJAX endpoints) would be
	// misidentified as static assets because `.json` matched, causing the
	// request to fall through to WordPress.
	$ext = strtolower(pathinfo($route, PATHINFO_EXTENSION));
	$static_extensions = array('css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
		'woff', 'woff2', 'ttf', 'eot', 'pdf', 'map', 'json', 'xml', 'txt',
		'html', 'htm', 'otf', 'webp', 'bmp', 'tiff', 'tif');

	// Reject path traversal in the static-asset branch (was missing — only
	// deskuss_resolve_route() had the '..' guard, and the static branch runs first).
	if (strpos($route, '..') !== false) {
		return;
	}

	if (strpos($route, 'assets/') === 0 && in_array($ext, $static_extensions)) {
		// Strip leading 'assets/' from the route since $assets_dir already points to the assets directory
		$route_path = $route;
		if (strpos($route_path, 'assets/') === 0) {
			$route_path = substr($route_path, 7);
		}
		$real_assets = realpath($assets_dir);
		$real_file = realpath($assets_dir . '/' . $route_path);
		if ($real_file === false || $real_assets === false) {
			return;
		}
		// Ensure the resolved file is strictly inside the assets directory.
		if (strpos($real_file, $real_assets . DIRECTORY_SEPARATOR) !== 0) {
			return;
		}
		deskuss_serve_static_file($real_file, $ext);
		exit;
	}

	// Map the route to a physical file and set PATH_INFO for dispatcher-based endpoints
	$file = deskuss_resolve_route($route, $public_html);

	if ($file === false) {
		return;
	}

	// Defense in depth: confirm the resolved PHP file is strictly inside
	// the packages directory (guards against symlinks / future route bugs).
	$real_public = realpath($public_html);
	$real_file = realpath($file);
	if ($real_file === false || $real_public === false
		|| strpos($real_file, $real_public . DIRECTORY_SEPARATOR) !== 0
	) {
		return;
	}
	$file = $real_file;

	// Set PATH_INFO for dispatcher URLs (api, apps, admin/apps, pages)
	$path_info = deskuss_compute_path_info($route);

	// Pre-define DESKUSS_ROOT_PATH so bootstrap.php doesn't need to compute it.
	// This ensures all URL construction (assets, links, redirects) uses the
	// correct path including any WordPress subdirectory install prefix.
	if (!defined('DESKUSS_ROOT_PATH')) {
		$home_path = rtrim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
		define('DESKUSS_ROOT_PATH', ($home_path ? $home_path : '') . '/' . $slug . '/');
	}

	// Set up the server environment that the package expects
	// SCRIPT_NAME is used by bootstrap.php's get_root_path() and by the package
	// for self-referencing URLs. We simulate what .htaccess ModRewrite would set.
	$_SERVER['SCRIPT_NAME'] = $site_url_path . '/' . $slug . '/' . $route;
	$_SERVER['SCRIPT_FILENAME'] = $file;

	if ($path_info !== null) {
		$_SERVER['PATH_INFO'] = $path_info;
		$_SERVER['ORIG_PATH_INFO'] = $path_info;
	}

	// --- License expired gate (runs first — most critical) ---
	global $deskuss;
	if (empty($deskuss->license) || empty($deskuss->license['active'])) {
		if (current_user_can('manage_options')) {
			$get_license_url = DESKUSS_PRO_URL;
			$add_license_url = admin_url('admin.php?page=deskuss_license');
			wp_die(
				'<div style="max-width:640px;margin:40px auto;text-align:center;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">'
				. '<h2>' . esc_html__('Deskuss License Expired', 'deskuss') . '</h2>'
				. '<p>' . esc_html__('Your Deskuss license has expired. Please renew or add a valid license key to continue using the helpdesk.', 'deskuss') . '</p>'
				. '<p>'
				. '<a href="' . esc_url($get_license_url) . '" target="_blank" style="text-decoration:none;padding:8px 16px;background:#2271b1;color:#fff;border-radius:4px;margin:0 4px;">' . esc_html__('Get License', 'deskuss') . '</a>'
				. '<a href="' . esc_url($add_license_url) . '" style="text-decoration:none;padding:8px 16px;background:#f0f0f1;color:#2271b1;border:1px solid #2271b1;border-radius:4px;margin:0 4px;">' . esc_html__('Add License', 'deskuss') . '</a>'
				. '</p>'
				. '</div>',
				esc_html__('Deskuss License Expired', 'deskuss'),
				array('response' => 503)
			);
		} else {
			deskuss_show_maintenance_notice();
		}
		exit;
	}

	// --- Development mode gate ---
	if (get_option('deskuss_client_dev_mode') && !current_user_can('manage_options')) {
		deskuss_show_maintenance_notice();
		exit;
	}

	// Include the resolved file and stop WordPress from proceeding further
	include $file;
	exit;
}

/**
 * Serve a static file with proper headers and caching.
 *
 * @param string $file  Absolute path to the static file
 * @param string $ext   The file extension (lowercase)
 */
function deskuss_serve_static_file($file, $ext) {
	$mime_types = array(
		'css'   => 'text/css',
		'js'    => 'application/javascript',
		'png'   => 'image/png',
		'jpg'   => 'image/jpeg',
		'jpeg'  => 'image/jpeg',
		'gif'   => 'image/gif',
		'svg'   => 'image/svg+xml',
		'ico'   => 'image/x-icon',
		'webp'  => 'image/webp',
		'bmp'   => 'image/bmp',
		'tiff'  => 'image/tiff',
		'tif'   => 'image/tiff',
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
		'ttf'   => 'font/ttf',
		'otf'   => 'font/otf',
		'eot'   => 'application/vnd.ms-fontobject',
		'pdf'   => 'application/pdf',
		'map'   => 'application/json',
		'json'  => 'application/json',
		'xml'   => 'application/xml',
		'html'  => 'text/html',
		'htm'   => 'text/html',
		'txt'   => 'text/plain',
	);

	$content_type = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
	$last_modified = filemtime($file);
	$etag = md5($file . $last_modified);

	header('Content-Type: ' . $content_type);
	header('ETag: "' . $etag . '"');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	header('Cache-Control: public, max-age=86400');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

	// Handle conditional requests (304 Not Modified)
	if (
		(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified) ||
		(isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag)
	) {
		header('HTTP/1.1 304 Not Modified');
		return;
	}

	readfile($file);
}

/**
 * Resolve a DeskUss route to the physical PHP file that should handle it.
 *
 * Handles: direct PHP files, dispatcher-based directories (api, apps, pages, admin/apps),
 * static assets (css, js, images), and default index files.
 *
 * @param string $route        The route path after /deskuss/
 * @param string $public_html  Absolute path to the public_html directory
 * @return string|false        The resolved file path, or false to let WP handle
 */
function deskuss_resolve_route($route, $public_html) {

	// Prevent directory traversal attacks
	if (strpos($route, '..') !== false) {
		return false;
	}

	// Empty or trailing-slash routes → index.php
	if ($route === '' || $route === '/') {
		return $public_html . '/index.php';
	}

	// Route to a real PHP file that exists directly
	$direct = $public_html . '/' . $route;
	if (is_file($direct) && pathinfo($direct, PATHINFO_EXTENSION) === 'php') {
		return $direct;
	}

	// Static assets (css, js, images, fonts, etc.) — serve directly if the file exists
	if (is_file($direct)) {
		return $direct;
	}

	// api/ → api/http.php (dispatcher)
	if (preg_match('#^api(/.*)?$#', $route, $m)) {
		return $public_html . '/api/http.php';
	}

	// apps/ → apps/dispatcher.php (dispatcher)
	if (preg_match('#^apps(/.*)?$#', $route, $m)) {
		return $public_html . '/apps/dispatcher.php';
	}

	// pages/ → pages/index.php (dispatcher)
	if (preg_match('#^pages(/.*)?$#', $route, $m)) {
		return $public_html . '/pages/index.php';
	}

	// admin/apps/ → admin/apps/dispatcher.php (dispatcher)
	if (preg_match('#^admin/apps(/.*)?$#', $route, $m)) {
		return $public_html . '/admin/apps/dispatcher.php';
	}

	// admin/ with no specific file → admin/index.php
	if ($route === 'admin' || $route === 'admin/') {
		return $public_html . '/admin/index.php';
	}

	// admin/<page> without .php → try admin/<page>.php
	if (preg_match('#^admin/([a-zA-Z0-9_-]+?)/?$#', $route, $m)) {
		$admin_file = $public_html . '/admin/' . $m[1] . '.php';
		if (is_file($admin_file)) {
			return $admin_file;
		}
	}

	// admin/<file>.php/<sub-route> — PHP dispatcher with PATH_INFO.
	// Examples: admin/ajax.php/tickets/55/canned-resp/1.json,
	//           admin/ajax.php/forms/manage, admin/heartbeat.php
	if (preg_match('#^admin/([a-zA-Z0-9_.-]+\.php)(/.*)?$#', $route, $m)) {
		$admin_file = $public_html . '/admin/' . $m[1];
		if (is_file($admin_file)) {
			return $admin_file;
		}
	}

	// Directories that have index.php
	if (is_dir($public_html . '/' . $route) && is_file($public_html . '/' . rtrim($route, '/') . '/index.php')) {
		return $public_html . '/' . rtrim($route, '/') . '/index.php';
	}

	// PHP file as dispatcher (e.g. admin/ajax.php/tickets/1/transfer → admin/ajax.php with PATH_INFO /tickets/1/transfer)
	$parts = explode('/', $route);
	for ($i = 1; $i <= count($parts); $i++) {
		$candidate = implode('/', array_slice($parts, 0, $i));
		$candidate_file = $public_html . '/' . $candidate;
		if (is_file($candidate_file) && pathinfo($candidate_file, PATHINFO_EXTENSION) === 'php') {
			return $candidate_file;
		}
	}

	// Let WordPress handle 404
	return false;
}

/**
 * Compute PATH_INFO for dispatcher-based routes.
 *
 * The .htaccess rules rewrite URLs like:
 *   /deskuss/api/tickets.xml → api/http.php with PATH_INFO /tickets.xml
 *   /deskuss/apps/some/path  → apps/dispatcher.php with PATH_INFO /some/path
 *   /deskuss/pages/about     → pages/index.php with PATH_INFO /about
 *   /deskuss/admin/apps/x    → admin/apps/dispatcher.php with PATH_INFO /x
 *
 * @param string $route  The route path after /deskuss/
 * @return string|null   PATH_INFO value, or null if not applicable
 */
function deskuss_compute_path_info($route) {

	// api/... → everything after "api"
	if (preg_match('#^api(/.*)$#', $route, $m)) {
		return $m[1];
	}

	// apps/... → everything after "apps"
	if (preg_match('#^apps(/.*)$#', $route, $m)) {
		return $m[1];
	}

	// pages/... → everything after "pages"
	if (preg_match('#^pages(/.*)$#', $route, $m)) {
		return $m[1];
	}

	// admin/apps/... → everything after "admin/apps"
	if (preg_match('#^admin/apps(/.*)$#', $route, $m)) {
		return $m[1];
	}

	// PHP file as dispatcher (e.g. admin/ajax.php/tickets/1/transfer → PATH_INFO /tickets/1/transfer)
	$parts = explode('/', $route);
	for ($i = 1; $i < count($parts); $i++) {
		$candidate = implode('/', array_slice($parts, 0, $i));
		if (preg_match('/\.php$/', $candidate)) {
			return '/' . implode('/', array_slice($parts, $i));
		}
	}

	return null;
}

add_action('admin_enqueue_scripts', 'deskuss_enqueue_assets', 10);
function deskuss_enqueue_assets(){
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	// Only load on Deskuss admin pages.
	if ( false === strpos( $screen->id, 'deskuss' ) && false === strpos( $screen->id, 'dsk_' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script('jquery-ui-sortable');
}

if(wp_doing_ajax()){	
	include_once DESKUSS_DIR.'/main/ajax.php';
}
