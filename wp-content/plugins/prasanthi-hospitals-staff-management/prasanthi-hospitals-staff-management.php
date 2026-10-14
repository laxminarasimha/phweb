<?php
/**
 * Plugin Name: Prasanthi Hospitals Staff Management
 * Description: Employee master records for Prasanthi Hospitals, designed to support future staff attendance and individual staff accounts.
 * Version: 1.0.0
 * Author: Prasanthi Hospitals
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PH_STAFF_MANAGEMENT_DB_VERSION', '1.0.0' );

function ph_staff_management_table() {
    global $wpdb;
    return $wpdb->prefix . 'ph_staff';
}

function ph_staff_management_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = ph_staff_management_table();
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        employee_id VARCHAR(30) NOT NULL,
        staff_name VARCHAR(150) NOT NULL,
        designation VARCHAR(100) NOT NULL DEFAULT '',
        mobile VARCHAR(30) NOT NULL DEFAULT '',
        date_of_joining DATE NULL,
        employment_status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY employee_id (employee_id),
        KEY staff_name (staff_name),
        KEY mobile (mobile),
        KEY employment_status (employment_status)
    ) {$charset};";
    dbDelta( $sql );
    update_option( 'ph_staff_management_db_version', PH_STAFF_MANAGEMENT_DB_VERSION );
}

function ph_staff_management_add_capabilities() {
    // Only administrators can create or edit staff master records.
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'ph_manage_staff' );
        $admin_role->add_cap( 'ph_view_staff' );
    }

    // The existing front-desk role may view the staff directory and use
    // attendance functions, but must not manage employee master records.
    $front_desk_role = get_role( 'hospital_front_desk' );
    if ( $front_desk_role ) {
        $front_desk_role->remove_cap( 'ph_manage_staff' );
        $front_desk_role->add_cap( 'ph_view_staff' );
        $front_desk_role->add_cap( 'ph_manage_attendance' );
    }
}

function ph_staff_management_activate() {
    ph_staff_management_install();
    ph_staff_management_add_capabilities();
}
register_activation_hook( __FILE__, 'ph_staff_management_activate' );

function ph_staff_management_upgrade() {
    if ( PH_STAFF_MANAGEMENT_DB_VERSION !== get_option( 'ph_staff_management_db_version', '' ) ) {
        ph_staff_management_install();
    }
    ph_staff_management_add_capabilities();
}
add_action( 'plugins_loaded', 'ph_staff_management_upgrade', 20 );

function ph_staff_management_register_menu() {
    add_submenu_page( 'prasanthi-hospitals', 'Staff Management', 'Staff Management', 'ph_view_staff', 'ph-staff-management', 'ph_staff_management_render_page' );
}
add_action( 'admin_menu', 'ph_staff_management_register_menu', 20 );

function ph_staff_management_generate_employee_id() {
    global $wpdb;
    $table = ph_staff_management_table();
    $next = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$table}" ) + 1;
    do {
        $candidate = 'PH-EMP-' . str_pad( (string) $next, 6, '0', STR_PAD_LEFT );
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE employee_id = %s LIMIT 1", $candidate ) );
        $next++;
    } while ( $exists );
    return $candidate;
}

function ph_staff_management_redirect( $args = array() ) {
    wp_safe_redirect( add_query_arg( array_merge( array( 'page' => 'ph-staff-management' ), $args ), admin_url( 'admin.php' ) ) );
    exit;
}

function ph_staff_management_handle_post() {
    if ( ! is_admin() || ! current_user_can( 'ph_manage_staff' ) || empty( $_POST['ph_staff_management_action'] ) ) return;
    check_admin_referer( 'ph_staff_management_save', 'ph_staff_management_nonce' );
    if ( 'save_staff' !== sanitize_key( wp_unslash( $_POST['ph_staff_management_action'] ) ) ) return;

    global $wpdb;
    $table = ph_staff_management_table();
    $id = isset( $_POST['staff_record_id'] ) ? absint( $_POST['staff_record_id'] ) : 0;
    $name = isset( $_POST['staff_name'] ) ? sanitize_text_field( wp_unslash( $_POST['staff_name'] ) ) : '';
    if ( '' === $name ) ph_staff_management_redirect( array( 'ph_notice' => 'missing_name', 'edit' => $id ) );

    $designation = isset( $_POST['designation'] ) ? sanitize_text_field( wp_unslash( $_POST['designation'] ) ) : '';
    $mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
    $joining = isset( $_POST['date_of_joining'] ) ? sanitize_text_field( wp_unslash( $_POST['date_of_joining'] ) ) : '';
    if ( $joining && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $joining ) ) $joining = '';
    $status = isset( $_POST['employment_status'] ) ? sanitize_key( wp_unslash( $_POST['employment_status'] ) ) : 'active';
    if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) $status = 'active';

    $now = current_time( 'mysql' );
    $data = array(
        'staff_name' => $name,
        'designation' => $designation,
        'mobile' => $mobile,
        'date_of_joining' => $joining ? $joining : null,
        'employment_status' => $status,
        'updated_at' => $now,
    );

    if ( $id ) {
        $result = $wpdb->update( $table, $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%s', '%s', '%s' ), array( '%d' ) );
        if ( false === $result ) ph_staff_management_redirect( array( 'ph_notice' => 'save_error', 'edit' => $id ) );
        ph_staff_management_redirect( array( 'ph_notice' => 'updated' ) );
    }

    $data['employee_id'] = ph_staff_management_generate_employee_id();
    $data['created_at'] = $now;
    $result = $wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
    if ( false === $result ) ph_staff_management_redirect( array( 'ph_notice' => 'save_error' ) );
    ph_staff_management_redirect( array( 'ph_notice' => 'created' ) );
}
add_action( 'admin_init', 'ph_staff_management_handle_post' );

function ph_staff_management_render_page() {
    if ( ! current_user_can( 'ph_view_staff' ) && ! current_user_can( 'ph_manage_staff' ) ) wp_die( esc_html__( 'You do not have permission to access this page.', 'prasanthi-hospitals' ), 403 );
    global $wpdb;
    $table = ph_staff_management_table();
    $edit_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
    $editing = ( $edit_id && current_user_can( 'ph_manage_staff' ) ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ) ) : null;
    $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $filter = isset( $_GET['staff_status'] ) ? sanitize_key( wp_unslash( $_GET['staff_status'] ) ) : '';
    $where = array( '1=1' ); $args = array();
    if ( $search !== '' ) {
        $like = '%' . $wpdb->esc_like( $search ) . '%';
        $where[] = '(employee_id LIKE %s OR staff_name LIKE %s OR designation LIKE %s OR mobile LIKE %s)';
        array_push( $args, $like, $like, $like, $like );
    }
    if ( in_array( $filter, array( 'active', 'inactive' ), true ) ) { $where[] = 'employment_status = %s'; $args[] = $filter; }
    $sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY id DESC LIMIT 500';
    $rows = $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_results( $sql );
    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $active = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE employment_status = 'active'" );
    ?>
    <div class="wrap ph-staff-management">
    <h1>Staff Management</h1>
    <?php
    if ( isset( $_GET['ph_notice'] ) ) {
        $notices = array(
            'created' => array( 'success', 'Staff member added successfully.' ),
            'updated' => array( 'success', 'Staff member updated successfully.' ),
            'missing_name' => array( 'error', 'Staff name is required.' ),
            'save_error' => array( 'error', 'The record could not be saved. Please try again.' ),
        );
        $notice = sanitize_key( wp_unslash( $_GET['ph_notice'] ) );
        if ( isset( $notices[$notice] ) ) printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $notices[$notice][0] ), esc_html( $notices[$notice][1] ) );
    }
    ?>
    <p>Maintain employee records for Prasanthi Hospitals. Employee IDs are generated automatically and remain unchanged when a record is edited.</p>
    <style>
    .phsm-summary{display:flex;gap:12px;flex-wrap:wrap;margin:18px 0}.phsm-stat{background:#fff;border:1px solid #e2dced;border-radius:8px;padding:14px 20px;min-width:150px}.phsm-stat strong{display:block;font-size:24px;color:#51417a}.phsm-form{background:#fff;border:1px solid #e2dced;border-radius:8px;padding:18px;max-width:900px;margin:22px 0}.phsm-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.phsm-field label{display:block;font-weight:600;margin-bottom:5px}.phsm-field input,.phsm-field select{width:100%;max-width:100%}.phsm-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}.phsm-status{display:inline-block;border-radius:12px;padding:3px 9px;font-weight:600;font-size:12px}.phsm-active{background:#e7f7ed;color:#176b35}.phsm-inactive{background:#f0f0f1;color:#50575e}@media(max-width:700px){.phsm-grid{grid-template-columns:1fr}}
    </style>
    <div class="phsm-summary"><div class="phsm-stat">Total staff records<strong><?php echo esc_html( $total ); ?></strong></div><div class="phsm-stat">Active staff<strong><?php echo esc_html( $active ); ?></strong></div></div>
    <?php if ( current_user_can( 'ph_manage_staff' ) ) : ?>
    <div class="phsm-form">
      <h2><?php echo $editing ? 'Edit Staff Member' : 'Add Staff Member'; ?></h2>
      <?php if ( $editing ) : ?><p><strong>Employee ID:</strong> <?php echo esc_html( $editing->employee_id ); ?></p><?php endif; ?>
      <form method="post" action="">
        <?php wp_nonce_field( 'ph_staff_management_save', 'ph_staff_management_nonce' ); ?>
        <input type="hidden" name="ph_staff_management_action" value="save_staff">
        <input type="hidden" name="staff_record_id" value="<?php echo esc_attr( $editing ? $editing->id : 0 ); ?>">
        <div class="phsm-grid">
          <div class="phsm-field"><label for="staff_name">Staff Name *</label><input type="text" id="staff_name" name="staff_name" required maxlength="150" value="<?php echo esc_attr( $editing ? $editing->staff_name : '' ); ?>"></div>
          <div class="phsm-field"><label for="designation">Designation</label><input type="text" id="designation" name="designation" maxlength="100" placeholder="e.g. Nurse, Technician, Receptionist" value="<?php echo esc_attr( $editing ? $editing->designation : '' ); ?>"></div>
          <div class="phsm-field"><label for="mobile">Mobile Number</label><input type="tel" id="mobile" name="mobile" maxlength="30" value="<?php echo esc_attr( $editing ? $editing->mobile : '' ); ?>"></div>
          <div class="phsm-field"><label for="date_of_joining">Date of Joining</label><input type="date" id="date_of_joining" name="date_of_joining" value="<?php echo esc_attr( $editing && $editing->date_of_joining ? $editing->date_of_joining : '' ); ?>"></div>
          <div class="phsm-field"><label for="employment_status">Employment Status</label><select id="employment_status" name="employment_status"><option value="active" <?php selected( $editing ? $editing->employment_status : 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $editing ? $editing->employment_status : 'active', 'inactive' ); ?>>Inactive</option></select></div>
        </div>
        <div class="phsm-actions"><button type="submit" class="button button-primary"><?php echo $editing ? 'Update Staff Member' : 'Add Staff Member'; ?></button><?php if ( $editing ) : ?><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-staff-management' ) ); ?>">Cancel Edit</a><?php endif; ?></div>
      </form>
    </div>
    <?php else : ?>
      <div class="notice notice-info inline"><p>You can view the staff directory here. Only an administrator can add or edit staff records. Attendance actions will be available in the attendance module.</p></div>
    <?php endif; ?>
    <h2>Staff Directory</h2>
    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin:12px 0;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
      <input type="hidden" name="page" value="ph-staff-management">
      <input type="search" name="s" placeholder="Search ID, name, designation or mobile" value="<?php echo esc_attr( $search ); ?>">
      <select name="staff_status"><option value="">All statuses</option><option value="active" <?php selected( $filter, 'active' ); ?>>Active</option><option value="inactive" <?php selected( $filter, 'inactive' ); ?>>Inactive</option></select>
      <button class="button">Filter</button><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-staff-management' ) ); ?>">Reset</a>
    </form>
    <table class="widefat striped"><thead><tr><th>Employee ID</th><th>Staff Name</th><th>Designation</th><th>Mobile</th><th>Date of Joining</th><th>Status</th><th>Action</th></tr></thead><tbody>
    <?php if ( $rows ) : foreach ( $rows as $staff ) : ?>
      <tr><td><strong><?php echo esc_html( $staff->employee_id ); ?></strong></td><td><?php echo esc_html( $staff->staff_name ); ?></td><td><?php echo esc_html( $staff->designation ?: '—' ); ?></td><td><?php echo esc_html( $staff->mobile ?: '—' ); ?></td><td><?php echo esc_html( $staff->date_of_joining ?: '—' ); ?></td><td><span class="phsm-status <?php echo 'active' === $staff->employment_status ? 'phsm-active' : 'phsm-inactive'; ?>"><?php echo esc_html( ucfirst( $staff->employment_status ) ); ?></span></td><td><a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'ph-staff-management', 'edit' => $staff->id ), admin_url( 'admin.php' ) ) ); ?>">Edit</a></td></tr>
    <?php endforeach; else : ?><tr><td colspan="7">No staff records found. Use the form above to add your first employee.</td></tr><?php endif; ?>
    </tbody></table>
    <p class="description">Up to 500 staff records are shown at a time. Inactive records are retained for historical attendance and reporting.</p>
    </div>
    <?php
}
