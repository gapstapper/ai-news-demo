<?php
// Minimal theme supports
add_theme_support('post-thumbnails');
add_theme_support('title-tag');

// Enqueue the modern block template skip link 
// (replaces deprecated the_block_template_skip_link)
add_action('wp_enqueue_scripts', function () {
  if ( function_exists('wp_enqueue_block_template_skip_link') ) {
    wp_enqueue_block_template_skip_link();
  }
});
 