<?php
/**
 * Detail jednoho oddělení / ambulance.
 */
get_header();

while ( have_posts() ) :
	the_post();
	$floor         = get_post_meta( get_the_ID(), 'kolf_floor', true );
	$door_number   = get_post_meta( get_the_ID(), 'kolf_door_number', true );
	$web           = get_post_meta( get_the_ID(), 'kolf_web', true );
	$location_spec = get_post_meta( get_the_ID(), 'kolf_location_specification', true );
	$phones        = kolf_get_department_phones( get_the_ID() );
	$persons       = kolf_get_department_persons( get_the_ID() );
	$hours         = kolf_get_department_hours( get_the_ID() );
	?>
	<section class="kolf-single">
		<p class="kolf-single__eyebrow"><?php echo esc_html( kolf_floor_label( $floor ) ); ?><?php echo $door_number ? ' · ' . esc_html( sprintf( __( 'dveře %s', 'kolf' ), $door_number ) ) : ''; ?></p>
		<h1 class="kolf-single__title"><?php the_title(); ?></h1>

		<?php if ( $phones ) : ?>
			<p class="kolf-single__meta">
				<?php foreach ( $phones as $i => $phone ) : ?>
					<?php echo $i > 0 ? ', ' : ''; ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>

		<?php if ( $web ) : ?>
			<p class="kolf-single__meta"><a href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', $web ) ); ?></a></p>
		<?php endif; ?>

		<?php if ( $location_spec ) : ?>
			<div class="kolf-single__body"><?php echo wp_kses_post( $location_spec ); ?></div>
		<?php endif; ?>

		<?php foreach ( $hours as $block ) : ?>
			<?php kolf_render_hours_block( $block ); ?>
		<?php endforeach; ?>

		<?php if ( get_the_content() ) : ?>
			<div class="kolf-single__body"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( $persons ) : ?>
			<h2 class="kolf-quick__title" style="margin-top:40px"><?php esc_html_e( 'Lékaři a personál', 'kolf' ); ?></h2>
			<div class="kolf-directory__group">
				<?php foreach ( $persons as $person ) : ?>
					<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $person ) ); ?>">
						<span class="kolf-directory__row-name"><?php echo esc_html( kolf_person_display_name( $person->ID ) ); ?></span>
						<span class="kolf-directory__row-meta"><?php echo esc_html( get_post_meta( $person->ID, 'kolf_specialization', true ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<a class="kolf-single__back" href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>">&larr; <?php esc_html_e( 'Zpět na seznam oddělení', 'kolf' ); ?></a>
	</section>
	<?php
endwhile;

get_footer();
