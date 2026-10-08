<?php
/**
 * Plugin Name: Prasanthi Hospitals Appointments
 * Description: Appointment request and front desk management system for Prasanthi Hospitals.
 * Version: 1.1.0
 * Author: Prasanthi Hospitals
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * ---------------------------------------------------------
 * Constants
 * ---------------------------------------------------------
 */

define('PH_APPOINTMENTS_VERSION', '1.1.0');
define('PH_APPOINTMENTS_DB_VERSION', '1.0.0');


/**
 * ---------------------------------------------------------
 * Database table
 * ---------------------------------------------------------
 */

function ph_appointments_table_name() {
    global $wpdb;

    return $wpdb->prefix . 'ph_appointments';
}


/**
 * ---------------------------------------------------------
 * Create database table
 * ---------------------------------------------------------
 */

function ph_appointments_create_table() {
    global $wpdb;

    $table_name = ph_appointments_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        request_reference VARCHAR(30) NOT NULL,
        patient_name VARCHAR(150) NOT NULL,
        mobile VARCHAR(30) NOT NULL,
        doctor_id BIGINT UNSIGNED NULL,
        preferred_date DATE NULL,
        preferred_session VARCHAR(20) NULL,
        patient_type VARCHAR(20) NULL,
        preferred_language VARCHAR(20) NULL,
        reason TEXT NULL,
        consent TINYINT(1) NOT NULL DEFAULT 0,
        status VARCHAR(30) NOT NULL DEFAULT 'new_request',
        staff_notes TEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY request_reference (request_reference),
        KEY doctor_id (doctor_id),
        KEY preferred_date (preferred_date),
        KEY status (status),
        KEY mobile (mobile)
    ) {$charset_collate};";

    dbDelta($sql);

    update_option(
        'ph_appointments_db_version',
        PH_APPOINTMENTS_DB_VERSION
    );
}


/**
 * ---------------------------------------------------------
 * Activation
 * ---------------------------------------------------------
 */

function ph_appointments_activate() {

    ph_appointments_create_table();

    if (!get_role('hospital_front_desk')) {

        add_role(
            'hospital_front_desk',
            'Hospital Front Desk',
            array(
                'read' => true,
            )
        );
    }
}

register_activation_hook(
    __FILE__,
    'ph_appointments_activate'
);


/**
 * ---------------------------------------------------------
 * Generate appointment request reference
 * ---------------------------------------------------------
 */

function ph_appointments_generate_reference() {

    do {

        $reference = 'PH-APT-' . strtoupper(
            wp_generate_password(
                8,
                false,
                false
            )
        );

    } while (
        ph_appointments_reference_exists($reference)
    );

    return $reference;
}


/**
 * ---------------------------------------------------------
 * Check request reference
 * ---------------------------------------------------------
 */

function ph_appointments_reference_exists($reference) {

    global $wpdb;

    $table_name = ph_appointments_table_name();

    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$table_name}
             WHERE request_reference = %s",
            $reference
        )
    );

    return ((int) $count > 0);
}


/**
 * ---------------------------------------------------------
 * Appointment status labels
 * ---------------------------------------------------------
 */

function ph_appointments_status_label($status) {

    $labels = array(

        'new_request'      => 'New Request',
        'confirmed'       => 'Confirmed',
        'rescheduled'     => 'Rescheduled',
        'cancelled'       => 'Cancelled',
        'no_show'         => 'No Show',
        'completed'       => 'Completed',
        'arrived'         => 'Arrived',
        'in_consultation' => 'In Consultation',

    );

    return isset($labels[$status])
        ? $labels[$status]
        : ucfirst(
            str_replace(
                '_',
                ' ',
                $status
            )
        );
}


/**
 * ---------------------------------------------------------
 * Admin menu
 * ---------------------------------------------------------
 */

function ph_appointments_admin_menu() {

    add_menu_page(
        'Appointments',
        'Appointments',
        'manage_options',
        'ph-appointments',
        'ph_appointments_admin_page',
        'dashicons-calendar-alt',
        25
    );
}

add_action(
    'admin_menu',
    'ph_appointments_admin_menu'
);


/**
 * ---------------------------------------------------------
 * Admin appointments page
 * ---------------------------------------------------------
 */

function ph_appointments_admin_page() {

    if (!current_user_can('manage_options')) {

        wp_die(
            'You do not have permission to access this page.'
        );
    }

    global $wpdb;

    $table_name = ph_appointments_table_name();

    $appointments = $wpdb->get_results(
        "SELECT *
         FROM {$table_name}
         ORDER BY created_at DESC
         LIMIT 100"
    );

    ?>

    <div class="wrap">

        <h1>Prasanthi Hospitals Appointments</h1>

        <p>
            Appointment requests received through the hospital website.
        </p>

        <table class="widefat fixed striped">

            <thead>

                <tr>

                    <th>Reference</th>
                    <th>Patient</th>
                    <th>Mobile</th>
                    <th>Doctor</th>
                    <th>Preferred Date</th>
                    <th>Session</th>
                    <th>Status</th>
                    <th>Received</th>

                </tr>

            </thead>

            <tbody>

            <?php if (!empty($appointments)) : ?>

                <?php foreach ($appointments as $appointment) : ?>

                    <tr>

                        <td>

                            <strong>
                                <?php
                                echo esc_html(
                                    $appointment->request_reference
                                );
                                ?>
                            </strong>

                        </td>

                        <td>
                            <?php
                            echo esc_html(
                                $appointment->patient_name
                            );
                            ?>
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

                            if (!empty($appointment->doctor_id)) {

                                $doctor_name = get_the_title(
                                    $appointment->doctor_id
                                );

                                echo esc_html(
                                    $doctor_name ?: '—'
                                );

                            } else {

                                echo 'Any / Front Desk';

                            }

                            ?>

                        </td>

                        <td>

                            <?php

                            echo !empty(
                                $appointment->preferred_date
                            )
                                ? esc_html(
                                    date_i18n(
                                        'j M Y',
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
                                    ?: '—'
                                )
                            );

                            ?>

                        </td>

                        <td>

                            <?php

                            echo esc_html(
                                ph_appointments_status_label(
                                    $appointment->status
                                )
                            );

                            ?>

                        </td>

                        <td>

                            <?php

                            echo esc_html(
                                date_i18n(
                                    'j M Y, g:i A',
                                    strtotime(
                                        $appointment->created_at
                                    )
                                )
                            );

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else : ?>

                <tr>

                    <td colspan="8">

                        No appointment requests have been received yet.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <?php
}


/**
 * ---------------------------------------------------------
 * Process appointment request
 * ---------------------------------------------------------
 */

function ph_appointments_process_request() {

    if (
        !isset(
            $_POST['ph_appointment_nonce']
        )
    ) {

        return;
    }


    if (
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash(
                    $_POST['ph_appointment_nonce']
                )
            ),
            'ph_submit_appointment'
        )
    ) {

        return;
    }


    /*
     * Prevent accidental processing if this request
     * did not come from our form.
     */

    if (
        !isset(
            $_POST['ph_submit_appointment']
        )
    ) {

        return;
    }


    /*
     * Read form fields.
     */

    $patient_name = isset(
        $_POST['patient_name']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['patient_name']
            )
        )
        : '';


    $mobile = isset(
        $_POST['mobile']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['mobile']
            )
        )
        : '';


    $doctor_id = isset(
        $_POST['doctor_id']
    )
        ? absint(
            $_POST['doctor_id']
        )
        : 0;


    $preferred_date = isset(
        $_POST['preferred_date']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['preferred_date']
            )
        )
        : '';


    $preferred_session = isset(
        $_POST['preferred_session']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['preferred_session']
            )
        )
        : '';


    $patient_type = isset(
        $_POST['patient_type']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['patient_type']
            )
        )
        : '';


    $preferred_language = isset(
        $_POST['preferred_language']
    )
        ? sanitize_text_field(
            wp_unslash(
                $_POST['preferred_language']
            )
        )
        : '';


    $reason = isset(
        $_POST['reason']
    )
        ? sanitize_textarea_field(
            wp_unslash(
                $_POST['reason']
            )
        )
        : '';


    $consent = isset(
        $_POST['consent']
    )
        ? 1
        : 0;


    /*
     * Validate required fields.
     */

    $errors = array();


    if (empty($patient_name)) {

        $errors[] = 'Please enter the patient name.';
    }


    if (empty($mobile)) {

        $errors[] = 'Please enter a mobile number.';
    }


    if (
        !empty($mobile)
        &&
        !preg_match(
            '/^[0-9+\-\s]{8,20}$/',
            $mobile
        )
    ) {

        $errors[] = 'Please enter a valid mobile number.';
    }


    if (empty($preferred_date)) {

        $errors[] = 'Please select a preferred date.';
    }


    if (
        !empty($preferred_date)
        &&
        !preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $preferred_date
        )
    ) {

        $errors[] = 'Please select a valid date.';
    }


    $allowed_sessions = array(
        'morning',
        'evening',
    );


    if (
        !in_array(
            $preferred_session,
            $allowed_sessions,
            true
        )
    ) {

        $errors[] = 'Please select a preferred session.';
    }


    $allowed_patient_types = array(
        'new',
        'existing',
    );


    if (
        !in_array(
            $patient_type,
            $allowed_patient_types,
            true
        )
    ) {

        $errors[] = 'Please select whether you are a new or existing patient.';
    }


    $allowed_languages = array(
        'english',
        'telugu',
    );


    if (
        !in_array(
            $preferred_language,
            $allowed_languages,
            true
        )
    ) {

        $errors[] = 'Please select your preferred language.';
    }


    if (!$consent) {

        $errors[] =
            'Please provide consent for the hospital to contact you regarding this appointment request.';
    }


    /*
     * Validate selected doctor.
     */

    if ($doctor_id > 0) {

        $doctor = get_post(
            $doctor_id
        );

        if (
            !$doctor
            ||
            $doctor->post_type !== 'doctor'
            ||
            $doctor->post_status !== 'publish'
        ) {

            $errors[] =
                'The selected doctor is not available.';
        }
    }


    /*
     * If there are errors, store them temporarily
     * in the current request.
     */

    if (!empty($errors)) {

        $GLOBALS['ph_appointment_form_errors'] =
            $errors;

        return;
    }


    /*
     * Generate request reference.
     */

    $request_reference =
        ph_appointments_generate_reference();


    /*
     * Current time.
     */

    $current_time =
        current_time('mysql');


    /*
     * Insert appointment request.
     */

    global $wpdb;

    $table_name =
        ph_appointments_table_name();


    $inserted = $wpdb->insert(

        $table_name,

        array(

            'request_reference' =>
                $request_reference,

            'patient_name' =>
                $patient_name,

            'mobile' =>
                $mobile,

            'doctor_id' =>
                $doctor_id > 0
                    ? $doctor_id
                    : null,

            'preferred_date' =>
                $preferred_date,

            'preferred_session' =>
                $preferred_session,

            'patient_type' =>
                $patient_type,

            'preferred_language' =>
                $preferred_language,

            'reason' =>
                $reason,

            'consent' =>
                $consent,

            'status' =>
                'new_request',

            'staff_notes' =>
                '',

            'created_at' =>
                $current_time,

            'updated_at' =>
                $current_time,

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
            '%s',

        )

    );


    /*
     * Check database result.
     */

    if ($inserted === false) {

        $GLOBALS['ph_appointment_form_errors'] =
            array(
                'We could not submit your appointment request. Please try again or contact the hospital directly.'
            );

        return;
    }


    /*
     * Store successful reference.
     */

    $GLOBALS['ph_appointment_success'] =
        $request_reference;
}

add_action(
    'init',
    'ph_appointments_process_request'
);


/**
 * ---------------------------------------------------------
 * Appointment form shortcode
 * ---------------------------------------------------------
 */

function ph_appointments_shortcode() {

    $success_reference =
        isset(
            $GLOBALS['ph_appointment_success']
        )
            ? $GLOBALS['ph_appointment_success']
            : '';


    $errors =
        isset(
            $GLOBALS['ph_appointment_form_errors']
        )
            ? $GLOBALS['ph_appointment_form_errors']
            : array();


    ob_start();

    ?>

    <div class="ph-appointment-form-wrapper">

        <?php if ($success_reference) : ?>

            <div class="ph-appointment-success">

                <h2>
                    Request Received
                </h2>

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
                            $success_reference
                        );
                        ?>
                    </strong>
                </p>

            </div>

        <?php else : ?>

            <?php if (!empty($errors)) : ?>

                <div class="ph-appointment-errors">

                    <strong>
                        Please check the following:
                    </strong>

                    <ul>

                        <?php foreach ($errors as $error) : ?>

                            <li>
                                <?php
                                echo esc_html(
                                    $error
                                );
                                ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


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


                <div class="ph-form-section">

                    <h3>
                        Appointment Request
                    </h3>

                    <p>
                        Please provide the details below.
                        Our front desk will contact you to
                        confirm availability.
                    </p>

                </div>


                <div class="ph-form-row">

                    <div class="ph-form-field">

                        <label for="ph_patient_name">
                            Patient Name
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="ph_patient_name"
                            name="patient_name"
                            maxlength="150"
                            required
                        >

                    </div>


                    <div class="ph-form-field">

                        <label for="ph_mobile">
                            Mobile Number
                            <span>*</span>
                        </label>

                        <input
                            type="tel"
                            id="ph_mobile"
                            name="mobile"
                            maxlength="20"
                            required
                        >

                    </div>

                </div>


                <div class="ph-form-row">

                    <div class="ph-form-field">

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

                            <?php

                            $doctors = new WP_Query(

                                array(

                                    'post_type' =>
                                        'doctor',

                                    'post_status' =>
                                        'publish',

                                    'posts_per_page' =>
                                        -1,

                                    'orderby' =>
                                        'title',

                                    'order' =>
                                        'ASC',

                                )

                            );

                            if (
                                $doctors->have_posts()
                            ) :

                                while (
                                    $doctors->have_posts()
                                ) :

                                    $doctors->the_post();

                                    ?>

                                    <option
                                        value="<?php the_ID(); ?>"
                                    >
                                        <?php
                                        the_title();
                                        ?>
                                    </option>

                                    <?php

                                endwhile;

                                wp_reset_postdata();

                            endif;

                            ?>

                        </select>

                    </div>


                    <div class="ph-form-field">

                        <label for="ph_preferred_date">
                            Preferred Date
                            <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="ph_preferred_date"
                            name="preferred_date"
                            min="<?php echo esc_attr(
                                current_time('Y-m-d')
                            ); ?>"
                            required
                        >

                    </div>

                </div>


                <div class="ph-form-row">

                    <div class="ph-form-field">

                        <label>
                            Preferred Session
                            <span>*</span>
                        </label>

                        <div class="ph-radio-group">

                            <label>

                                <input
                                    type="radio"
                                    name="preferred_session"
                                    value="morning"
                                    required
                                >

                                Morning
                                <small>
                                    10:30 AM – 2:00 PM
                                </small>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="preferred_session"
                                    value="evening"
                                >

                                Evening
                                <small>
                                    6:00 PM – 9:00 PM
                                </small>

                            </label>

                        </div>

                    </div>


                    <div class="ph-form-field">

                        <label>
                            Patient Type
                            <span>*</span>
                        </label>

                        <div class="ph-radio-group">

                            <label>

                                <input
                                    type="radio"
                                    name="patient_type"
                                    value="new"
                                    required
                                >

                                New Patient

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="patient_type"
                                    value="existing"
                                >

                                Existing Patient

                            </label>

                        </div>

                    </div>

                </div>


                <div class="ph-form-row">

                    <div class="ph-form-field">

                        <label for="ph_language">
                            Preferred Language
                            <span>*</span>
                        </label>

                        <select
                            id="ph_language"
                            name="preferred_language"
                            required
                        >

                            <option value="">
                                Select Language
                            </option>

                            <option value="english">
                                English
                            </option>

                            <option value="telugu">
                                Telugu
                            </option>

                        </select>

                    </div>


                    <div class="ph-form-field">

                        <label for="ph_reason">
                            Reason for Visit
                        </label>

                        <textarea
                            id="ph_reason"
                            name="reason"
                            rows="4"
                            maxlength="1000"
                            placeholder="Optional"
                        ></textarea>

                    </div>

                </div>


                <div class="ph-form-consent">

                    <label>

                        <input
                            type="checkbox"
                            name="consent"
                            value="1"
                            required
                        >

                        I agree that Prasanthi Hospitals
                        may contact me regarding this
                        appointment request.

                    </label>

                </div>


                <div class="ph-form-submit">

                    <button
                        type="submit"
                        name="ph_submit_appointment"
                        value="1"
                    >
                        Submit Appointment Request
                    </button>

                </div>


                <p class="ph-form-note">
                    Submitting this form does not confirm
                    an appointment. Our front desk will
                    contact you to confirm availability.
                </p>

            </form>

        <?php endif; ?>

    </div>

    <?php

    return ob_get_clean();
}

add_shortcode(
    'ph_appointment_form',
    'ph_appointments_shortcode'
);


/**
 * ---------------------------------------------------------
 * Front-end styles
 * ---------------------------------------------------------
 */

function ph_appointments_enqueue_styles() {

    if (
        is_page()
        &&
        has_shortcode(
            get_post_field(
                'post_content',
                get_queried_object_id()
            ),
            'ph_appointment_form'
        )
    ) {

        wp_register_style(
            'ph-appointments',
            false
        );

        wp_enqueue_style(
            'ph-appointments'
        );


        $css = '

        .ph-appointment-form-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .ph-appointment-form {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.06);
        }

        .ph-form-section {
            margin-bottom: 25px;
        }

        .ph-form-section h3 {
            margin-bottom: 8px;
        }

        .ph-form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .ph-form-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .ph-form-field label span {
            color: #c0392b;
        }

        .ph-form-field input[type="text"],
        .ph-form-field input[type="tel"],
        .ph-form-field input[type="date"],
        .ph-form-field select,
        .ph-form-field textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 12px;
            border: 1px solid #d8d8d8;
            border-radius: 6px;
            font-size: 15px;
        }

        .ph-radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .ph-radio-group label {
            font-weight: 400;
        }

        .ph-radio-group small {
            color: #666;
            margin-left: 5px;
        }

        .ph-form-consent {
            margin: 20px 0;
        }

        .ph-form-consent label {
            font-size: 14px;
            line-height: 1.6;
        }

        .ph-form-submit button {
            background: var(--purple, #5b2c83);
            color: #ffffff;
            border: none;
            padding: 13px 25px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .ph-form-submit button:hover {
            opacity: 0.92;
        }

        .ph-form-note {
            margin-top: 15px;
            font-size: 13px;
            color: #666;
        }

        .ph-appointment-success {
            padding: 25px;
            border-left: 4px solid #2e8b57;
            background: #f3faf6;
            border-radius: 8px;
        }

        .ph-appointment-success h2 {
            color: #26734a;
            margin-top: 0;
        }

        .ph-appointment-errors {
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #c0392b;
            background: #fdf5f4;
            border-radius: 8px;
        }

        .ph-appointment-errors strong {
            color: #a93226;
        }

        .ph-appointment-errors li {
            margin-bottom: 5px;
        }

        @media (max-width: 700px) {

            .ph-form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .ph-appointment-form {
                padding: 20px;
            }

        }

        ';

        wp_add_inline_style(
            'ph-appointments',
            $css
        );
    }
}

add_action(
    'wp_enqueue_scripts',
    'ph_appointments_enqueue_styles'
);