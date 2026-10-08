<?php get_header(); ?>

<main class="container profile-page">

<?php while (have_posts()) : the_post(); ?>

    <?php
    $doctor_id = get_the_ID();

    $qualification = get_post_meta(
        $doctor_id,
        '_doctor_qualification',
        true
    );

    $role = get_post_meta(
        $doctor_id,
        '_doctor_role',
        true
    );

    $short_intro = get_post_meta(
        $doctor_id,
        '_doctor_short_intro',
        true
    );

    $consultation = get_post_meta(
        $doctor_id,
        '_doctor_consultation',
        true
    );

    /*
     * Doctor availability fields
     */

    $doctor_type = get_post_meta(
        $doctor_id,
        '_doctor_type',
        true
    );

    $availability_status = get_post_meta(
        $doctor_id,
        '_doctor_availability_status',
        true
    );

    $next_available_date = get_post_meta(
        $doctor_id,
        '_doctor_next_available_date',
        true
    );

    $availability_note = get_post_meta(
        $doctor_id,
        '_doctor_availability_note',
        true
    );


    /*
     * Human-readable Doctor Type
     */

    $doctor_type_labels = array(
        'regular_opd'         => 'Regular OPD',
        'consultant'          => 'Consultant',
        'visiting_consultant' => 'Visiting Consultant'
    );

    $doctor_type_label = isset(
        $doctor_type_labels[$doctor_type]
    )
        ? $doctor_type_labels[$doctor_type]
        : '';


    /*
     * Human-readable Availability Status
     */

    $availability_labels = array(
        'available'        => 'Available',
        'unavailable'      => 'Currently Unavailable',
        'appointment_only' => 'Appointment Only'
    );

    $availability_label = isset(
        $availability_labels[$availability_status]
    )
        ? $availability_labels[$availability_status]
        : '';


    /*
     * Availability CSS class
     */

    $availability_class = '';

    if ($availability_status === 'available') {

        $availability_class = 'availability-available';

    } elseif ($availability_status === 'unavailable') {

        $availability_class = 'availability-unavailable';

    } elseif ($availability_status === 'appointment_only') {

        $availability_class = 'availability-appointment';

    }


    /*
     * Format next available date
     */

    $formatted_next_date = '';

    if ($next_available_date) {

        $timestamp = strtotime($next_available_date);

        if ($timestamp) {

            $formatted_next_date = date(
                'j F Y',
                $timestamp
            );

        }

    }


    /*
     * Doctor-specific WhatsApp message
     */

    $doctor_name = get_the_title();

    $whatsapp_message =
        'Hello, I would like to enquire about an appointment with ' .
        $doctor_name .
        ' at Prasanthi Hospitals.';

    $whatsapp_url =
        'https://wa.me/918956969895?text=' .
        rawurlencode($whatsapp_message);
    ?>

    <?php if (get_post_type() === 'doctor') : ?>

        <div class="profile-image">

            <?php if (has_post_thumbnail()) : ?>

                <?php
                the_post_thumbnail(
                    'large',
                    array(
                        'class' => 'doctor-profile-image',
                        'alt'   => get_the_title()
                    )
                );
                ?>

            <?php endif; ?>

        </div>


        <div class="profile-content">

            <?php if ($role) : ?>

                <p class="profile-meta">
                    <?php echo esc_html($role); ?>
                </p>

            <?php endif; ?>


            <h1><?php the_title(); ?></h1>


            <?php if ($qualification) : ?>

                <p class="profile-qualification">
                    <strong>
                        <?php echo esc_html($qualification); ?>
                    </strong>
                </p>

            <?php endif; ?>


            <?php if ($short_intro) : ?>

                <p class="profile-intro">
                    <?php echo esc_html($short_intro); ?>
                </p>

            <?php endif; ?>


            <?php if ($doctor_type_label) : ?>

                <p class="doctor-type-label">
                    <?php echo esc_html($doctor_type_label); ?>
                </p>

            <?php endif; ?>


            <?php if ($availability_label) : ?>

                <div class="doctor-availability <?php echo esc_attr($availability_class); ?>">

                    <strong>
                        <?php echo esc_html($availability_label); ?>
                    </strong>

                    <?php if ($formatted_next_date) : ?>

                        <span>
                            Next available:
                            <?php echo esc_html($formatted_next_date); ?>
                        </span>

                    <?php endif; ?>

                    <?php if ($availability_note) : ?>

                        <p>
                            <?php echo esc_html($availability_note); ?>
                        </p>

                    <?php endif; ?>

                </div>

            <?php endif; ?>


            <?php if ($consultation) : ?>

                <div class="consultation-box">

                    <h3>Consultation Timing</h3>

                    <p>
                        <?php echo esc_html($consultation); ?>
                    </p>

                </div>

            <?php endif; ?>


            <hr>


            <div class="doctor-professional-profile">

                <h2>Professional Profile</h2>

                <?php the_content(); ?>

            </div>


            <p class="doctor-enquiry">

                <a
                    class="button"
                    href="<?php echo esc_url($whatsapp_url); ?>"
                    target="_blank"
                    rel="noopener"
                >
                    WhatsApp to Book / Enquire
                </a>

            </p>

        </div>


    <?php else : ?>

        <!-- Normal WordPress posts/pages -->

        <article class="entry-content">

            <div class="section-heading">
                <h1><?php the_title(); ?></h1>
            </div>

            <?php the_content(); ?>

        </article>

    <?php endif; ?>

<?php endwhile; ?>

</main>

<?php get_footer(); ?>