<?php
/**
 * Detail jednoho oddělení / ambulance.
 */
get_header();

while ( have_posts() ) :
	the_post();
	$floor   = get_post_meta( get_the_ID(), 'kolf_floor', true );
	$contact = get_post_meta( get_the_ID(), 'kolf_contact', true );
	?>
	<section class="kolf-single">
		<p class="kolf-single__eyebrow"><?php echo esc_html( kolf_floor_label( $floor ) ); ?></p>
		<h1 class="kolf-single__title"><?php the_title(); ?></h1>
		<p class="kolf-single__meta"><?php echo esc_html( $contact ); ?></p>
		<?php if ( get_the_content() ) : ?>
			<div class="kolf-single__body"><?php the_content(); ?></div>
		<?php endif; ?>
		<a class="kolf-single__back" href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>">&larr; <?php esc_html_e( 'Zpět na telefonní seznam', 'kolf' ); ?></a>
	</section>
	<?php
endwhile;

get_footer();
