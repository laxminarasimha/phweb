<?php get_header(); ?>
<main class="container entry-content">
<?php if (have_posts()) : while (have_posts()) : the_post(); the_content(); endwhile; else : ?>
<p>No content found.</p>
<?php endif; ?>
</main>
<?php get_footer(); ?>
