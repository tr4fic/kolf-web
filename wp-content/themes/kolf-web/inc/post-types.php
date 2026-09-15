<?php
/**
 * Custom post types: Oddělení, Osoby, Telefony, Hodiny.
 *
 * Datový model odpovídá reálné produkční struktuře (MSSQL Department / Person / Phone / DayClock):
 *   oddeleni (1) ---- (N) osoba        — osoba patří max. do jednoho oddělení (kolf_department_id)
 *   oddeleni (1) ---- (N) telefon      — telefonní číslo patřící oddělení (kolf_phone_department_id)
 *   osoba    (1) ---- (N) telefon      — telefonní číslo patřící osobě (kolf_phone_person_id)
 *   oddeleni (1) ---- (N) hodiny       — rozvrh (ordinační/provozní hodiny) patřící oddělení (kolf_hours_department_id)
 *   osoba    (1) ---- (N) hodiny       — rozvrh patřící osobě (kolf_hours_person_id)
 *
 * "telefon" a "hodiny" nemají vlastní veřejnou stránku ani položku v adminu —
 * zadávají se přímo ve formuláři oddělení/osoby (viz inc/meta-boxes.php),
 * v DB ale žijí jako samostatné záznamy.
 */

defined( 'ABSPATH' ) || exit;

function kolf_register_post_types() {

	register_post_type( 'oddeleni', array(
		'labels' => array(
			'name'          => __( 'Oddělení', 'kolf' ),
			'singular_name' => __( 'Oddělení', 'kolf' ),
			'add_new_item'  => __( 'Přidat oddělení', 'kolf' ),
			'edit_item'     => __( 'Upravit oddělení', 'kolf' ),
			'search_items'  => __( 'Hledat oddělení', 'kolf' ),
			'all_items'     => __( 'Oddělení a ambulance', 'kolf' ),
			'not_found'     => __( 'Žádná oddělení nenalezena', 'kolf' ),
		),
		'public'       => true,
		'has_archive'  => 'oddeleni',
		'rewrite'      => array( 'slug' => 'oddeleni' ),
		'menu_icon'    => 'dashicons-building',
		'supports'     => array( 'title', 'editor' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'osoba', array(
		'labels' => array(
			'name'          => __( 'Osoby', 'kolf' ),
			'singular_name' => __( 'Osoba', 'kolf' ),
			'add_new_item'  => __( 'Přidat osobu', 'kolf' ),
			'edit_item'     => __( 'Upravit osobu', 'kolf' ),
			'search_items'  => __( 'Hledat osobu', 'kolf' ),
			'all_items'     => __( 'Osoby', 'kolf' ),
			'not_found'     => __( 'Žádné osoby nenalezeny', 'kolf' ),
		),
		'public'       => true,
		'has_archive'  => 'osoby',
		'rewrite'      => array( 'slug' => 'osoby' ),
		'menu_icon'    => 'dashicons-groups',
		'supports'     => array( 'title', 'editor' ),
		'show_in_rest' => true,
	) );

	// Bez veřejné stránky (žádné URL pro jedno číslo), ale s vlastní sekcí
	// v adminu — čísla se sice normálně zadávají přes opakovatelný seznam
	// u oddělení/osoby, "Telefony" slouží k přehledu a k zaškrtnutí "Zvýraznit".
	register_post_type( 'telefon', array(
		'labels' => array(
			'name'          => __( 'Telefony', 'kolf' ),
			'singular_name' => __( 'Telefon', 'kolf' ),
			'edit_item'     => __( 'Upravit telefon', 'kolf' ),
			'search_items'  => __( 'Hledat telefon', 'kolf' ),
			'all_items'     => __( 'Telefony', 'kolf' ),
			'not_found'     => __( 'Žádné telefony nenalezeny', 'kolf' ),
		),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-phone',
		'has_archive'        => false,
		'rewrite'            => false,
		'supports'           => array( 'title' ),
		'show_in_rest'       => false,
	) );

	// Ordinační/provozní hodiny — stejný princip jako "telefon": žádná vlastní
	// stránka ani položka v adminu, zadává se ve formuláři oddělení/osoby.
	// Na jedno oddělení/osobu jich může být víc (např. různý rozvrh pro různé dny).
	register_post_type( 'hodiny', array(
		'labels' => array(
			'name'          => __( 'Hodiny', 'kolf' ),
			'singular_name' => __( 'Hodiny', 'kolf' ),
		),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => false,
		'show_in_menu'       => false,
		'has_archive'        => false,
		'rewrite'            => false,
		'supports'           => array( 'title' ),
		'show_in_rest'       => false,
	) );
}
add_action( 'init', 'kolf_register_post_types' );

/**
 * Popisky pater — jediné místo, kde se definuje mapování čísla patra na text.
 */
function kolf_floor_label( $floor ) {
	if ( '' === $floor || null === $floor ) {
		return __( 'Patro neuvedeno', 'kolf' );
	}
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
	if ( '' === $floor || null === $floor ) {
		return '?';
	}
	$floor = (int) $floor;
	return $floor < 0 ? '−1' : (string) $floor;
}

/**
 * HTML id skupiny patra na /oddeleni/ — používá se jako kotva (#patro-1) při
 * odkazování z karet pater na front-page.
 */
function kolf_floor_anchor_id( $floor ) {
	if ( '' === $floor || null === $floor || 'none' === $floor ) {
		return 'patro-neuvedeno';
	}
	$floor = (int) $floor;
	return $floor < 0 ? 'suteren' : 'patro-' . $floor;
}
