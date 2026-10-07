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
        <span>Trusted healthcare.</span>
      </h1>

      <p>
        A family-run modern medicine hospital in Governorpet, Vijayawada,
        centred on personal care with an integrative approach that also
        includes Ayurvedic consultations.
      </p>


      <div class="hero-actions">

        <a
          class="button"
          href="tel:8956969895"
        >
          Call for Appointment
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
  <div class="container"><strong>Appointments & enquiries:</strong> <a href="tel:8956969895" style="color:#fff;">895 6969 895</a> &nbsp; · &nbsp; WhatsApp available</div>
</div>

<section id="about" class="section">
  <div class="container">
    <div class="section-heading">
      <h2>About Prasanthi Hospitals</h2>
      <p>A hospital rooted in personal care.</p>
    </div>
    <div class="about-grid">
      <div>
        <p>Founded by <strong>Dr. C.N. Murthy, BAMS</strong>, in Governorpet, Vijayawada, Prasanthi Hospitals continues family physician consultations alongside <strong>Dr. C.S.K. Aditya, M.D. (General Medicine)</strong>.</p>
        <p>Modern medical care is at the centre of the hospital's approach. Integrative care also includes Ayurvedic consultations with <strong>Dr. Nishteshwar, M.D. (Ayurveda)</strong>.</p>
      </div>
      <div class="card">
        <h3>Our approach</h3>
        <p>Facilities support outpatient consultations, daycare, acute medical inpatient care and selected surgical work. Laboratory and pharmacy support day-to-day care.</p>
        <p>When ICU care or treatment beyond available facilities is needed, referral to an appropriate hospital is arranged.</p>
      </div>
    </div>
  </div>
</section>

<section id="doctors" class="section alt">
  <div class="container">

    <div class="section-heading">
      <h2>Our Doctors</h2>
      <p>Experienced doctors providing personal, accessible care.</p>
    </div>

    <?php
    $doctors = new WP_Query(array(
      'post_type'      => 'doctor',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
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
      <p>Core hospital services available at Prasanthi Hospitals.</p>
    </div>
    <div class="services-grid">
      <div class="service-card"><h3>Outpatient Consultations</h3><p>General medicine and family physician consultations, with Ayurvedic consultations as part of the integrative approach.</p></div>
      <div class="service-card"><h3>Daycare</h3><p>Care, treatment and observation with same-day admission and discharge when recommended by the doctor.</p></div>
      <div class="service-card"><h3>Medical Inpatient Care</h3><p>Acute medical admissions within available facilities. ICU or care beyond facilities is referred appropriately.</p></div>
      <div class="service-card"><h3>Surgical Care</h3><p>Selected procedures within available facilities after consultation. Contact the hospital regarding surgeon and procedure availability.</p></div>
      <div class="service-card"><h3>Laboratory Services</h3><p>Routine tests in-house, with specialised tests arranged through external laboratories where required.</p></div>
      <div class="service-card"><h3>On-site Pharmacy</h3><p>Pharmacy support for day-to-day patient care and prescriptions.</p></div>
    </div>
  </div>
</section>

<section id="contact" class="section alt">
  <div class="container">
    <div class="section-heading">
      <h2>Contact & Location</h2>
      <p>Contact us for appointments, doctor availability and service enquiries.</p>
    </div>
    <div class="contact-grid">
      <div class="card">
        <h3>Prasanthi Hospitals</h3>
        <ul class="contact-list">
          <li><strong>Appointments:</strong> <a href="tel:8956969895">895 6969 895</a></li>
          <li><strong>Landline:</strong> 0866-7960268</li>
          <li><strong>WhatsApp:</strong> <a href="https://wa.me/918956969895" target="_blank" rel="noopener">Chat on WhatsApp</a></li>
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
