<?php
/**
 * Pomocné funkce pro práci s daty oddělení.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Všechna oddělení jako pole pro JS živé vyhledávání (viz assets/js/department-search.js).
 */
function kolf_get_departments_for_search() {
	$posts = get_posts( array(
		'post_type'      => 'oddeleni',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$out = array();
	foreach ( $posts as $post ) {
		$floor   = get_post_meta( $post->ID, 'kolf_floor', true );
		$contact = get_post_meta( $post->ID, 'kolf_contact', true );
		$out[]   = array(
			'name'  => html_entity_decode( $post->post_title, ENT_QUOTES ),
			'meta'  => kolf_floor_label( $floor ) . ' · ' . $contact,
			'url'   => get_permalink( $post ),
		);
	}
	return $out;
}

/**
 * Oddělení označená pro box "Rychlá čísla" na úvodní stránce, seřazená podle kolf_quick_order.
 */
function kolf_get_quick_numbers() {
	$posts = get_posts( array(
		'post_type'      => 'oddeleni',
		'posts_per_page' => -1,
		'meta_key'       => 'kolf_quick_order',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'kolf_quick_order',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		),
	) );

	$out = array();
	foreach ( $posts as $post ) {
		$label = get_post_meta( $post->ID, 'kolf_quick_label', true );
		$out[] = array(
			'label'  => $label ? $label : $post->post_title,
			'number' => get_post_meta( $post->ID, 'kolf_contact', true ),
		);
	}
	return $out;
}

/**
 * Všechna oddělení seskupená podle patra, v pořadí odpovídajícím návrhu (suterén → 4. patro).
 */
function kolf_get_departments_grouped_by_floor() {
	$posts = get_posts( array(
		'post_type'      => 'oddeleni',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$groups = array();
	foreach ( $posts as $post ) {
		$floor = (int) get_post_meta( $post->ID, 'kolf_floor', true );
		if ( ! isset( $groups[ $floor ] ) ) {
			$groups[ $floor ] = array();
		}
		$groups[ $floor ][] = $post;
	}
	ksort( $groups );
	return $groups;
}

/**
 * Zdravotnické služby (Lékárna, Laboratoř, …) v pořadí nastaveném v adminu (page-attributes menu_order).
 */
function kolf_get_services() {
	return get_posts( array(
		'post_type'      => 'sluzba',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );
}
