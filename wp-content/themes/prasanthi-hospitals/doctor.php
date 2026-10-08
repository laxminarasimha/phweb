<?php
/**
 * Single Doctor Profile Template
 * Prasanthi Hospitals
 */

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        $doctor_id = get_the_ID();

        // Doctor details
        $qualification = get_post_meta($doctor_id, '_doctor_qualification', true);
        $role          = get_post_meta($doctor_id, '_doctor_role', true);
        $short_intro   = get_post_meta($doctor_id, '_doctor_short_intro', true);
        $consultation  = get_post_meta($doctor_id, '_doctor_consultation', true);
        ?>

        <main class="container profile-page">

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

                    <?php
                    the_content();
                    ?>

                </div>


                <p class="doctor-enquiry">

                    <a
                        class="button"
                        href="<?php echo esc_url(home_url('/#contact')); ?>"
                    >
                        Book / Enquire
                    </a>

                </p>

            </div>

        </main>

        <?php

    endwhile;

endif;

get_footer();
?>