<?php
/**
 * Plugin Name: Prasanthi Hospitals Visits
 * Description: Visit management for Prasanthi Hospitals.
 * Version: 1.0.5
 * Author: Prasanthi Hospitals
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PH_VISITS_VERSION', '1.0.5' );
define( 'PH_VISITS_DB_VERSION', '1.0.2' );
define( 'PH_VISITS_TABLE', 'ph_visits' );

function ph_visits_statuses() {
    return array(
        'scheduled'       => 'Scheduled',
        'arrived'         => 'Arrived',
        'in_consultation' => 'In Consultation',
        'completed'       => 'Completed',
        'cancelled'       => 'Cancelled',
        'no_show'         => 'No Show',
    );
}

function ph_visits_types() {
    return array(
        'appointment' => 'Appointment',
        'walk_in'     => 'Walk-in',
    );
}

function ph_visits_install() {
    global $wpdb;

    $table   = $wpdb->prefix . PH_VISITS_TABLE;
    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        visit_reference VARCHAR(30) NOT NULL,
        patient_id VARCHAR(30) NOT NULL,
        appointment_id BIGINT UNSIGNED NULL,
        doctor_id BIGINT UNSIGNED NULL,
        visit_date DATE NOT NULL,
        visit_time TIME NULL,
        visit_type VARCHAR(20) NOT NULL DEFAULT 'appointment',
        status VARCHAR(30) NOT NULL DEFAULT 'scheduled',
        visit_notes TEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY visit_reference (visit_reference),
        KEY patient_id (patient_id),
        KEY appointment_id (appointment_id),
        KEY doctor_id (doctor_id),
        KEY visit_date (visit_date),
        KEY status (status)
    ) {$charset};";

    dbDelta( $sql );
    update_option( 'ph_visits_db_version', PH_VISITS_DB_VERSION );
}

function ph_visits_add_capabilities() {
    foreach ( array( 'administrator', 'hospital_front_desk' ) as $role_name ) {
        $role = get_role( $role_name );

        if ( $role ) {
            $role->add_cap( 'ph_manage_visits' );
        }
    }
}

function ph_visits_activate() {
    ph_visits_install();
    ph_visits_add_capabilities();
}
register_activation_hook( __FILE__, 'ph_visits_activate' );

function ph_visits_upgrade() {
    if ( PH_VISITS_DB_VERSION !== get_option( 'ph_visits_db_version', '' ) ) {
        ph_visits_install();
        ph_visits_add_capabilities();
    }
}
add_action( 'plugins_loaded', 'ph_visits_upgrade' );

function ph_visits_ref() {
    global $wpdb;

    $table = $wpdb->prefix . PH_VISITS_TABLE;
    $last  = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$table}" );

    return 'PH-VIS-' . str_pad( (string) ( $last + 1 ), 6, '0', STR_PAD_LEFT );
}

function ph_visits_patient( $ref ) {
    global $wpdb;

    $table = $wpdb->prefix . 'ph_patients';

    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return null;
    }

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE patient_reference = %s LIMIT 1",
            $ref
        )
    );
}

function ph_visits_appointment( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return null;
    }

    $table = $wpdb->prefix . 'ph_appointments';

    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return null;
    }

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
            $id
        )
    );
}

function ph_visits_doctor( $id ) {
    if ( ! $id ) {
        return 'Not specified';
    }

    $post = get_post( (int) $id );

    return ( $post && 'doctor' === $post->post_type )
        ? get_the_title( $post )
        : 'Not specified';
}

function ph_visits_date( $value ) {
    return empty( $value ) || '0000-00-00' === $value
        ? '—'
        : wp_date( 'd M Y', strtotime( $value ) );
}

function ph_visits_time( $value ) {
    return empty( $value ) || '00:00:00' === $value
        ? '—'
        : wp_date( 'g:i A', strtotime( $value ) );
}

function ph_visits_cap() {
    return 'ph_manage_visits';
}

function ph_visits_menu() {
    add_submenu_page(
        'prasanthi-hospitals',
        'Visits',
        'Visits',
        ph_visits_cap(),
        'ph-visits',
        'ph_visits_admin_page'
    );

    add_submenu_page(
        'prasanthi-hospitals',
        'Add Visit',
        'Add Visit',
        ph_visits_cap(),
        'ph-visits-add',
        'ph_visits_add'
    );
}
add_action( 'admin_menu', 'ph_visits_menu' );

function ph_visits_css( $hook ) {
    if ( false === strpos( $hook, 'ph-visits' ) ) {
        return;
    }
    ?>
    <style>
        .phv .card { background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:20px; margin-top:20px; max-width:1100px; }
        .phv .row { margin-bottom:18px; }
        .phv label { display:block; font-weight:600; margin-bottom:6px; }
        .phv input[type=text], .phv input[type=date], .phv input[type=time], .phv input[type=number], .phv select, .phv textarea { width:100%; max-width:700px; }
        .phv textarea { min-height:120px; }
        .phv .help { color:#646970; font-size:13px; margin-top:5px; }
        .phv .box { background:#f6f7f7; border:1px solid #dcdcde; padding:12px 14px; border-radius:6px; max-width:700px; margin-top:8px; }
        .phv .notice-box { background:#fff8e5; border-left:4px solid #dba617; padding:12px 14px; max-width:700px; margin-top:8px; }
        .phv .tablewrap { overflow-x:auto; }
        .phv table { min-width:1050px; }
        .phv .status { display:inline-block; padding:3px 8px; border-radius:12px; background:#f0f0f1; font-size:12px; font-weight:600; }
        .phv .grid { display:grid; grid-template-columns:180px 1fr; gap:10px 20px; max-width:850px; }
        .phv .lbl { font-weight:600; }
        .phv .source-choice { display:inline-flex; align-items:center; gap:7px; margin-right:25px; font-weight:500; }
        .phv .source-choice input { margin:0; }
        @media (max-width:700px) { .phv .grid { grid-template-columns:1fr; gap:4px; } }
    </style>
    <?php
}
add_action( 'admin_enqueue_scripts', 'ph_visits_css' );

function ph_visits_list() {
    if ( ! current_user_can( ph_visits_cap() ) ) {
        wp_die( 'You do not have permission to manage visits.' );
    }

    global $wpdb;

    $table  = $wpdb->prefix . PH_VISITS_TABLE;
    $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';

    $where  = array( '1=1' );
    $params = array();

    if ( '' !== $search ) {
        $where[]  = '(visit_reference LIKE %s OR patient_id LIKE %s)';
        $like     = '%' . $wpdb->esc_like( $search ) . '%';
        $params[] = $like;
        $params[] = $like;
    }

    if ( isset( ph_visits_statuses()[ $status ] ) ) {
        $where[]  = 'status = %s';
        $params[] = $status;
    }

    $sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY visit_date DESC, id DESC LIMIT 200';

    if ( $params ) {
        $sql = $wpdb->prepare( $sql, $params );
    }

    $visits = $wpdb->get_results( $sql );
    ?>
    <div class="wrap phv">
        <h1 class="wp-heading-inline">Visits</h1>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits-add' ) ); ?>" class="page-title-action">Add Visit</a>

        <div class="card">
            <form method="get">
                <input type="hidden" name="page" value="ph-visits">
                <p><label>Search</label><input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Visit reference or Patient ID"></p>
                <p><label>Status</label><select name="status"><option value="">All statuses</option><?php foreach ( ph_visits_statuses() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></p>
                <p><button class="button button-secondary">Search</button> <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>">Reset</a></p>
            </form>
        </div>

        <div class="card">
            <div class="tablewrap">
                <table class="widefat striped">
                    <thead><tr><th>Visit</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ( ! $visits ) : ?>
                        <tr><td colspan="7">No visits found.</td></tr>
                    <?php else : ?>
                        <?php foreach ( $visits as $visit ) : $patient = ph_visits_patient( $visit->patient_id ); ?>
                            <tr>
                                <td><a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . (int) $visit->id ) ); ?>"><strong><?php echo esc_html( $visit->visit_reference ); ?></strong></a></td>
                                <td><?php echo esc_html( $patient ? $patient->patient_name : 'Patient not found' ); ?><br><small><?php echo esc_html( $visit->patient_id ); ?></small></td>
                                <td><?php echo esc_html( ph_visits_doctor( $visit->doctor_id ) ); ?></td>
                                <td><?php echo esc_html( ph_visits_date( $visit->visit_date ) ); ?></td>
                                <td><?php echo esc_html( ph_visits_time( $visit->visit_time ) ); ?></td>
                                <td><?php echo esc_html( ph_visits_types()[ $visit->visit_type ] ?? $visit->visit_type ); ?></td>
                                <td><span class="status"><?php echo esc_html( ph_visits_statuses()[ $visit->status ] ?? $visit->status ); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

function ph_visits_get_available_appointments() {
    global $wpdb;

    $table = $wpdb->prefix . 'ph_appointments';

    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return array();
    }

    $columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" );

    if ( ! in_array( 'patient_id', $columns, true ) ) {
        return array();
    }

    $statuses = array( 'confirmed', 'arrived', 'in_consultation' );
    $placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE patient_id IS NOT NULL
               AND patient_id <> ''
               AND status IN ({$placeholders})
             ORDER BY preferred_date DESC, id DESC
             LIMIT 200",
            $statuses
        )
    );
}

function ph_visits_get_patients() {
    global $wpdb;

    $table = $wpdb->prefix . 'ph_patients';

    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return array();
    }

    return $wpdb->get_results(
        "SELECT patient_reference, patient_name, mobile
         FROM {$table}
         ORDER BY patient_name ASC
         LIMIT 500"
    );
}

function ph_visits_add() {
    if ( ! current_user_can( ph_visits_cap() ) ) {
        wp_die( 'You do not have permission to manage visits.' );
    }

    global $wpdb;

    $errors = array();

    $visit_type = isset( $_POST['visit_type'] ) ? sanitize_key( wp_unslash( $_POST['visit_type'] ) ) : 'appointment';
    $appointment_id = isset( $_POST['appointment_id'] ) ? absint( $_POST['appointment_id'] ) : 0;
    $patient_id = isset( $_POST['patient_id'] ) ? sanitize_text_field( wp_unslash( $_POST['patient_id'] ) ) : '';
    $doctor_id = isset( $_POST['doctor_id'] ) ? absint( $_POST['doctor_id'] ) : 0;
    $visit_date = isset( $_POST['visit_date'] ) ? sanitize_text_field( wp_unslash( $_POST['visit_date'] ) ) : current_time( 'Y-m-d' );
    $visit_time = isset( $_POST['visit_time'] ) ? sanitize_text_field( wp_unslash( $_POST['visit_time'] ) ) : '';
    $status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'scheduled';
    $notes = isset( $_POST['visit_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['visit_notes'] ) ) : '';

    $prefill_appointment_id = isset( $_GET['appointment_id'] ) ? absint( $_GET['appointment_id'] ) : 0;
    if ( ! $appointment_id && $prefill_appointment_id && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
        $appointment_id = $prefill_appointment_id;
    }

    $selected_appointment = $appointment_id ? ph_visits_appointment( $appointment_id ) : null;

    if ( 'GET' === $_SERVER['REQUEST_METHOD'] && $selected_appointment && empty( $_POST ) ) {
        $visit_type = 'appointment';
        $patient_id = ! empty( $selected_appointment->patient_id ) ? $selected_appointment->patient_id : '';
        $doctor_id = ! empty( $selected_appointment->doctor_id ) ? (int) $selected_appointment->doctor_id : 0;
        $visit_date = ! empty( $selected_appointment->confirmed_date ) ? $selected_appointment->confirmed_date : ( ! empty( $selected_appointment->preferred_date ) ? $selected_appointment->preferred_date : current_time( 'Y-m-d' ) );
        $status = 'scheduled';
    }

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ph_visits_action'] ) && 'save' === $_POST['ph_visits_action'] ) {
        check_admin_referer( 'ph_visits_save' );

        if ( ! isset( ph_visits_types()[ $visit_type ] ) ) {
            $errors[] = 'Please select a valid visit source.';
        }

        if ( 'appointment' === $visit_type ) {
            if ( ! $appointment_id ) {
                $errors[] = 'Please select an appointment.';
            } else {
                $selected_appointment = ph_visits_appointment( $appointment_id );

                if ( ! $selected_appointment ) {
                    $errors[] = 'The selected appointment could not be found.';
                } elseif ( empty( $selected_appointment->patient_id ) ) {
                    $errors[] = 'This appointment does not have a registered patient linked to it.';
                } elseif ( ! in_array( $selected_appointment->status, array( 'confirmed', 'arrived', 'in_consultation' ), true ) ) {
                    $errors[] = 'Only confirmed or active appointments can be converted into a visit.';
                } else {
                    $patient_id = $selected_appointment->patient_id;
                    if ( ! empty( $selected_appointment->doctor_id ) ) {
                        $doctor_id = (int) $selected_appointment->doctor_id;
                    }
                    if ( ! empty( $selected_appointment->confirmed_date ) ) {
                        $visit_date = $selected_appointment->confirmed_date;
                    } elseif ( ! empty( $selected_appointment->preferred_date ) ) {
                        $visit_date = $selected_appointment->preferred_date;
                    }
                }
            }
        } else {
            $appointment_id = 0;
            if ( '' === $patient_id || ! ph_visits_patient( $patient_id ) ) {
                $errors[] = 'Please select a registered patient for this walk-in visit.';
            }
        }

        if ( 'appointment' === $visit_type && $patient_id && ! ph_visits_patient( $patient_id ) ) {
            $errors[] = 'The patient linked to this appointment could not be found.';
        }

        if ( ! isset( ph_visits_statuses()[ $status ] ) ) {
            $errors[] = 'Please select a valid visit status.';
        }

        if ( ! $visit_date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $visit_date ) ) {
            $errors[] = 'Please enter a valid visit date.';
        }

        if ( ! $errors && $appointment_id ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}ph_visits WHERE appointment_id = %d LIMIT 1",
                    $appointment_id
                )
            );

            if ( $existing ) {
                $errors[] = 'A visit has already been created for this appointment.';
            }
        }

        if ( ! $errors ) {
            $table = $wpdb->prefix . PH_VISITS_TABLE;
            $now   = current_time( 'mysql' );

            $inserted = $wpdb->insert(
                $table,
                array(
                    'visit_reference' => ph_visits_ref(),
                    'patient_id'      => $patient_id,
                    'appointment_id'  => $appointment_id ? $appointment_id : null,
                    'doctor_id'       => $doctor_id ? $doctor_id : null,
                    'visit_date'      => $visit_date,
                    'visit_time'      => $visit_time ?: null,
                    'visit_type'      => $visit_type,
                    'status'          => $status,
                    'visit_notes'     => $notes,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ),
                array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            if ( false === $inserted ) {
                $errors[] = 'The visit could not be saved.';
            } else {
                $id = (int) $wpdb->insert_id;
                $reference = $wpdb->get_var( $wpdb->prepare( "SELECT visit_reference FROM {$table} WHERE id = %d", $id ) );

                echo '<div class="wrap phv"><div class="notice notice-success"><p><strong>Visit created successfully.</strong> Visit reference: ' . esc_html( $reference ) . '</p></div><p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . $id ) ) . '">View Visit</a> <a class="button" href="' . esc_url( admin_url( 'admin.php?page=ph-visits-add' ) ) . '">Add Another Visit</a></p></div>';
                return;
            }
        }
    }

    $doctors = get_posts(
        array(
            'post_type'      => 'doctor',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );

    $appointments = ph_visits_get_available_appointments();
    $patients = ph_visits_get_patients();
    ?>
    <div class="wrap phv">
        <h1>Add Visit</h1>

        <?php if ( $success ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Visit updated successfully.</strong></p></div>
        <?php endif; ?>

        <?php if ( $errors ) : ?>
            <div class="notice notice-error">
                <p><strong>Please correct the following:</strong></p>
                <ul><?php foreach ( $errors as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="post">
                <?php wp_nonce_field( 'ph_visits_save' ); ?>
                <input type="hidden" name="ph_visits_action" value="save">

                <div class="row">
                    <label>Visit Source *</label>
                    <label class="source-choice"><input type="radio" name="visit_type" value="appointment" <?php checked( $visit_type, 'appointment' ); ?>> Appointment</label>
                    <label class="source-choice"><input type="radio" name="visit_type" value="walk_in" <?php checked( $visit_type, 'walk_in' ); ?>> Walk-in</label>
                    <div class="help">An appointment visit must come from a registered patient with a confirmed/active appointment. A walk-in must also use an existing registered patient.</div>
                </div>

                <div class="row" id="ph-appointment-row">
                    <label for="ph-appointment-id">Appointment *</label>
                    <select id="ph-appointment-id" name="appointment_id">
                        <option value="0">Select an appointment</option>
                        <?php foreach ( $appointments as $appointment ) : ?>
                            <?php
                            $patient = ph_visits_patient( $appointment->patient_id );
                            $patient_name = $patient ? $patient->patient_name : $appointment->patient_name;
                            $doctor_name = ph_visits_doctor( $appointment->doctor_id );
                            $date = ! empty( $appointment->confirmed_date ) ? $appointment->confirmed_date : $appointment->preferred_date;
                            ?>
                            <option value="<?php echo (int) $appointment->id; ?>" <?php selected( $appointment_id, $appointment->id ); ?>>
                                <?php echo esc_html( $appointment->request_reference . ' — ' . $patient_name . ' — ' . $doctor_name . ' — ' . ph_visits_date( $date ) . ' — ' . ucfirst( str_replace( '_', ' ', $appointment->status ) ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help">Only appointments with a registered patient and an active status are shown.</div>
                    <?php if ( ! $appointments ) : ?>
                        <div class="notice-box">No eligible appointments are available. Confirm an appointment with a registered patient first, or choose Walk-in.</div>
                    <?php endif; ?>
                </div>

                <div class="row" id="ph-patient-row">
                    <label for="ph-patient-id">Registered Patient *</label>
                    <select id="ph-patient-id" name="patient_id">
                        <option value="">Select a registered patient</option>
                        <?php foreach ( $patients as $patient ) : ?>
                            <option value="<?php echo esc_attr( $patient->patient_reference ); ?>" <?php selected( $patient_id, $patient->patient_reference ); ?>>
                                <?php echo esc_html( $patient->patient_reference . ' — ' . $patient->patient_name . ' — ' . $patient->mobile ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help">For walk-ins, select an existing registered patient. New patients must be registered in Patients first.</div>
                </div>

                <div class="row">
                    <label for="ph-doctor-id">Doctor</label>
                    <select id="ph-doctor-id" name="doctor_id">
                        <option value="0">Not specified</option>
                        <?php foreach ( $doctors as $doctor ) : ?>
                            <option value="<?php echo esc_attr( $doctor->ID ); ?>" <?php selected( $doctor_id, $doctor->ID ); ?>><?php echo esc_html( get_the_title( $doctor ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help">For an appointment visit, the selected appointment's doctor is used automatically.</div>
                </div>

                <div class="row">
                    <label for="ph-visit-date">Visit Date *</label>
                    <input type="date" id="ph-visit-date" name="visit_date" value="<?php echo esc_attr( $visit_date ); ?>" required>
                </div>

                <div class="row">
                    <label for="ph-visit-time">Visit Time</label>
                    <input type="time" id="ph-visit-time" name="visit_time" value="<?php echo esc_attr( $visit_time ); ?>">
                </div>

                <div class="row">
                    <label for="ph-visit-status">Status *</label>
                    <select id="ph-visit-status" name="status">
                        <?php foreach ( ph_visits_statuses() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <label for="ph-visit-notes">Visit Notes</label>
                    <textarea id="ph-visit-notes" name="visit_notes"><?php echo esc_textarea( $notes ); ?></textarea>
                    <div class="help">Administrative/general visit notes only. Clinical consultation fields will be added separately.</div>
                </div>

                <p><button type="submit" class="button button-primary">Create Visit</button> <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>">Cancel</a></p>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const appointmentRadio = document.querySelector('input[name="visit_type"][value="appointment"]');
        const walkInRadio = document.querySelector('input[name="visit_type"][value="walk_in"]');
        const appointmentRow = document.getElementById('ph-appointment-row');
        const patientRow = document.getElementById('ph-patient-row');
        const appointmentSelect = document.getElementById('ph-appointment-id');
        const patientSelect = document.getElementById('ph-patient-id');

        function refreshFields() {
            const appointmentMode = appointmentRadio && appointmentRadio.checked;
            appointmentRow.style.display = appointmentMode ? '' : 'none';
            patientRow.style.display = appointmentMode ? 'none' : '';
            appointmentSelect.required = appointmentMode;
            patientSelect.required = !appointmentMode;
        }

        if (appointmentRadio) appointmentRadio.addEventListener('change', refreshFields);
        if (walkInRadio) walkInRadio.addEventListener('change', refreshFields);
        refreshFields();
    });
    </script>
    <?php
}

function ph_visits_detail( $id ) {
    if ( ! current_user_can( ph_visits_cap() ) ) {
        wp_die( 'You do not have permission to manage visits.' );
    }

    global $wpdb;

    $table = $wpdb->prefix . PH_VISITS_TABLE;
    $visit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) );

    if ( ! $visit ) {
        echo '<div class="wrap phv"><div class="notice notice-error"><p>Visit not found.</p></div></div>';
        return;
    }

    $patient = ph_visits_patient( $visit->patient_id );
    $appointment = ph_visits_appointment( $visit->appointment_id );
    ?>
    <div class="wrap phv">
        <h1><?php echo esc_html( $visit->visit_reference ); ?></h1>
        <p>
            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>">← Back to Visits</a>
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&edit_visit_id=' . (int) $visit->id ) ); ?>">Edit Visit / Update Status</a>
        </p>
        <?php if ( isset( $_GET['updated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Visit updated successfully.</p></div>
        <?php endif; ?>

        <div class="card">
            <h2>Visit Information</h2>
            <div class="grid">
                <div class="lbl">Visit Reference</div><div><?php echo esc_html( $visit->visit_reference ); ?></div>
                <div class="lbl">Patient</div><div><?php if ( $patient ) : ?><strong><?php echo esc_html( $patient->patient_name ); ?></strong><br><?php echo esc_html( $patient->patient_reference ); ?><br><?php echo esc_html( $patient->mobile ); ?><?php else : ?><?php echo esc_html( $visit->patient_id ); ?> — Patient not found<?php endif; ?></div>
                <div class="lbl">Appointment</div><div><?php if ( $appointment ) : ?><?php echo esc_html( $appointment->request_reference ); ?><?php else : ?>Walk-in / no linked appointment<?php endif; ?></div>
                <div class="lbl">Doctor</div><div><?php echo esc_html( ph_visits_doctor( $visit->doctor_id ) ); ?></div>
                <div class="lbl">Visit Date</div><div><?php echo esc_html( ph_visits_date( $visit->visit_date ) ); ?></div>
                <div class="lbl">Visit Time</div><div><?php echo esc_html( ph_visits_time( $visit->visit_time ) ); ?></div>
                <div class="lbl">Visit Type</div><div><?php echo esc_html( ph_visits_types()[ $visit->visit_type ] ?? $visit->visit_type ); ?></div>
                <div class="lbl">Status</div><div><span class="status"><?php echo esc_html( ph_visits_statuses()[ $visit->status ] ?? $visit->status ); ?></span></div>
                <div class="lbl">Visit Notes</div><div><?php echo '' !== $visit->visit_notes ? nl2br( esc_html( $visit->visit_notes ) ) : '—'; ?></div>
                <div class="lbl">Created</div><div><?php echo esc_html( $visit->created_at ); ?></div>
                <div class="lbl">Updated</div><div><?php echo esc_html( $visit->updated_at ); ?></div>
            </div>
        </div>
    </div>
    <?php
}


function ph_visits_edit( $id ) {
    if ( ! current_user_can( ph_visits_cap() ) ) {
        wp_die( 'You do not have permission to manage visits.' );
    }

    global $wpdb;

    $table = $wpdb->prefix . PH_VISITS_TABLE;
    $visit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) );

    if ( ! $visit ) {
        echo '<div class="wrap phv"><div class="notice notice-error"><p>Visit not found.</p></div></div>';
        return;
    }

    $errors = array();
    $success = false;
    $status = $visit->status;
    $visit_date = $visit->visit_date;
    $visit_time = ( '00:00:00' === $visit->visit_time ) ? '' : substr( (string) $visit->visit_time, 0, 5 );
    $doctor_id = (int) $visit->doctor_id;
    $notes = $visit->visit_notes;

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ph_visits_action'] ) && 'update' === sanitize_key( wp_unslash( $_POST['ph_visits_action'] ) ) ) {
        check_admin_referer( 'ph_visits_update_' . (int) $visit->id );

        $status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
        $visit_date = isset( $_POST['visit_date'] ) ? sanitize_text_field( wp_unslash( $_POST['visit_date'] ) ) : '';
        $visit_time = isset( $_POST['visit_time'] ) ? sanitize_text_field( wp_unslash( $_POST['visit_time'] ) ) : '';
        $doctor_id = isset( $_POST['doctor_id'] ) ? absint( $_POST['doctor_id'] ) : 0;
        $notes = isset( $_POST['visit_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['visit_notes'] ) ) : '';

        if ( ! isset( ph_visits_statuses()[ $status ] ) ) {
            $errors[] = 'Please select a valid visit status.';
        }

        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $visit_date ) ) {
            $errors[] = 'Please enter a valid visit date.';
        } else {
            $date_parts = explode( '-', $visit_date );
            if ( ! checkdate( (int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[0] ) ) {
                $errors[] = 'Please enter a valid calendar date.';
            }
        }

        if ( '' !== $visit_time && ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $visit_time ) ) {
            $errors[] = 'Please enter a valid visit time.';
        }

        if ( $doctor_id ) {
            $doctor_post = get_post( $doctor_id );
            if ( ! $doctor_post || 'doctor' !== $doctor_post->post_type || 'publish' !== $doctor_post->post_status ) {
                $errors[] = 'Please select a valid published doctor.';
            }
        }

        if ( ! $errors ) {
            $updated = $wpdb->update(
                $table,
                array(
                    'status'     => $status,
                    'visit_date' => $visit_date,
                    'visit_time' => '' !== $visit_time ? $visit_time . ':00' : null,
                    'doctor_id'  => $doctor_id ? $doctor_id : null,
                    'visit_notes'=> $notes,
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => (int) $visit->id ),
                array( '%s', '%s', '%s', '%d', '%s', '%s' ),
                array( '%d' )
            );

            if ( false === $updated ) {
                $errors[] = 'The visit could not be updated. Please try again.';
            } else {
                // Admin page callbacks may run after output has started, so render a success
                // notice here instead of redirecting and risking a blank response.
                $success = true;
                $visit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", (int) $visit->id ) );
                $status = $visit->status;
                $visit_date = $visit->visit_date;
                $visit_time = ( '00:00:00' === $visit->visit_time ) ? '' : substr( (string) $visit->visit_time, 0, 5 );
                $doctor_id = (int) $visit->doctor_id;
                $notes = $visit->visit_notes;
            }
        }
    }

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
    <div class="wrap phv">
        <h1>Edit Visit — <?php echo esc_html( $visit->visit_reference ); ?></h1>
        <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . (int) $visit->id ) ); ?>">← Cancel and View Visit</a></p>

        <?php if ( $success ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Visit updated successfully.</strong></p></div>
        <?php endif; ?>

        <?php if ( $errors ) : ?>
            <div class="notice notice-error">
                <p><strong>Please correct the following:</strong></p>
                <ul><?php foreach ( $errors as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <p><strong>Patient:</strong> <?php echo esc_html( $visit->patient_id ); ?> &nbsp; | &nbsp;
            <strong>Type:</strong> <?php echo esc_html( ph_visits_types()[ $visit->visit_type ] ?? $visit->visit_type ); ?>
            <?php if ( $visit->appointment_id ) : ?> &nbsp; | &nbsp; <strong>Appointment ID:</strong> <?php echo (int) $visit->appointment_id; ?><?php endif; ?></p>
            <p class="help">Patient, visit source, and linked appointment are fixed here to preserve the visit's original association.</p>

            <form method="post">
                <?php wp_nonce_field( 'ph_visits_update_' . (int) $visit->id ); ?>
                <input type="hidden" name="ph_visits_action" value="update">

                <div class="row">
                    <label for="ph-edit-visit-status">Status *</label>
                    <select id="ph-edit-visit-status" name="status" required>
                        <?php foreach ( ph_visits_statuses() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <label for="ph-edit-visit-date">Visit Date *</label>
                    <input type="date" id="ph-edit-visit-date" name="visit_date" value="<?php echo esc_attr( $visit_date ); ?>" required>
                </div>

                <div class="row">
                    <label for="ph-edit-visit-time">Visit Time</label>
                    <input type="time" id="ph-edit-visit-time" name="visit_time" value="<?php echo esc_attr( $visit_time ); ?>">
                </div>

                <div class="row">
                    <label for="ph-edit-doctor-id">Doctor</label>
                    <select id="ph-edit-doctor-id" name="doctor_id">
                        <option value="0">Not specified</option>
                        <?php foreach ( $doctors as $doctor ) : ?>
                            <option value="<?php echo (int) $doctor->ID; ?>" <?php selected( $doctor_id, $doctor->ID ); ?>><?php echo esc_html( get_the_title( $doctor ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <label for="ph-edit-visit-notes">Visit Notes</label>
                    <textarea id="ph-edit-visit-notes" name="visit_notes"><?php echo esc_textarea( $notes ); ?></textarea>
                </div>

                <p><button type="submit" class="button button-primary">Save Visit Changes</button>
                <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits&visit_id=' . (int) $visit->id ) ); ?>">Cancel</a></p>
            </form>
        </div>
    </div>
    <?php
}

function ph_visits_admin_page() {
    if ( ! current_user_can( ph_visits_cap() ) ) {
        wp_die( 'You do not have permission to manage visits.' );
    }

    if ( isset( $_GET['edit_visit_id'] ) && absint( $_GET['edit_visit_id'] ) ) {
        ph_visits_edit( absint( $_GET['edit_visit_id'] ) );
        return;
    }

    if ( isset( $_GET['visit_id'] ) && absint( $_GET['visit_id'] ) ) {
        ph_visits_detail( absint( $_GET['visit_id'] ) );
        return;
    }

    ph_visits_list();
}
