<?php

/*
 * Template Name: Gateway Dashboard Template
 * description: Page template without sidebar
 */

if (! defined( 'ABSPATH' ) ){
    exit;
}

// Detect shortcode if exists and apply styles
function gw_shortcode_scripts()
{
    wp_enqueue_style('child-style', get_stylesheet_directory_uri(). '/gw-styles.css');
}
add_action('wp_enqueue_scripts', 'gw_shortcode_scripts');

do_action('gw_frontend_render');