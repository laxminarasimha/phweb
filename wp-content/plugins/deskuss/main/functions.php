<?php
/*
* Deskuss
* https://deskuss.com
* (c) Softaculous Team
*/

if ( ! defined( 'DESKUSS_VERSION' ) ) {
	wp_die( esc_html__( 'Hacking Attempt!', 'deskuss' ), 403 );
}

/**
 * Get current user's Deskuss role
 * @param int $user_id Optional user ID (defaults to current user)
 * @return string 'admin' | 'manager' | 'staff' | 'none'
 */
function deskuss_get_user_role($user_id = 0) {
	static $cache = array();

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( isset( $cache[ $user_id ] ) ) {
		return $cache[ $user_id ];
	}

	if ( current_user_can( 'administrator' ) ) {
		$cache[ $user_id ] = 'admin';
		return 'admin';
	}

	// Check if Department Manager (staff with dept.manager_id = staff_id).
	$staff_info = deskuss_get_user_info( $user_id );
	if ( ! empty( $staff_info ) ) {
		global $wpdb;
		$manager_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}dk_department WHERE manager_id = %d",
			$staff_info->member_id
		) );
		if ( $manager_count > 0 ) {
			$cache[ $user_id ] = 'manager';
			return 'manager';
		}
	}

	// Check if Staff role.
	if ( user_can( $user_id, 'deskuss_staff' ) ) {
		$cache[ $user_id ] = 'staff';
		return 'staff';
	}

	$cache[ $user_id ] = 'none';
	return 'none';
}
function deskuss_sparse($string, $data){

	foreach($data as $k => $v){
		if(!is_array($v)){
			$string = str_replace('[['.$k.']]', $v, $string);
		}
		
	}
	
	return $string;
}
function deskuss_create_tables(){
	
	global $wpdb;
	$table = $wpdb->prefix.'dk_thread';
	
	// Check if databases already imported
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table){
		deskuss_ensure_schema_signature();
		return;
	}
	
	$sql_file = DESKUSS_DIR.'/packages/deskuss_1.sql';
	
	// Read the SQL file
	$sql = file_get_contents($sql_file);
	
	if(!$sql){
		error_log('[Deskuss] Failed to read SQL file: '.$sql_file);
		return false;
	}

	// Get admin
	$admin_email = get_option('admin_email');
	$args = array(
		'role'    => 'administrator',
		'orderby' => 'registered',
		'order'   => 'ASC'
	);

	$admins = get_users($args);
	$first_admin = isset($admins[0]) ? $admins[0] : null;
		
	$data = array(
		'dbprefix' => $wpdb->prefix,
		'regtime' => date('Y-m-d H:i:s'),
		'admin_email' => !empty($first_admin) ? $first_admin->user_email : $admin_email,
		'admin_username' => !empty($first_admin) ? $first_admin->user_login : 'admin',
		'first_name' => !empty($first_admin) ? $first_admin->first_name : 'Admin',
		'last_name' => !empty($first_admin) ? $first_admin->last_name : '',
		'regtime_nextweek' => date('Y-m-d H:i:s', strtotime('+1 week')),
		'helpdesk_full' => str_replace(array('http://', 'https://'), '', home_url()),
	);
	
	$sql = deskuss_sparse($sql, $data);
	
	$_sql = dsk_sqlsplit($sql);
	
	// Make Sitepad compatible 
	$upgrade = ABSPATH . 'site-admin/includes/upgrade.php';
	$upgrade = file_exists($upgrade) ? $upgrade : ABSPATH . 'wp-admin/includes/upgrade.php';
	require_once( $upgrade );
	
	// Import queries with error tracking
	$errors = array();
	foreach($_sql as $q){
		$result = $wpdb->query( $q );
		if($result === false){
			$errors[] = $wpdb->last_error;
		}
	}

	if(!empty($errors)){
		error_log('[Deskuss] SQL import errors: '.implode('; ', $errors));
	}

	// Ensure schema_signature is set correctly after import
	deskuss_ensure_schema_signature();
	
	return empty($errors); 	
}
function deskuss_ensure_schema_signature(){
	return;
}
function dsk_sqlsplit($data){

	$ret = array();
	$buffer = '';
	// Defaults for parser
	$sql = '';
	$start_pos = 0;
	$i = 0;
	$len= 0;
	$big_value = 200000000;
	$sql_delimiter = ';';
	
	$finished = false;
	
	while (!($finished && $i >= $len)) {
	
		if ($data === FALSE) {
			// subtract data we didn't handle yet and stop processing
			//$offset -= strlen($buffer);
			break;
		} elseif ($data === TRUE) {
			// Handle rest of buffer
		} else {
			// Append new data to buffer
			$buffer .= $data;
			// free memory
			$data = false;
			// Do not parse string when we're not at the end and don't have ; inside
			if ((strpos($buffer, $sql_delimiter, $i) === FALSE) && !$finished)  {
				continue;
			}
		}
		// Current length of our buffer
		$len = strlen($buffer);
		
		// Grab some SQL queries out of it
		while ($i < $len) {
			$found_delimiter = false;
			// Find first interesting character
			$old_i = $i;
			// this is about 7 times faster that looking for each sequence i
			// one by one with strpos()
			if (preg_match('/(\'|"|#|-- |\/\*|`|(?i)DELIMITER)/', $buffer, $matches, PREG_OFFSET_CAPTURE, $i)) {
				// in $matches, index 0 contains the match for the complete 
				// expression but we don't use it
				$first_position = $matches[1][1];
			} else {
				$first_position = $big_value;
			}
			/**
			 * @todo we should not look for a delimiter that might be
			 *       inside quotes (or even double-quotes)
			 */
			// the cost of doing this one with preg_match() would be too high
			$first_sql_delimiter = strpos($buffer, $sql_delimiter, $i);
			if ($first_sql_delimiter === FALSE) {
				$first_sql_delimiter = $big_value;
			} else {
				$found_delimiter = true;
			}
	
			// set $i to the position of the first quote, comment.start or delimiter found
			$i = min($first_position, $first_sql_delimiter);
	
			if ($i == $big_value) {
				// none of the above was found in the string
	
				$i = $old_i;
				if (!$finished) {
					break;
				}
				// at the end there might be some whitespace...
				if (trim($buffer) == '') {
					$buffer = '';
					$len = 0;
					break;
				}
				// We hit end of query, go there!
				$i = strlen($buffer) - 1;
			}
	
			// Grab current character
			$ch = $buffer[$i];
	
			// Quotes
			if (strpos('\'"`', $ch) !== FALSE) {
				$quote = $ch;
				$endq = FALSE;
				while (!$endq) {
					// Find next quote
					$pos = strpos($buffer, $quote, $i + 1);
					// No quote? Too short string
					if ($pos === FALSE) {
						// We hit end of string => unclosed quote, but we handle it as end of query
						if ($finished) {
							$endq = TRUE;
							$i = $len - 1;
						}
						$found_delimiter = false;
						break;
					}
					// Was not the quote escaped?
					$j = $pos - 1;
					while ($buffer[$j] == '\\') $j--;
					// Even count means it was not escaped
					$endq = (((($pos - 1) - $j) % 2) == 0);
					// Skip the string
					$i = $pos;
	
					if ($first_sql_delimiter < $pos) {
						$found_delimiter = false;
					}
				}
				if (!$endq) {
					break;
				}
				$i++;
				// Aren't we at the end?
				if ($finished && $i == $len) {
					$i--;
				} else {
					continue;
				}
			}
	
			// Not enough data to decide
			if ((($i == ($len - 1) && ($ch == '-' || $ch == '/'))
			  || ($i == ($len - 2) && (($ch == '-' && $buffer[$i + 1] == '-')
				|| ($ch == '/' && $buffer[$i + 1] == '*')))) && !$finished) {
				break;
			}
	
			// Comments
			if ($ch == '#'
			 || ($i < ($len - 1) && $ch == '-' && $buffer[$i + 1] == '-'
			  && (($i < ($len - 2) && $buffer[$i + 2] <= ' ')
			   || ($i == ($len - 1)  && $finished)))
			 || ($i < ($len - 1) && $ch == '/' && $buffer[$i + 1] == '*')
					) {
				// Copy current string to SQL
				if ($start_pos != $i) {
					$sql .= substr($buffer, $start_pos, $i - $start_pos);
				}
				// Skip the rest
				$j = $i;
				$i = strpos($buffer, $ch == '/' ? '*/' : "\n", $i);
				// didn't we hit end of string?
				if ($i === FALSE) {
					if ($finished) {
						$i = $len - 1;
					} else {
						break;
					}
				}
				// Skip *
				if ($ch == '/') {
					// Check for MySQL conditional comments and include them as-is
					if ($buffer[$j + 2] == '!') {
						$comment = substr($buffer, $j + 3, $i - $j - 3);
						if (preg_match('/^[0-9]{5}/', $comment, $version)) {
							if ($version[0] <= 50000000) {
								$sql .= substr($comment, 5);
							}
						} else {
							$sql .= $comment;
						}
					}
					$i++;
				}
				// Skip last char
				$i++;
				// Next query part will start here
				$start_pos = $i;
				// Aren't we at the end?
				if ($i == $len) {
					$i--;
				} else {
					continue;
				}
			}
			// Change delimiter, if redefined, and skip it (don't send to server!)
			if (strtoupper(substr($buffer, $i, 9)) == "DELIMITER"
			 && ($buffer[$i + 9] <= ' ')
			 && ($i < $len - 11)
			 && strpos($buffer, "\n", $i + 11) !== FALSE) {
			   $new_line_pos = strpos($buffer, "\n", $i + 10);
			   $sql_delimiter = substr($buffer, $i + 10, $new_line_pos - $i - 10);
			   $i = $new_line_pos + 1;
			   // Next query part will start here
			   $start_pos = $i;
			   continue;
			}
	
			// End of SQL
			if ($found_delimiter || ($finished && ($i == $len - 1))) {
				$tmp_sql = $sql;
				if ($start_pos < $len) {
					$length_to_grab = $i - $start_pos;
	
					if (! $found_delimiter) {
						$length_to_grab++;
					}
					$tmp_sql .= substr($buffer, $start_pos, $length_to_grab);
					unset($length_to_grab);
				}
				// Do not try to execute empty SQL
				if (! preg_match('/^([\s]*;)*$/', trim($tmp_sql))) {
					$sql = $tmp_sql;
					$ret[] = $sql;
					
					$buffer = substr($buffer, $i + strlen($sql_delimiter));
					// Reset parser:
					$len = strlen($buffer);
					$sql = '';
					$i = 0;
					$start_pos = 0;
					// Any chance we will get a complete query?
					//if ((strpos($buffer, ';') === FALSE) && !$finished) {
					if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
						break;
					}
				} else {
					$i++;
					$start_pos = $i;
				}
			}
		} // End of parser loop
	} // End of import loop

	return $ret;

}
function deskuss_create_threads_table() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();	
	$table_name = $wpdb->prefix . 'deskuss_threads';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`board_id` INT UNSIGNED NOT NULL,
		`task_id` INT UNSIGNED NOT NULL,
		`type` VARCHAR(50) NULL DEFAULT 'comment' COMMENT 'comment|note|reply',
		`post_id` BIGINT(20) NOT NULL,
		`author_name` VARCHAR(192) NULL DEFAULT '',
		`author_email` VARCHAR(192) NULL DEFAULT '',
		`author_ip` VARCHAR(50) NULL DEFAULT '',
		`description` TEXT NULL,
		`created_by` BIGINT UNSIGNED NULL,
		`created_at` TIMESTAMP NULL,
		`updated_at` TIMESTAMP NULL
	) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
function deskuss_create_notifications_table() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'deskuss_notifications';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`user_id` BIGINT UNSIGNED NOT NULL,
		`from_user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
		`task_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
		`board_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
		`type` VARCHAR(50) NOT NULL DEFAULT 'mention',
		`message` VARCHAR(500) NULL DEFAULT '',
		`is_read` TINYINT(1) NOT NULL DEFAULT 0,
		`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		INDEX `user_read` (`user_id`, `is_read`),
		INDEX `user_created` (`user_id`, `created_at` DESC)
	) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
function deskuss_get_user_info($user) {
	static $cache = array();

	$cache_key = is_numeric( $user ) ? 'id_' . absint( $user ) : 'u_' . sanitize_text_field( $user );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	require_once DESKUSS_DIR . '/main/builder.php';

	$query = new WPDynamicQueryBuilder();
	$query->select([
		's.staff_id AS member_id',
		's.username AS member_username',
		's.firstname AS member_firstname',
		's.lastname AS member_lastname',
		's.dept_id AS dept_id',
	])
	->from( 'dk_staff', 's' );

	// Determine whether it's an ID or username.
	if ( is_numeric( $user ) ) {
		$query->where( 's.staff_id', absint( $user ), '=' );
	} else {
		$query->where( 's.username', sanitize_text_field( $user ), '=' );
	}

	$result = $query->getOne();
	$cache[ $cache_key ] = $result;

	return $result;
}
function deskuss_get_ticket_activity($id){
	global $wpdb;
	require_once(DESKUSS_DIR . '/main/builder.php');

	$query = new WPDynamicQueryBuilder();

	$query->select([
		't.ticket_id',
		't.number',
		't.dept_id',
		'c.subject',
		'e.poster',
		'e.body',
		'e.staff_id',
		'e.created AS entry_created'
	])
	->from('dk_ticket', 't')
	->join('dk_ticket__cdata', 'c.ticket_id = t.ticket_id', 'LEFT', 'c')
	->join('dk_thread', 'th.object_id = t.ticket_id AND th.object_type = "T"', 'LEFT', 'th')
	->join('dk_thread_entry', 'e.thread_id = th.id', 'LEFT', 'e')
	->where('t.ticket_id', $id)
	->orderBy('e.created', 'ASC');

	return $query->get();
}

// Bridge hooks for Kanbanz integration — moved from packages/include/plugins/deskuss/deskuss.php

add_action('deskuss_wp_add_department', 'deskuss_wp_add_department', 10, 2);
function deskuss_wp_add_department(&$vars, $post_data) {
	// Handled by Kanbanz bridge via on_department_added()
}

add_action('deskuss_wp_update_department', 'deskuss_wp_update_department', 10, 2);
function deskuss_wp_update_department(&$vars, $post_data) {
	// Handled by Kanbanz bridge via on_department_updated()
}

add_action('deskuss_wp_create_task', 'deskuss_wp_create_task', 10, 1);
function deskuss_wp_create_task($ticket_data) {
	// Kanbanz bridge handles task creation from tickets
}

add_filter('ticket_assigned', 'deskuss_wp_sync_task_assignment', 1, 1);
function deskuss_wp_sync_task_assignment($ticket_id) {
	do_action('deskuss_wp_sync_task_assignment', $ticket_id);
	return $ticket_id;
}

add_filter('ticket_unassigned', 'deskuss_wp_sync_task_unassignment', 1, 1);
function deskuss_wp_sync_task_unassignment($ticket_id) {
	do_action('deskuss_wp_sync_task_unassignment', $ticket_id);
	return $ticket_id;
}

add_filter('post_ticket_closed', 'deskuss_wp_close_task', 1, 1);
function deskuss_wp_close_task($ticket_id) {
	do_action('deskuss_wp_close_task', $ticket_id);
	return $ticket_id;
}

add_filter('ticket_status_changed', 'deskuss_wp_sync_task_status', 1, 3);
function deskuss_wp_sync_task_status($ticket_id, $status_id, $status_state) {
	do_action('deskuss_wp_sync_task_status', $ticket_id, $status_id, $status_state);
	return $ticket_id;
}

add_filter('ticket_event_logged', 'deskuss_wp_sync_ticket_event', 1, 3);
function deskuss_wp_sync_ticket_event($ticket_id, $state, $data) {
	do_action('deskuss_wp_sync_ticket_event', $ticket_id, $state, $data);
	return $ticket_id;
}

add_action('deskuss_update_site_user', 'deskuss_update_site_user', 10, 3);
function deskuss_update_site_user($error, &$vars, &$staff){

	$staff_updating = false;

	if(isset($staff->staff_id) && $staff->getId() == $vars['id']){
		$staff_updating = true;
	}

	$user_data = array(
		'user_login' => $vars['username'],
		'first_name' => $vars['firstname'],
		'last_name' => $vars['lastname'],
		'user_pass' => $vars['passwd'],
		'user_email' => $vars['email']
	);

	$user = get_user_by('email', $vars['email']);

	if(!empty($user)){

		if($staff_updating && $staff->getEmail() != $vars['email']){
			$error['email'] = __('Email already in use');
			return $error;
		}

		if(!user_can( $user->ID, 'deskuss_staff' ) && empty($vars['make_deskuss_staff'])){
			$error['email'] = sprintf(
				__('A site user with the address ID <b>%s</b> already exists. Check the following checkbox if you want to make the site user <b>%s</b> an agent. Otherwise you will have to change the email address in the form. <br /><input type="checkbox" name="make_deskuss_staff" value="1"/> Make site user an agent!'),
				$vars['email'],
				$vars['email']
			);
		}

		if($vars['username'] != $user->user_login ){
			if(empty($vars['use_site_name'])){
				$error['username'] = sprintf(
					__('The user name must be <b>%s</b> according to the site user name. Check the following checkbox to allow using the site user name <b>%s</b>. Otherwise you must change the name in the form. <br /><input type="checkbox" name="use_site_name" value="1"/> Allow to use site username!'),
					$user->user_login,
					$user->user_login
				);
			}else{
				$vars['username'] = $user->user_login;
				$user_data['user_login'] = $user->user_login;
			}
		}

		if(!empty($error)){
			return $error;
		}

		$user_data['ID'] = $user->ID;
		$user_id = wp_update_user($user_data);

		if (is_wp_error($user_id)) {
			$error['update_user'] = __('Unable to update site user!');
		}elseif(!user_can( $user_id, 'deskuss_staff' )){
			$user = new WP_User($user_id);
			$user->set_role('deskuss_staff');
		}

		return $error;
	}

	$user = get_user_by('login', $vars['username']);

	if(!empty($user)){

		if($staff_updating && $staff->getUserName() == $vars['username'] && $staff->getEmail() != $vars['email']){
			if(user_can( $user->ID, 'deskuss_staff' ) && empty($vars['use_site_email'])){
				$error['email'] = sprintf(
					__('The user email address must be <b>%s</b> according to the site user email. Check the following checkbox to allow using the site user email <b>%s</b>. Otherwise you must change the email in the form. <br /><input type="checkbox" name="use_site_email" value="1"/> Allow to use site user email!'),
					$user->email,
					$user->email
				);
			}else{
				$vars['email'] = $user->user_email;
				$user_data['user_email'] = $user->user_email;
			}

			if(empty($error)){
				$user_id = wp_update_user($user_data);
				if(is_wp_error($user_id)){
					$error['update_user'] = __('Unable to update site user!');
				}elseif(!user_can( $user_id, 'deskuss_staff' )){
					$user = new WP_User($user_id);
					$user->set_role('deskuss_staff');
				}
				return $error;
			}
		}elseif(!$staff_updating || $staff->getUserName() != $vars['username']){
			$error['username'] = __('Username already in use.');
		}
	}

	$user = get_user_by('login', $vars['ref_username']);
	if(!empty($user)){
		$user_info = $user->ID;
		$user_id = wp_update_user([
			'ID' => $user_info,
			'user_email' => $user_data['user_email'],
		]);
		if(is_wp_error($user_id)){
			$error['update_user'] = __('Unable to update site user!');
		}elseif(!user_can( $user_id, 'deskuss_staff' )){
			$user = new WP_User($user_id);
			$user->set_role('deskuss_staff');
		}
		return $error;
	}

	if(!empty($error)){
		return $error;
	}

	$user_id = wp_insert_user($user_data);

	if(is_wp_error($user_id)){
		$error['update_user'] = __('Unable to create site user!');
	}elseif(!user_can( $user_id, 'deskuss_staff' )){
		$user = new WP_User($user_id);
		$user->set_role('deskuss_staff');
	}
	return $error;
}

add_filter('get_config', 'deskuss_get_config', 1, 2);
function deskuss_get_config($value, $config_name){

	switch($config_name){

		default:
		break;

		case 'default_storage_bk':
		$value = 'F';
		break;
	}

	return $value;
}

/**
 * Get the active views directory.
 *
 * Auto-detects staff/admin vs client context using the
 * existing DSKSTAFFINC / DSKADMININC constants.
 *
 * @return string Path to the views directory with trailing slash.
 */
function deskuss_get_views_dir() {
	if ( defined( 'DSKSTAFFINC' ) || defined( 'DSKADMININC' ) ) {
		return STAFFINC_DIR;
	}
	return CLIENTINC_DIR;
}

/**
 * Get the path to the header template.
 *
 * Use with require or include in the caller's scope:
 *   require deskuss_get_header();
 *
 * @return string Full path to header.php
 */
function deskuss_get_header() {
	return deskuss_get_views_dir() . 'header.php';
}

/**
 * Get the path to the footer template.
 *
 * Use with require or include in the caller's scope:
 *   require deskuss_get_footer();
 *
 * @return string Full path to footer.php
 */
function deskuss_get_footer() {
	return deskuss_get_views_dir() . 'footer.php';
}

/**
 * Get the path to a view / template file.
 *
 * Use with require or include in the caller's scope:
 *   require deskuss_load_view('tickets.php');
 *
 * @param string $file View filename, e.g. 'dashboard.php', 'tickets.php'.
 * @return string Full path to the view file
 */
function deskuss_load_view( $file ) {
	return deskuss_get_views_dir() . ltrim( $file, '/' );
}

/**
 * Return the path to page-wrapper.php after setting the view file.
 *
 * Use with require to preserve caller's variable scope:
 *   require deskuss_load_page('dashboard.php');
 *
 * The view file name is filterable via 'deskuss_view_file' filter.
 * The wrapper path is filterable via 'deskuss_load_page_path' filter.
 *
 * @param string $view_file View filename, e.g. 'dashboard.php'.
 * @return string Path to page-wrapper.php
 */
function deskuss_load_page( $view_file ) {
	$GLOBALS['deskuss_view_file'] = apply_filters( 'deskuss_view_file', $view_file );
	return apply_filters( 'deskuss_load_page_path', DESKUSS_DIR . '/main/page-wrapper.php' );
}

function deskuss_load_license($parent = 0){
	
	global $deskuss, $dsk_lic_resp;
	
	$license_field = 'deskuss_license';
	$license_api_url = DESKUSS_API;
	
	if(!empty($parent) && is_string($parent) && strlen($parent) > 5){		
		$lic['license'] = $parent;
	
	}elseif(!empty($parent)){
		$license_field = 'softaculous_pro_license';
		$lic = get_option('softaculous_pro_license', []);
	
	}else{
		$lic = get_option($license_field, []);
	}
	
	if(!empty($lic['license']) && preg_match('/^softwp/is', $lic['license'])){
		$license_field = 'softaculous_pro_license';
		$license_api_url = 'https://a.softaculous.com/softwp/';
		$prods = apply_filters('softaculous_pro_products', []);
	}else{
		$prods = [];
	}

	if(empty($lic['last_update'])){
		$lic['last_update'] = time() - 86600;
	}
	
	if(!empty($lic) && !empty($lic['license']) && (time() - @$lic['last_update']) >= 86400){
		
		$url = $license_api_url.'/license.php?license='.$lic['license'].'&prods='.implode(',', $prods).'&url='.rawurlencode(site_url());
		$resp = wp_remote_get($url);
		$dsk_lic_resp = $resp;

		if(is_array($resp)){
			
			$tosave = json_decode($resp['body'], true);
			
			if(!empty($tosave['license'])){
				$tosave['last_update'] = time();
				update_option($license_field, $tosave);
				$lic = $tosave;
			}
		}
	}
	
	if(empty($lic) || empty($lic['active'])){
		
		if(function_exists('softaculous_pro_load_license')){
			$softaculous_license = softaculous_pro_load_license();
			if(!empty($softaculous_license['license']) && 
				(!empty($softaculous_license['active']) || empty($lic['license']))
			){
				$lic = $softaculous_license;
			}
		}elseif(empty($parent)){
			$lic = get_option('softaculous_pro_license', []);
			
			if(!empty($lic)){
				return deskuss_load_license(1);
			}
		}
	}
	
	if(!empty($lic['license'])){
		$deskuss->license = $lic;
	}
	
}

add_filter('softaculous_pro_products', 'deskuss_softaculous_pro_products', 10, 1);
function deskuss_softaculous_pro_products($r = []){
	$r['deskuss'] = 'deskuss';
	return $r;
}

function deskuss_pro_updater_filter_args($queryArgs){
	
	global $deskuss;
	
	if(!empty($deskuss->license['license'])){
		$queryArgs['license'] = $deskuss->license['license'];
	}
	
	$queryArgs['url'] = rawurlencode(site_url());
	
	return $queryArgs;
}

function deskuss_pro_updater_check_link($final_link){
	
	global $deskuss;
	
	if(empty($deskuss->license['license'])){
		return '<a href="'.admin_url('admin.php?page=deskuss_license').'">Install Deskuss License Key</a>';
	}
	
	return $final_link;
}

function deskuss_pro_api_url($main_server = 0, $suffix = 'deskuss'){
	
	global $deskuss;
	
	$r = array(
		'https://s0.softaculous.com/a/softwp/',
		'https://s1.softaculous.com/a/softwp/',
		'https://s2.softaculous.com/a/softwp/',
		'https://s3.softaculous.com/a/softwp/',
		'https://s4.softaculous.com/a/softwp/',
		'https://s5.softaculous.com/a/softwp/',
		'https://s7.softaculous.com/a/softwp/',
		'https://s8.softaculous.com/a/softwp/'
	);
	
	$mirror = $r[array_rand($r)];
	
	if(!empty($main_server) || empty($deskuss->license['last_edit']) || 
		(!empty($deskuss->license['last_edit']) && (time() - 3600) < $deskuss->license['last_edit'])
	){
		$mirror = DESKUSS_API;
	}
	
	if(!empty($suffix)){
		$mirror = str_replace('/softwp', '/'.$suffix, $mirror);
	}
	
	return $mirror;
	
}

function deskuss_optpost($name, $default = ''){

	if(!empty($_POST[$name])){
		return deskuss_inputsec(dsk_htmlizer(trim($_POST[$name])));
	}

	return $default;
}

function deskuss_inputsec($string){

	$string = addslashes($string);
	$string = str_replace('`', '\`', $string);

	return $string;
}

function dsk_htmlizer($string){

	global $globals;
	
	$charset = !empty($globals['charset']) ? $globals['charset'] : 'UTF-8';
	
	$string = htmlentities($string, ENT_QUOTES, $charset);
	
	preg_match_all('/(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)/', $string, $matches);//r_print($matches);
	
	foreach($matches[1] as $mk => $mv){		
		$tmp_m = dsk_entity_check($matches[2][$mk]);
		$string = str_replace($matches[1][$mk], $tmp_m, $string);
	}
	
	return $string;
}

function deskuss_entity_check($string){

	$num = ((substr($string, 0, 1) === 'x') ? hexdec(substr($string, 1)) : (int) $string);
	$string = (($num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num < 0x20) ? '' : '&#'.$num.';');

	return $string;
}

function deskuss_report_error($error = array()){

	if(empty($error)){
		return true;
	}

	$error_string = '<b>'.__('Please fix the below error(s) :', 'deskuss').'</b> <br />';

	foreach($error as $ek => $ev){
		$error_string .= '* '.$ev.'<br />';
	}

	echo '<div id="message" class="error"><p>'
					. $error_string
					. '</p></div>';
}

// Full admin permissions JSON applied to synced WP admins.
function deskuss_admin_permissions_json(){
	return '{"user.create":1,"user.edit":1,"user.delete":1,"user.manage":1,"user.dir":1,"org.create":1,"org.edit":1,"org.delete":1,"faq.manage":1,"emails.banlist":1}';
}