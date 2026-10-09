<?php
/**
 * Plugin Name: Prasanthi Hospitals Core
 * Description: Core content model for Prasanthi Hospitals, including editable doctor profiles.
 * Version: 1.1.0
 * Author: Prasanthi Hospitals
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) exit;


/*
|--------------------------------------------------------------------------
| Register Doctor Custom Post Type
|--------------------------------------------------------------------------
*/

function prasanthi_register_doctor_cpt() {

    register_post_type('doctor', array(
        'labels' => array(
            'name'          => 'Doctors',
            'singular_name' => 'Doctor',
            'menu_name'     => 'Doctors',
            'add_new'       => 'Add Doctor',
            'add_new_item'  => 'Add New Doctor',
            'edit_item'     => 'Edit Doctor',
            'view_item'     => 'View Doctor',
            'search_items'  => 'Search Doctors',
            'not_found'     => 'No doctors found'
        ),

        'public'             => true,
        'show_in_rest'       => true,
        'show_in_menu'       => 'prasanthi-hospitals',
        'menu_icon'          => 'dashicons-id',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'page-attributes',
            'excerpt'
        ),
        'has_archive'        => true,
        'rewrite'            => array(
            'slug'       => 'doctor',
            'with_front' => false
        ),
        'publicly_queryable' => true
    ));

}

add_action('init', 'prasanthi_register_doctor_cpt');

/*
|--------------------------------------------------------------------------
| Prasanthi Hospitals Admin Menu and Dashboard
|--------------------------------------------------------------------------
| The shared parent is registered here once. Feature plugins add their own
| submenu pages beneath this slug. Existing page slugs remain unchanged.
*/
function prasanthi_hospitals_admin_menu() {
    add_menu_page(
        'Prasanthi Hospitals',
        'Prasanthi Hospitals',
        'read',
        'prasanthi-hospitals',
        'prasanthi_hospitals_dashboard_page',
        'dashicons-heart',
        25
    );
}
add_action( 'admin_menu', 'prasanthi_hospitals_admin_menu', 5 );

function prasanthi_hospitals_dashboard_page() {
    if ( ! current_user_can( 'read' ) ) {
        wp_die( 'You do not have permission to view this page.' );
    }

    global $wpdb;
    $patients_table     = $wpdb->prefix . 'ph_patients';
    $appointments_table = $wpdb->prefix . 'ph_appointments';
    $visits_table       = $wpdb->prefix . 'ph_visits';

    $count_table = static function ( $table ) use ( $wpdb ) {
        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        if ( $exists !== $table ) {
            return null;
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `" . esc_sql( $table ) . "`" );
    };

    $patients_count     = $count_table( $patients_table );
    $appointments_count = $count_table( $appointments_table );
    $visits_count       = $count_table( $visits_table );
    $doctors_count      = (int) wp_count_posts( 'doctor' )->publish;
    ?>
    <div class="wrap">
        <h1>Prasanthi Hospitals</h1>
        <p>Hospital administration dashboard. Choose a module below to continue.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;max-width:1100px;margin-top:22px;">
            <?php if ( current_user_can( 'ph_manage_patients' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-patients' ) ); ?>" style="display:block;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px;text-decoration:none;color:#1d2327;">
                    <strong style="font-size:16px;">Patients</strong><div style="font-size:28px;font-weight:700;margin:10px 0;"><?php echo null === $patients_count ? '—' : esc_html( number_format_i18n( $patients_count ) ); ?></div><span>Register and manage patient records</span>
                </a>
            <?php endif; ?>
            <?php if ( current_user_can( 'ph_manage_appointments' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-appointments' ) ); ?>" style="display:block;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px;text-decoration:none;color:#1d2327;">
                    <strong style="font-size:16px;">Appointments</strong><div style="font-size:28px;font-weight:700;margin:10px 0;"><?php echo null === $appointments_count ? '—' : esc_html( number_format_i18n( $appointments_count ) ); ?></div><span>Review requests and manage bookings</span>
                </a>
            <?php endif; ?>
            <?php if ( current_user_can( 'ph_manage_visits' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-visits' ) ); ?>" style="display:block;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px;text-decoration:none;color:#1d2327;">
                    <strong style="font-size:16px;">Visits</strong><div style="font-size:28px;font-weight:700;margin:10px 0;"><?php echo null === $visits_count ? '—' : esc_html( number_format_i18n( $visits_count ) ); ?></div><span>Manage outpatient visits</span>
                </a>
            <?php endif; ?>
            <?php if ( current_user_can( 'edit_posts' ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=doctor' ) ); ?>" style="display:block;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px;text-decoration:none;color:#1d2327;">
                    <strong style="font-size:16px;">Doctors</strong><div style="font-size:28px;font-weight:700;margin:10px 0;"><?php echo esc_html( number_format_i18n( $doctors_count ) ); ?></div><span>Maintain doctor profiles</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}




/*
|--------------------------------------------------------------------------
| Doctor Meta Box
|--------------------------------------------------------------------------
*/

function prasanthi_doctor_meta_boxes() {

    add_meta_box(
        'prasanthi_doctor_details',
        'Doctor Details',
        'prasanthi_doctor_details_box',
        'doctor',
        'normal',
        'high'
    );

}

add_action('add_meta_boxes', 'prasanthi_doctor_meta_boxes');


/*
|--------------------------------------------------------------------------
| Doctor Details Form
|--------------------------------------------------------------------------
*/

function prasanthi_doctor_details_box($post) {

    wp_nonce_field(
        'prasanthi_save_doctor',
        'prasanthi_doctor_nonce'
    );

    /*
     * Existing fields
     */

    $qualification = get_post_meta(
        $post->ID,
        '_doctor_qualification',
        true
    );

    $role = get_post_meta(
        $post->ID,
        '_doctor_role',
        true
    );

    $short_intro = get_post_meta(
        $post->ID,
        '_doctor_short_intro',
        true
    );

    $consultation = get_post_meta(
        $post->ID,
        '_doctor_consultation',
        true
    );


    /*
     * New fields
     */

    $doctor_type = get_post_meta(
        $post->ID,
        '_doctor_type',
        true
    );

    $availability_status = get_post_meta(
        $post->ID,
        '_doctor_availability_status',
        true
    );

    $next_available_date = get_post_meta(
        $post->ID,
        '_doctor_next_available_date',
        true
    );

    $availability_note = get_post_meta(
        $post->ID,
        '_doctor_availability_note',
        true
    );


    /*
     * Defaults for existing doctors
     */

    if (!$doctor_type) {
        $doctor_type = 'regular_opd';
    }

    if (!$availability_status) {
        $availability_status = 'available';
    }


    /*
     * Existing profile fields
     */

    echo '<h3 style="margin-top:0;">Basic Information</h3>';

    echo '<p>';
    echo '<label><strong>Qualification</strong></label><br>';
    echo '<input type="text"
        name="doctor_qualification"
        value="' . esc_attr($qualification) . '"
        style="width:100%;">';
    echo '</p>';


    echo '<p>';
    echo '<label><strong>Role / Speciality</strong></label><br>';
    echo '<input type="text"
        name="doctor_role"
        value="' . esc_attr($role) . '"
        style="width:100%;">';
    echo '</p>';


    echo '<p>';
    echo '<label><strong>Short introduction</strong></label><br>';
    echo '<textarea
        name="doctor_short_intro"
        rows="4"
        style="width:100%;">' .
        esc_textarea($short_intro) .
        '</textarea>';
    echo '</p>';


    echo '<p>';
    echo '<label><strong>Consultation timing</strong></label><br>';
    echo '<input type="text"
        name="doctor_consultation"
        value="' . esc_attr($consultation) . '"
        style="width:100%;">';
    echo '</p>';


    /*
     * Doctor type
     */

    echo '<hr style="margin:25px 0;">';

    echo '<h3>Doctor Availability</h3>';

    echo '<p>';
    echo '<label><strong>Doctor Type</strong></label><br>';

    echo '<select
        name="doctor_type"
        style="width:100%; max-width:400px;">';

    $doctor_types = array(
        'regular_opd'       => 'Regular OPD',
        'consultant'        => 'Consultant',
        'visiting_consultant' => 'Visiting Consultant'
    );

    foreach ($doctor_types as $value => $label) {

        echo '<option value="' . esc_attr($value) . '" ' .
            selected($doctor_type, $value, false) .
            '>' .
            esc_html($label) .
            '</option>';

    }

    echo '</select>';
    echo '</p>';


    /*
     * Availability status
     */

    echo '<p>';
    echo '<label><strong>Availability Status</strong></label><br>';

    echo '<select
        name="doctor_availability_status"
        style="width:100%; max-width:400px;">';

    $availability_statuses = array(
        'available'          => 'Available',
        'unavailable'        => 'Unavailable',
        'appointment_only'   => 'Appointment Only'
    );

    foreach ($availability_statuses as $value => $label) {

        echo '<option value="' . esc_attr($value) . '" ' .
            selected($availability_status, $value, false) .
            '>' .
            esc_html($label) .
            '</option>';

    }

    echo '</select>';
    echo '</p>';


    /*
     * Next available date
     */

    echo '<p>';
    echo '<label><strong>Next Available Date</strong></label><br>';

    echo '<input
        type="date"
        name="doctor_next_available_date"
        value="' . esc_attr($next_available_date) . '"
        style="width:100%; max-width:400px;">';

    echo '<br>';
    echo '<span style="color:#666;">
        Use this for visiting or appointment-based doctors when a specific upcoming date is known.
    </span>';

    echo '</p>';


    /*
     * Availability note
     */

    echo '<p>';
    echo '<label><strong>Availability Note</strong></label><br>';

    echo '<textarea
        name="doctor_availability_note"
        rows="3"
        style="width:100%;">' .
        esc_textarea($availability_note) .
        '</textarea>';

    echo '<br>';
    echo '<span style="color:#666;">
        Example: Currently on leave. Please contact the hospital for the next available appointment.
    </span>';

    echo '</p>';


    /*
     * Professional profile information
     */

    echo '<hr style="margin:25px 0;">';

    echo '<p>';
    echo '<strong>Detailed professional profile:</strong>
    Use the main WordPress editor above for education, experience,
    expertise, achievements, awards, memberships and other profile content.
    Use the Featured Image box for the doctor photo.';
    echo '</p>';

}


/*
|--------------------------------------------------------------------------
| Save Doctor Data
|--------------------------------------------------------------------------
*/

function prasanthi_save_doctor($post_id) {

    if (
        !isset($_POST['prasanthi_doctor_nonce']) ||
        !wp_verify_nonce(
            $_POST['prasanthi_doctor_nonce'],
            'prasanthi_save_doctor'
        )
    ) {
        return;
    }


    if (
        defined('DOING_AUTOSAVE') &&
        DOING_AUTOSAVE
    ) {
        return;
    }


    if (
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }


    /*
     * Existing text fields
     */

    $text_fields = array(
        'qualification',
        'role',
        'short_intro',
        'consultation'
    );


    foreach ($text_fields as $key) {

        if (isset($_POST['doctor_' . $key])) {

            update_post_meta(
                $post_id,
                '_doctor_' . $key,
                sanitize_textarea_field(
                    $_POST['doctor_' . $key]
                )
            );

        }

    }


    /*
     * Doctor type
     */

    if (isset($_POST['doctor_type'])) {

        $allowed_types = array(
            'regular_opd',
            'consultant',
            'visiting_consultant'
        );

        $doctor_type = sanitize_key(
            $_POST['doctor_type']
        );

        if (in_array($doctor_type, $allowed_types, true)) {

            update_post_meta(
                $post_id,
                '_doctor_type',
                $doctor_type
            );

        }

    }


    /*
     * Availability status
     */

    if (isset($_POST['doctor_availability_status'])) {

        $allowed_statuses = array(
            'available',
            'unavailable',
            'appointment_only'
        );

        $availability_status = sanitize_key(
            $_POST['doctor_availability_status']
        );

        if (
            in_array(
                $availability_status,
                $allowed_statuses,
                true
            )
        ) {

            update_post_meta(
                $post_id,
                '_doctor_availability_status',
                $availability_status
            );

        }

    }


    /*
     * Next available date
     */

    if (isset($_POST['doctor_next_available_date'])) {

        $next_available_date = sanitize_text_field(
            $_POST['doctor_next_available_date']
        );

        update_post_meta(
            $post_id,
            '_doctor_next_available_date',
            $next_available_date
        );

    }


    /*
     * Availability note
     */

    if (isset($_POST['doctor_availability_note'])) {

        update_post_meta(
            $post_id,
            '_doctor_availability_note',
            sanitize_textarea_field(
                $_POST['doctor_availability_note']
            )
        );

    }

}

add_action(
    'save_post_doctor',
    'prasanthi_save_doctor'
);


/*
|--------------------------------------------------------------------------
| Seed Initial Doctors
|--------------------------------------------------------------------------
*/

function prasanthi_seed_doctors() {

    if (get_option('prasanthi_doctors_seeded')) {
        return;
    }


    $doctors = array(

        array(
            'title'         => 'Dr. C.N. Murthy',
            'slug'          => 'dr-cn-murthy',
            'qualification' => 'BAMS',
            'role'          => 'Founder · Family Physician',
            'short_intro'   => 'Founded Prasanthi Hospitals as a family-run multi-speciality centre. Provides family physician consultations and works alongside Dr. C.S.K. Aditya in the hospital’s personal and integrative approach to patient care.',
            'consultation'  => 'Please contact the hospital for current consultation timings.',
            'content'       => '<p>Founder of Prasanthi Hospitals and family physician.</p><h2>Professional Profile</h2><p>Detailed education, experience, achievements, awards, memberships and other professional information will be added here.</p>',
            'order'         => 1
        ),

        array(
            'title'         => 'Dr. C.S.K. Aditya',
            'slug'          => 'dr-csk-aditya',
            'qualification' => 'M.D. (General Medicine)',
            'role'          => 'General Physician',
            'short_intro'   => 'Provides general medicine consultations and oversees medical daycare and inpatient care. Works alongside Dr. C.N. Murthy in day-to-day patient care.',
            'consultation'  => 'Please contact the hospital for current consultation timings.',
            'content'       => '<p>General physician providing general medicine consultations and overseeing medical daycare and inpatient care.</p><h2>Professional Profile</h2><p>Detailed education, experience, achievements, awards, memberships and other professional information will be added here.</p>',
            'order'         => 2
        ),

        array(
            'title'         => 'Dr. Nishteshwar',
            'slug'          => 'dr-nishteshwar',
            'qualification' => 'M.D. (Ayurveda)',
            'role'          => 'Ayurveda',
            'short_intro'   => 'Provides Ayurvedic outpatient consultations as part of the hospital’s integrative approach. Not available for evening OPD; contact the hospital to confirm consultation time.',
            'consultation'  => 'Not available for evening OPD. Please contact the hospital to confirm consultation time.',
            'content'       => '<p>Ayurvedic physician providing outpatient consultations as part of the hospital’s integrative approach.</p><h2>Professional Profile</h2><p>Detailed education, experience, achievements, awards, memberships and other professional information will be added here.</p>',
            'order'         => 3
        )

    );


    foreach ($doctors as $d) {

        if (
            get_page_by_path(
                $d['slug'],
                OBJECT,
                'doctor'
            )
        ) {
            continue;
        }


        $id = wp_insert_post(
            array(
                'post_type'    => 'doctor',
                'post_status'  => 'publish',
                'post_title'   => $d['title'],
                'post_name'    => $d['slug'],
                'post_content' => $d['content'],
                'menu_order'   => $d['order']
            )
        );


        if (
            $id &&
            !is_wp_error($id)
        ) {

            update_post_meta(
                $id,
                '_doctor_qualification',
                $d['qualification']
            );

            update_post_meta(
                $id,
                '_doctor_role',
                $d['role']
            );

            update_post_meta(
                $id,
                '_doctor_short_intro',
                $d['short_intro']
            );

            update_post_meta(
                $id,
                '_doctor_consultation',
                $d['consultation']
            );

        }

    }


    update_option(
        'prasanthi_doctors_seeded',
        1
    );

    flush_rewrite_rules();

}


register_activation_hook(
    __FILE__,
    'prasanthi_seed_doctors'
);


/*
|--------------------------------------------------------------------------
| Plugin Deactivation
|--------------------------------------------------------------------------
*/

register_deactivation_hook(
    __FILE__,
    function() {
        flush_rewrite_rules();
    }
);


/**
 * Prasanthi Hospitals - WordPress Login Branding
 */

/**
 * Add hospital branding to the WordPress login screen.
 */
function prasanthi_hospital_login_branding() {
    $logo_url = content_url( '/uploads/2026/10/cropped-logo.png' );
    ?>
    <style>
        body.login {
            background: #f5f3fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px 16px;
            box-sizing: border-box;
        }

        body.login #login {
            width: 100%;
            max-width: 360px;
            padding: 0;
            margin: 0;
        }

        body.login h1 a {
            background-image: url('<?php echo esc_url( $logo_url ); ?>');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            width: 100%;
            height: 110px;
            margin: 0 auto 24px;
        }

        body.login #loginform,
        body.login #lostpasswordform,
        body.login #registerform {
            background: #ffffff;
            border: 1px solid #e8e2f0;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 8px 28px rgba(70, 40, 100, 0.10);
        }

        body.login label {
            color: #43345c;
            font-weight: 500;
        }

        body.login input[type="text"],
        body.login input[type="password"],
        body.login input[type="email"] {
            border: 1px solid #d8cfe5;
            border-radius: 7px;
            padding: 8px 10px;
            min-height: 42px;
            box-shadow: none;
        }

        body.login input[type="text"]:focus,
        body.login input[type="password"]:focus,
        body.login input[type="email"]:focus {
            border-color: #6b3fa0;
            box-shadow: 0 0 0 1px #6b3fa0;
            outline: none;
        }

        
body.login .wp-core-ui .button-primary {
    background: #6b3fa0 !important;
    border-color: #6b3fa0 !important;
    color: #ffffff !important;
    border-radius: 7px;
    padding: 4px 18px;
    min-height: 40px;
    text-shadow: none;
    box-shadow: none;
}

body.login .wp-core-ui .button-primary:hover,
body.login .wp-core-ui .button-primary:focus {
    background: #512d7d !important;
    border-color: #512d7d !important;
    color: #ffffff !important;
}


        body.login .wp-core-ui .button-primary:hover,
        body.login .wp-core-ui .button-primary:focus {
            background: #512d7d;
            border-color: #512d7d;
        }

        body.login a {
            color: #6b3fa0;
        }

        body.login a:hover {
            color: #e47b36;
        }

        body.login #backtoblog,
        body.login #nav {
            text-align: center;
        }

        body.login #backtoblog {
            margin-top: 18px;
        }

        body.login #login_error,
        body.login .message,
        body.login .success {
            border-left-color: #e47b36;
            border-radius: 5px;
        }

        @media screen and (max-width: 480px) {
            body.login {
                align-items: flex-start;
                padding-top: 36px;
            }

            body.login #loginform,
            body.login #lostpasswordform,
            body.login #registerform {
                padding: 22px;
            }

            body.login h1 a {
                height: 90px;
                margin-bottom: 18px;
            }
        }
    </style>
    <?php
}
add_action( 'login_enqueue_scripts', 'prasanthi_hospital_login_branding' );

/**
 * Replace the WordPress logo link and hover title.
 */
function prasanthi_hospital_login_logo_url() {
    return home_url( '/' );
}
add_filter( 'login_headerurl', 'prasanthi_hospital_login_logo_url' );

function prasanthi_hospital_login_logo_title() {
    return 'Prasanthi Hospitals';
}
add_filter( 'login_headertext', 'prasanthi_hospital_login_logo_title' );
