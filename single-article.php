<?php
get_header();
?>
<main>
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article>
      <h1><?php echo esc_html( get_the_title() ); ?></h1>
      <div><?php the_content(); ?></div>

      <?php if ( has_excerpt() ) : ?>
        <p><em><?php echo esc_html( get_the_excerpt() ); ?></em></p>
      <?php endif; ?>

      <?php
      $post_tags = get_the_tags();
      if ( $post_tags ) {
        echo '<p>Tags: ';
        $first = true;
        foreach ( $post_tags as $t ) {
          if ( ! $first ) echo ', ';
          echo esc_html( $t->name );
          $first = false;
        }
        echo '</p>';
      }
      ?>
    </article>
  <?php endwhile; else: ?>
    <p>Not found.</p>
  <?php endif; ?>
</main>
<?php
get_footer();
