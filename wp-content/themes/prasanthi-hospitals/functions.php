<?php
/**
 * Prasanthi Hospitals theme functions.
 */

function prasanthi_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 200,
        'width'       => 500,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'prasanthi-hospitals'),
    ));
}
add_action('after_setup_theme', 'prasanthi_setup');

function prasanthi_assets() {
    wp_enqueue_style('prasanthi-style', get_stylesheet_uri(), array(), '1.0.2');
}
add_action('wp_enqueue_scripts', 'prasanthi_assets');

function prasanthi_widgets() {
    register_sidebar(array(
        'name'          => __('Footer Widget Area', 'prasanthi-hospitals'),
        'id'            => 'footer-1',
        'description'   => __('Optional footer widgets.', 'prasanthi-hospitals'),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'prasanthi_widgets');
