<?php
/**
 * Úvodní stránka: hero s vyhledáváním, rychlá čísla, patra, zdravotnické služby, kontakt.
 */
get_header();

$floors_meta = array(
	-1 => __( 'Suterén', 'kolf' ),
	0  => __( 'Přízemí', 'kolf' ),
	1  => __( '1. patro', 'kolf' ),
	2  => __( '2. patro', 'kolf' ),
	3  => __( '3. patro', 'kolf' ),
	4  => __( '4. patro', 'kolf' ),
);
$floor_groups  = kolf_get_departments_grouped_by_floor();
$quick_numbers = kolf_get_quick_numbers();
$services      = kolf_get_services();
?>

<section class="kolf-hero">
	<div>
		<div class="kolf-search">
			<label for="kolf-search-input" class="kolf-search__label"><?php esc_html_e( 'Hledat oddělení nebo lékaře', 'kolf' ); ?></label>
			<input type="text" id="kolf-search-input" class="kolf-search__input" placeholder="<?php esc_attr_e( 'např. ortopedie, Erben, rentgen', 'kolf' ); ?>" autocomplete="off">
			<div id="kolf-search-results" class="kolf-search__results"></div>
		</div>

		<h1 class="kolf-hero__title"><?php esc_html_e( 'Více než čtyřicet ambulancí', 'kolf' ); ?><br><?php esc_html_e( 'pod jednou střechou.', 'kolf' ); ?></h1>
		<p class="kolf-hero__lead"><?php esc_html_e( 'Najděte oddělení, lékaře nebo telefonní číslo za pár vteřin. Vše ostatní — lékárna, laboratoř, rentgen i optika — je ve stejné budově.', 'kolf' ); ?></p>
	</div>

	<aside class="kolf-quick">
		<h2 class="kolf-quick__title"><?php esc_html_e( 'Rychlá čísla', 'kolf' ); ?></h2>
		<ul class="kolf-quick__list">
			<li class="kolf-quick__item">
				<span class="kolf-quick__label"><?php esc_html_e( 'Ústředna', 'kolf' ); ?></span>
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', get_theme_mod( 'kolf_phone', '466 753 111' ) ) ); ?>" class="kolf-quick__number"><?php echo esc_html( get_theme_mod( 'kolf_phone', '466 753 111' ) ); ?></a>
			</li>
			<?php foreach ( $quick_numbers as $q ) : ?>
			<li class="kolf-quick__item">
				<span class="kolf-quick__label"><?php echo esc_html( $q['label'] ); ?></span>
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $q['number'] ) ); ?>" class="kolf-quick__number"><?php echo esc_html( $q['number'] ); ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
		<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-quick__more"><?php esc_html_e( 'Celý telefonní seznam →', 'kolf' ); ?></a>
	</aside>
</section>

<section class="kolf-floors">
	<div class="kolf-section-head">
		<h2><?php esc_html_e( 'Kde co najdete', 'kolf' ); ?></h2>
		<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>"><?php esc_html_e( 'Všechna oddělení →', 'kolf' ); ?></a>
	</div>
	<div class="kolf-floors__grid">
		<?php foreach ( $floor_groups as $floor => $posts ) :
			$names       = wp_list_pluck( $posts, 'post_title' );
			$floor_value = 'none' === $floor ? null : $floor;
			?>
			<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-floor-card">
				<div class="kolf-floor-card__head">
					<span class="kolf-floor-card__num"><?php echo esc_html( kolf_floor_number_display( $floor_value ) ); ?></span>
					<span class="kolf-floor-card__caption"><?php echo esc_html( $floors_meta[ $floor ] ?? kolf_floor_label( $floor_value ) ); ?></span>
				</div>
				<p class="kolf-floor-card__list"><?php echo esc_html( implode( ' · ', $names ) ); ?></p>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section id="sluzby" class="kolf-services">
	<div class="kolf-section-head">
		<h2><?php esc_html_e( 'Zdravotnické služby', 'kolf' ); ?></h2>
	</div>
	<?php foreach ( $services as $service ) : ?>
		<article class="kolf-service">
			<div>
				<h3 class="kolf-service__title"><?php echo esc_html( $service->post_title ); ?></h3>
				<p class="kolf-service__subtitle"><?php echo esc_html( get_post_meta( $service->ID, 'kolf_subtitle', true ) ); ?></p>
			</div>
			<div>
				<p class="kolf-service__body"><?php echo esc_html( $service->post_content ); ?></p>
				<p class="kolf-service__meta"><?php echo esc_html( get_post_meta( $service->ID, 'kolf_meta', true ) ); ?></p>
			</div>
		</article>
	<?php endforeach; ?>
</section>

<section id="kontakt" class="kolf-contact">
	<div>
		<h2><?php esc_html_e( 'Kontaktní informace', 'kolf' ); ?></h2>
		<div class="kolf-contact__info">
			<div>
				<?php echo esc_html( get_theme_mod( 'kolf_address_2', 'Poliklinika KOLF s.r.o., Masarykovo nám. 2667, Pardubice, 53002' ) ); ?>
			</div>
			<div><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', get_theme_mod( 'kolf_phone', '466 753 111' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'kolf_phone', '466 753 111' ) ); ?></a></div>
			<div><a href="mailto:<?php echo esc_attr( get_theme_mod( 'kolf_email', 'info@kolf.cz' ) ); ?>"><?php echo esc_html( get_theme_mod( 'kolf_email', 'info@kolf.cz' ) ); ?></a></div>
		</div>
		<div class="kolf-contact__actions">
			<a href="<?php echo esc_url( home_url( '/oddeleni/' ) ); ?>" class="kolf-btn-outline"><?php esc_html_e( 'Telefonní seznam', 'kolf' ); ?></a>
			<a href="<?php echo esc_url( get_theme_mod( 'kolf_maps_url', 'https://maps.google.com' ) ); ?>" class="kolf-btn-outline" target="_blank" rel="noopener"><?php esc_html_e( 'Mapa budovy', 'kolf' ); ?></a>
		</div>
	</div>
	<?php $front_page_id = (int) get_option( 'page_on_front' ); ?>
	<div class="kolf-contact__media">
		<?php if ( $front_page_id && has_post_thumbnail( $front_page_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $front_page_id, 'large' ); ?>
		<?php else : ?>
			<span><?php esc_html_e( 'mapa / foto budovy', 'kolf' ); ?></span>
			<span class="small"><?php echo esc_html( get_theme_mod( 'kolf_address', 'Masarykovo nám. 2667' ) ); ?></span>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
