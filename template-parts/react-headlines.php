<?php
$posts = get_posts([
  'post_type'      => 'article',
  'post_status'    => 'publish',
  'posts_per_page' => 10,
  'orderby'        => 'date',
  'order'          => 'DESC',
  'fields'         => 'ids',
]);

$initial = array_map(function ($id) {
  return [
    'id'    => $id,
    'title' => ['rendered' => get_the_title($id)],
    'link'  => get_permalink($id),
    'date'  => get_post_field('post_date', $id),
  ];
}, $posts);

$api = rest_url('wp/v2/articles');
?>
<div
  id="ai-news-headlines-root"
  data-initial="<?php echo esc_attr(wp_json_encode($initial)); ?>"
  data-api="<?php echo esc_url($api); ?>"
>
  <noscript>Please enable JavaScript to view headlines.</noscript>
</div>
