<?php get_header(); ?>
<main>
  <h1>Search Article Archive</h1>
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article>
      <h2><?php the_title(); ?></h2>
      <div><?php the_content(); ?></div>
    </article>
  <?php endwhile; else: ?>
    <p>No content.</p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
