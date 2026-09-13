<?php
/**
 * Kompletní seznam všech oddělení, seskupený podle patra.
 */
get_header();
?>

<section class="kolf-directory">
	<div class="kolf-section-head">
		<h2><?php esc_html_e( 'Oddělení', 'kolf' ); ?></h2>
	</div>

	<div class="kolf-directory__tabs">
		<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-directory__tab is-active"><?php esc_html_e( 'Oddělení', 'kolf' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/osoby/' ) ); ?>" class="kolf-directory__tab"><?php esc_html_e( 'Osoby', 'kolf' ); ?></a>
	</div>

	<?php foreach ( kolf_get_departments_grouped_by_floor() as $floor => $depts ) : ?>
		<div class="kolf-directory__group">
			<h3 class="kolf-directory__group-title"><?php echo esc_html( kolf_floor_label( 'none' === $floor ? null : $floor ) ); ?></h3>
			<?php foreach ( $depts as $dept ) :
				$phones = kolf_get_department_phones( $dept->ID );
				?>
				<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $dept ) ); ?>">
					<span class="kolf-directory__row-name"><?php echo esc_html( $dept->post_title ); ?></span>
					<span class="kolf-directory__row-meta"><?php echo esc_html( implode( ', ', $phones ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</section>

<?php get_footer(); ?>
