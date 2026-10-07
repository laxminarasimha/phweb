<?php get_header(); ?>

<main class="site-page">

  <?php while (have_posts()) : the_post(); ?>

    <section class="page-header">
      <div class="container">

        <h1><?php the_title(); ?></h1>

        <?php if (has_excerpt()) : ?>
          <p><?php echo esc_html(get_the_excerpt()); ?></p>
        <?php endif; ?>

      </div>
    </section>


    <?php if (is_page('services')) : ?>

      <!-- =====================================================
           SERVICES PAGE
           ===================================================== -->

      <section class="section">
        <div class="container">

          <div class="section-heading">
            <h2>Our Services</h2>
            <p>
              Comprehensive healthcare services focused on accessible,
              compassionate and patient-centred care.
            </p>
          </div>

          <div class="services-grid">

            <article class="service-card">
              <div class="service-icon">🩺</div>
              <h3>General Medicine</h3>
              <p>
                Comprehensive medical consultations for common illnesses,
                infections, chronic conditions and general health concerns.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Book / Enquire
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🏥</div>
              <h3>Inpatient Care</h3>
              <p>
                Personalised inpatient care with medical supervision,
                nursing support and attention to each patient's needs.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Book / Enquire
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">💊</div>
              <h3>Medical Daycare</h3>
              <p>
                Daycare medical services for patients requiring observation,
                treatment or procedures without overnight admission.
              </p>
              <a href="<?php echo esc_url(home_url('/#contact')); ?>">
                Book / Enquire
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🌿</div>
              <h3>Ayurveda</h3>
              <p>
                Ayurvedic consultations supporting a holistic and
                integrative approach to health and well-being.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Book / Enquire
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🔬</div>
              <h3>Diagnostics</h3>
              <p>
                Diagnostic support to help doctors evaluate symptoms,
                identify health concerns and plan appropriate treatment.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Book / Enquire
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">❤️</div>
              <h3>Preventive Healthcare</h3>
              <p>
                Guidance focused on preventive health, lifestyle,
                early identification of health concerns and overall wellness.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Book / Enquire
              </a>
            </article>

          </div>

        </div>
      </section>


      <section class="section alt">
        <div class="container">

          <div class="section-heading">
            <h2>Why Choose Prasanthi Hospitals?</h2>
          </div>

          <div class="about-grid">

            <div>
              <h3>Personalised Care</h3>
              <p>
                We focus on understanding each patient's needs and providing
                care that is personal, accessible and compassionate.
              </p>
            </div>

            <div>
              <h3>Experienced Doctors</h3>
              <p>
                Our doctors bring experience across general medicine and
                Ayurveda, supporting a comprehensive approach to patient care.
              </p>
            </div>

            <div>
              <h3>Patient-Centred Approach</h3>
              <p>
                From consultation to treatment and follow-up, we aim to make
                the healthcare experience clear, comfortable and supportive.
              </p>
            </div>

          </div>

        </div>
      </section>


    <?php elseif (is_page('about-us')) : ?>

      <!-- =====================================================
           ABOUT US PAGE
           ===================================================== -->

      <section class="section about-introduction">
        <div class="container">

          <div class="about-page-intro">

            <div class="about-page-content">

              <p class="doctor-role">ABOUT PRASANTHI HOSPITALS</p>

              <h2>A Personal Approach to Healthcare</h2>

              <p>
                Prasanthi Hospitals is committed to providing accessible,
                compassionate and personalised healthcare for individuals
                and families.
              </p>

              <p>
                Our approach is centred on understanding each patient's
                health concerns, providing appropriate medical guidance and
                supporting patients throughout their healthcare journey.
              </p>

              <p>
                With experienced doctors providing general medicine and
                Ayurvedic consultations, Prasanthi Hospitals brings together
                different approaches to patient care while keeping the
                individual patient's needs at the centre.
              </p>

            </div>

            <div class="about-highlight">

              <div class="about-highlight-box">
                <span class="about-highlight-number">30+</span>
                <span class="about-highlight-label">
                  Years of Medical Experience
                </span>
              </div>

              <div class="about-highlight-box">
                <span class="about-highlight-number">3</span>
                <span class="about-highlight-label">
                  Doctors &amp; Medical Practitioners
                </span>
              </div>

              <div class="about-highlight-box">
                <span class="about-highlight-number">1</span>
                <span class="about-highlight-label">
                  Patient-Centred Approach
                </span>
              </div>

            </div>

          </div>

        </div>
      </section>


      <section class="section alt">
        <div class="container">

          <div class="section-heading">
            <h2>Our Approach to Care</h2>
            <p>
              Healthcare that focuses on the person, not just the condition.
            </p>
          </div>

          <div class="about-values-grid">

            <article class="about-value-card">
              <div class="about-value-icon">❤️</div>
              <h3>Compassionate Care</h3>
              <p>
                We believe patients deserve to be treated with respect,
                empathy and understanding at every stage of their care.
              </p>
            </article>

            <article class="about-value-card">
              <div class="about-value-icon">🩺</div>
              <h3>Experienced Medical Care</h3>
              <p>
                Our doctors focus on careful evaluation, appropriate medical
                guidance and personalised treatment based on individual needs.
              </p>
            </article>

            <article class="about-value-card">
              <div class="about-value-icon">🌿</div>
              <h3>Integrative Approach</h3>
              <p>
                General medicine and Ayurveda consultations are available,
                supporting an approach that considers the patient's overall
                health and well-being.
              </p>
            </article>

          </div>

        </div>
      </section>


      <section class="section">
        <div class="container">

          <div class="about-story-grid">

            <div>
              <p class="doctor-role">OUR COMMITMENT</p>

              <h2>Care That Builds Trust</h2>

              <p>
                Good healthcare begins with listening. We aim to understand
                our patients' concerns, explain their care clearly and help
                them make informed decisions about their health.
              </p>

              <p>
                Our focus is on creating a comfortable and supportive
                healthcare experience for patients and their families.
              </p>

              <p>
                From consultation and diagnosis to treatment and follow-up,
                we strive to provide care that is personal, responsible and
                centred around the patient's needs.
              </p>
            </div>

            <div class="about-story-box">

              <h3>Our Focus</h3>

              <ul>
                <li>Personalised medical consultations</li>
                <li>Accessible healthcare</li>
                <li>Clear communication with patients</li>
                <li>Comprehensive general medical care</li>
                <li>Support for overall health and well-being</li>
              </ul>

            </div>

          </div>

        </div>
      </section>


      <section class="section alt">
        <div class="container">

          <div class="section-heading">
            <h2>Meet Our Doctors</h2>
            <p>
              Experienced medical practitioners committed to providing
              personal and accessible care.
            </p>

            <a
              class="button"
              href="<?php echo esc_url(home_url('/doctor/')); ?>"
            >
              View Our Doctors
            </a>
          </div>

        </div>
      </section>


      <section class="section">
        <div class="container">

          <div class="about-cta">

            <h2>Need Medical Assistance?</h2>

            <p>
              Contact Prasanthi Hospitals to enquire about consultations
              and available healthcare services.
            </p>

            <a
              class="button"
              href="<?php echo esc_url(home_url('/contact-us/')); ?>"
            >
              Contact Us
            </a>

          </div>

        </div>
      </section>
    <?php elseif (is_page('contact-us')) : ?>

      <!-- =====================================================
           CONTACT US PAGE
           ===================================================== -->

      <section class="section contact-page-section">
        <div class="container">

          <div class="section-heading">
            <h2>Contact Prasanthi Hospitals</h2>
            <p>
              For appointments, consultations and general enquiries,
              please contact us directly.
            </p>
          </div>


          <div class="contact-page-grid">

            <!-- Contact Details -->

            <div class="contact-page-details">

              <div class="contact-detail-card">

                <div class="contact-detail-icon">📞</div>

                <div>
                  <h3>Appointments &amp; Enquiries</h3>

                  <p>
                    <a href="tel:+918956969895">
                      895 6969 895
                    </a>
                  </p>
                </div>

              </div>


              <div class="contact-detail-card">

                <div class="contact-detail-icon">💬</div>

                <div>
                  <h3>WhatsApp</h3>

                  <p>
                    <a
                      href="https://wa.me/918956969895"
                      target="_blank"
                      rel="noopener"
                    >
                      Chat with us on WhatsApp
                    </a>
                  </p>
                </div>

              </div>


              <div class="contact-detail-card">

                <div class="contact-detail-icon">✉️</div>

                <div>
                  <h3>Email</h3>

                  <p>
                    <a href="mailto:info@prasanthihospitals.com">
                      info@prasanthihospitals.com
                    </a>
                  </p>
                </div>

              </div>


              <div class="contact-detail-card">

                <div class="contact-detail-icon">☎️</div>

                <div>
                  <h3>Landline</h3>

                  <p>
                    <a href="tel:08667960268">
                      0866-7960268
                    </a>
                  </p>
                </div>

              </div>


              <div class="contact-detail-card">

                <div class="contact-detail-icon">📍</div>

                <div>
                  <h3>Hospital Address</h3>

                  <p>
                    Ksheerasagar Hospital Road,<br>
                    Beside N.T.R. Sahakara Bhavan,<br>
                    Governorpet, Vijayawada
                  </p>
                </div>

              </div>

            </div>


            <!-- WhatsApp CTA -->

            <div class="contact-whatsapp-card">

              <div class="contact-whatsapp-icon">💬</div>

              <h2>Need an Appointment?</h2>

              <p>
                For appointments and enquiries, the quickest way to reach
                us is through WhatsApp.
              </p>

              <a
                class="button"
                href="https://wa.me/918956969895"
                target="_blank"
                rel="noopener"
              >
                Chat on WhatsApp
              </a>

              <p class="contact-whatsapp-number">
                895 6969 895
              </p>

            </div>

          </div>

        </div>
      </section>


      <!-- OPD Timings -->

      <section class="section alt">
        <div class="container">

          <div class="section-heading">
            <h2>OPD Timings</h2>
            <p>
              Please contact the hospital before visiting to confirm
              the doctor's availability.
            </p>
          </div>


          <div class="opd-timing-card">

            <div>
              <span class="opd-label">Morning OPD</span>
              <strong>10:30 AM – 2:00 PM</strong>
            </div>

            <div>
              <span class="opd-label">Evening OPD</span>
              <strong>6:00 PM – 9:00 PM</strong>
            </div>

          </div>

        </div>
      </section>


      <!-- Location -->

      <section class="section contact-location-section">
        <div class="container">

          <div class="section-heading">
            <h2>Find Us</h2>
            <p>
              Visit Prasanthi Hospitals at Governorpet, Vijayawada.
            </p>
          </div>


          <div class="contact-map">

         <iframe
  src="https://www.google.com/maps?q=Prasanthi%20Hospitals%2C%20Vijayawada&output=embed"
  width="600"
  height="450"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade"
  title="Prasanthi Hospitals Location">
</iframe>

          </div>

        </div>
      </section>


      <!-- Final CTA -->

      <section class="section alt">
        <div class="container">

          <div class="about-cta">

            <h2>We're Here to Help</h2>

            <p>
              For appointments and healthcare enquiries, contact us
              directly through WhatsApp or phone.
            </p>

            <a
              class="button"
              href="https://wa.me/918956969895"
              target="_blank"
              rel="noopener"
            >
              WhatsApp Us
            </a>

          </div>

        </div>
      </section>

    <?php else : ?>

      <!-- =====================================================
           NORMAL WORDPRESS PAGE
           ===================================================== -->

      <section class="section">
        <div class="container">

          <div class="entry-content">
            <?php the_content(); ?>
          </div>

        </div>
      </section>

    <?php endif; ?>

  <?php endwhile; ?>

</main>

<?php get_footer(); ?>