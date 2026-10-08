<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="topbar">
  <div class="container">
    <span>Appointments & Enquiries: <a href="tel:8956969895">895 6969 895</a></span>
    <span><a href="https://wa.me/918956969895" target="_blank" rel="noopener">WhatsApp</a> &nbsp;|&nbsp; <a href="mailto:info@prasanthihospitals.com">info@prasanthihospitals.com</a></span>
  </div>
</div>

<header class="site-header">
  <div class="container header-inner">
 <div class="site-branding">

  <a
    class="site-logo-link"
    href="<?php echo esc_url(home_url('/')); ?>"
    aria-label="Prasanthi Hospitals Home"
  >
    <?php
    if (has_custom_logo()) {
      the_custom_logo();
    }
    ?>
  </a>

  <div class="site-brand-text">

    <a
      class="site-title-link"
      href="<?php echo esc_url(home_url('/')); ?>"
    >
      <span class="site-title">
        Prasanthi Hospitals
      </span>
    </a>

    <span class="site-tagline">
      MULTI SPECIALITY CENTRE
    </span>

  </div>

</div>

    <nav class="main-navigation" aria-label="Primary Navigation">
      <?php
      if (has_nav_menu('primary')) {
          wp_nav_menu(array('theme_location' => 'primary', 'container' => false));
      } else {
          echo '<ul>';
          echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#about')) . '">About</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#doctors')) . '">Doctors</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#services')) . '">Services</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#contact')) . '">Contact</a></li>';
          echo '</ul>';
      }
      ?>
    </nav>
  </div>
</header>
