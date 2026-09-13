<?php
/**
 * Naplnění obsahu z reálné produkční databáze (MSSQL export Department/Person/Phone/
 * DayClock, viz inc/data/*.php) + marketingový obsah pro "Zdravotnické služby". Spouští
 * se jednou při aktivaci tématu — pokud obsah už existuje (poznáno podle kolf_legacy_id),
 * nic nepřepisuje. Vše je poté běžně editovatelné v adminu.
 *
 * Osoba nemá v produkční databázi přímý cizí klíč na oddělení (tabulka Person žádný
 * DepartmentId nemá) — spárování proběhlo při přípravě dat v inc/data/persons.php
 * (pole department_legacy_id): primárně podle shodného čísla dveří (DoorNumber),
 * u 15 osob, kde se čísla dveří přesně neshodovala, ručně podle patra + specializace.
 * V nové databázi to už ale je uložené jako běžný cizí klíč (kolf_department_id).
 *
 * DayClock (ordinační/provozní hodiny) v produkční databázi existovaly jen u osob
 * (žádné DepartmentId) — u oddělení jde od teď přidat ručně v adminu.
 */

defined( 'ABSPATH' ) || exit;

function kolf_seed_departments() {
	return require KOLF_DIR . '/inc/data/departments.php';
}

function kolf_seed_persons() {
	return require KOLF_DIR . '/inc/data/persons.php';
}

function kolf_seed_phones() {
	return require KOLF_DIR . '/inc/data/phones.php';
}

function kolf_seed_hours() {
	return require KOLF_DIR . '/inc/data/hours.php';
}

function kolf_seed_services() {
	// [ název, podtitul, meta řádek, popis ]
	return array(
		array(
			'Lékárna', 'Lékárna na poliklinice', 'Přízemí · 466 753 332',
			'Naše lékárna zajišťuje kompletní zásobování léky, infuzními roztoky, částečně zdravotnickým materiálem a dezinfekčními prostředky. Veřejná část lékárny slouží individuálním pacientům a zákazníkům - odborný personál zajišťuje expedici volně prodejných léků a léků vázaných na recept. Lékárna též nabízí velký rozsah potravinových doplňků, čajů, vitamínů, dětské výživy i kosmetiky pro alergiky.',
		),
		array(
			'Laboratoř', 'Odběry krve', '2. patro · MeDiLa spol. s r.o. · www.medila.cz',
			'Oddělení laboratoře provozuje MeDiLa spol. s.r.o. www.medila.cz a zajišťuje odběry krve a příjem biologického materiálu.',
		),
		array(
			'Rentgen', 'Radiologické oddělení', '1. patro · 466 753 264, 466 753 265',
			'Diagnostické obrazy získáváme pomocí ionizujícího záření - skiagrafie. Oddělení je vybaveno rovněž moderním RTG přístrojem s přímou digitalizací. Přístroje na RTG oddělení jsou pravidelně kontrolovány a proměřovány, a tudíž odpovídají evropským normám týkajících se zátěže záření pro populaci.',
		),
		array(
			'Zdravotnické potřeby', 'Zdravotnický materiál', 'Přízemí · 466 753 272',
			'Oddělení zdravotnických prostředků zajišťuje výdej prostředků zdravotnické techniky na poukazy tj. poukazy na léčebnou a ortopedickou pomůcku. Zároveň nabízí volně dostupný zdravotnický materiál za hotové.',
		),
		array(
			'Oční optika', 'Vyšetření, dioptrické i sluneční brýle', 'Přízemí · 466 753 240',
			'Zajišťujeme vyšetření zraku lékařem bez čekání, kompletní péči o Váš zrak, včetně odborných vyšetření a aplikaci kontaktních čoček.',
		),
	);
}

/**
 * Smaže placeholder oddělení z první (ukázkové) verze tématu — poznají se podle
 * toho, že nemají kolf_legacy_id (ta u reálně naimportovaných dat vždy je).
 */
function kolf_purge_placeholder_departments() {
	$old = get_posts( array(
		'post_type'      => 'oddeleni',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array( 'key' => 'kolf_legacy_id', 'compare' => 'NOT EXISTS' ),
		),
	) );
	foreach ( $old as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

/**
 * @return array legacy_id (z Department.sql) => nové WP post ID
 */
function kolf_seed_run_departments() {
	$map = array();

	foreach ( kolf_seed_departments() as $dept ) {
		$existing = get_posts( array(
			'post_type'      => 'oddeleni',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $dept['legacy_id'],
		) );
		if ( $existing ) {
			$map[ $dept['legacy_id'] ] = $existing[0]->ID;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'oddeleni',
			'post_title'   => $dept['name'],
			'post_name'    => $dept['slug'],
			'post_content' => $dept['note'] ? $dept['note'] : '',
			'post_status'  => 'publish',
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_legacy_id', $dept['legacy_id'] );
		update_post_meta( $post_id, 'kolf_is_ordination', $dept['is_ordination'] );
		update_post_meta( $post_id, 'kolf_floor', null !== $dept['floor'] ? $dept['floor'] : '' );
		update_post_meta( $post_id, 'kolf_door_number', null !== $dept['door_number'] ? $dept['door_number'] : '' );
		update_post_meta( $post_id, 'kolf_location_specification', $dept['location_specification'] ? $dept['location_specification'] : '' );
		update_post_meta( $post_id, 'kolf_web', $dept['web'] ? $dept['web'] : '' );

		// Editorial volba, která oddělení se ukážou v boxu "Rychlá čísla" na úvodní
		// stránce — v produkční DB tohle není, jde o rozhodnutí z návrhu webu.
		$quick = kolf_quick_number_overrides();
		if ( isset( $quick[ $dept['legacy_id'] ] ) ) {
			update_post_meta( $post_id, 'kolf_quick_order', $quick[ $dept['legacy_id'] ]['order'] );
			update_post_meta( $post_id, 'kolf_quick_label', $quick[ $dept['legacy_id'] ]['label'] );
		}

		$map[ $dept['legacy_id'] ] = $post_id;
	}

	return $map;
}

/**
 * legacy_id (Department.Id) => pořadí a popisek v boxu "Rychlá čísla".
 */
function kolf_quick_number_overrides() {
	return array(
		35 => array( 'order' => 2, 'label' => __( 'Lékárna', 'kolf' ) ),               // Lékárna
		28 => array( 'order' => 3, 'label' => __( 'Rentgen', 'kolf' ) ),               // Rentgen
		42 => array( 'order' => 4, 'label' => __( 'Zdravotnické potřeby', 'kolf' ) ),  // Zdravotnické potřeby MEDESA care
	);
}

/**
 * @return array legacy_id (z Person.sql) => nové WP post ID
 */
function kolf_seed_run_persons( $department_map ) {
	$map = array();

	foreach ( kolf_seed_persons() as $person ) {
		$existing = get_posts( array(
			'post_type'      => 'osoba',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $person['legacy_id'],
		) );
		if ( $existing ) {
			$map[ $person['legacy_id'] ] = $existing[0]->ID;
			continue;
		}

		$title   = trim( $person['first_name'] . ' ' . $person['last_name'] );
		$post_id = wp_insert_post( array(
			'post_type'    => 'osoba',
			'post_title'   => $title,
			'post_name'    => $person['slug'],
			'post_content' => $person['note'] ? $person['note'] : '',
			'post_status'  => 'publish',
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_legacy_id', $person['legacy_id'] );
		update_post_meta( $post_id, 'kolf_first_name', $person['first_name'] );
		update_post_meta( $post_id, 'kolf_last_name', $person['last_name'] );
		update_post_meta( $post_id, 'kolf_honor_before', $person['honor_before'] ? $person['honor_before'] : '' );
		update_post_meta( $post_id, 'kolf_honor_after', $person['honor_after'] ? $person['honor_after'] : '' );
		update_post_meta( $post_id, 'kolf_specialization', $person['specialization'] ? $person['specialization'] : '' );
		update_post_meta( $post_id, 'kolf_floor', null !== $person['floor'] ? $person['floor'] : '' );
		update_post_meta( $post_id, 'kolf_door_number', null !== $person['door_number'] ? $person['door_number'] : '' );
		update_post_meta( $post_id, 'kolf_ico', $person['ico'] ? $person['ico'] : '' );
		update_post_meta( $post_id, 'kolf_web', $person['web'] ? $person['web'] : '' );

		$dept_post_id = ( null !== $person['department_legacy_id'] && isset( $department_map[ $person['department_legacy_id'] ] ) )
			? $department_map[ $person['department_legacy_id'] ]
			: 0;
		update_post_meta( $post_id, 'kolf_department_id', $dept_post_id );

		$map[ $person['legacy_id'] ] = $post_id;
	}

	return $map;
}

function kolf_seed_run_phones( $department_map, $person_map ) {
	foreach ( kolf_seed_phones() as $phone ) {
		$dept_post_id   = ( null !== $phone['department_legacy_id'] && isset( $department_map[ $phone['department_legacy_id'] ] ) )
			? $department_map[ $phone['department_legacy_id'] ] : 0;
		$person_post_id = ( null !== $phone['person_legacy_id'] && isset( $person_map[ $phone['person_legacy_id'] ] ) )
			? $person_map[ $phone['person_legacy_id'] ] : 0;

		if ( ! $dept_post_id && ! $person_post_id ) {
			continue;
		}

		$existing = get_posts( array(
			'post_type'      => 'telefon',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $phone['legacy_id'],
		) );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'telefon',
			'post_title'  => $phone['number'],
			'post_status' => 'publish',
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_legacy_id', $phone['legacy_id'] );
		update_post_meta( $post_id, 'kolf_phone_number', $phone['number'] );
		if ( $dept_post_id ) {
			update_post_meta( $post_id, 'kolf_phone_department_id', $dept_post_id );
		}
		if ( $person_post_id ) {
			update_post_meta( $post_id, 'kolf_phone_person_id', $person_post_id );
		}
	}
}

function kolf_seed_run_hours( $department_map, $person_map ) {
	foreach ( kolf_seed_hours() as $hours ) {
		$dept_post_id   = ( null !== $hours['department_legacy_id'] && isset( $department_map[ $hours['department_legacy_id'] ] ) )
			? $department_map[ $hours['department_legacy_id'] ] : 0;
		$person_post_id = ( null !== $hours['person_legacy_id'] && isset( $person_map[ $hours['person_legacy_id'] ] ) )
			? $person_map[ $hours['person_legacy_id'] ] : 0;

		if ( ! $dept_post_id && ! $person_post_id ) {
			continue;
		}

		$existing = get_posts( array(
			'post_type'      => 'hodiny',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $hours['legacy_id'],
		) );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'hodiny',
			'post_title'  => __( 'Rozvrh', 'kolf' ),
			'post_status' => 'publish',
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_legacy_id', $hours['legacy_id'] );
		update_post_meta( $post_id, 'kolf_hours_type', $hours['type'] );
		update_post_meta( $post_id, 'kolf_hours_note', $hours['note'] ? $hours['note'] : '' );
		update_post_meta( $post_id, 'kolf_hours_days', $hours['days'] );
		if ( $dept_post_id ) {
			update_post_meta( $post_id, 'kolf_hours_department_id', $dept_post_id );
		}
		if ( $person_post_id ) {
			update_post_meta( $post_id, 'kolf_hours_person_id', $person_post_id );
		}
	}
}

function kolf_seed_run_services() {
	$order = 0;
	foreach ( kolf_seed_services() as $service ) {
		list( $name, $subtitle, $meta, $body ) = $service;

		if ( get_page_by_title( $name, OBJECT, 'sluzba' ) ) {
			$order++;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'sluzba',
			'post_title'   => $name,
			'post_content' => $body,
			'post_status'  => 'publish',
			'menu_order'   => $order,
		) );

		if ( ! is_wp_error( $post_id ) && $post_id ) {
			update_post_meta( $post_id, 'kolf_subtitle', $subtitle );
			update_post_meta( $post_id, 'kolf_meta', $meta );
		}
		$order++;
	}
}

function kolf_run_seed() {
	if ( get_option( 'kolf_seeded_v3' ) ) {
		return;
	}

	kolf_purge_placeholder_departments();

	$department_map = kolf_seed_run_departments();
	$person_map      = kolf_seed_run_persons( $department_map );
	kolf_seed_run_phones( $department_map, $person_map );
	kolf_seed_run_hours( $department_map, $person_map );
	kolf_seed_run_services();

	update_option( 'kolf_seeded_v3', 1 );
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kolf_run_seed' );

/**
 * Bez tohohle by /oddeleni/ a /osoby/ po aktivaci tématu vracelo 404, dokud by někdo
 * ručně neuložil Nastavení → Trvalé odkazy.
 */
function kolf_flush_rewrites_on_activation() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kolf_flush_rewrites_on_activation' );

/**
 * Ruční spuštění/přenaplnění pro lokální vývoj: navštivte /wp-admin/?kolf_reseed=1
 * přihlášeni jako administrátor. Použijte i po aktualizaci šablony z první (ukázkové)
 * verze na tuhle — jinak se after_switch_theme znovu nespustí.
 */
function kolf_maybe_reseed() {
	if ( isset( $_GET['kolf_reseed'] ) && current_user_can( 'manage_options' ) ) {
		delete_option( 'kolf_seeded_v3' );
		kolf_run_seed();
		wp_safe_redirect( remove_query_arg( 'kolf_reseed' ) );
		exit;
	}
}
add_action( 'admin_init', 'kolf_maybe_reseed' );
