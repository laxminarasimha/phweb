<?php
/**
 * Doctor Archive
 * Prasanthi Hospitals
 */

get_header();
?>

<main class="doctor-archive">

  <section class="page-header">
    <div class="container">

      <h1>Our Doctors</h1>

      <p>
        Experienced doctors providing personal, accessible care.
      </p>

    </div>
  </section>


  <section class="section">
    <div class="container">

      <?php if (have_posts()) : ?>

        <div class="doctors-grid">

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

            /*
             * Doctor availability
             */

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


            /*
             * Availability labels
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
            ?>

            <article class="doctor-card">

              <div class="doctor-card-photo">

                <?php if (has_post_thumbnail()) : ?>

                  <?php
                  the_post_thumbnail(
                    'large',
                    array(
                      'alt' => get_the_title()
                    )
                  );
                  ?>

                <?php endif; ?>

              </div>


              <div class="doctor-card-content">

                <?php if ($role) : ?>

                  <p class="doctor-role">
                    <?php echo esc_html($role); ?>
                  </p>

                <?php endif; ?>


                <h3>
                  <?php the_title(); ?>
                </h3>


                <?php if ($qualification) : ?>

                  <p class="qualification">
                    <?php echo esc_html($qualification); ?>
                  </p>

                <?php endif; ?>


                <?php if ($availability_label) : ?>

                  <div class="doctor-card-availability <?php echo esc_attr($availability_class); ?>">

                    <strong>
                      <?php echo esc_html($availability_label); ?>
                    </strong>

                    <?php if ($formatted_next_date) : ?>

                      <span>
                        Next available:
                        <?php echo esc_html($formatted_next_date); ?>
                      </span>

                    <?php endif; ?>

                  </div>

                <?php endif; ?>


                <?php if ($short_intro) : ?>

                  <p>
                    <?php echo esc_html($short_intro); ?>
                  </p>

                <?php endif; ?>


                <a
                  class="view-profile"
                  href="<?php echo esc_url(get_permalink()); ?>"
                >
                  View Profile
                </a>

              </div>

            </article>

          <?php endwhile; ?>

        </div>

      <?php else : ?>

        <p>No doctors are currently available.</p>

      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>