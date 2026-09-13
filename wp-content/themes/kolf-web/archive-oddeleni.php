<?php
/**
 * Telefonní seznam — "Oddělení" (podle patra) i "Osoby" (abecedně podle kontaktu),
 * přepínané přes ?view=osoby. Odpovídá odkazům Oddělení / Osoby v hlavičce.
 */
get_header();

$view = ( isset( $_GET['view'] ) && 'osoby' === $_GET['view'] ) ? 'osoby' : 'oddeleni';
?>

<section class="kolf-directory">
	<div class="kolf-section-head">
		<h2><?php esc_html_e( 'Telefonní seznam', 'kolf' ); ?></h2>
	</div>

	<div class="kolf-directory__tabs">
		<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-directory__tab <?php echo 'oddeleni' === $view ? 'is-active' : ''; ?>"><?php esc_html_e( 'Podle oddělení', 'kolf' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/oddeleni/?view=osoby' ) ); ?>" class="kolf-directory__tab <?php echo 'osoby' === $view ? 'is-active' : ''; ?>"><?php esc_html_e( 'Podle osob', 'kolf' ); ?></a>
	</div>

	<?php if ( 'osoby' === $view ) :
		$depts = get_posts( array( 'post_type' => 'oddeleni', 'posts_per_page' => -1 ) );
		usort( $depts, function ( $a, $b ) {
			return strcasecmp( get_post_meta( $a->ID, 'kolf_contact', true ), get_post_meta( $b->ID, 'kolf_contact', true ) );
		} );
		?>
		<div class="kolf-directory__group">
			<?php foreach ( $depts as $dept ) : ?>
				<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $dept ) ); ?>">
					<span class="kolf-directory__row-name"><?php echo esc_html( get_post_meta( $dept->ID, 'kolf_contact', true ) ); ?></span>
					<span class="kolf-directory__row-meta"><?php echo esc_html( $dept->post_title . ' · ' . kolf_floor_label( get_post_meta( $dept->ID, 'kolf_floor', true ) ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else :
		foreach ( kolf_get_departments_grouped_by_floor() as $floor => $depts ) : ?>
		<div class="kolf-directory__group">
			<h3 class="kolf-directory__group-title"><?php echo esc_html( kolf_floor_label( $floor ) ); ?></h3>
			<?php foreach ( $depts as $dept ) : ?>
				<a class="kolf-directory__row" href="<?php echo esc_url( get_permalink( $dept ) ); ?>">
					<span class="kolf-directory__row-name"><?php echo esc_html( $dept->post_title ); ?></span>
					<span class="kolf-directory__row-meta"><?php echo esc_html( get_post_meta( $dept->ID, 'kolf_contact', true ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endforeach;
	endif; ?>
</section>

<?php get_footer(); ?>
