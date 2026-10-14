<?php
/**
 * Plugin Name: Prasanthi Hospitals Staff Portal
 * Description: Adds a dedicated dashboard and restricted administration menu for the existing Hospital Front Desk role.
 * Version: 1.0.3
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

function ph_staff_portal_register_waiting_list_menu() {
    add_submenu_page(
        'prasanthi-hospitals',
        'Waiting List',
        'Waiting List',
        'ph_manage_visits',
        'ph-staff-waiting-list',
        'ph_staff_portal_render_waiting_list'
    );
}
add_action( 'admin_menu', 'ph_staff_portal_register_waiting_list_menu', 6 );

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

/**
 * Show today's arrived visits in a receptionist-friendly waiting list.
 * Uses the existing Visits table; it does not create queue or duplicate visit records.
 */
function ph_staff_portal_render_waiting_list() {
    if ( ! current_user_can( 'ph_manage_visits' ) ) {
        wp_die( esc_html__( 'You do not have permission to access the waiting list.', 'prasanthi-hospitals' ), 403 );
    }

    global $wpdb;
    $visits_table = $wpdb->prefix . 'ph_visits';
    $today = current_time( 'Y-m-d' );
    $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $visits_table ) ) );

    if ( $exists !== $visits_table ) {
        echo '<div class="wrap"><h1>Waiting List</h1><div class="notice notice-error"><p>The Visits module is not available. Please activate Prasanthi Hospitals Visits.</p></div></div>';
        return;
    }

    $visits = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM `{$visits_table}` WHERE visit_date = %s AND status = %s ORDER BY visit_time ASC, id ASC",
            $today,
            'arrived'
        )
    );
    ?>
    <div class="wrap ph-staff-portal">
        <h1>Waiting List</h1>
        <p>Patients who have arrived and are waiting for consultation today.</p>
        <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-staff-dashboard' ) ); ?>">← Back to Dashboard</a>
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>">All Visits</a></p>

        <style>
            .ph-staff-waiting-table { width:100%; border-collapse:collapse; margin-top:16px; background:#fff; }
            .ph-staff-waiting-table th, .ph-staff-waiting-table td { padding:11px 12px; border:1px solid #e2dced; text-align:left; vertical-align:top; }
            .ph-staff-waiting-table th { background:#f7f4fb; }
            .ph-staff-waiting-table .ph-waiting-status { display:inline-block; padding:3px 9px; border-radius:12px; background:#fff1df; color:#7a4300; font-weight:600; }
            @media(max-width:782px) { .ph-staff-waiting-table { display:block; overflow-x:auto; white-space:nowrap; } }
        </style>

        <p><strong>Waiting patients:</strong> <?php echo esc_html( number_format_i18n( count( $visits ) ) ); ?></p>

        <?php if ( empty( $visits ) ) : ?>
            <div class="notice notice-info inline"><p>No patients are currently waiting for consultation today.</p></div>
        <?php else : ?>
            <table class="ph-staff-waiting-table">
                <thead>
                    <tr>
                        <th>Visit Reference</th>
                        <th>Patient</th>
                        <th>Patient ID</th>
                        <th>Doctor</th>
                        <th>Arrival / Visit Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $visits as $visit ) :
                    $patient = function_exists( 'ph_visits_patient' ) ? ph_visits_patient( $visit->patient_id ) : null;
                    $doctor_name = function_exists( 'ph_visits_doctor' ) ? ph_visits_doctor( $visit->doctor_id ) : '—';
                    $patient_name = ( $patient && ! empty( $patient->patient_name ) ) ? $patient->patient_name : 'Patient record unavailable';
                    $patient_reference = ( $patient && ! empty( $patient->patient_reference ) ) ? $patient->patient_reference : $visit->patient_id;
                    $time_label = ( ! empty( $visit->visit_time ) && '00:00:00' !== $visit->visit_time ) ? mysql2date( get_option( 'time_format' ), $visit->visit_time ) : '—';
                    ?>
                    <tr>
                        <td><?php echo esc_html( $visit->visit_reference ); ?></td>
                        <td><?php echo esc_html( $patient_name ); ?></td>
                        <td><?php echo esc_html( $patient_reference ); ?></td>
                        <td><?php echo esc_html( $doctor_name ); ?></td>
                        <td><?php echo esc_html( $time_label ); ?></td>
                        <td><span class="ph-waiting-status">Arrived</span></td>
                        <td><a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . (int) $visit->id ) ); ?>">View Visit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

function ph_staff_portal_render_dashboard() {

    if ( ! current_user_can( 'ph_manage_patients' ) ) {
        wp_die(
            esc_html__( 'You do not have permission to access this page.', 'prasanthi-hospitals' ),
            403
        );
    }

    global $wpdb;

    $patients_table     = $wpdb->prefix . 'ph_patients';
    $appointments_table = $wpdb->prefix . 'ph_appointments';
    $visits_table       = $wpdb->prefix . 'ph_visits';

    $today = current_time( 'Y-m-d' );

    // Registered patients.
    $patient_count = ph_staff_portal_count_rows(
        $patients_table
    );

    // Appointments scheduled for today.
    $appointment_count = ph_staff_portal_count_rows(
        $appointments_table,
        'COALESCE(confirmed_date, preferred_date) = %s',
        array( $today )
    );

    // New appointment requests awaiting processing.
    $new_request_count = ph_staff_portal_count_rows(
        $appointments_table,
        'status = %s',
        array( 'new_request' )
    );

    // Patients registered today.
    $patients_today_count = ph_staff_portal_count_rows(
        $patients_table,
        'DATE(created_at) = %s',
        array( $today )
    );

    // Visits recorded for today.
    $visit_count = ph_staff_portal_count_rows(
        $visits_table,
        'visit_date = %s',
        array( $today )
    );

    // Visits for patients who have arrived and are awaiting consultation.
    $waiting_count = ph_staff_portal_count_rows(
        $visits_table,
        'visit_date = %s AND status = %s',
        array( $today, 'arrived' )
    );

    // Visits currently in consultation.
    $consultation_count = ph_staff_portal_count_rows(
        $visits_table,
        'visit_date = %s AND status = %s',
        array( $today, 'in_consultation' )
    );

    ?>

    <div class="wrap ph-staff-portal">

        <h1>Hospital Front Desk Dashboard</h1>

        <p>
            Welcome to Prasanthi Hospitals.
            Here is today's reception overview.
        </p>

        <style>
            .ph-staff-portal .phsp-cards {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                max-width: 1200px;
                margin: 24px 0;
            }

            .ph-staff-portal .phsp-card {
                background: #fff;
                border: 1px solid #e2dced;
                border-radius: 10px;
                padding: 22px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            }

            .ph-staff-portal .phsp-card h2 {
                font-size: 15px;
                line-height: 1.5;
                margin: 0 0 14px;
            }

            .ph-staff-portal .phsp-number {
                font-size: 34px;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 18px;
                color: #51417a;
            }

            .ph-staff-portal .phsp-card .button {
                white-space: normal;
            }

            .ph-staff-portal .phsp-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin: 20px 0;
            }

            .ph-staff-portal .phsp-actions .button {
                padding: 5px 14px;
            }

            @media (max-width: 1000px) {
                .ph-staff-portal .phsp-cards {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 600px) {
                .ph-staff-portal .phsp-cards {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="phsp-cards">

            <section class="phsp-card">
                <h2>Today's Appointments</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $appointment_count
                        ? '—'
                        : esc_html( number_format_i18n( $appointment_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>"
                >
                    View Appointments
                </a>
            </section>

            <section class="phsp-card">
                <h2>New Requests</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $new_request_count
                        ? '—'
                        : esc_html( number_format_i18n( $new_request_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>"
                >
                    Review Requests
                </a>
            </section>

            <section class="phsp-card">
                <h2>Patients Registered Today</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $patients_today_count
                        ? '—'
                        : esc_html( number_format_i18n( $patients_today_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-patients' ) ); ?>"
                >
                    View Patients
                </a>
            </section>

            <section class="phsp-card">
                <h2>Today's Visits</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $visit_count
                        ? '—'
                        : esc_html( number_format_i18n( $visit_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>"
                >
                    View Visits
                </a>
            </section>

            <section class="phsp-card">
                <h2>Waiting for Consultation</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $waiting_count
                        ? '—'
                        : esc_html( number_format_i18n( $waiting_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-staff-waiting-list' ) ); ?>"
                >
                    View Waiting List
                </a>
            </section>

            <section class="phsp-card">
                <h2>In Consultation</h2>

                <div class="phsp-number">
                    <?php
                    echo null === $consultation_count
                        ? '—'
                        : esc_html( number_format_i18n( $consultation_count ) );
                    ?>
                </div>

                <a
                    class="button button-primary"
                    href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>"
                >
                    View Visits
                </a>
            </section>

        </div>

        <h2>Quick Actions</h2>

        <div class="phsp-actions">

            <a
                class="button button-primary"
                href="<?php echo esc_url( admin_url( 'admin.php?page=ph-patients&action=add' ) ); ?>"
            >
                Register Patient
            </a>

            <a
                class="button button-primary"
                href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>"
            >
                Manage Appointments
            </a>

            <a
                class="button button-primary"
                href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits-add' ) ); ?>"
            >
                Create Walk-in Visit
            </a>

        </div>

        <p class="description">
            Please sign out when leaving the reception desk.
        </p>

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
    $allowed_submenus = array( 'ph-staff-dashboard', 'ph-staff-waiting-list', 'ph-patients', 'ph-appointments', 'ph-visits', 'ph-visits-add' );
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
