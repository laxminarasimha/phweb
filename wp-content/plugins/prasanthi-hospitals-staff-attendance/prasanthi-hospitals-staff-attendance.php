<?php
/**
 * Plugin Name: Prasanthi Hospitals Staff Attendance
 * Description: Staff check-in/check-out, overnight attendance, administrator corrections and audit history.
 * Version: 1.0.0
 * Author: Prasanthi Hospitals
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PH_STAFF_ATTENDANCE_VERSION', '1.0.0');

function ph_staff_attendance_table() {
    global $wpdb;
    return $wpdb->prefix . 'ph_staff_attendance';
}

function ph_staff_attendance_audit_table() {
    global $wpdb;
    return $wpdb->prefix . 'ph_staff_attendance_audit';
}

function ph_staff_attendance_now() {
    // Store a WordPress-local datetime string. WordPress's current_time('mysql')
    // may add the offset to a UTC timestamp in some configurations; using
    // current_datetime() keeps the site's configured timezone explicit.
    return current_datetime()->format('Y-m-d H:i:s');
}

function ph_staff_attendance_is_admin() {
    return current_user_can('manage_options');
}

function ph_staff_attendance_can_use() {
    return ph_staff_attendance_is_admin() || current_user_can('ph_manage_attendance');
}

function ph_staff_attendance_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();
    $attendance = ph_staff_attendance_table();
    $audit = ph_staff_attendance_audit_table();

    $sql1 = "CREATE TABLE {$attendance} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        staff_id bigint(20) unsigned NOT NULL,
        attendance_date date NOT NULL,
        check_in datetime NOT NULL,
        check_out datetime NULL DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'present',
        created_by bigint(20) unsigned NOT NULL DEFAULT 0,
        updated_by bigint(20) unsigned NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY staff_date (staff_id, attendance_date),
        KEY attendance_date (attendance_date),
        KEY check_out (check_out)
    ) {$charset_collate};";

    $sql2 = "CREATE TABLE {$audit} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        attendance_id bigint(20) unsigned NOT NULL,
        changed_by bigint(20) unsigned NOT NULL DEFAULT 0,
        changed_at datetime NOT NULL,
        change_note text NULL,
        old_check_in datetime NULL,
        new_check_in datetime NULL,
        old_check_out datetime NULL,
        new_check_out datetime NULL,
        old_attendance_date date NULL,
        new_attendance_date date NULL,
        old_status varchar(20) NULL,
        new_status varchar(20) NULL,
        PRIMARY KEY  (id),
        KEY attendance_id (attendance_id),
        KEY changed_at (changed_at)
    ) {$charset_collate};";

    dbDelta($sql1);
    dbDelta($sql2);
    update_option('ph_staff_attendance_db_version', PH_STAFF_ATTENDANCE_VERSION);

    // Administrator can always manage attendance. Front Desk capability is assigned
    // by Staff Management; do not create or modify any WordPress roles here.
    $admin = get_role('administrator');
    if ($admin && !$admin->has_cap('ph_manage_attendance')) {
        $admin->add_cap('ph_manage_attendance');
    }
}
register_activation_hook(__FILE__, 'ph_staff_attendance_install');

function ph_staff_attendance_maybe_upgrade() {
    if (get_option('ph_staff_attendance_db_version') !== PH_STAFF_ATTENDANCE_VERSION) {
        ph_staff_attendance_install();
    }
}
add_action('plugins_loaded', 'ph_staff_attendance_maybe_upgrade');

function ph_staff_attendance_staff_table() {
    global $wpdb;
    return $wpdb->prefix . 'ph_staff';
}

function ph_staff_attendance_get_staff($active_only = true) {
    global $wpdb;
    $table = ph_staff_attendance_staff_table();
    // Staff Management owns this table. If it is not installed, show a clear admin notice.
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return new WP_Error('ph_staff_table_missing', 'Staff Management table was not found. Activate Prasanthi Hospitals Staff Management first.');
    }
    $where = $active_only ? "WHERE employment_status = 'active'" : '';
    return $wpdb->get_results("SELECT id, employee_id, staff_name, designation, employment_status FROM {$table} {$where} ORDER BY staff_name ASC");
}

function ph_staff_attendance_menu() {
    if (!ph_staff_attendance_can_use()) {
        return;
    }
    add_submenu_page(
        'prasanthi-hospitals',
        'Staff Attendance',
        'Staff Attendance',
        'ph_manage_attendance',
        'ph-staff-attendance',
        'ph_staff_attendance_page'
    );
}
add_action('admin_menu', 'ph_staff_attendance_menu', 30);

function ph_staff_attendance_notice($message, $type = 'success') {
    printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr($type), esc_html($message));
}

function ph_staff_attendance_redirect($args = array()) {
    $url = admin_url('admin.php?page=ph-staff-attendance');
    if ($args) {
        $url = add_query_arg($args, $url);
    }
    wp_safe_redirect($url);
    exit;
}

function ph_staff_attendance_handle_actions() {
    if (!is_admin() || !isset($_POST['ph_attendance_action'])) {
        return;
    }
    if (!ph_staff_attendance_can_use()) {
        wp_die('You do not have permission to manage attendance.');
    }
    check_admin_referer('ph_staff_attendance_action', 'ph_attendance_nonce');

    global $wpdb;
    $attendance = ph_staff_attendance_table();
    $action = sanitize_key(wp_unslash($_POST['ph_attendance_action']));
    $staff_id = isset($_POST['staff_id']) ? absint($_POST['staff_id']) : 0;
    $now = ph_staff_attendance_now();
    $today = current_datetime()->format('Y-m-d');
    $user_id = get_current_user_id();

    if ($action === 'check_in') {
        $staff_table = ph_staff_attendance_staff_table();
        $staff = $wpdb->get_row($wpdb->prepare(
            "SELECT id, employee_id, staff_name FROM {$staff_table} WHERE id = %d AND employment_status = 'active'",
            $staff_id
        ));
        if (!$staff) {
            ph_staff_attendance_redirect(array('phmsg' => 'invalid_staff'));
        }
        $open = $wpdb->get_row($wpdb->prepare(
            "SELECT id, attendance_date FROM {$attendance} WHERE staff_id = %d AND check_out IS NULL ORDER BY check_in DESC LIMIT 1",
            $staff_id
        ));
        if ($open) {
            ph_staff_attendance_redirect(array('phmsg' => 'open_record'));
        }
        $ok = $wpdb->insert($attendance, array(
            'staff_id' => $staff_id,
            'attendance_date' => $today,
            'check_in' => $now,
            'check_out' => null,
            'status' => 'present',
            'created_by' => $user_id,
            'updated_by' => $user_id,
            'created_at' => $now,
            'updated_at' => $now,
        ), array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'));
        ph_staff_attendance_redirect(array('phmsg' => $ok ? 'checked_in' : 'save_error'));
    }

    if ($action === 'check_out') {
        $open = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$attendance} WHERE staff_id = %d AND check_out IS NULL ORDER BY check_in DESC LIMIT 1",
            $staff_id
        ));
        if (!$open) {
            ph_staff_attendance_redirect(array('phmsg' => 'no_open_record'));
        }
        $ok = $wpdb->update($attendance, array(
            'check_out' => $now,
            'updated_by' => $user_id,
            'updated_at' => $now,
        ), array('id' => (int) $open->id), array('%s', '%d', '%s'), array('%d'));
        ph_staff_attendance_redirect(array('phmsg' => $ok !== false ? 'checked_out' : 'save_error'));
    }

    if ($action === 'admin_edit') {
        if (!ph_staff_attendance_is_admin()) {
            wp_die('Only an administrator can correct attendance records.');
        }
        $record_id = isset($_POST['record_id']) ? absint($_POST['record_id']) : 0;
        $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$attendance} WHERE id = %d", $record_id));
        if (!$record) {
            ph_staff_attendance_redirect(array('phmsg' => 'record_missing'));
        }
        $date = isset($_POST['attendance_date']) ? sanitize_text_field(wp_unslash($_POST['attendance_date'])) : '';
        $check_in_input = isset($_POST['check_in']) ? sanitize_text_field(wp_unslash($_POST['check_in'])) : '';
        $check_out_input = isset($_POST['check_out']) ? sanitize_text_field(wp_unslash($_POST['check_out'])) : '';
        $note = isset($_POST['change_note']) ? sanitize_textarea_field(wp_unslash($_POST['change_note'])) : '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $check_in_input) ||
            ($check_out_input !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $check_out_input))) {
            ph_staff_attendance_redirect(array('phmsg' => 'invalid_datetime'));
        }
        $new_in = str_replace('T', ' ', $check_in_input) . ':00';
        $new_out = $check_out_input === '' ? null : str_replace('T', ' ', $check_out_input) . ':00';
        if ($new_out !== null && strtotime($new_out) < strtotime($new_in)) {
            ph_staff_attendance_redirect(array('phmsg' => 'checkout_before_checkin'));
        }
        if (trim($note) === '') {
            ph_staff_attendance_redirect(array('phmsg' => 'note_required'));
        }
        $old = clone $record;
        $wpdb->update($attendance, array(
            'attendance_date' => $date,
            'check_in' => $new_in,
            'check_out' => $new_out,
            'updated_by' => $user_id,
            'updated_at' => $now,
        ), array('id' => $record_id), array('%s', '%s', '%s', '%d', '%s'), array('%d'));
        $wpdb->insert(ph_staff_attendance_audit_table(), array(
            'attendance_id' => $record_id,
            'changed_by' => $user_id,
            'changed_at' => $now,
            'change_note' => $note,
            'old_check_in' => $old->check_in,
            'new_check_in' => $new_in,
            'old_check_out' => $old->check_out,
            'new_check_out' => $new_out,
            'old_attendance_date' => $old->attendance_date,
            'new_attendance_date' => $date,
            'old_status' => $old->status,
            'new_status' => $old->status,
        ));
        ph_staff_attendance_redirect(array('phmsg' => 'corrected'));
    }
}
add_action('admin_init', 'ph_staff_attendance_handle_actions');

function ph_staff_attendance_messages() {
    if (empty($_GET['phmsg'])) return;
    $messages = array(
        'checked_in' => array('Check-in recorded using the current hospital date and time.', 'success'),
        'checked_out' => array('Check-out recorded. The original attendance date has been preserved.', 'success'),
        'open_record' => array('This employee already has an open attendance record. Check them out before starting another record.', 'warning'),
        'no_open_record' => array('No open attendance record was found for this employee.', 'error'),
        'invalid_staff' => array('Active staff member not found.', 'error'),
        'save_error' => array('Attendance could not be saved. Please try again.', 'error'),
        'corrected' => array('Attendance corrected and the change was added to the audit history.', 'success'),
        'record_missing' => array('Attendance record not found.', 'error'),
        'invalid_datetime' => array('Please provide valid attendance dates and times.', 'error'),
        'checkout_before_checkin' => array('Checkout cannot be earlier than check-in.', 'error'),
        'note_required' => array('Enter a reason for the correction.', 'error'),
    );
    $key = sanitize_key(wp_unslash($_GET['phmsg']));
    if (isset($messages[$key])) ph_staff_attendance_notice($messages[$key][0], $messages[$key][1]);
}

function ph_staff_attendance_format_datetime($value) {
    if (empty($value)) {
        return '—';
    }

    // Attendance DATETIME values are stored as WordPress-local wall-clock times.
    // Do not pass them through strtotime() and then wp_date(): strtotime() uses
    // the PHP/server timezone and can shift the displayed time.
    try {
        $datetime = new DateTimeImmutable($value, wp_timezone());
        return esc_html(wp_date(
            get_option('date_format') . ' ' . get_option('time_format'),
            $datetime->getTimestamp(),
            wp_timezone()
        ));
    } catch (Exception $e) {
        return esc_html($value);
    }
}

function ph_staff_attendance_page() {
    if (!ph_staff_attendance_can_use()) {
        wp_die('You do not have permission to view this page.');
    }
    global $wpdb;
    $attendance = ph_staff_attendance_table();
    $is_admin = ph_staff_attendance_is_admin();
    $staff_result = ph_staff_attendance_get_staff(true);

    echo '<div class="wrap"><h1>Staff Attendance</h1>';
    ph_staff_attendance_messages();
    if (is_wp_error($staff_result)) {
        ph_staff_attendance_notice($staff_result->get_error_message(), 'error');
        echo '</div>';
        return;
    }
    $staff = $staff_result;
    $today = current_datetime()->format('Y-m-d');

    echo '<p>Hospital date: <strong>' . esc_html(current_datetime()->format(get_option('date_format'))) . '</strong>. Check-in and check-out times are recorded automatically.</p>';
    echo '<h2>Daily Attendance</h2>';
    echo '<table class="widefat striped"><thead><tr><th>Employee ID</th><th>Staff Name</th><th>Designation</th><th>Attendance Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Action</th></tr></thead><tbody>';

    if (!$staff) {
        echo '<tr><td colspan="8">No active staff members found. Add staff in Staff Management first.</td></tr>';
    } else {
        foreach ($staff as $person) {
            $open = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$attendance} WHERE staff_id = %d AND check_out IS NULL ORDER BY check_in DESC LIMIT 1",
                $person->id
            ));
            $today_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$attendance} WHERE staff_id = %d AND attendance_date = %s ORDER BY id DESC LIMIT 1",
                $person->id, $today
            ));
            $record = $open ?: $today_record;
            $record_date = $record ? $record->attendance_date : $today;
            $status = !$record ? 'Not Marked' : (empty($record->check_out) ? 'Present — Open' : 'Completed');
            echo '<tr><td>' . esc_html($person->employee_id) . '</td><td>' . esc_html($person->staff_name) . '</td><td>' . esc_html($person->designation) . '</td><td>' . esc_html($record_date) . '</td><td>' . ($record ? ph_staff_attendance_format_datetime($record->check_in) : '—') . '</td><td>' . ($record ? ph_staff_attendance_format_datetime($record->check_out) : '—') . '</td><td>' . esc_html($status) . '</td><td>';
            if ($open) {
                echo '<form method="post" style="display:inline">';
                wp_nonce_field('ph_staff_attendance_action', 'ph_attendance_nonce');
                echo '<input type="hidden" name="ph_attendance_action" value="check_out"><input type="hidden" name="staff_id" value="' . (int) $person->id . '"><button class="button button-primary" type="submit" onclick="return confirm(\'Record checkout at the current time?\')">Check Out</button></form>';
                if (strtotime($open->check_in) < strtotime($today . ' 00:00:00')) {
                    echo '<br><small>Overnight/open record — started ' . esc_html($open->attendance_date) . '</small>';
                }
            } else {
                // A completed shift should not prevent another check-in on the same day.
                // Only an open record blocks a new shift; the latest completed shift remains visible above.
                echo '<form method="post" style="display:inline">';
                wp_nonce_field('ph_staff_attendance_action', 'ph_attendance_nonce');
                echo '<input type="hidden" name="ph_attendance_action" value="check_in"><input type="hidden" name="staff_id" value="' . (int) $person->id . '"><button class="button button-primary" type="submit" onclick="return confirm(\'Record check-in at the current time?\')">Check In</button></form>';
            }
            echo '</td></tr>';
        }
    }
    echo '</tbody></table>';

    if ($is_admin) {
        echo '<hr><h2>Attendance History &amp; Corrections (Administrator)</h2>';
        $history_date = isset($_GET['history_date']) ? sanitize_text_field(wp_unslash($_GET['history_date'])) : $today;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $history_date)) $history_date = $today;
        echo '<form method="get" style="margin:12px 0"><input type="hidden" name="page" value="ph-staff-attendance"><label for="history_date">Attendance date: </label> <input type="date" id="history_date" name="history_date" value="' . esc_attr($history_date) . '"> <button class="button">Search</button></form>';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, s.employee_id, s.staff_name, s.designation FROM {$attendance} a LEFT JOIN " . ph_staff_attendance_staff_table() . " s ON s.id = a.staff_id WHERE a.attendance_date = %s ORDER BY a.check_in DESC LIMIT 300",
            $history_date
        ));
        echo '<table class="widefat striped"><thead><tr><th>Employee</th><th>Attendance Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Correction</th></tr></thead><tbody>';
        if (!$rows) {
            echo '<tr><td colspan="6">No attendance records found for this date.</td></tr>';
        } else {
            foreach ($rows as $row) {
                echo '<tr><td>' . esc_html(($row->employee_id ?: '') . ' — ' . ($row->staff_name ?: 'Staff record missing')) . '</td><td>' . esc_html($row->attendance_date) . '</td><td>' . ph_staff_attendance_format_datetime($row->check_in) . '</td><td>' . ph_staff_attendance_format_datetime($row->check_out) . '</td><td>' . esc_html(empty($row->check_out) ? 'Open' : 'Completed') . '</td><td>';
                echo '<details><summary>Edit record</summary><form method="post" style="margin-top:8px;min-width:260px">';
                wp_nonce_field('ph_staff_attendance_action', 'ph_attendance_nonce');
                echo '<input type="hidden" name="ph_attendance_action" value="admin_edit"><input type="hidden" name="record_id" value="' . (int) $row->id . '">';
                echo '<p><label>Attendance date<br><input required type="date" name="attendance_date" value="' . esc_attr($row->attendance_date) . '"></label></p>';
                echo '<p><label>Check in<br><input required type="datetime-local" name="check_in" value="' . esc_attr(str_replace(' ', 'T', substr($row->check_in, 0, 16))) . '"></label></p>';
                echo '<p><label>Check out (leave blank if still open)<br><input type="datetime-local" name="check_out" value="' . esc_attr($row->check_out ? str_replace(' ', 'T', substr($row->check_out, 0, 16)) : '') . '"></label></p>';
                echo '<p><label>Reason for correction<br><textarea required name="change_note" rows="2"></textarea></label></p><button class="button button-primary" type="submit">Save Correction</button></form></details></td></tr>';
            }
        }
        echo '</tbody></table>';

        echo '<h2>Correction Audit History</h2>';
        $audit_rows = $wpdb->get_results("SELECT * FROM " . ph_staff_attendance_audit_table() . " ORDER BY changed_at DESC LIMIT 100");
        echo '<table class="widefat striped"><thead><tr><th>Changed At</th><th>Attendance Record</th><th>Changed By</th><th>Reason</th><th>Old Check In</th><th>New Check In</th><th>Old Check Out</th><th>New Check Out</th></tr></thead><tbody>';
        if (!$audit_rows) {
            echo '<tr><td colspan="8">No administrator corrections have been recorded.</td></tr>';
        } else {
            foreach ($audit_rows as $audit) {
                $user = get_user_by('id', (int) $audit->changed_by);
                echo '<tr><td>' . ph_staff_attendance_format_datetime($audit->changed_at) . '</td><td>#' . (int) $audit->attendance_id . '</td><td>' . esc_html($user ? $user->user_login : 'Unknown user') . '</td><td>' . esc_html($audit->change_note) . '</td><td>' . ph_staff_attendance_format_datetime($audit->old_check_in) . '</td><td>' . ph_staff_attendance_format_datetime($audit->new_check_in) . '</td><td>' . ph_staff_attendance_format_datetime($audit->old_check_out) . '</td><td>' . ph_staff_attendance_format_datetime($audit->new_check_out) . '</td></tr>';
            }
        }
        echo '</tbody></table>';
    }
    echo '</div>';
}
