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

	// Úvodní stránka má rozbalovací našeptávač s vlastním seznamem výsledků
	// (kolfDepartments jako data); /oddeleni/ a /osoby/ místo toho jen živě
	// filtrují řádky, které jsou na stránce už vypsané — viz directory-filter.js.
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
	} elseif ( is_post_type_archive( 'oddeleni' ) || is_post_type_archive( 'osoba' ) ) {
		wp_enqueue_script( 'kolf-directory-filter', KOLF_URI . '/assets/js/directory-filter.js', array(), KOLF_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'kolf_assets' );

/**
 * Menu fallback odpovídající původnímu návrhu, pokud si klient menu v Appearance > Menus zatím nenastavil.
 * Oddělení/Osoby se zvýrazní i na detailu konkrétního oddělení/osoby, ne jen
 * na archivu — stejná logika jako kolf_nav_menu_active_class() níže pro
 * případ, že si klient menu později nastaví přes Vzhled → Menu.
 */
function kolf_nav_fallback() {
	$items = array(
		array( 'label' => __( 'Oddělení', 'kolf' ), 'url' => home_url( '/oddeleni/' ), 'active' => is_post_type_archive( 'oddeleni' ) || is_singular( 'oddeleni' ) ),
		array( 'label' => __( 'Osoby', 'kolf' ), 'url' => home_url( '/osoby/' ), 'active' => is_post_type_archive( 'osoba' ) || is_singular( 'osoba' ) ),
		array( 'label' => __( 'Zdravotnické služby', 'kolf' ), 'url' => home_url( '/#sluzby' ), 'active' => false ),
		array( 'label' => __( 'Kontakt', 'kolf' ), 'url' => home_url( '/#kontakt' ), 'active' => false ),
	);
	foreach ( $items as $item ) {
		printf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $item['url'] ),
			esc_attr( $item['active'] ? 'is-active' : '' ),
			esc_html( $item['label'] )
		);
	}
}

/**
 * Zvýrazní položky Oddělení/Osoby v menu "primary", i když je administrátor
 * v adminu vytvořil jako obyčejný Custom Link — WP samo current-menu-item
 * pozná jen na přesné shodě URL (tj. na archivu), ne na detailu jednotlivého
 * oddělení/osoby, kde je URL jiná.
 */
function kolf_nav_menu_active_class( $classes, $item, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $classes;
	}

	$path      = trim( (string) parse_url( $item->url, PHP_URL_PATH ), '/' );
	$is_active = ( 'oddeleni' === $path && ( is_post_type_archive( 'oddeleni' ) || is_singular( 'oddeleni' ) ) )
		|| ( 'osoby' === $path && ( is_post_type_archive( 'osoba' ) || is_singular( 'osoba' ) ) );

	if ( $is_active ) {
		$classes[] = 'is-active';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'kolf_nav_menu_active_class', 10, 3 );
