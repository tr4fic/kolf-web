<?php
/**
 * Custom post types: Oddělení (departments) a Zdravotnické služby.
 */

defined( 'ABSPATH' ) || exit;

function kolf_register_post_types() {

	register_post_type( 'oddeleni', array(
		'labels' => array(
			'name'               => __( 'Oddělení', 'kolf' ),
			'singular_name'      => __( 'Oddělení', 'kolf' ),
			'add_new_item'       => __( 'Přidat oddělení', 'kolf' ),
			'edit_item'          => __( 'Upravit oddělení', 'kolf' ),
			'search_items'       => __( 'Hledat oddělení', 'kolf' ),
			'all_items'          => __( 'Oddělení a ambulance', 'kolf' ),
			'not_found'          => __( 'Žádná oddělení nenalezena', 'kolf' ),
		),
		'public'        => true,
		'has_archive'   => 'oddeleni',
		'rewrite'       => array( 'slug' => 'oddeleni' ),
		'menu_icon'     => 'dashicons-building',
		'supports'      => array( 'title', 'editor' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'sluzba', array(
		'labels' => array(
			'name'               => __( 'Zdravotnické služby', 'kolf' ),
			'singular_name'      => __( 'Zdravotnická služba', 'kolf' ),
			'add_new_item'       => __( 'Přidat službu', 'kolf' ),
			'edit_item'          => __( 'Upravit službu', 'kolf' ),
			'all_items'          => __( 'Zdravotnické služby', 'kolf' ),
		),
		'public'        => true,
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'sluzby' ),
		'menu_icon'     => 'dashicons-plus-alt',
		'supports'      => array( 'title', 'editor', 'page-attributes' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'kolf_register_post_types' );

/**
 * Popisky pater — jediné místo, kde se definuje mapování čísla patra na text.
 */
function kolf_floor_label( $floor ) {
	$floor = (int) $floor;
	if ( $floor < 0 ) {
		return __( 'Suterén', 'kolf' );
	}
	if ( 0 === $floor ) {
		return __( 'Přízemí', 'kolf' );
	}
	return sprintf( __( '%d. patro', 'kolf' ), $floor );
}

function kolf_floor_number_display( $floor ) {
	$floor = (int) $floor;
	return $floor < 0 ? '−1' : (string) $floor;
}
