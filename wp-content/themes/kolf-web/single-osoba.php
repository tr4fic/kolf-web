<?php
/**
 * Detail jedné osoby (lékaře / personálu).
 */
get_header();

while ( have_posts() ) :
	the_post();
	$specialization = get_post_meta( get_the_ID(), 'kolf_specialization', true );
	$floor          = get_post_meta( get_the_ID(), 'kolf_floor', true );
	$door_number    = get_post_meta( get_the_ID(), 'kolf_door_number', true );
	$ico            = get_post_meta( get_the_ID(), 'kolf_ico', true );
	$web            = get_post_meta( get_the_ID(), 'kolf_web', true );
	$department_id  = (int) get_post_meta( get_the_ID(), 'kolf_department_id', true );
	$phones         = kolf_get_person_phones( get_the_ID() );
	$hours          = kolf_get_person_hours( get_the_ID() );
	?>
	<section class="kolf-single">
		<p class="kolf-single__eyebrow">
			<?php echo esc_html( kolf_floor_label( $floor ) ); ?><?php echo $door_number ? ' · ' . esc_html( sprintf( __( 'dveře %s', 'kolf' ), $door_number ) ) : ''; ?>
		</p>
		<h1 class="kolf-single__title"><?php echo esc_html( kolf_person_display_name( get_the_ID() ) ); ?></h1>

		<?php if ( $specialization ) : ?>
			<p class="kolf-single__meta"><?php echo esc_html( $specialization ); ?></p>
		<?php endif; ?>

		<?php if ( $department_id ) : ?>
			<p class="kolf-single__meta"><a href="<?php echo esc_url( get_permalink( $department_id ) ); ?>"><?php echo esc_html( get_the_title( $department_id ) ); ?></a></p>
		<?php endif; ?>

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

		<?php if ( $ico ) : ?>
			<p class="kolf-single__meta"><?php esc_html_e( 'IČO', 'kolf' ); ?>: <?php echo esc_html( $ico ); ?></p>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<div class="kolf-single__body"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php foreach ( $hours as $block ) : ?>
			<?php kolf_render_hours_block( $block ); ?>
		<?php endforeach; ?>

		<a class="kolf-single__back" href="<?php echo esc_url( home_url( '/osoby/' ) ); ?>">&larr; <?php esc_html_e( 'Zpět na seznam osob', 'kolf' ); ?></a>
	</section>
	<?php
endwhile;

get_footer();
