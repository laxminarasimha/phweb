<?php get_header(); ?>

<section class="hero">

  <div class="container hero-grid">

    <!-- Hero Content -->

    <div class="hero-content">

      <div class="eyebrow">
        Prasanthi Hospitals · Vijayawada
      </div>

      <h1>
        Personal care.<br>
        <span>For you and your family.</span>
      </h1>

      <p>
        At our family-run hospital in Governorpet, Vijayawada, you can talk
        to a doctor about what’s troubling you and ask about the next steps.
        We provide modern medical care, with Ayurvedic consultations also available.
      </p>


      <div class="hero-actions">

        <a
          class="button"
          href="tel:8956969895"
        >
          Call for an Appointment
        </a>

        <a
          class="button secondary"
          href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
          target="_blank"
          rel="noopener"
        >
          WhatsApp Us
        </a>

      </div>


      <div class="hero-opd">

        <div>
          <strong>Morning OPD</strong>
          <span>10:30 AM – 2:00 PM</span>
        </div>

        <div>
          <strong>Evening OPD</strong>
          <span>6:00 PM – 9:00 PM</span>
        </div>

      </div>

    </div>


    <!-- Hero Image -->

    <div class="hero-image-card">

      <?php
      $custom_logo_id = get_theme_mod('custom_logo');

      if ($custom_logo_id) :
        echo wp_get_attachment_image(
          $custom_logo_id,
          'large',
          false,
          array(
            'class' => 'hero-hospital-image',
            'alt'   => 'Prasanthi Hospitals'
          )
        );
      endif;
      ?>

      <h2>Prasanthi Hospitals</h2>

      <p>Multi Speciality Centre</p>

      <span>Governorpet · Vijayawada</span>

    </div>

  </div>

</section>

<div class="notice">
  <div class="container"><strong>Appointments & doctor availability:</strong> <a href="tel:8956969895" style="color:#fff;">895 6969 895</a> &nbsp; · &nbsp; Call or WhatsApp before your visit</div>
</div>

<section id="about" class="section">
  <div class="container">
    <div class="section-heading">
      <h2>About Prasanthi Hospitals</h2>
      <p>A family-run hospital in Governorpet.</p>
    </div>
    <div class="about-grid">
      <div>
        <p>Prasanthi Hospitals was founded by <strong>Dr. C.N. Murthy, BAMS</strong>. He provides family physician consultations alongside <strong>Dr. C.S.K. Aditya, M.D. (General Medicine)</strong>, who oversees general medicine consultations, medical daycare and inpatient care.</p>
        <p>Ayurvedic consultations with <strong>Dr. Nishteshwar, M.D. (Ayurveda)</strong> are also available. Please call to confirm his consultation time; he is not available for evening OPD.</p>
      </div>
      <div class="card">
        <h3>Our approach</h3>
        <p>Depending on your doctor's assessment, you may need an outpatient consultation, a daycare visit or a medical admission. Routine laboratory testing, an on-site pharmacy and selected surgical care support the hospital's work.</p>
        <p>If you need ICU care or treatment beyond our facilities, we arrange a referral to a hospital with the appropriate services.</p>
      </div>
    </div>
  </div>
</section>

<section id="doctors" class="section alt">
  <div class="container">

    <div class="section-heading">
      <h2>Our Doctors</h2>
      <p>
        Meet the doctors at Prasanthi Hospitals and find out about the
        consultations they provide. Please confirm your doctor's availability
        before visiting.
      </p>
    </div>

    <?php
    $doctors = new WP_Query(array(
      'post_type'      => 'doctor',
      'post_status'    => 'publish',
      'posts_per_page' => 3,
      'orderby'        => 'rand',
    ));
    ?>

    <?php if ($doctors->have_posts()) : ?>

      <div class="doctors-grid">

        <?php while ($doctors->have_posts()) : $doctors->the_post(); ?>

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


              <h3><?php the_title(); ?></h3>
				
			

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

    <?php endif; ?>

    <?php wp_reset_postdata(); ?>

  </div>
</section>

<section id="services" class="section">
  <div class="container">
    <div class="section-heading">
      <h2>Our Services</h2>
      <p>Find out about the care available and what to confirm before your visit.</p>
    </div>
    <div class="services-grid">
      <div class="service-card"><h3>Outpatient Consultations</h3><p>General medicine, family physician and gynaecology consultations, with Ayurvedic consultations also available. Call to confirm the doctor and consultation time.</p></div>
      <div class="service-card"><h3>Daycare</h3><p>Treatment and observation without an overnight stay, when your doctor recommends admission and discharge on the same day.</p></div>
      <div class="service-card"><h3>Medical Inpatient Care</h3><p>Medical admissions within the hospital's facilities. When ICU care or other services are needed, we arrange an appropriate referral.</p></div>
      <div class="service-card"><h3>Surgical Care</h3><p>Selected procedures following a surgical consultation. Please call to confirm the surgeon's availability and the procedure you wish to discuss.</p></div>
      <div class="service-card"><h3>Laboratory Services</h3><p>Routine tests are performed in-house. Specialised testing is arranged through external laboratories when needed.</p></div>
      <div class="service-card"><h3>On-site Pharmacy</h3><p>Our on-site pharmacy supports the medicines prescribed for hospital patients.</p></div>
    </div>
  </div>
</section>

<section id="contact" class="section alt">
  <div class="container">
    <div class="section-heading">
      <h2>Contact & Location</h2>
      <p>Call or WhatsApp to arrange a visit, check your doctor's availability or ask about a hospital service.</p>
    </div>
    <div class="contact-grid">
      <div class="card">
        <h3>Prasanthi Hospitals</h3>
        <ul class="contact-list">
          <li><strong>Appointments:</strong> <a href="tel:8956969895">895 6969 895</a></li>
          <li><strong>Landline:</strong> 0866-7960268</li>
          <li><strong>WhatsApp:</strong> <a href="https://wa.me/918956969895" target="_blank" rel="noopener">Chat with the hospital</a></li>
          <li><strong>Email:</strong> <a href="mailto:info@prasanthihospitals.com">info@prasanthihospitals.com</a></li>
          <li><strong>Address:</strong> Ksheerasagar Hospital Road, beside N.T.R. Sahakara Bhavan, Governorpet, Vijayawada – 520002, Andhra Pradesh.</li>
        </ul>
      </div>
      <div class="map-wrap">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3825.34288235212!2d80.62442417469858!3d16.50877848423618!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a35fab1403de667%3A0x22655d921ff62c2!2sPrasanthi%20Hospitals!5e0!3m2!1sen!2sin!4v1791276981192!5m2!1sen!2sin" width="600" height="450" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
