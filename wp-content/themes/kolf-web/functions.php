<?php
/**
 * Poliklinika KOLF theme bootstrap.
 * Žádné závislosti na pluginech — vlastní CPT, vlastní meta boxy, vlastní JS.
 */

defined( 'ABSPATH' ) || exit;

define( 'KOLF_VERSION', '1.0.0' );
define( 'KOLF_DIR', get_template_directory() );
define( 'KOLF_URI', get_template_directory_uri() );

require KOLF_DIR . '/inc/post-types.php';
require KOLF_DIR . '/inc/meta-boxes.php';
require KOLF_DIR . '/inc/customizer.php';
require KOLF_DIR . '/inc/seed-content.php';
require KOLF_DIR . '/inc/helpers.php';
require KOLF_DIR . '/inc/performance.php';

/**
 * Theme setup.
 */
function kolf_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ) );

	register_nav_menus( array(
		'primary'      => __( 'Hlavní navigace', 'kolf' ),
		'legacy_pages' => __( 'CMS stránky (import ze starého webu)', 'kolf' ),
	) );
}
add_action( 'after_setup_theme', 'kolf_setup' );

/**
 * Assets — vlastní hostovaný CSS/JS, jen jeden externí request na Google Fonts.
 * Žádné jQuery, žádné buildovací nástroje.
 */
function kolf_assets() {
	wp_enqueue_style(
		'kolf-fonts',
		'https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&family=IBM+Plex+Sans:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'kolf-style', KOLF_URI . '/assets/css/style.css', array(), KOLF_VERSION );

	if ( is_front_page() ) {
		wp_enqueue_script( 'kolf-search', KOLF_URI . '/assets/js/department-search.js', array(), KOLF_VERSION, true );
		wp_localize_script( 'kolf-search', 'kolfDepartments', kolf_get_search_index() );
		wp_localize_script( 'kolf-search', 'kolfSearchI18n', array(
			'noResults' => sprintf(
				/* translators: %s: telefonní číslo ústředny */
				__( 'Nic jsme nenašli. Zkuste zavolat na ústřednu %s.', 'kolf' ),
				get_theme_mod( 'kolf_phone', '466 753 111' )
			),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'kolf_assets' );

/**
 * Menu fallback odpovídající původnímu návrhu, pokud si klient menu v Appearance > Menus zatím nenastavil.
 */
function kolf_nav_fallback() {
	$items = array(
		array( 'label' => __( 'Oddělení', 'kolf' ), 'url' => home_url( '/oddeleni/' ) ),
		array( 'label' => __( 'Osoby', 'kolf' ), 'url' => home_url( '/osoby/' ) ),
		array( 'label' => __( 'Zdravotnické služby', 'kolf' ), 'url' => home_url( '/#sluzby' ) ),
		array( 'label' => __( 'Kontakt', 'kolf' ), 'url' => home_url( '/#kontakt' ) ),
	);
	foreach ( $items as $item ) {
		printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
	}
}
