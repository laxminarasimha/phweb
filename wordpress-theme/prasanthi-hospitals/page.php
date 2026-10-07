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
              From a consultation to daycare or a medical admission, the care you need depends on your doctor's assessment. Call ahead about doctor, test and procedure availability.
            </p>
          </div>

          <div class="services-grid">

            <article class="service-card">
              <div class="service-icon">🩺</div>
              <h3>Outpatient Consultations</h3>
              <p>
                General medicine, family physician and gynaecology consultations are available. Call to confirm the doctor and consultation time.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Enquire About This Service
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🏥</div>
              <h3>Medical Inpatient Care</h3>
              <p>
                Medical admissions provide care within the hospital's facilities. Patients who need ICU care or services beyond these facilities are referred to an appropriate hospital.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Enquire About This Service
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">💊</div>
              <h3>Medical Daycare &amp; Pharmacy</h3>
              <p>
                Treatment and observation with admission and discharge on the same day, when recommended by your doctor. Our on-site pharmacy supports medicines prescribed for hospital patients.
              </p>
              <a href="<?php echo esc_url(home_url('/#contact')); ?>">
                Enquire About This Service
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🌿</div>
              <h3>Ayurvedic Consultations</h3>
              <p>
                Modern medicine remains central to our care. Ayurvedic consultations are also available with Dr. Nishteshwar, M.D. (Ayurveda). He is not available for evening OPD; please call to confirm his consultation time.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Enquire About This Service
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">🔬</div>
              <h3>Laboratory Services</h3>
              <p>
                Routine tests are performed in-house. Specialised testing is arranged through external laboratories when needed. Call ahead for test availability and preparation instructions.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Enquire About This Service
              </a>
            </article>

            <article class="service-card">
              <div class="service-icon">❤️</div>
              <h3>Further Care &amp; Referral</h3>
              <p>
                Selected surgical care follows assessment by the attending surgeon. Call to confirm the surgeon and procedure availability. We arrange a referral when the care needed is beyond our facilities.
              </p>
              <a   href="https://wa.me/918956969895?text=Hello%2C%20I%20would%20like%20to%20enquire%20about%20an%20appointment%20at%20Prasanthi%20Hospitals."
  target="_blank"
  rel="noopener">
                Enquire About This Service
              </a>
            </article>

          </div>

        </div>
      </section>


      <section class="section alt">
        <div class="container">

          <div class="section-heading">
            <h2>What to expect from your care.</h2>
          </div>

          <div class="about-grid">

            <div>
              <h3>A conversation about your needs.</h3>
              <p>
                Discuss your symptoms, medical history and concerns with your doctor. Bring earlier prescriptions and reports to help explain your health history.
              </p>
            </div>

            <div>
              <h3>Advice on the next step.</h3>
              <p>
                Your doctor will advise whether you need tests, outpatient treatment, daycare, admission or assessment by another specialist.
              </p>
            </div>

            <div>
              <h3>Referral when needed.</h3>
              <p>
                When you need ICU care or treatment beyond the hospital's facilities, we arrange a referral to an appropriate hospital.
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

              <h2>A family-run hospital, focused on your care.</h2>

              <p>
                Prasanthi Hospitals is a family-run hospital in Governorpet, Vijayawada, founded by Dr. C.N. Murthy, BAMS. Modern medical care is at the centre of the hospital's work.
              </p>

              <p>
                You may be visiting us for a new health concern, a follow-up or care that needs a short stay. Discuss your concerns with your doctor and ask about the next steps, whether that means treatment during your visit, daycare or a medical admission.
              </p>

              <p>
                Ayurvedic consultations are also available as part of our integrative approach. Contact the hospital to confirm the doctor and consultation time before visiting.
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
                <span class="about-highlight-number">5</span>
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
              Tell us what is troubling you, ask questions and discuss the next steps with your doctor.
            </p>
          </div>

          <div class="about-values-grid">

            <article class="about-value-card">
              <div class="about-value-icon">❤️</div>
              <h3>Space to discuss your concerns.</h3>
              <p>
                Your symptoms, medical history and questions matter. Share what has changed and bring previous prescriptions or reports that may help your consultation.
              </p>
            </article>

            <article class="about-value-card">
              <div class="about-value-icon">🩺</div>
              <h3>Care guided by medical assessment.</h3>
              <p>
                Your doctor assesses your needs and advises on consultation, tests, treatment or admission. We refer patients when the care needed is beyond our facilities.
              </p>
            </article>

            <article class="about-value-card">
              <div class="about-value-icon">🌿</div>
              <h3>Ayurvedic consultations.</h3>
              <p>
                Modern medicine remains central to the hospital's care. Ayurvedic consultations are also available with Dr. Nishteshwar. Please call to confirm his consultation time; he is not available for evening OPD.
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

              <h2>Helping you understand your care.</h2>

              <p>
                Good care begins with a conversation. Tell your doctor about your concerns and ask about the care being recommended.
              </p>

              <p>
                Our aim is to help you understand the next steps, whether that means treatment during an outpatient visit, further testing, daycare or a medical admission.
              </p>

              <p>
                If you need care elsewhere, the hospital arranges an appropriate referral. Bring your prescriptions and medical reports when you return for a consultation.
              </p>
            </div>

            <div class="about-story-box">

              <h3>Our Focus</h3>

              <ul>
                <li>Medical consultations based on your concerns</li>
                <li>Clear discussion of the recommended care</li>
                <li>Information to prepare for tests or a visit</li>
                <li>Daycare and medical admission when advised</li>
                <li>Referral for care beyond our facilities</li>
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
              Read about our doctors and the consultations they provide at Prasanthi Hospitals.
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

            <h2>Planning a visit?</h2>

            <p>
              Contact the hospital to check your doctor's availability and ask about the services you may need.
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
              Call or WhatsApp to arrange a consultation, check your doctor's availability or ask about a test or hospital service.
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
                      Chat with the hospital
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
                    Alternate official email: PrashantiHospitals1985@gmail.com
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
                    Governorpet, Vijayawada – 520002, Andhra Pradesh, India
                  </p>
                </div>

              </div>

            </div>


            <!-- WhatsApp CTA -->

            <div class="contact-whatsapp-card">

              <div class="contact-whatsapp-icon">💬</div>

              <h2>Need an Appointment?</h2>

              <p>
                Call or message the hospital with the doctor you wish to see. Confirm the consultation time with the team before travelling.
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
              These are the hospital's general OPD hours. Please check individual doctor availability before your visit. Dr. Nishteshwar is not available for evening OPD. Please call to confirm his consultation time and other specialist appointments.
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
              Use the map to plan your route. The hospital is beside N.T.R. Sahakara Bhavan on Ksheerasagar Hospital Road, Governorpet.
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
              Have a question about a test or an upcoming visit? Call or WhatsApp the hospital team before you set out.
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