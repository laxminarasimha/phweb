<?php
/**
 * Plugin Name: Prasanthi Hospitals Staff Portal
 * Description: Adds a dedicated dashboard and restricted administration menu for the existing Hospital Front Desk role.
 * Version: 1.0.1
 * Author: Prasanthi Hospitals
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Extend the existing Hospital Front Desk role; do not create a duplicate reception role. */
function ph_staff_portal_sync_role() {
    $role = get_role( 'hospital_front_desk' );
    if ( ! $role ) {
        return;
    }

    foreach ( array( 'read', 'ph_manage_patients', 'ph_manage_appointments', 'ph_manage_visits' ) as $cap ) {
        $role->add_cap( $cap );
    }

    // Migrate any local test users from the duplicate role created by v1.0.0.
    $duplicate_role = get_role( 'ph_reception_staff' );
    if ( $duplicate_role ) {
        $users = get_users( array( 'role' => 'ph_reception_staff', 'fields' => array( 'ID' ) ) );
        foreach ( $users as $user ) {
            $user_object = new WP_User( $user->ID );
            $user_object->set_role( 'hospital_front_desk' );
        }
        remove_role( 'ph_reception_staff' );
    }
}
register_activation_hook( __FILE__, 'ph_staff_portal_sync_role' );
add_action( 'plugins_loaded', 'ph_staff_portal_sync_role', 20 );

function ph_staff_portal_is_reception() {
    $user = wp_get_current_user();
    return $user && in_array( 'hospital_front_desk', (array) $user->roles, true );
}

/** Put the dedicated dashboard under the existing Prasanthi Hospitals admin menu. */
function ph_staff_portal_register_menu() {
    add_submenu_page(
        'prasanthi-hospitals',
        'Staff Dashboard',
        'Dashboard',
        'ph_manage_patients',
        'ph-staff-dashboard',
        'ph_staff_portal_render_dashboard',
        0
    );
}
add_action( 'admin_menu', 'ph_staff_portal_register_menu', 5 );

function ph_staff_portal_count_rows( $table, $where_sql = '', $where_args = array() ) {
    global $wpdb;
    $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
    if ( $exists !== $table ) {
        return null;
    }
    if ( $where_sql ) {
        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE {$where_sql}", $where_args ) );
    }
    return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
}

function ph_staff_portal_render_dashboard() {
    if ( ! current_user_can( 'ph_manage_patients' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'prasanthi-hospitals' ), 403 );
    }
    global $wpdb;
    $patients_table = $wpdb->prefix . 'ph_patients';
    $appointments_table = $wpdb->prefix . 'ph_appointments';
    $visits_table = $wpdb->prefix . 'ph_visits';
    $today = current_time( 'Y-m-d' );

    $patient_count = ph_staff_portal_count_rows( $patients_table );
    $appointment_count = ph_staff_portal_count_rows( $appointments_table, 'COALESCE(confirmed_date, preferred_date) = %s', array( $today ) );
    $visit_count = ph_staff_portal_count_rows( $visits_table, 'visit_date = %s', array( $today ) );
    ?>
    <div class="wrap ph-staff-portal">
        <h1>Hospital Front Desk Dashboard</h1>
        <p>Welcome to Prasanthi Hospitals. Use the shortcuts below to manage patient registration, appointments, and visits.</p>
        <style>
            .ph-staff-portal .phsp-cards{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:16px;max-width:1100px;margin:22px 0}
            .ph-staff-portal .phsp-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px}
            .ph-staff-portal .phsp-card h2{font-size:15px;margin:0 0 12px}
            .ph-staff-portal .phsp-number{font-size:30px;font-weight:700;line-height:1.2;margin-bottom:14px;color:#51417a}
            .ph-staff-portal .phsp-actions{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0}
            .ph-staff-portal .phsp-actions .button{padding:5px 14px}
            @media(max-width:782px){.ph-staff-portal .phsp-cards{grid-template-columns:1fr}}
        </style>
        <div class="phsp-cards">
            <section class="phsp-card"><h2>Registered Patients</h2><div class="phsp-number"><?php echo null === $patient_count ? '—' : esc_html( number_format_i18n( $patient_count ) ); ?></div><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-patients' ) ); ?>">Manage Patients</a></section>
            <section class="phsp-card"><h2>Appointments Today</h2><div class="phsp-number"><?php echo null === $appointment_count ? '—' : esc_html( number_format_i18n( $appointment_count ) ); ?></div><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>">View Appointments</a></section>
            <section class="phsp-card"><h2>Visits Today</h2><div class="phsp-number"><?php echo null === $visit_count ? '—' : esc_html( number_format_i18n( $visit_count ) ); ?></div><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>">View Visits</a></section>
        </div>
        <h2>Quick Actions</h2>
        <div class="phsp-actions">
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-patients&action=add' ) ); ?>">Register Patient</a>
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>">Manage Appointments</a>
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits-add' ) ); ?>">Create Visit / Walk-in</a>
        </div>
        <p class="description">Patient access is available to authorized Hospital Front Desk accounts. Please sign out when leaving the reception desk.</p>
    </div>
    <?php
}

/** Hide unrelated WordPress menus from Hospital Front Desk users, retaining only hospital workflow menus. */
function ph_staff_portal_restrict_admin_menu() {
    if ( ! ph_staff_portal_is_reception() ) {
        return;
    }

    global $menu, $submenu;
    $allowed_top_level = array( 'prasanthi-hospitals' );
    if ( is_array( $menu ) ) {
        foreach ( $menu as $item ) {
            if ( empty( $item[2] ) || in_array( $item[2], $allowed_top_level, true ) ) {
                continue;
            }
            remove_menu_page( $item[2] );
        }
    }

    // Keep only the workflow items beneath the hospital menu.
    $allowed_submenus = array( 'ph-staff-dashboard', 'ph-patients', 'ph-appointments', 'ph-visits', 'ph-visits-add' );
    if ( isset( $submenu['prasanthi-hospitals'] ) && is_array( $submenu['prasanthi-hospitals'] ) ) {
        foreach ( $submenu['prasanthi-hospitals'] as $item ) {
            if ( isset( $item[2] ) && ! in_array( $item[2], $allowed_submenus, true ) ) {
                remove_submenu_page( 'prasanthi-hospitals', $item[2] );
            }
        }
    }
}
add_action( 'admin_menu', 'ph_staff_portal_restrict_admin_menu', 9999 );

/** Send Hospital Front Desk users to their dashboard after login. */
function ph_staff_portal_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( $user instanceof WP_User && in_array( 'hospital_front_desk', (array) $user->roles, true ) ) {
        return admin_url( 'admin.php?page=ph-staff-dashboard' );
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'ph_staff_portal_login_redirect', 10, 3 );

/** Prevent Hospital Front Desk users landing on the generic WordPress dashboard. */
function ph_staff_portal_redirect_default_dashboard() {
    if ( ! is_admin() || ! ph_staff_portal_is_reception() || wp_doing_ajax() ) {
        return;
    }
    global $pagenow;
    if ( 'index.php' === $pagenow ) {
        wp_safe_redirect( admin_url( 'admin.php?page=ph-staff-dashboard' ) );
        exit;
    }
}
add_action( 'admin_init', 'ph_staff_portal_redirect_default_dashboard' );

/** Simplify the admin bar for Hospital Front Desk users. */
function ph_staff_portal_admin_bar( $wp_admin_bar ) {
    if ( ! ph_staff_portal_is_reception() ) {
        return;
    }
    $wp_admin_bar->remove_node( 'new-content' );
    $wp_admin_bar->remove_node( 'comments' );
    $wp_admin_bar->remove_node( 'customize' );
}
add_action( 'admin_bar_menu', 'ph_staff_portal_admin_bar', 999 );
