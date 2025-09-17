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


// Enqueue built React bundle (after npm run build). main-BH2fKHtS.js
add_action('wp_enqueue_scripts', function () {
  $theme_uri  = get_template_directory_uri();
  $theme_path = get_template_directory();

  // Update JS filename to your current hashed output if needed
  wp_enqueue_script(
    'ai-headlines',
    $theme_uri . '/assets/headlines/assets/main-BH2fKHtS.js',
    [],
    null,
    true
  );

  // Enqueue CSS only if Vite emitted one
  $css_rel  = '/assets/headlines/assets/main.css';
  $css_path = $theme_path . $css_rel;
  if (file_exists($css_path)) {
    wp_enqueue_style('ai-headlines', $theme_uri . $css_rel, [], null);
  }
});

// Shortcode to render the mount where you want.
add_shortcode('ai_headlines', function () {
  ob_start();
  get_template_part('template-parts/react-headlines');
  return ob_get_clean();
});


// Snippet: append near the end of wp-content/themes/ai-news-hub/functions.php
require_once __DIR__ . '/inc/ai-research.php';

