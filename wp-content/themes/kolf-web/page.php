<?php
/**
 * Obecná stránka (WP admin > Stránky) — pro obsah, který nezapadá do úvodní stránky.
 */
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="kolf-single">
		<h1 class="kolf-single__title"><?php the_title(); ?></h1>
		<div class="kolf-single__body"><?php the_content(); ?></div>
	</section>
	<?php
endwhile;

get_footer();
