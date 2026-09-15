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

	<div class="kolf-search">
		<label for="kolf-search-input" class="kolf-search__label"><?php esc_html_e( 'Filtrovat oddělení', 'kolf' ); ?></label>
		<input type="text" id="kolf-search-input" class="kolf-search__input" placeholder="<?php esc_attr_e( 'např. ortopedie, rentgen', 'kolf' ); ?>" autocomplete="off">
	</div>

	<?php foreach ( kolf_get_departments_grouped_by_floor() as $floor => $depts ) :
		$floor_label = kolf_floor_label( 'none' === $floor ? null : $floor );
		?>
		<div class="kolf-directory__group" id="<?php echo esc_attr( kolf_floor_anchor_id( $floor ) ); ?>">
			<h3 class="kolf-directory__group-title"><?php echo esc_html( $floor_label ); ?></h3>
			<?php foreach ( $depts as $dept ) :
				$phones = kolf_get_department_phones( $dept->ID );
				$search = $dept->post_title . ' ' . $floor_label . ' ' . implode( ' ', $phones );
				?>
				<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $dept ) ); ?>" data-search="<?php echo esc_attr( $search ); ?>">
					<span class="kolf-directory__row-name"><?php echo esc_html( $dept->post_title ); ?></span>
					<span class="kolf-directory__row-meta"><?php echo esc_html( implode( ', ', $phones ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<p class="kolf-directory__empty" hidden><?php esc_html_e( 'Nic jsme nenašli.', 'kolf' ); ?></p>
</section>

<?php get_footer(); ?>
