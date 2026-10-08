<?php
/**
 * Deskuss Database Upgrader
 *
 * Centralised, idempotent migration runner. Each migration is keyed by a
 * "schema version" integer stored in the `deskuss_schema_version` option.
 * Migrations run inside `deskuss_update_check()` on plugin update, and
 * inside `deskuss_activation()` on fresh install (after table creation).
 *
 * Conventions:
 *  - Every migration is a static method named `migrate_<version>`.
 *  - Migrations MUST be idempotent (safe to run twice).
 *  - Migrations MUST use the global $wpdb (NOT the db_* helpers or *_TABLE
 *    constants, because the Deskuss bootstrap may not be loaded yet when
 *    the upgrader runs on `plugins_loaded`).
 *  - Never assume a row/column exists; guard with existence checks.
 *
 * @package Deskuss
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the current schema version (0 if not yet set).
 */
function deskuss_get_schema_version() {
	return (int) get_option( 'deskuss_schema_version', 0 );
}

/**
 * Persists the schema version.
 */
function deskuss_set_schema_version( $version ) {
	update_option( 'deskuss_schema_version', (int) $version );
}

/**
 * Runs all pending migrations up to the target schema version.
 *
 * @param int $target Target schema version (defaults to the latest known).
 */
function deskuss_run_db_upgrades( $target = null ) {
	global $wpdb;

	if ( null === $target ) {
		$target = deskuss_get_latest_schema_version();
	}

	$current = deskuss_get_schema_version();

	if ( $current >= $target ) {
		return;
	}

	// Ensure errors are logged but do not halt the request.
	$wpdb->show_errors( false );

	for ( $next = $current + 1; $next <= $target; $next++ ) {
		$method = 'deskuss_migrate_' . $next;
		if ( ! function_exists( $method ) ) {
			continue;
		}
		$result = call_user_func( $method );
		if ( false === $result ) {
			error_log( sprintf( '[Deskuss] DB upgrade to schema %d FAILED: %s', $next, $wpdb->last_error ) );
			// Stop on first failure so we don't skip ahead and mask the issue.
			return;
		}
		deskuss_set_schema_version( $next );
	}
}

/**
 * Latest schema version known to this version of the plugin.
 * Bump this when adding a new migration.
 */
function deskuss_get_latest_schema_version() {
	return 3;
}

/* -------------------------------------------------------------------------
 * Migration 1 — Auto-Close feature (introduced in 1.0.6)
 *
 * Adds:
 *   - One new ticket status with state = 'pending' ("In Progress").
 *   - Extends dk_thread_event.state enum to include 'auto-closed' and 'pending'.
 *   - Adds a composite index on dk_ticket for the auto-close scan.
 *   - Seeds the new 'ticket.autoclose' email template row into the default
 *     template group (only if it doesn't already exist).
 *   - Seeds the new auto_close_* config defaults.
 * ------------------------------------------------------------------------- */

function deskuss_migrate_1() {
	global $wpdb;

	$prefix     = $wpdb->prefix . 'dk_';
	$status_tbl = $prefix . 'ticket_status';
	$event_tbl  = $prefix . 'thread_event';
	$ticket_tbl = $prefix . 'ticket';

	$now = current_time( 'mysql', true );

	// -----------------------------------------------------------------
	// 1a. Insert the two new pending-state statuses (idempotent by name).
	//     `name` has a UNIQUE KEY, so use INSERT IGNORE.
	// -----------------------------------------------------------------
	$rows = array(
		array(
			'name'       => 'In Progress',
			'state'      => 'pending',
			'sort'       => 6,
			'properties' => wp_json_encode( array(
				'description' => 'Agent is actively working on this ticket.',
				'allowreopen' => true,
				'reopenstatus' => 0,
			) ),
		),
	);

	foreach ( $rows as $row ) {
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$status_tbl}` WHERE name = %s LIMIT 1",
			$row['name']
		) );
		if ( $exists ) {
			continue;
		}
		$wpdb->insert(
			$status_tbl,
			array(
				'name'       => $row['name'],
				'state'      => $row['state'],
				'mode'       => 3, // ENABLED bit on.
				'flags'      => 0,
				'sort'       => $row['sort'],
				'properties' => $row['properties'],
				'created'    => $now,
				'updated'    => $now,
			),
			array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' )
		);
	}

	// Ensure AUTO_INCREMENT can grow past the inserted ids.
	$max_id = (int) $wpdb->get_var( "SELECT COALESCE(MAX(id), 0) FROM `{$status_tbl}`" );
	$ai     = (int) $wpdb->get_var( "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$status_tbl}'" );
	if ( $ai <= $max_id ) {
		$wpdb->query( "ALTER TABLE `{$status_tbl}` AUTO_INCREMENT = " . ( $max_id + 1 ) );
	}

	// -----------------------------------------------------------------
	// 1b. Extend dk_thread_event.state enum to include 'auto-closed'.
	//     MySQL ENUM is stored as a string list in information_schema;
	//     we detect the column type and only ALTER if 'auto-closed' is
	//     missing from the enum definition.
	// -----------------------------------------------------------------
	$col_type = $wpdb->get_var(
		"SELECT COLUMN_TYPE FROM information_schema.COLUMNS
		 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$event_tbl}' AND COLUMN_NAME = 'state'"
	);
	if ( $col_type && stripos( $col_type, 'auto-closed' ) === false ) {
		// Rebuild the enum preserving the existing order and appending
		// 'pending' and 'auto-closed' before the closing paren. Also keep
		// NOT NULL.
		$wpdb->query(
			"ALTER TABLE `{$event_tbl}`
			 MODIFY `state` ENUM('created','closed','reopened','assigned','transferred',
			                    'overdue','edited','viewed','error','collab','resent',
			                    'pending','auto-closed')
			 NOT NULL"
		);
	} elseif ( $col_type && stripos( $col_type, "'pending'" ) === false ) {
		// 'auto-closed' already present but 'pending' missing.
		$wpdb->query(
			"ALTER TABLE `{$event_tbl}`
			 MODIFY `state` ENUM('created','closed','reopened','assigned','transferred',
			                    'overdue','edited','viewed','error','collab','resent',
			                    'pending','auto-closed')
			 NOT NULL"
		);
	}

	// -----------------------------------------------------------------
	// 1c. Add a composite index to dk_ticket to speed the auto-close
	//     scan. Check information_schema.STATISTICS for existence first.
	// -----------------------------------------------------------------
	$index_exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM information_schema.STATISTICS
			 WHERE TABLE_SCHEMA = DATABASE()
			   AND TABLE_NAME   = %s
			   AND INDEX_NAME   = 'auto_close_scan'",
			$ticket_tbl
		)
	);
	if ( ! $index_exists ) {
		$wpdb->query(
			"ALTER TABLE `{$ticket_tbl}` ADD INDEX `auto_close_scan` (`status_id`, `isanswered`, `lastupdate`)"
		);
	}


	return true;
}

/* -------------------------------------------------------------------------
 * Migration 2 — Seed auto_close_* config defaults.
 *
 * Deskuss stores config in the dk_config table keyed by `namespace` / `key`.
 * The admin panel reads these via the Config ORM (class.config.php). To keep
 * the feature opt-in by default we only set keys that do not yet exist.
 * ------------------------------------------------------------------------- */

function deskuss_migrate_2() {
	global $wpdb;

	$cfg_tbl = $wpdb->prefix . 'dk_config';

	$defaults = array(
		'auto_close_enabled'            => 0,
		'auto_close_days'               => 7,
		'auto_close_status_id'          => 3,        // "Closed" by default
		'auto_close_skip_pending'       => 1,
		'auto_close_skip_assigned'      => 0,
		'auto_close_skip_locked'        => 1,
		'auto_close_batch_size'         => 50,
		'auto_close_min_priority'       => 0,
		'auto_close_dry_run'            => 0,
		'auto_close_last_run'           => 0,
	);

	// Deskuss stores config values as plain strings (see dk_config.value in
	// deskuss_1.sql). Booleans become '1'/'0', ints become their string form.
	foreach ( $defaults as $key => $value ) {
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$cfg_tbl}` WHERE `key` = %s LIMIT 1",
			$key
		) );
		if ( $exists ) {
			continue;
		}
		$stored = is_bool( $value ) ? ( $value ? '1' : '0' ) : (string) $value;
		$wpdb->insert(
			$cfg_tbl,
			array(
				'namespace' => 'core',
				'key'       => $key,
				'value'      => $stored,
				'updated'    => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}

	return true;
}

/* -------------------------------------------------------------------------
 * Migration 3 — Register the 'pending' state with the TicketStateField.
 *
 * This is a code-level change (handled in class.forms.php), but we keep a
 * schema marker so that a future migration can back-fill any list items that
 * were created before this state existed.
 * ------------------------------------------------------------------------- */

function deskuss_migrate_3() {
	// Back-fill: any status row whose state is NULL or empty and whose
	// properties mention "awaiting" or "in progress" gets state='pending'.
	global $wpdb;
	$status_tbl = $wpdb->prefix . 'dk_ticket_status';

	$wpdb->query(
		"UPDATE `{$status_tbl}`
		 SET state = 'pending'
		 WHERE (state IS NULL OR state = '')
		   AND (properties LIKE '%awaiting%' OR name LIKE '%In Progress%')"
	);

	return true;
}