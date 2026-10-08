<?php

/**

 * Plugin Name: Prasanthi Hospitals Patients

 * Description: Patient registration and management for Prasanthi Hospitals.

 * Version: 1.0.2

 * Author: Prasanthi Hospitals

 */



if ( ! defined( 'ABSPATH' ) ) {

    exit;

}



define( 'PH_PATIENTS_VERSION', '1.0.2' );



/**

 * ============================================================

 * HELPERS

 * ============================================================

 */



function ph_patients_table() {

    global $wpdb;



    return $wpdb->prefix . 'ph_patients';

}



/**

 * ============================================================

 * CAPABILITY

 * ============================================================

 */



function ph_patients_add_capabilities() {



    $capability = 'ph_manage_patients';



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



function ph_patients_create_table() {



    global $wpdb;



    $table_name = ph_patients_table();



    $charset_collate = $wpdb->get_charset_collate();



    require_once ABSPATH . 'wp-admin/includes/upgrade.php';



    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        patient_reference varchar(30) NOT NULL,
        patient_name varchar(150) NOT NULL,
        mobile varchar(30) NOT NULL,
        gender varchar(20) NULL,
        date_of_birth date NULL,
        preferred_language varchar(20) NULL,
        city varchar(100) NULL,
        address text NULL,
        pin varchar(20) NULL,
        marital_status varchar(30) NULL,
        blood_group varchar(10) NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY patient_reference (patient_reference),
        KEY patient_name (patient_name),
        KEY mobile (mobile),
        KEY date_of_birth (date_of_birth)
    ) {$charset_collate};";



    dbDelta( $sql );



    update_option(

        'ph_patients_db_version',

        PH_PATIENTS_VERSION

    );

}



/**

 * ============================================================

 * ACTIVATION

 * ============================================================

 */



function ph_patients_activate() {



    ph_patients_create_table();



    ph_patients_add_capabilities();

}



register_activation_hook(

    __FILE__,

    'ph_patients_activate'

);



/**

 * ============================================================

 * UPGRADE CHECK

 * ============================================================

 */



function ph_patients_maybe_upgrade() {



    $installed_version = get_option(

        'ph_patients_db_version',

        '0'

    );



    if (

        version_compare(

            $installed_version,

            PH_PATIENTS_VERSION,

            '<'

        )

    ) {



        ph_patients_create_table();



        ph_patients_add_capabilities();



    } else {



        ph_patients_add_capabilities();

    }

}



add_action(

    'plugins_loaded',

    'ph_patients_maybe_upgrade'

);



/**

 * ============================================================

 * PATIENT REFERENCE

 * ============================================================

 */



function ph_patients_generate_reference() {



    global $wpdb;



    $table_name = ph_patients_table();



    $last_reference = $wpdb->get_var(

        "SELECT patient_reference

         FROM {$table_name}

         WHERE patient_reference LIKE 'PH-PAT-%'

         ORDER BY id DESC

         LIMIT 1"

    );



    $next_number = 1;



    if ( $last_reference ) {



        $number = (int) str_replace(

            'PH-PAT-',

            '',

            $last_reference

        );



        if ( $number > 0 ) {

            $next_number = $number + 1;

        }

    }



    do {



        $reference = sprintf(

            'PH-PAT-%06d',

            $next_number

        );



        $exists = $wpdb->get_var(

            $wpdb->prepare(

                "SELECT id

                 FROM {$table_name}

                 WHERE patient_reference = %s

                 LIMIT 1",

                $reference

            )

        );



        if ( $exists ) {

            $next_number++;

        }



    } while ( $exists );



    return $reference;

}



/**

 * ============================================================

 * ADMIN MENU

 * ============================================================

 */



function ph_patients_admin_menu() {



    add_menu_page(

        'Patients',

        'Patients',

        'ph_manage_patients',

        'ph-patients',

        'ph_patients_admin_page',

        'dashicons-id-alt',

        27

    );

}



add_action(

    'admin_menu',

    'ph_patients_admin_menu'

);



/**

 * ============================================================

 * ADMIN STYLES

 * ============================================================

 */



function ph_patients_admin_styles( $hook ) {



    if (

        'toplevel_page_ph-patients' !== $hook

    ) {

        return;

    }



    ?>



    <style>



        .ph-patients-wrap {

            max-width: 1400px;

        }



        .ph-patient-box {

            background: #fff;

            border: 1px solid #dcdcde;

            border-radius: 8px;

            padding: 24px;

            margin-top: 20px;

        }



        .ph-patient-grid {

            display: grid;

            grid-template-columns:

                repeat(2, minmax(250px, 1fr));

            gap: 20px;

        }



        .ph-patient-field {

            margin-bottom: 4px;

        }



        .ph-patient-field label {

            display: block;

            font-weight: 600;

            margin-bottom: 6px;

        }



        .ph-patient-field input,

        .ph-patient-field select,

        .ph-patient-field textarea {

            width: 100%;

            max-width: 100%;

        }



        .ph-patient-full {

            grid-column: 1 / -1;

        }



        .ph-patient-search {

            display: flex;

            gap: 10px;

            align-items: center;

            margin: 20px 0;

        }



        .ph-patient-search input {

            width: 350px;

        }



        .ph-patient-reference {

            font-weight: 600;

        }



        .ph-patient-detail-grid {

            display: grid;

            grid-template-columns:

                repeat(2, minmax(250px, 1fr));

            gap: 20px;

        }



        .ph-patient-detail label {

            display: block;

            font-size: 12px;

            font-weight: 600;

            color: #646970;

            text-transform: uppercase;

            margin-bottom: 5px;

        }



        .ph-patient-detail .value {

            font-size: 14px;

        }



        .ph-success-message {

            background: #edfaef;

            border-left: 4px solid #00a32a;

            padding: 12px 15px;

            margin: 15px 0;

        }



        .ph-error-message {

            background: #fcf0f1;

            border-left: 4px solid #d63638;

            padding: 12px 15px;

            margin: 15px 0;

        }



        @media (max-width: 800px) {



            .ph-patient-grid,

            .ph-patient-detail-grid {

                grid-template-columns: 1fr;

            }



            .ph-patient-full {

                grid-column: auto;

            }



            .ph-patient-search {

                flex-direction: column;

                align-items: flex-start;

            }



            .ph-patient-search input {

                width: 100%;

            }

        }



    </style>



    <?php

}



add_action(

    'admin_enqueue_scripts',

    'ph_patients_admin_styles'

);



/**

 * ============================================================

 * ADMIN ROUTER

 * ============================================================

 */



function ph_patients_admin_page() {



    if (

        ! current_user_can(

            'ph_manage_patients'

        )

    ) {



        wp_die(

            'You do not have permission to manage patients.'

        );

    }



    if (

        isset(

            $_GET['patient_id']

        )

    ) {



        $patient_id = absint(

            $_GET['patient_id']

        );



        if ( $patient_id ) {



            $action =

                isset( $_GET['action'] )

                ? sanitize_key(

                    wp_unslash(

                        $_GET['action']

                    )

                )

                : '';



            if ( 'edit' === $action ) {



                ph_patients_edit_page(

                    $patient_id

                );



            } else {



                ph_patients_detail_page(

                    $patient_id

                );

            }



            return;

        }

    }



    if (

        isset(

            $_GET['action']

        )

        &&

        'add' ===

        sanitize_key(

            wp_unslash(

                $_GET['action']

            )

        )

    ) {



        ph_patients_edit_page();



        return;

    }



    ph_patients_list_page();

}



/**

 * ============================================================

 * PATIENT LIST

 * ============================================================

 */



function ph_patients_list_page() {



    global $wpdb;



    $table_name = ph_patients_table();



    $search =

        isset( $_GET['s'] )

        ? sanitize_text_field(

            wp_unslash(

                $_GET['s']

            )

        )

        : '';



    if ( $search ) {



        $like =

            '%' .

            $wpdb->esc_like(

                $search

            ) .

            '%';



        $patients =

            $wpdb->get_results(

                $wpdb->prepare(

                    "SELECT *

                     FROM {$table_name}

                     WHERE patient_reference LIKE %s

                        OR patient_name LIKE %s

                        OR mobile LIKE %s

                     ORDER BY created_at DESC

                     LIMIT 100",

                    $like,

                    $like,

                    $like

                )

            );



    } else {



        $patients =

            $wpdb->get_results(

                "SELECT *

                 FROM {$table_name}

                 ORDER BY created_at DESC

                 LIMIT 100"

            );

    }



    $add_url = add_query_arg(

        array(

            'page'   => 'ph-patients',

            'action' => 'add',

        ),

        admin_url(

            'admin.php'

        )

    );



    ?>



    <div class="wrap ph-patients-wrap">



        <h1 class="wp-heading-inline">

            Prasanthi Hospitals — Patients

        </h1>



        <a

            href="<?php

            echo esc_url(

                $add_url

            );

            ?>"

            class="page-title-action"

        >

            Add Patient

        </a>



        <hr class="wp-header-end">



        <form

            method="get"

            class="ph-patient-search"

        >



            <input

                type="hidden"

                name="page"

                value="ph-patients"

            >



            <input

                type="search"

                name="s"

                placeholder="Search Patient ID, name or mobile"

                value="<?php

                echo esc_attr(

                    $search

                );

                ?>"

            >



            <button

                type="submit"

                class="button"

            >

                Search

            </button>



            <?php if ( $search ) : ?>



                <a

                    href="<?php

                    echo esc_url(

                        admin_url(

                            'admin.php?page=ph-patients'

                        )

                    );

                    ?>"

                    class="button"

                >

                    Clear

                </a>



            <?php endif; ?>



        </form>



        <table

            class="wp-list-table widefat fixed striped"

        >



            <thead>



                <tr>



                    <th>Patient ID</th>



                    <th>Patient Name</th>



                    <th>Mobile</th>



                    <th>Gender</th>



                    <th>Date of Birth</th>



                    <th>City</th>



                    <th>Created</th>



                </tr>



            </thead>



            <tbody>



            <?php if ( empty( $patients ) ) : ?>



                <tr>



                    <td colspan="7">

                        No patients found.

                    </td>



                </tr>



            <?php else : ?>



                <?php foreach (

                    $patients

                    as $patient

                ) : ?>



                    <?php



                    $detail_url =

                        add_query_arg(

                            array(

                                'page' =>

                                    'ph-patients',



                                'patient_id' =>

                                    (int)

                                    $patient->id,

                            ),

                            admin_url(

                                'admin.php'

                            )

                        );



                    ?>



                    <tr>



                        <td>



                            <a

                                href="<?php

                                echo esc_url(

                                    $detail_url

                                );

                                ?>"

                                class="ph-patient-reference"

                            >



                                <?php

                                echo esc_html(

                                    $patient->patient_reference

                                );

                                ?>



                            </a>



                        </td>



                        <td>

                            <?php

                            echo esc_html(

                                $patient->patient_name

                            );

                            ?>

                        </td>



                        <td>

                            <?php

                            echo esc_html(

                                $patient->mobile

                            );

                            ?>

                        </td>



                        <td>



                            <?php

                            echo ! empty(

                                $patient->gender

                            )

                                ? esc_html(

                                    ucfirst(

                                        $patient->gender

                                    )

                                )

                                : '—';

                            ?>



                        </td>



                        <td>



                            <?php



                            if (

                                ! empty(

                                    $patient->date_of_birth

                                )

                            ) {



                                echo esc_html(

                                    wp_date(

                                        'd M Y',

                                        strtotime(

                                            $patient->date_of_birth

                                        )

                                    )

                                );



                            } else {



                                echo '—';

                            }



                            ?>



                        </td>



                        <td>



                            <?php

                            echo ! empty(

                                $patient->city

                            )

                                ? esc_html(

                                    $patient->city

                                )

                                : '—';

                            ?>



                        </td>



                        <td>



                            <?php

                            echo esc_html(

                                wp_date(

                                    'd M Y g:i A',

                                    strtotime(

                                        $patient->created_at

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



    <?php

}



/**

 * ============================================================

 * ADD / EDIT PATIENT

 * ============================================================

 */



function ph_patients_edit_page(

    $patient_id = 0

) {



    global $wpdb;



    $table_name =

        ph_patients_table();



    $patient = null;



    if ( $patient_id ) {



        $patient =

            $wpdb->get_row(

                $wpdb->prepare(

                    "SELECT *

                     FROM {$table_name}

                     WHERE id = %d

                     LIMIT 1",

                    $patient_id

                )

            );

    }



    $errors = array();



    $saved_patient_id = 0;



    /**

     * --------------------------------------------------------

     * SAVE

     * --------------------------------------------------------

     */



    if (

        isset(

            $_POST['ph_save_patient']

        )

    ) {



        if (

            ! isset(

                $_POST['ph_patient_nonce']

            )

            ||

            ! wp_verify_nonce(

                sanitize_text_field(

                    wp_unslash(

                        $_POST['ph_patient_nonce']

                    )

                ),

                'ph_save_patient'

            )

        ) {



            $errors[] =

                'Security verification failed. Please try again.';



        } else {



            $patient_name =

                isset(

                    $_POST['patient_name']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['patient_name']

                    )

                )

                : '';



            $mobile =

                isset(

                    $_POST['mobile']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['mobile']

                    )

                )

                : '';



            $gender =

                isset(

                    $_POST['gender']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['gender']

                    )

                )

                : '';



            $date_of_birth =

                isset(

                    $_POST['date_of_birth']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['date_of_birth']

                    )

                )

                : '';



            $preferred_language =

                isset(

                    $_POST['preferred_language']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['preferred_language']

                    )

                )

                : '';



            $city =

                isset(

                    $_POST['city']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['city']

                    )

                )

                : '';



            $address =

                isset(

                    $_POST['address']

                )

                ? sanitize_textarea_field(

                    wp_unslash(

                        $_POST['address']

                    )

                )

                : '';



            $pin =

                isset(

                    $_POST['pin']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['pin']

                    )

                )

                : '';



            $marital_status =

                isset(

                    $_POST['marital_status']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['marital_status']

                    )

                )

                : '';



            $blood_group =

                isset(

                    $_POST['blood_group']

                )

                ? sanitize_text_field(

                    wp_unslash(

                        $_POST['blood_group']

                    )

                )

                : '';



            /**

             * Validation

             */



            if ( empty( $patient_name ) ) {



                $errors[] =

                    'Patient name is required.';

            }



            if ( empty( $mobile ) ) {



                $errors[] =

                    'Mobile number is required.';

            }



            if (

                ! preg_match(

                    '/^[0-9+\-\s()]{7,20}$/',

                    $mobile

                )

            ) {



                $errors[] =

                    'Please enter a valid mobile number.';

            }



            if (

                $gender

                &&

                ! in_array(

                    $gender,

                    array(

                        'male',

                        'female',

                        'other',

                    ),

                    true

                )

            ) {



                $errors[] =

                    'Please select a valid gender.';

            }



            if ( $date_of_birth ) {



                $date_object =

                    DateTime::createFromFormat(

                        'Y-m-d',

                        $date_of_birth

                    );



                if (

                    ! $date_object

                    ||

                    $date_object->format(

                        'Y-m-d'

                    ) !== $date_of_birth

                ) {



                    $errors[] =

                        'Please enter a valid date of birth.';

                }

            }



            if (

                $preferred_language

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

                    'Please select a valid preferred language.';

            }



            if (

                $marital_status

                &&

                ! in_array(

                    $marital_status,

                    array(

                        'Single',

                        'Married',

                        'Widowed',

                        'Divorced',

                        'Other',

                    ),

                    true

                )

            ) {



                $errors[] =

                    'Please select a valid marital status.';

            }



            if (

                $blood_group

                &&

                ! in_array(

                    $blood_group,

                    array(

                        'A+',

                        'A-',

                        'B+',

                        'B-',

                        'AB+',

                        'AB-',

                        'O+',

                        'O-',

                        'Unknown',

                    ),

                    true

                )

            ) {



                $errors[] =

                    'Please select a valid blood group.';

            }



            /**

             * Save

             */



            if ( empty( $errors ) ) {



                $now =

                    current_time(

                        'mysql'

                    );



                if ( $patient ) {



                    $updated =

                        $wpdb->update(

                            $table_name,

                            array(

                                'patient_name' =>

                                    $patient_name,



                                'mobile' =>

                                    $mobile,



                                'gender' =>

                                    $gender

                                    ? $gender

                                    : null,



                                'date_of_birth' =>

                                    $date_of_birth

                                    ? $date_of_birth

                                    : null,



                                'preferred_language' =>

                                    $preferred_language

                                    ? $preferred_language

                                    : null,



                                'city' =>

                                    $city

                                    ? $city

                                    : null,



                                'address' =>

                                    $address,



                                'pin' =>

                                    $pin

                                    ? $pin

                                    : null,



                                'marital_status' =>

                                    $marital_status

                                    ? $marital_status

                                    : null,



                                'blood_group' =>

                                    $blood_group

                                    ? $blood_group

                                    : null,



                                'updated_at' =>

                                    $now,

                            ),

                            array(

                                'id' =>

                                    $patient->id,

                            ),

                            array(

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

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



                    if ( false !== $updated ) {



                        $saved_patient_id =

                            (int) $patient->id;



                    } else {



                        $errors[] =

                            'The patient could not be updated. Please try again.';

                    }



                } else {



                    $reference =

                        ph_patients_generate_reference();



                    $inserted =

                        $wpdb->insert(

                            $table_name,

                            array(

                                'patient_reference' =>

                                    $reference,



                                'patient_name' =>

                                    $patient_name,



                                'mobile' =>

                                    $mobile,



                                'gender' =>

                                    $gender

                                    ? $gender

                                    : null,



                                'date_of_birth' =>

                                    $date_of_birth

                                    ? $date_of_birth

                                    : null,



                                'preferred_language' =>

                                    $preferred_language

                                    ? $preferred_language

                                    : null,



                                'city' =>

                                    $city

                                    ? $city

                                    : null,



                                'address' =>

                                    $address,



                                'pin' =>

                                    $pin

                                    ? $pin

                                    : null,



                                'marital_status' =>

                                    $marital_status

                                    ? $marital_status

                                    : null,



                                'blood_group' =>

                                    $blood_group

                                    ? $blood_group

                                    : null,



                                'created_at' =>

                                    $now,



                                'updated_at' =>

                                    $now,

                            ),

                            array(

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                                '%s',

                            )

                        );



                    if ( false !== $inserted ) {



                        $saved_patient_id =

                            (int) $wpdb->insert_id;



                    } else {



                        $errors[] =

                            'The patient could not be created. Please try again.';

                    }

                }

            }

        }

    }



    /**

     * --------------------------------------------------------

     * If saved, load the patient and show the detail page.

     * No redirect is performed.

     * --------------------------------------------------------

     */



    if (

        $saved_patient_id

        &&

        empty( $errors )

    ) {



        ph_patients_detail_page(

            $saved_patient_id,

            $patient

                ? 'updated'

                : 'created'

        );



        return;

    }



    /**

     * --------------------------------------------------------

     * PAGE

     * --------------------------------------------------------

     */



    $page_title =

        $patient

        ? 'Edit Patient'

        : 'Add Patient';



    $back_url =

        admin_url(

            'admin.php?page=ph-patients'

        );



    ?>



    <div class="wrap ph-patients-wrap">



        <p>



            <a href="<?php

            echo esc_url(

                $back_url

            );

            ?>">

                &larr; Back to Patients

            </a>



        </p>



        <h1>

            <?php

            echo esc_html(

                $page_title

            );

            ?>

        </h1>



        <?php if ( ! empty( $errors ) ) : ?>



            <div class="ph-error-message">



                <?php foreach (

                    $errors

                    as $error

                ) : ?>



                    <p>

                        <?php

                        echo esc_html(

                            $error

                        );

                        ?>

                    </p>



                <?php endforeach; ?>



            </div>



        <?php endif; ?>



        <div class="ph-patient-box">



            <form method="post">



                <?php

                wp_nonce_field(

                    'ph_save_patient',

                    'ph_patient_nonce'

                );

                ?>



                <div class="ph-patient-grid">



                    <div class="ph-patient-field">



                        <label>

                            Patient Name *

                        </label>



                        <input

                            type="text"

                            name="patient_name"

                            required

                            value="<?php

                            echo esc_attr(

                                $patient

                                ? $patient->patient_name

                                : (

                                    isset(

                                        $_POST['patient_name']

                                    )

                                        ? wp_unslash(

                                            $_POST['patient_name']

                                        )

                                        : ''

                                )

                            );

                            ?>"

                        >



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Mobile Number *

                        </label>



                        <input

                            type="tel"

                            name="mobile"

                            required

                            value="<?php

                            echo esc_attr(

                                $patient

                                ? $patient->mobile

                                : (

                                    isset(

                                        $_POST['mobile']

                                    )

                                        ? wp_unslash(

                                            $_POST['mobile']

                                        )

                                        : ''

                                )

                            );

                            ?>"

                        >



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Gender

                        </label>



                        <select name="gender">



                            <option value="">

                                Select

                            </option>



                            <?php

                            $current_gender =

                                $patient

                                ? $patient->gender

                                : (

                                    isset(

                                        $_POST['gender']

                                    )

                                        ? sanitize_text_field(

                                            wp_unslash(

                                                $_POST['gender']

                                            )

                                        )

                                        : ''

                                );

                            ?>



                            <option

                                value="male"

                                <?php

                                selected(

                                    $current_gender,

                                    'male'

                                );

                                ?>

                            >

                                Male

                            </option>



                            <option

                                value="female"

                                <?php

                                selected(

                                    $current_gender,

                                    'female'

                                );

                                ?>

                            >

                                Female

                            </option>



                            <option

                                value="other"

                                <?php

                                selected(

                                    $current_gender,

                                    'other'

                                );

                                ?>

                            >

                                Other

                            </option>



                        </select>



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Date of Birth

                        </label>



                        <input

                            type="date"

                            name="date_of_birth"

                            value="<?php

                            echo esc_attr(

                                $patient

                                ? $patient->date_of_birth

                                : (

                                    isset(

                                        $_POST['date_of_birth']

                                    )

                                        ? wp_unslash(

                                            $_POST['date_of_birth']

                                        )

                                        : ''

                                )

                            );

                            ?>"

                        >



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Preferred Language

                        </label>



                        <?php

                        $current_language =

                            $patient

                            ? $patient->preferred_language

                            : (

                                isset(

                                    $_POST['preferred_language']

                                )

                                    ? sanitize_text_field(

                                        wp_unslash(

                                            $_POST['preferred_language']

                                        )

                                    )

                                    : ''

                            );

                        ?>



                        <select

                            name="preferred_language"

                        >



                            <option value="">

                                Select

                            </option>



                            <option

                                value="English"

                                <?php

                                selected(

                                    $current_language,

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

                                    $current_language,

                                    'Telugu'

                                );

                                ?>

                            >

                                Telugu

                            </option>



                        </select>



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            City

                        </label>



                        <input

                            type="text"

                            name="city"

                            value="<?php

                            echo esc_attr(

                                $patient

                                ? $patient->city

                                : (

                                    isset(

                                        $_POST['city']

                                    )

                                        ? wp_unslash(

                                            $_POST['city']

                                        )

                                        : ''

                                )

                            );

                            ?>"

                        >



                    </div>



                    <div class="ph-patient-field ph-patient-full">



                        <label>

                            Address

                        </label>



                        <textarea

                            name="address"

                            rows="4"

                        ><?php

                        echo esc_textarea(

                            $patient

                            ? $patient->address

                            : (

                                isset(

                                    $_POST['address']

                                )

                                    ? wp_unslash(

                                        $_POST['address']

                                    )

                                    : ''

                            )

                        );

                        ?></textarea>



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            PIN

                        </label>



                        <input

                            type="text"

                            name="pin"

                            value="<?php

                            echo esc_attr(

                                $patient

                                ? $patient->pin

                                : (

                                    isset(

                                        $_POST['pin']

                                    )

                                        ? wp_unslash(

                                            $_POST['pin']

                                        )

                                        : ''

                                )

                            );

                            ?>"

                        >



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Marital Status

                        </label>



                        <?php

                        $marital_options =

                            array(

                                'Single',

                                'Married',

                                'Widowed',

                                'Divorced',

                                'Other',

                            );



                        $current_marital =

                            $patient

                            ? $patient->marital_status

                            : (

                                isset(

                                    $_POST['marital_status']

                                )

                                    ? sanitize_text_field(

                                        wp_unslash(

                                            $_POST['marital_status']

                                        )

                                    )

                                    : ''

                            );

                        ?>



                        <select

                            name="marital_status"

                        >



                            <option value="">

                                Select

                            </option>



                            <?php foreach (

                                $marital_options

                                as $option

                            ) : ?>



                                <option

                                    value="<?php

                                    echo esc_attr(

                                        $option

                                    );

                                    ?>"

                                    <?php

                                    selected(

                                        $current_marital,

                                        $option

                                    );

                                    ?>

                                >

                                    <?php

                                    echo esc_html(

                                        $option

                                    );

                                    ?>

                                </option>



                            <?php endforeach; ?>



                        </select>



                    </div>



                    <div class="ph-patient-field">



                        <label>

                            Blood Group

                        </label>



                        <?php

                        $blood_groups =

                            array(

                                'A+',

                                'A-',

                                'B+',

                                'B-',

                                'AB+',

                                'AB-',

                                'O+',

                                'O-',

                                'Unknown',

                            );



                        $current_blood =

                            $patient

                            ? $patient->blood_group

                            : (

                                isset(

                                    $_POST['blood_group']

                                )

                                    ? sanitize_text_field(

                                        wp_unslash(

                                            $_POST['blood_group']

                                        )

                                    )

                                    : ''

                            );

                        ?>



                        <select

                            name="blood_group"

                        >



                            <option value="">

                                Select

                            </option>



                            <?php foreach (

                                $blood_groups

                                as $blood_group_option

                            ) : ?>



                                <option

                                    value="<?php

                                    echo esc_attr(

                                        $blood_group_option

                                    );

                                    ?>"

                                    <?php

                                    selected(

                                        $current_blood,

                                        $blood_group_option

                                    );

                                    ?>

                                >

                                    <?php

                                    echo esc_html(

                                        $blood_group_option

                                    );

                                    ?>

                                </option>



                            <?php endforeach; ?>



                        </select>



                    </div>



                </div>



                <p>



                    <button

                        type="submit"

                        name="ph_save_patient"

                        class="button button-primary button-large"

                    >

                        <?php

                        echo $patient

                            ? 'Update Patient'

                            : 'Create Patient';

                        ?>

                    </button>



                </p>



            </form>



        </div>



    </div>



    <?php

}



/**

 * ============================================================

 * PATIENT DETAIL

 * ============================================================

 */



function ph_patients_detail_page(

    $patient_id,

    $message = ''

) {



    global $wpdb;



    $table_name =

        ph_patients_table();



    $patient =

        $wpdb->get_row(

            $wpdb->prepare(

                "SELECT *

                 FROM {$table_name}

                 WHERE id = %d

                 LIMIT 1",

                $patient_id

            )

        );



    if ( ! $patient ) {



        echo '<div class="wrap">';

        echo '<h1>Patient Not Found</h1>';

        echo '<p>The patient could not be found.</p>';

        echo '</div>';



        return;

    }



    $edit_url =

        add_query_arg(

            array(

                'page' =>

                    'ph-patients',



                'action' =>

                    'edit',



                'patient_id' =>

                    (int)

                    $patient->id,

            ),

            admin_url(

                'admin.php'

            )

        );



    ?>



    <div class="wrap ph-patients-wrap">



        <?php if ( 'created' === $message ) : ?>



            <div class="ph-success-message">



                <strong>

                    Patient created successfully.

                </strong>



            </div>



        <?php elseif ( 'updated' === $message ) : ?>



            <div class="ph-success-message">



                <strong>

                    Patient updated successfully.

                </strong>



            </div>



        <?php endif; ?>



        <p>



            <a

                href="<?php

                echo esc_url(

                    admin_url(

                        'admin.php?page=ph-patients'

                    )

                );

                ?>"

            >

                &larr; Back to Patients

            </a>



        </p>



        <h1 class="wp-heading-inline">



            <?php

            echo esc_html(

                $patient->patient_name

            );

            ?>



        </h1>



        <a

            href="<?php

            echo esc_url(

                $edit_url

            );

            ?>"

            class="page-title-action"

        >

            Edit Patient

        </a>



        <div class="ph-patient-box">



            <h2>

                Patient Information

            </h2>



            <div class="ph-patient-detail-grid">



                <div class="ph-patient-detail">



                    <label>

                        Patient ID

                    </label>



                    <div class="value">



                        <strong>

                            <?php

                            echo esc_html(

                                $patient->patient_reference

                            );

                            ?>

                        </strong>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Patient Name

                    </label>



                    <div class="value">

                        <?php

                        echo esc_html(

                            $patient->patient_name

                        );

                        ?>

                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Mobile

                    </label>



                    <div class="value">

                        <?php

                        echo esc_html(

                            $patient->mobile

                        );

                        ?>

                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Gender

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->gender

                        )

                            ? esc_html(

                                ucfirst(

                                    $patient->gender

                                )

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Date of Birth

                    </label>



                    <div class="value">



                        <?php



                        if (

                            ! empty(

                                $patient->date_of_birth

                            )

                        ) {



                            echo esc_html(

                                wp_date(

                                    'd M Y',

                                    strtotime(

                                        $patient->date_of_birth

                                    )

                                )

                            );



                        } else {



                            echo '—';

                        }



                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Preferred Language

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->preferred_language

                        )

                            ? esc_html(

                                $patient->preferred_language

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        City

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->city

                        )

                            ? esc_html(

                                $patient->city

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        PIN

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->pin

                        )

                            ? esc_html(

                                $patient->pin

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Marital Status

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->marital_status

                        )

                            ? esc_html(

                                $patient->marital_status

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Blood Group

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->blood_group

                        )

                            ? esc_html(

                                $patient->blood_group

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail ph-patient-full">



                    <label>

                        Address

                    </label>



                    <div class="value">



                        <?php

                        echo ! empty(

                            $patient->address

                        )

                            ? nl2br(

                                esc_html(

                                    $patient->address

                                )

                            )

                            : '—';

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Created

                    </label>



                    <div class="value">



                        <?php

                        echo esc_html(

                            wp_date(

                                'd M Y g:i A',

                                strtotime(

                                    $patient->created_at

                                )

                            )

                        );

                        ?>



                    </div>



                </div>



                <div class="ph-patient-detail">



                    <label>

                        Last Updated

                    </label>



                    <div class="value">



                        <?php

                        echo esc_html(

                            wp_date(

                                'd M Y g:i A',

                                strtotime(

                                    $patient->updated_at

                                )

                            )

                        );

                        ?>



                    </div>



                </div>



            </div>



        </div>



    </div>



    <?php

}
