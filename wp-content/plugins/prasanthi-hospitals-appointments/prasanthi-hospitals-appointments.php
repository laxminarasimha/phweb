<?php
/**
 * Plugin Name: Prasanthi Hospitals Appointments
 * Description: Appointment request form and front-desk appointment management for Prasanthi Hospitals.
 * Version: 1.3.3
 * Author: Prasanthi Hospitals
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PH_APPOINTMENTS_VERSION', '1.3.3' );

/**
 * ============================================================
 * CONSTANTS / HELPERS
 * ============================================================
 */

function ph_appointments_table() {
    global $wpdb;
    return $wpdb->prefix . 'ph_appointments';
}

function ph_appointments_statuses() {
    return array(
        'new_request'     => 'New Request',
        'confirmed'       => 'Confirmed',
        'rescheduled'     => 'Rescheduled',
        'cancelled'       => 'Cancelled',
        'no_show'         => 'No Show',
        'arrived'         => 'Arrived',
        'in_consultation' => 'In Consultation',
        'completed'       => 'Completed',
    );
}

function ph_appointments_status_label( $status ) {
    $statuses = ph_appointments_statuses();

    return isset( $statuses[ $status ] )
        ? $statuses[ $status ]
        : ucfirst( str_replace( '_', ' ', $status ) );
}

/**
 * ============================================================
 * CAPABILITIES
 * ============================================================
 */

function ph_appointments_add_capabilities() {

    $capability = 'ph_manage_appointments';

    $admin_role = get_role( 'administrator' );

    if ( $admin_role ) {
        $admin_role->add_cap( $capability );
    }

    $front_desk_role = get_role( 'hospital_front_desk' );

    if ( ! $front_desk_role ) {
        add_role(
            'hospital_front_desk',
            'Hospital Front Desk',
            array(
                'read' => true,
            )
        );

        $front_desk_role = get_role( 'hospital_front_desk' );
    }

    if ( $front_desk_role ) {
        $front_desk_role->add_cap( $capability );
    }
}

/**
 * ============================================================
 * DATABASE
 * ============================================================
 */

function ph_appointments_create_table() {

    global $wpdb;

    $table_name      = ph_appointments_table();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        request_reference VARCHAR(30) NOT NULL,
        patient_name VARCHAR(150) NOT NULL,
        mobile VARCHAR(30) NOT NULL,
        patient_id VARCHAR(30) NULL,
        doctor_id BIGINT UNSIGNED NULL,
        preferred_date DATE NULL,
        preferred_session VARCHAR(20) NULL,
        patient_type VARCHAR(20) NULL,
        preferred_language VARCHAR(20) NULL,
        reason TEXT NULL,
        consent TINYINT(1) NOT NULL DEFAULT 0,

        confirmed_date DATE NULL,
        confirmed_time TIME NULL,

        status VARCHAR(30) NOT NULL DEFAULT 'new_request',
        staff_notes TEXT NULL,

        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,

        PRIMARY KEY (id),
        UNIQUE KEY request_reference (request_reference),
        KEY doctor_id (doctor_id),
        KEY preferred_date (preferred_date),
        KEY confirmed_date (confirmed_date),
        KEY status (status),
        KEY mobile (mobile)
    ) {$charset_collate};";

    dbDelta( $sql );

    update_option(
        'ph_appointments_db_version',
        PH_APPOINTMENTS_VERSION
    );
}

/**
 * ============================================================
 * ACTIVATION
 * ============================================================
 */

function ph_appointments_ensure_patient_id_column() {

    global $wpdb;

    $table_name = ph_appointments_table();

    $column = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} LIKE %s",
            'patient_id'
        )
    );

    if ( ! $column ) {
        $wpdb->query(
            "ALTER TABLE {$table_name}
             ADD COLUMN patient_id VARCHAR(30) NULL
             AFTER mobile"
        );
    }
}

function ph_appointments_activate() {

    ph_appointments_create_table();
    ph_appointments_ensure_patient_id_column();
    ph_appointments_add_capabilities();

    flush_rewrite_rules();
}

register_activation_hook(
    __FILE__,
    'ph_appointments_activate'
);

/**
 * ============================================================
 * UPGRADE CHECK
 * ============================================================
 */

function ph_appointments_maybe_upgrade() {

    $installed_version = get_option(
        'ph_appointments_db_version',
        '0'
    );

    if ( version_compare(
        $installed_version,
        PH_APPOINTMENTS_VERSION,
        '<'
    ) ) {

        ph_appointments_create_table();
        ph_appointments_ensure_patient_id_column();
        ph_appointments_add_capabilities();
    } else {
        /*
         * Make sure the capability exists even if the plugin
         * was installed before the capability was introduced.
         */
        ph_appointments_add_capabilities();
    }
}

add_action(
    'plugins_loaded',
    'ph_appointments_maybe_upgrade'
);

/**
 * ============================================================
 * REQUEST REFERENCE
 * ============================================================
 */

function ph_generate_request_reference() {

    global $wpdb;

    $table_name = ph_appointments_table();

    do {

        $reference =
            'PH-APT-' .
            strtoupper(
                wp_generate_password(
                    8,
                    false,
                    false
                )
            );

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                 FROM {$table_name}
                 WHERE request_reference = %s
                 LIMIT 1",
                $reference
            )
        );

    } while ( $exists );

    return $reference;
}

/**
 * ============================================================
 * ADMIN MENU
 * ============================================================
 */

function ph_appointments_admin_menu() {
    add_submenu_page(
        'prasanthi-hospitals',
        'Appointments',
        'Appointments',
        'ph_manage_appointments',
        'ph-appointments',
        'ph_appointments_admin_page'
    );
}

add_action(
    'admin_menu',
    'ph_appointments_admin_menu'
);

/**
 * ============================================================
 * ADMIN STYLES
 * ============================================================
 */

function ph_appointments_admin_styles( $hook ) {

    if ( 'prasanthi-hospitals_page_ph-appointments' !== $hook && 'toplevel_page_ph-appointments' !== $hook ) {
        return;
    }

    ?>
    <style>
        .ph-appointment-wrap {
            max-width: 1400px;
        }

        .ph-dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .ph-dashboard-card {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 18px;
            text-decoration: none;
            color: #1d2327;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }

        .ph-dashboard-card:hover {
            border-color: #2271b1;
        }

        .ph-dashboard-card-number {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .ph-dashboard-card-label {
            color: #646970;
        }

        .ph-dashboard-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 20px 0;
        }

        .ph-status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .ph-status-new_request {
            background: #fff3cd;
            color: #856404;
        }

        .ph-status-confirmed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .ph-status-rescheduled {
            background: #cff4fc;
            color: #055160;
        }

        .ph-status-cancelled,
        .ph-status-no_show {
            background: #f8d7da;
            color: #842029;
        }

        .ph-status-arrived {
            background: #e2e3e5;
            color: #41464b;
        }

        .ph-status-in_consultation {
            background: #cfe2ff;
            color: #084298;
        }

        .ph-status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .ph-detail-box {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 24px;
            margin-top: 20px;
        }

        .ph-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(250px, 1fr));
            gap: 20px;
        }

        .ph-detail-item label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #646970;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .ph-detail-item .value {
            font-size: 14px;
        }

        .ph-full-width {
            grid-column: 1 / -1;
        }

        .ph-confirmation-fields {
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            padding: 18px;
            margin-top: 20px;
        }

        .ph-confirmation-fields h3 {
            margin-top: 0;
        }

        .ph-form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 16px;
        }

        .ph-form-field {
            flex: 1;
        }

        .ph-form-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .ph-form-field input,
        .ph-form-field select,
        .ph-form-field textarea {
            width: 100%;
            max-width: 100%;
        }

        .ph-back-link {
            margin-bottom: 15px;
        }

        .ph-patient-link-box {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 24px;
            margin-top: 20px;
        }

        .ph-patient-linked {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 15px;
        }

        .ph-patient-search-results {
            margin-top: 15px;
        }

        .ph-patient-search-results table {
            margin-top: 10px;
        }

        .ph-patient-search-results td {
            vertical-align: middle;
        }

        @media (max-width: 900px) {
            .ph-dashboard-cards {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }

            .ph-detail-grid {
                grid-template-columns: 1fr;
            }

            .ph-form-row {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 600px) {
            .ph-dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
}

add_action(
    'admin_enqueue_scripts',
    'ph_appointments_admin_styles'
);

/**
 * ============================================================
 * ADMIN PAGE ROUTER
 * ============================================================
 */

function ph_appointments_admin_page() {

    if ( ! current_user_can( 'ph_manage_appointments' ) ) {
        wp_die(
            'You do not have permission to manage appointments.'
        );
    }

    if ( isset( $_GET['appointment_id'] ) ) {

        $appointment_id = absint(
            $_GET['appointment_id']
        );

        if ( $appointment_id ) {
            ph_appointments_detail_page(
                $appointment_id
            );

            return;
        }
    }

    ph_appointments_list_page();
}

/**
 * ============================================================
 * DASHBOARD / LIST
 * ============================================================
 */

function ph_appointments_list_page() {

    global $wpdb;

    $table_name = ph_appointments_table();

    $statuses = ph_appointments_statuses();

    $selected_status =
        isset( $_GET['status'] )
        ? sanitize_key( wp_unslash( $_GET['status'] ) )
        : '';

    /*
     * Summary counts
     */
    $counts = array();

    foreach ( $statuses as $status => $label ) {

        $counts[ $status ] = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$table_name}
                 WHERE status = %s",
                $status
            )
        );
    }

    /*
     * Appointments
     */
    if ( $selected_status &&
        isset( $statuses[ $selected_status ] ) ) {

        $appointments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                 FROM {$table_name}
                 WHERE status = %s
                 ORDER BY created_at DESC
                 LIMIT 100",
                $selected_status
            )
        );

    } else {

        $appointments = $wpdb->get_results(
            "SELECT *
             FROM {$table_name}
             ORDER BY created_at DESC
             LIMIT 100"
        );
    }

    ?>
    <div class="wrap ph-appointment-wrap">

        <h1>Prasanthi Hospitals — Appointments</h1>

        <div class="ph-dashboard-cards">

            <?php foreach ( $statuses as $status => $label ) : ?>

                <a
                    class="ph-dashboard-card"
                    href="<?php
                        echo esc_url(
                            admin_url(
                                'admin.php?page=ph-appointments&status=' .
                                $status
                            )
                        );
                    ?>"
                >

                    <div class="ph-dashboard-card-number">
                        <?php
                        echo esc_html(
                            $counts[ $status ]
                        );
                        ?>
                    </div>

                    <div class="ph-dashboard-card-label">
                        <?php echo esc_html( $label ); ?>
                    </div>

                </a>

            <?php endforeach; ?>

        </div>

        <div class="ph-dashboard-filter">

            <strong>Filter:</strong>

            <a
                class="button <?php echo empty( $selected_status ) ? 'button-primary' : ''; ?>"
                href="<?php
                    echo esc_url(
                        admin_url(
                            'admin.php?page=ph-appointments'
                        )
                    );
                ?>"
            >
                All
            </a>

            <?php foreach ( $statuses as $status => $label ) : ?>

                <a
                    class="button <?php echo $selected_status === $status ? 'button-primary' : ''; ?>"
                    href="<?php
                        echo esc_url(
                            admin_url(
                                'admin.php?page=ph-appointments&status=' .
                                $status
                            )
                        );
                    ?>"
                >
                    <?php echo esc_html( $label ); ?>
                </a>

            <?php endforeach; ?>

        </div>

        <div class="ph-dashboard-table">

            <table class="wp-list-table widefat fixed striped">

                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Patient</th>
                        <th>Mobile</th>
                        <th>Doctor</th>
                        <th>Preferred Date</th>
                        <th>Session</th>
                        <th>Confirmed Date</th>
                        <th>Confirmed Time</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ( empty( $appointments ) ) : ?>

                    <tr>
                        <td colspan="10">
                            No appointments found.
                        </td>
                    </tr>

                <?php else : ?>

                    <?php foreach ( $appointments as $appointment ) : ?>

                        <?php
                        $doctor_name = 'Any Doctor / Front Desk';

                        if ( ! empty( $appointment->doctor_id ) ) {

                            $doctor = get_post(
                                (int) $appointment->doctor_id
                            );

                            if ( $doctor ) {
                                $doctor_name =
                                    get_the_title( $doctor );
                            }
                        }

                        $detail_url = add_query_arg(
                            array(
                                'page'           => 'ph-appointments',
                                'appointment_id' => (int) $appointment->id,
                            ),
                            admin_url( 'admin.php' )
                        );
                        ?>

                        <tr>

                            <td>
                                <strong>
                                    <a href="<?php echo esc_url( $detail_url ); ?>">
                                        <?php
                                        echo esc_html(
                                            $appointment->request_reference
                                        );
                                        ?>
                                    </a>
                                </strong>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                    echo esc_html(
                                        $appointment->patient_name
                                    );
                                    ?>
                                </strong>

                                <br>

                                <?php if ( ! empty( $appointment->patient_id ) ) : ?>

                                    <span style="color:#2271b1;font-size:12px;">
                                        Linked: <?php echo esc_html( $appointment->patient_id ); ?>
                                    </span>

                                <?php else : ?>

                                    <span style="color:#8c8f94;font-size:12px;">
                                        Not linked to patient record
                                    </span>

                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                echo esc_html(
                                    $appointment->mobile
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo esc_html(
                                    $doctor_name
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo ! empty(
                                    $appointment->preferred_date
                                )
                                    ? esc_html(
                                        wp_date(
                                            'd M Y',
                                            strtotime(
                                                $appointment->preferred_date
                                            )
                                        )
                                    )
                                    : '—';
                                ?>
                            </td>

                            <td>
                                <?php
                                echo esc_html(
                                    ucfirst(
                                        $appointment->preferred_session
                                    )
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo ! empty(
                                    $appointment->confirmed_date
                                )
                                    ? esc_html(
                                        wp_date(
                                            'd M Y',
                                            strtotime(
                                                $appointment->confirmed_date
                                            )
                                        )
                                    )
                                    : '—';
                                ?>
                            </td>

                            <td>
                                <?php
                                echo ! empty(
                                    $appointment->confirmed_time
                                )
                                    ? esc_html(
                                        wp_date(
                                            'g:i A',
                                            strtotime(
                                                $appointment->confirmed_time
                                            )
                                        )
                                    )
                                    : '—';
                                ?>
                            </td>

                            <td>

                                <span
                                    class="ph-status ph-status-<?php echo esc_attr( $appointment->status ); ?>"
                                >
                                    <?php
                                    echo esc_html(
                                        ph_appointments_status_label(
                                            $appointment->status
                                        )
                                    );
                                    ?>
                                </span>

                            </td>

                            <td>
                                <?php
                                echo esc_html(
                                    wp_date(
                                        'd M Y g:i A',
                                        strtotime(
                                            $appointment->created_at
                                        )
                                    )
                                );
                                ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>
    <?php
}

/**
 * ============================================================
 * APPOINTMENT DETAIL PAGE
 * ============================================================
 */

function ph_appointments_detail_page( $appointment_id ) {

    global $wpdb;

    $table_name = ph_appointments_table();

    $appointment = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
             FROM {$table_name}
             WHERE id = %d
             LIMIT 1",
            $appointment_id
        )
    );

    if ( ! $appointment ) {

        echo '<div class="wrap">';
        echo '<h1>Appointment Not Found</h1>';
        echo '<p>The appointment could not be found.</p>';
        echo '</div>';

        return;
    }

    /*
     * --------------------------------------------------------
     * LINK PATIENT
     * --------------------------------------------------------
     */

    if (
        isset( $_POST['ph_link_patient'] )
        &&
        check_admin_referer(
            'ph_link_patient_' . $appointment_id
        )
    ) {

        $patient_reference = isset( $_POST['patient_reference'] )
            ? sanitize_text_field(
                wp_unslash( $_POST['patient_reference'] )
            )
            : '';

        if ( empty( $patient_reference ) ) {

            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>Please select a patient before linking.</p>';
            echo '</div>';

        } else {

            $patient_table = $wpdb->prefix . 'ph_patients';

            $patient = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, patient_reference, patient_name, mobile
                     FROM {$patient_table}
                     WHERE patient_reference = %s
                     LIMIT 1",
                    $patient_reference
                )
            );

            if ( ! $patient ) {

                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>The selected patient could not be found.</p>';
                echo '</div>';

            } else {

                $updated = $wpdb->update(
                    $table_name,
                    array(
                        'patient_id' => $patient->patient_reference,
                        'updated_at' => current_time( 'mysql' ),
                    ),
                    array(
                        'id' => $appointment_id,
                    ),
                    array(
                        '%s',
                        '%s',
                    ),
                    array(
                        '%d',
                    )
                );

                if ( false !== $updated ) {

                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>Patient linked to the appointment successfully.</p>';
                    echo '</div>';

                    $appointment = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT *
                             FROM {$table_name}
                             WHERE id = %d
                             LIMIT 1",
                            $appointment_id
                        )
                    );

                } else {

                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>The patient could not be linked. Please try again.</p>';
                    echo '</div>';
                }
            }
        }
    }

    /*
     * --------------------------------------------------------
     * UNLINK PATIENT
     * --------------------------------------------------------
     */

    if (
        isset( $_POST['ph_unlink_patient'] )
        &&
        check_admin_referer(
            'ph_unlink_patient_' . $appointment_id
        )
    ) {

        $updated = $wpdb->update(
            $table_name,
            array(
                'patient_id' => null,
                'updated_at' => current_time( 'mysql' ),
            ),
            array(
                'id' => $appointment_id,
            ),
            array(
                '%s',
                '%s',
            ),
            array(
                '%d',
            )
        );

        if ( false !== $updated ) {

            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>Patient unlinked from the appointment.</p>';
            echo '</div>';

            $appointment = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT *
                     FROM {$table_name}
                     WHERE id = %d
                     LIMIT 1",
                    $appointment_id
                )
            );
        }
    }

    /*
     * --------------------------------------------------------
     * PATIENT SEARCH
     * --------------------------------------------------------
     */

    $patient_search = isset( $_GET['patient_search'] )
        ? sanitize_text_field(
            wp_unslash( $_GET['patient_search'] )
        )
        : '';

    $patient_results = array();

    if ( $patient_search ) {

        $patient_table = $wpdb->prefix . 'ph_patients';

        $like = '%' . $wpdb->esc_like( $patient_search ) . '%';

        $patient_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, patient_reference, patient_name, mobile
                 FROM {$patient_table}
                 WHERE patient_reference LIKE %s
                    OR patient_name LIKE %s
                    OR mobile LIKE %s
                 ORDER BY patient_name ASC
                 LIMIT 20",
                $like,
                $like,
                $like
            )
        );
    }

    /*
     * --------------------------------------------------------
     * SAVE
     * --------------------------------------------------------
     */

    if (
        isset( $_POST['ph_save_appointment'] )
        &&
        check_admin_referer(
            'ph_save_appointment_' . $appointment_id
        )
    ) {

        $status = isset( $_POST['status'] )
            ? sanitize_key(
                wp_unslash(
                    $_POST['status']
                )
            )
            : 'new_request';

        $valid_statuses =
            ph_appointments_statuses();

        if ( ! isset( $valid_statuses[ $status ] ) ) {
            $status = $appointment->status;
        }

        $confirmed_date = isset(
            $_POST['confirmed_date']
        )
            ? sanitize_text_field(
                wp_unslash(
                    $_POST['confirmed_date']
                )
            )
            : '';

        $confirmed_time = isset(
            $_POST['confirmed_time']
        )
            ? sanitize_text_field(
                wp_unslash(
                    $_POST['confirmed_time']
                )
            )
            : '';

        $staff_notes = isset(
            $_POST['staff_notes']
        )
            ? sanitize_textarea_field(
                wp_unslash(
                    $_POST['staff_notes']
                )
            )
            : '';

        /*
         * Validate date.
         */
        if ( $confirmed_date ) {

            $date_object = DateTime::createFromFormat(
                'Y-m-d',
                $confirmed_date
            );

            if (
                ! $date_object
                ||
                $date_object->format( 'Y-m-d' ) !==
                $confirmed_date
            ) {
                $confirmed_date = '';
            }
        }

        /*
         * Validate time.
         */
        if ( $confirmed_time ) {

            $time_object = DateTime::createFromFormat(
                'H:i',
                $confirmed_time
            );

            if (
                ! $time_object
                ||
                $time_object->format( 'H:i' ) !==
                $confirmed_time
            ) {
                $confirmed_time = '';
            }
        }

        /*
         * A confirmed/active appointment must belong to a
         * registered hospital patient.
         *
         * New requests may remain unlinked because they can
         * originate from the public website. The front desk
         * must register/link the patient before the request
         * becomes a booked appointment.
         */
        if (
            in_array(
                $status,
                array(
                    'confirmed',
                    'arrived',
                    'in_consultation',
                    'completed',
                ),
                true
            )
            &&
            empty( $appointment->patient_id )
        ) {

            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>';
            echo '<strong>Please register and link a patient before booking this appointment.</strong>';
            echo '</p>';
            echo '</div>';

        } elseif (
            'confirmed' === $status
            &&
            (
                empty( $confirmed_date )
                ||
                empty( $confirmed_time )
            )
        ) {

            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>';
            echo '<strong>Confirmed appointments require a confirmed date and time.</strong>';
            echo '</p>';
            echo '</div>';

        } else {

            $wpdb->update(
                $table_name,
                array(
                    'status'         => $status,
                    'confirmed_date' => $confirmed_date
                        ? $confirmed_date
                        : null,
                    'confirmed_time' => $confirmed_time
                        ? $confirmed_time
                        : null,
                    'staff_notes'    => $staff_notes,
                    'updated_at'     => current_time( 'mysql' ),
                ),
                array(
                    'id' => $appointment_id,
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ),
                array(
                    '%d',
                )
            );

            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>Appointment updated successfully.</p>';
            echo '</div>';

            /*
             * Reload the appointment after save.
             */
            $appointment = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT *
                     FROM {$table_name}
                     WHERE id = %d
                     LIMIT 1",
                    $appointment_id
                )
            );
        }
    }

    /*
     * Doctor
     */
    $doctor_name = 'Any Doctor / Front Desk';

    if ( ! empty( $appointment->doctor_id ) ) {

        $doctor = get_post(
            (int) $appointment->doctor_id
        );

        if ( $doctor ) {
            $doctor_name = get_the_title( $doctor );
        }
    }

    $back_url = add_query_arg(
        array(
            'page' => 'ph-appointments',
        ),
        admin_url( 'admin.php' )
    );

    ?>

    <div class="wrap ph-appointment-wrap">

        <div class="ph-back-link">

            <a href="<?php echo esc_url( $back_url ); ?>">
                &larr; Back to Appointments
            </a>

        </div>

        <h1>
            Appointment
            <?php
            echo esc_html(
                $appointment->request_reference
            );
            ?>
        </h1>

        <?php
        /*
         * Appointment-to-Visit workflow.
         * A visit can only be started from an eligible appointment that
         * already has a registered patient linked to it.
         */
        $ph_visit_table = $wpdb->prefix . 'ph_visits';
        $ph_visit_table_exists = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $ph_visit_table )
        ) === $ph_visit_table;
        $ph_existing_visit_id = 0;

        if ( $ph_visit_table_exists ) {
            $ph_existing_visit_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$ph_visit_table} WHERE appointment_id = %d LIMIT 1",
                    $appointment_id
                )
            );
        }

        $ph_visit_eligible_statuses = array( 'confirmed', 'arrived', 'in_consultation' );
        ?>
        <div class="ph-appointment-visit-action" style="margin: 16px 0;">
            <?php if ( $ph_existing_visit_id ) : ?>
                <a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . $ph_existing_visit_id ) ); ?>">View Existing Visit</a>
                <span class="description">A visit has already been created for this appointment.</span>
            <?php elseif ( empty( $appointment->patient_id ) ) : ?>
                <p class="description">Register and link a patient before creating a visit from this appointment.</p>
            <?php elseif ( ! in_array( $appointment->status, $ph_visit_eligible_statuses, true ) ) : ?>
                <p class="description">A visit can be created when this appointment is Confirmed, Arrived, or In Consultation.</p>
            <?php elseif ( ! $ph_visit_table_exists ) : ?>
                <p class="description">The Visits module is not active. Activate Prasanthi Hospitals Visits to create a visit.</p>
            <?php else : ?>
                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits-add&appointment_id=' . (int) $appointment_id ) ); ?>">Create Visit</a>
                <span class="description">Patient and doctor details will be prefilled from this appointment.</span>
            <?php endif; ?>
        </div>

        <div class="ph-detail-box">

            <h2>Appointment Request</h2>

            <div class="ph-detail-grid">

                <div class="ph-detail-item">

                    <label>Request Reference</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            $appointment->request_reference
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Status</label>

                    <div class="value">

                        <span
                            class="ph-status ph-status-<?php echo esc_attr( $appointment->status ); ?>"
                        >
                            <?php
                            echo esc_html(
                                ph_appointments_status_label(
                                    $appointment->status
                                )
                            );
                            ?>
                        </span>

                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Patient Name</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            $appointment->patient_name
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Mobile</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            $appointment->mobile
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Doctor Preference</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            $doctor_name
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Patient Type</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            ucfirst(
                                $appointment->patient_type
                            )
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Preferred Date</label>

                    <div class="value">
                        <?php
                        echo ! empty(
                            $appointment->preferred_date
                        )
                            ? esc_html(
                                wp_date(
                                    'd M Y',
                                    strtotime(
                                        $appointment->preferred_date
                                    )
                                )
                            )
                            : 'Not specified';
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Preferred Session</label>

                    <div class="value">
                        <?php
                        echo ! empty(
                            $appointment->preferred_session
                        )
                            ? esc_html(
                                ucfirst(
                                    $appointment->preferred_session
                                )
                            )
                            : 'Not specified';
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Preferred Language</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            $appointment->preferred_language
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Consent to Contact</label>

                    <div class="value">

                        <?php
                        echo $appointment->consent
                            ? 'Yes'
                            : 'No';
                        ?>

                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Request Created</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            wp_date(
                                'd M Y g:i A',
                                strtotime(
                                    $appointment->created_at
                                )
                            )
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item">

                    <label>Last Updated</label>

                    <div class="value">
                        <?php
                        echo esc_html(
                            wp_date(
                                'd M Y g:i A',
                                strtotime(
                                    $appointment->updated_at
                                )
                            )
                        );
                        ?>
                    </div>

                </div>

                <div class="ph-detail-item ph-full-width">

                    <label>Reason for Visit</label>

                    <div class="value">

                        <?php
                        echo ! empty(
                            $appointment->reason
                        )
                            ? nl2br(
                                esc_html(
                                    $appointment->reason
                                )
                            )
                            : 'Not provided';
                        ?>

                    </div>

                </div>

            </div>

        </div>

        <div class="ph-patient-link-box">

            <h2>Patient Record</h2>

            <?php
            $linked_patient = null;

            if ( ! empty( $appointment->patient_id ) ) {

                $patient_table = $wpdb->prefix . 'ph_patients';

                $linked_patient = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT id, patient_reference, patient_name, mobile
                         FROM {$patient_table}
                         WHERE patient_reference = %s
                         LIMIT 1",
                        $appointment->patient_id
                    )
                );
            }
            ?>

            <?php if ( $linked_patient ) : ?>

                <div class="ph-patient-linked">

                    <p>
                        <strong>Patient ID:</strong>
                        <?php echo esc_html( $linked_patient->patient_reference ); ?>
                    </p>

                    <p>
                        <strong>Name:</strong>
                        <?php echo esc_html( $linked_patient->patient_name ); ?>
                    </p>

                    <p>
                        <strong>Mobile:</strong>
                        <?php echo esc_html( $linked_patient->mobile ); ?>
                    </p>

                    <form method="post">
                        <?php
                        wp_nonce_field(
                            'ph_unlink_patient_' . $appointment_id
                        );
                        ?>
                        <button
                            type="submit"
                            name="ph_unlink_patient"
                            class="button"
                        >
                            Unlink Patient
                        </button>
                    </form>

                </div>

            <?php else : ?>

                <p>
                    This appointment is not linked to a registered patient.
                    Search by Patient ID, name or mobile number.
                </p>

                <p>
                    <a
                        href="<?php
                        echo esc_url(
                            add_query_arg(
                                array(
                                    'page'   => 'ph-patients',
                                    'action' => 'add',
                                ),
                                admin_url( 'admin.php' )
                            )
                        );
                        ?>"
                        class="button button-primary"
                    >
                        Register New Patient
                    </a>
                </p>

                <form method="get">

                    <input
                        type="hidden"
                        name="page"
                        value="ph-appointments"
                    >

                    <input
                        type="hidden"
                        name="appointment_id"
                        value="<?php echo esc_attr( $appointment_id ); ?>"
                    >

                    <p>
                        <input
                            type="search"
                            name="patient_search"
                            value="<?php echo esc_attr( $patient_search ); ?>"
                            placeholder="Patient ID, name or mobile"
                            class="regular-text"
                        >

                        <button
                            type="submit"
                            class="button"
                        >
                            Search Patient
                        </button>
                    </p>

                </form>

                <?php if ( $patient_search ) : ?>

                    <div class="ph-patient-search-results">

                        <?php if ( empty( $patient_results ) ) : ?>

                            <p><strong>No patients found.</strong></p>

                        <?php else : ?>

                            <table class="wp-list-table widefat fixed striped">

                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Patient Name</th>
                                        <th>Mobile</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ( $patient_results as $patient_result ) : ?>

                                        <tr>

                                            <td>
                                                <?php
                                                echo esc_html(
                                                    $patient_result->patient_reference
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo esc_html(
                                                    $patient_result->patient_name
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo esc_html(
                                                    $patient_result->mobile
                                                );
                                                ?>
                                            </td>

                                            <td>

                                                <form method="post">

                                                    <?php
                                                    wp_nonce_field(
                                                        'ph_link_patient_' . $appointment_id
                                                    );
                                                    ?>

                                                    <input
                                                        type="hidden"
                                                        name="patient_reference"
                                                        value="<?php
                                                        echo esc_attr(
                                                            $patient_result->patient_reference
                                                        );
                                                        ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="ph_link_patient"
                                                        class="button button-primary"
                                                    >
                                                        Link Patient
                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </div>

        <form
            method="post"
            class="ph-detail-box"
        >

            <?php
            wp_nonce_field(
                'ph_save_appointment_' . $appointment_id
            );
            ?>

            <h2>Front Desk Action</h2>

            <div class="ph-form-row">

                <div class="ph-form-field">

                    <label for="ph_status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="ph_status"
                    >

                        <?php
                        foreach (
                            ph_appointments_statuses()
                            as $status => $label
                        ) :
                        ?>

                            <option
                                value="<?php echo esc_attr( $status ); ?>"
                                <?php
                                selected(
                                    $appointment->status,
                                    $status
                                );
                                ?>
                            >
                                <?php echo esc_html( $label ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <div class="ph-confirmation-fields">

                <h3>Confirmed Appointment Details</h3>

                <p>
                    Enter these values only when the front desk has
                    actually confirmed the appointment.
                </p>

                <div class="ph-form-row">

                    <div class="ph-form-field">

                        <label for="ph_confirmed_date">
                            Confirmed Date
                        </label>

                        <input
                            type="date"
                            name="confirmed_date"
                            id="ph_confirmed_date"
                            value="<?php
                            echo esc_attr(
                                $appointment->confirmed_date
                            );
                            ?>"
                        >

                    </div>

                    <div class="ph-form-field">

                        <label for="ph_confirmed_time">
                            Confirmed Time
                        </label>

                        <input
                            type="time"
                            name="confirmed_time"
                            id="ph_confirmed_time"
                            value="<?php
                            echo esc_attr(
                                $appointment->confirmed_time
                            );
                            ?>"
                        >

                    </div>

                </div>

            </div>

            <div class="ph-form-row">

                <div class="ph-form-field">

                    <label for="ph_staff_notes">
                        Staff Notes
                    </label>

                    <textarea
                        name="staff_notes"
                        id="ph_staff_notes"
                        rows="5"
                    ><?php
                    echo esc_textarea(
                        $appointment->staff_notes
                    );
                    ?></textarea>

                </div>

            </div>

            <p>

                <button
                    type="submit"
                    name="ph_save_appointment"
                    class="button button-primary button-large"
                >
                    Save Appointment
                </button>

            </p>

        </form>

    </div>

    <?php
}

/**
 * ============================================================
 * PUBLIC APPOINTMENT FORM
 * ============================================================
 */

function ph_appointment_form_shortcode() {

    ob_start();

    $success = false;
    $errors  = array();

    /*
     * --------------------------------------------------------
     * FORM SUBMISSION
     * --------------------------------------------------------
     */

    if (
        isset(
            $_POST['ph_appointment_submit']
        )
    ) {

        if (
            ! isset(
                $_POST['ph_appointment_nonce']
            )
            ||
            ! wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash(
                        $_POST['ph_appointment_nonce']
                    )
                ),
                'ph_submit_appointment'
            )
        ) {

            $errors[] =
                'Security verification failed. Please try again.';

        } else {

            $patient_name =
                isset( $_POST['patient_name'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['patient_name']
                    )
                )
                : '';

            $mobile =
                isset( $_POST['mobile'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['mobile']
                    )
                )
                : '';

            $doctor_id =
                isset( $_POST['doctor_id'] )
                ? absint(
                    $_POST['doctor_id']
                )
                : 0;

            $preferred_date =
                isset( $_POST['preferred_date'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['preferred_date']
                    )
                )
                : '';

            $preferred_session =
                isset( $_POST['preferred_session'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['preferred_session']
                    )
                )
                : '';

            $patient_type =
                isset( $_POST['patient_type'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['patient_type']
                    )
                )
                : '';

            $preferred_language =
                isset( $_POST['preferred_language'] )
                ? sanitize_text_field(
                    wp_unslash(
                        $_POST['preferred_language']
                    )
                )
                : '';

            $reason =
                isset( $_POST['reason'] )
                ? sanitize_textarea_field(
                    wp_unslash(
                        $_POST['reason']
                    )
                )
                : '';

            $consent =
                isset( $_POST['consent'] )
                ? 1
                : 0;

            /*
             * Validation
             */

            if ( empty( $patient_name ) ) {
                $errors[] =
                    'Please enter the patient name.';
            }

            if ( empty( $mobile ) ) {
                $errors[] =
                    'Please enter the mobile number.';
            }

            if ( ! preg_match(
                '/^[0-9+\-\s()]{7,20}$/',
                $mobile
            ) ) {

                $errors[] =
                    'Please enter a valid mobile number.';
            }

            if (
                ! empty( $doctor_id )
                &&
                (
                    'doctor' !== get_post_type(
                        $doctor_id
                    )
                    ||
                    'publish' !== get_post_status(
                        $doctor_id
                    )
                )
            ) {

                $errors[] =
                    'The selected doctor is not available.';
            }

            if ( ! empty( $preferred_date ) ) {

                $date_object =
                    DateTime::createFromFormat(
                        'Y-m-d',
                        $preferred_date
                    );

                if (
                    ! $date_object
                    ||
                    $date_object->format(
                        'Y-m-d'
                    ) !== $preferred_date
                ) {

                    $errors[] =
                        'Please select a valid preferred date.';
                }
            }

            if (
                ! empty( $preferred_session )
                &&
                ! in_array(
                    $preferred_session,
                    array(
                        'morning',
                        'evening',
                    ),
                    true
                )
            ) {

                $errors[] =
                    'Please select a valid session.';
            }

            if (
                ! empty( $patient_type )
                &&
                ! in_array(
                    $patient_type,
                    array(
                        'new',
                        'existing',
                    ),
                    true
                )
            ) {

                $errors[] =
                    'Please select a valid patient type.';
            }

            if (
                ! empty( $preferred_language )
                &&
                ! in_array(
                    $preferred_language,
                    array(
                        'English',
                        'Telugu',
                    ),
                    true
                )
            ) {

                $errors[] =
                    'Please select a valid language.';
            }

            if ( ! $consent ) {

                $errors[] =
                    'Please provide consent for the hospital to contact you regarding the appointment request.';
            }

            /*
             * Insert
             */

            if ( empty( $errors ) ) {

                global $wpdb;

                $table_name =
                    ph_appointments_table();

                $reference =
                    ph_generate_request_reference();

                $now =
                    current_time( 'mysql' );

                $inserted =
                    $wpdb->insert(
                        $table_name,
                        array(
                            'request_reference' =>
                                $reference,

                            'patient_name' =>
                                $patient_name,

                            'mobile' =>
                                $mobile,

                            'doctor_id' =>
                                $doctor_id
                                ? $doctor_id
                                : null,

                            'preferred_date' =>
                                $preferred_date
                                ? $preferred_date
                                : null,

                            'preferred_session' =>
                                $preferred_session
                                ? $preferred_session
                                : null,

                            'patient_type' =>
                                $patient_type
                                ? $patient_type
                                : null,

                            'preferred_language' =>
                                $preferred_language
                                ? $preferred_language
                                : null,

                            'reason' =>
                                $reason,

                            'consent' =>
                                $consent,

                            'status' =>
                                'new_request',

                            'staff_notes' =>
                                '',

                            'created_at' =>
                                $now,

                            'updated_at' =>
                                $now,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                        )
                    );

                if ( false !== $inserted ) {

                    $success = true;

                } else {

                    $errors[] =
                        'We could not submit your appointment request. Please try again or contact the hospital.';
                }
            }
        }
    }

    /*
     * --------------------------------------------------------
     * SUCCESS
     * --------------------------------------------------------
     */

    if ( $success ) {

        ?>

        <div class="ph-appointment-success">

            <h2>Request Received</h2>

            <p>
                We have received your appointment request.
                Our front desk will contact you to confirm
                the doctor and available time.
            </p>

            <p>
                <strong>
                    Your appointment is not yet confirmed.
                </strong>
            </p>

            <p>
                Request Reference:
                <strong>
                    <?php
                    echo esc_html(
                        $reference
                    );
                    ?>
                </strong>
            </p>

        </div>

        <style>
            .ph-appointment-success {
                max-width: 700px;
                margin: 30px auto;
                padding: 30px;
                border-radius: 10px;
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
            }

            .ph-appointment-success h2 {
                margin-top: 0;
            }
        </style>

        <?php

        return ob_get_clean();
    }

    /*
     * --------------------------------------------------------
     * ERRORS
     * --------------------------------------------------------
     */

    if ( ! empty( $errors ) ) {

        ?>

        <div class="ph-appointment-errors">

            <?php foreach ( $errors as $error ) : ?>

                <p>
                    <?php
                    echo esc_html(
                        $error
                    );
                    ?>
                </p>

            <?php endforeach; ?>

        </div>

        <?php
    }

    /*
     * --------------------------------------------------------
     * DOCTORS
     * --------------------------------------------------------
     */

    $doctors = get_posts(
        array(
            'post_type'      => 'doctor',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );

    ?>

    <div class="ph-appointment-form-wrapper">

        <form
            method="post"
            class="ph-appointment-form"
        >

            <?php
            wp_nonce_field(
                'ph_submit_appointment',
                'ph_appointment_nonce'
            );
            ?>

            <div class="ph-form-group">

                <label for="ph_patient_name">
                    Patient Name
                    <span>*</span>
                </label>

                <input
                    type="text"
                    id="ph_patient_name"
                    name="patient_name"
                    value="<?php
                    echo isset(
                        $_POST['patient_name']
                    )
                        ? esc_attr(
                            wp_unslash(
                                $_POST['patient_name']
                            )
                        )
                        : '';
                    ?>"
                    required
                >

            </div>

            <div class="ph-form-group">

                <label for="ph_mobile">
                    Mobile Number
                    <span>*</span>
                </label>

                <input
                    type="tel"
                    id="ph_mobile"
                    name="mobile"
                    value="<?php
                    echo isset(
                        $_POST['mobile']
                    )
                        ? esc_attr(
                            wp_unslash(
                                $_POST['mobile']
                            )
                        )
                        : '';
                    ?>"
                    required
                >

            </div>

            <div class="ph-form-group">

                <label for="ph_doctor">
                    Preferred Doctor
                </label>

                <select
                    id="ph_doctor"
                    name="doctor_id"
                >

                    <option value="0">
                        Any Doctor / Let Front Desk Help
                    </option>

                    <?php foreach ( $doctors as $doctor ) : ?>

                        <option
                            value="<?php echo esc_attr( $doctor->ID ); ?>"
                            <?php
                            selected(
                                isset(
                                    $_POST['doctor_id']
                                )
                                    ? absint(
                                        $_POST['doctor_id']
                                    )
                                    : 0,
                                $doctor->ID
                            );
                            ?>
                        >
                            <?php
                            echo esc_html(
                                get_the_title(
                                    $doctor
                                )
                            );
                            ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="ph-form-row">

                <div class="ph-form-group">

                    <label for="ph_preferred_date">
                        Preferred Date
                    </label>

                    <input
                        type="date"
                        id="ph_preferred_date"
                        name="preferred_date"
                        min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"
                        value="<?php
                        echo isset(
                            $_POST['preferred_date']
                        )
                            ? esc_attr(
                                wp_unslash(
                                    $_POST['preferred_date']
                                )
                            )
                            : '';
                        ?>"
                    >

                </div>

                <div class="ph-form-group">

                    <label for="ph_preferred_session">
                        Preferred Session
                    </label>

                    <select
                        id="ph_preferred_session"
                        name="preferred_session"
                    >

                        <option value="">
                            Select Session
                        </option>

                        <option
                            value="morning"
                            <?php
                            selected(
                                isset(
                                    $_POST['preferred_session']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['preferred_session']
                                        )
                                    )
                                    : '',
                                'morning'
                            );
                            ?>
                        >
                            Morning
                        </option>

                        <option
                            value="evening"
                            <?php
                            selected(
                                isset(
                                    $_POST['preferred_session']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['preferred_session']
                                        )
                                    )
                                    : '',
                                'evening'
                            );
                            ?>
                        >
                            Evening
                        </option>

                    </select>

                </div>

            </div>

            <div class="ph-form-row">

                <div class="ph-form-group">

                    <label for="ph_patient_type">
                        Patient Type
                    </label>

                    <select
                        id="ph_patient_type"
                        name="patient_type"
                    >

                        <option value="">
                            Select
                        </option>

                        <option
                            value="new"
                            <?php
                            selected(
                                isset(
                                    $_POST['patient_type']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['patient_type']
                                        )
                                    )
                                    : '',
                                'new'
                            );
                            ?>
                        >
                            New Patient
                        </option>

                        <option
                            value="existing"
                            <?php
                            selected(
                                isset(
                                    $_POST['patient_type']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['patient_type']
                                        )
                                    )
                                    : '',
                                'existing'
                            );
                            ?>
                        >
                            Existing Patient
                        </option>

                    </select>

                </div>

                <div class="ph-form-group">

                    <label for="ph_preferred_language">
                        Preferred Language
                    </label>

                    <select
                        id="ph_preferred_language"
                        name="preferred_language"
                    >

                        <option value="">
                            Select
                        </option>

                        <option
                            value="English"
                            <?php
                            selected(
                                isset(
                                    $_POST['preferred_language']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['preferred_language']
                                        )
                                    )
                                    : '',
                                'English'
                            );
                            ?>
                        >
                            English
                        </option>

                        <option
                            value="Telugu"
                            <?php
                            selected(
                                isset(
                                    $_POST['preferred_language']
                                )
                                    ? sanitize_text_field(
                                        wp_unslash(
                                            $_POST['preferred_language']
                                        )
                                    )
                                    : '',
                                'Telugu'
                            );
                            ?>
                        >
                            Telugu
                        </option>

                    </select>

                </div>

            </div>

            <div class="ph-form-group">

                <label for="ph_reason">
                    Reason for Visit
                    <small>(Optional)</small>
                </label>

                <textarea
                    id="ph_reason"
                    name="reason"
                    rows="4"
                ><?php
                echo isset(
                    $_POST['reason']
                )
                    ? esc_textarea(
                        wp_unslash(
                            $_POST['reason']
                        )
                    )
                    : '';
                ?></textarea>

            </div>

            <div class="ph-form-group ph-consent">

                <label>

                    <input
                        type="checkbox"
                        name="consent"
                        value="1"
                        <?php
                        checked(
                            isset(
                                $_POST['consent']
                            ),
                            true
                        );
                        ?>
                        required
                    >

                    I consent to Prasanthi Hospitals
                    contacting me regarding this appointment request.

                </label>

            </div>

            <button
                type="submit"
                name="ph_appointment_submit"
                class="ph-appointment-submit"
            >
                Submit Appointment Request
            </button>

        </form>

    </div>

    <style>
        .ph-appointment-form-wrapper {
            max-width: 800px;
            margin: 0 auto;
        }

        .ph-appointment-form {
            display: block;
        }

        .ph-form-row {
            display: flex;
            gap: 20px;
        }

        .ph-form-row .ph-form-group {
            flex: 1;
        }

        .ph-form-group {
            margin-bottom: 20px;
        }

        .ph-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .ph-form-group label span {
            color: #c00;
        }

        .ph-form-group input,
        .ph-form-group select,
        .ph-form-group textarea {
            width: 100%;
            box-sizing: border-box;
        }

        .ph-consent label {
            font-weight: 400;
        }

        .ph-consent input {
            width: auto;
        }

        .ph-appointment-submit {
            border: 0;
            border-radius: 6px;
            padding: 12px 22px;
            cursor: pointer;
            font-weight: 600;
        }

        .ph-appointment-errors {
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 15px 20px;
            border-radius: 6px;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .ph-appointment-errors p {
            margin: 5px 0;
        }

        @media (max-width: 700px) {

            .ph-form-row {
                flex-direction: column;
                gap: 0;
            }

        }
    </style>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'ph_appointment_form',
    'ph_appointment_form_shortcode'
);
