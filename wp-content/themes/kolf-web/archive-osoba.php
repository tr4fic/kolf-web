<?php
/**
 * Kompletní seznam všech osob, seskupený podle prvního písmene příjmení.
 */
get_header();
?>

<section class="kolf-directory">
	<div class="kolf-section-head">
		<h2><?php esc_html_e( 'Osoby', 'kolf' ); ?></h2>
	</div>

	<div class="kolf-directory__tabs">
		<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-directory__tab"><?php esc_html_e( 'Oddělení', 'kolf' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/osoby/' ) ); ?>" class="kolf-directory__tab is-active"><?php esc_html_e( 'Osoby', 'kolf' ); ?></a>
	</div>

	<?php foreach ( kolf_get_persons_grouped_alpha() as $letter => $people ) : ?>
		<div class="kolf-directory__group">
			<h3 class="kolf-directory__group-title"><?php echo esc_html( $letter ); ?></h3>
			<?php foreach ( $people as $person ) :
				$dept_id = (int) get_post_meta( $person->ID, 'kolf_department_id', true );
				$meta    = $dept_id ? get_the_title( $dept_id ) : get_post_meta( $person->ID, 'kolf_specialization', true );
				?>
				<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $person ) ); ?>">
					<span class="kolf-directory__row-name"><?php echo esc_html( kolf_person_display_name( $person->ID ) ); ?></span>
					<span class="kolf-directory__row-meta"><?php echo esc_html( $meta ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</section>

<?php get_footer(); ?>
