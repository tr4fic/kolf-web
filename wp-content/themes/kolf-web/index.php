<?php
/**
 * Univerzální záložní šablona.
 */
get_header();
?>

<section class="kolf-single">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<h1 class="kolf-single__title"><?php the_title(); ?></h1>
		<div class="kolf-single__body"><?php the_content(); ?></div>
	<?php endwhile; else : ?>
		<h1 class="kolf-single__title"><?php esc_html_e( 'Nic tu není', 'kolf' ); ?></h1>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
