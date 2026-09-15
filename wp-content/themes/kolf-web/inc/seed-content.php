<?php
/**
 * Naplnění obsahu z reálné produkční databáze (MSSQL export Department/Person/Phone/
 * DayClock/Page/ContentItem/MenuItem, viz inc/data/*.php). Spouští se jednou při aktivaci
 * tématu — pokud obsah už existuje (poznáno podle kolf_legacy_id), nic nepřepisuje. Vše je
 * poté běžně editovatelné v adminu.
 *
 * Sekce "Zdravotnické služby" na úvodní stránce nemá vlastní CPT — vypisuje oddělení
 * zaškrtnutá jako "Zvýraznit" (kolf_highlight), viz kolf_department_highlight_overrides()
 * a kolf_seed_apply_highlight() níže. Stejný princip (jiné meta klíče, žádný text popisu
 * navíc) používá box "Rychlá čísla" — tam se "Zvýraznit" zaškrtává u konkrétního telefonu
 * (CPT "telefon", sekce Telefony v adminu), viz kolf_phone_highlight_overrides()
 * a kolf_seed_apply_phone_highlight().
 *
 * Stránky (Page + ContentItem) se importují jako obyčejné WP stránky (post_type "page"),
 * MenuItem jako nativní WP menu (Vzhled → Menu, "CMS stránky (import)") — žádný vlastní
 * CPT navíc, protože tohle už WordPress umí sám. Menu je přiřazené k pozici "legacy_pages"
 * a šablona ho vykresluje v patičce (footer.php).
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

function kolf_seed_pages() {
	return require KOLF_DIR . '/inc/data/pages.php';
}

function kolf_seed_menu() {
	return require KOLF_DIR . '/inc/data/menu.php';
}

/**
 * legacy_id (Department.Id) => marketingový popis pro sekci "Zdravotnické služby"
 * na úvodní stránce. Dřív šlo o samostatný CPT "sluzba" s ručně vyplněným
 * názvem/podtitulem/umístěním/telefonem — ty teď bere šablona přímo z oddělení
 * (viz front-page.php), tady zbývá jen text popisu navíc a příznak "Zvýraznit"
 * (kolf_highlight), který se při seedu nastaví na dotčených odděleních.
 */
function kolf_department_highlight_overrides() {
	return array(
		35 => 'Naše lékárna zajišťuje kompletní zásobování léky, infuzními roztoky, částečně zdravotnickým materiálem a dezinfekčními prostředky. Veřejná část lékárny slouží individuálním pacientům a zákazníkům - odborný personál zajišťuje expedici volně prodejných léků a léků vázaných na recept. Lékárna též nabízí velký rozsah potravinových doplňků, čajů, vitamínů, dětské výživy i kosmetiky pro alergiky.',
		20 => 'Oddělení laboratoře provozuje MeDiLa spol. s.r.o. www.medila.cz a zajišťuje odběry krve a příjem biologického materiálu.',
		28 => 'Diagnostické obrazy získáváme pomocí ionizujícího záření - skiagrafie. Oddělení je vybaveno rovněž moderním RTG přístrojem s přímou digitalizací. Přístroje na RTG oddělení jsou pravidelně kontrolovány a proměřovány, a tudíž odpovídají evropským normám týkajících se zátěže záření pro populaci.',
		42 => 'Oddělení zdravotnických prostředků zajišťuje výdej prostředků zdravotnické techniky na poukazy tj. poukazy na léčebnou a ortopedickou pomůcku. Zároveň nabízí volně dostupný zdravotnický materiál za hotové.',
		37 => 'Zajišťujeme vyšetření zraku lékařem bez čekání, kompletní péči o Váš zrak, včetně odborných vyšetření a aplikaci kontaktních čoček.',
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
 * Smaže starý obsah CPT "sluzba" (na úvodní stránce ho nahradily zvýrazněná
 * oddělení, viz kolf_department_highlight_overrides()). CPT už není
 * registrovaný, ale posty vytvořené předchozím seedem by jinak v databázi
 * zůstaly navždy.
 */
function kolf_purge_legacy_services() {
	$old = get_posts( array(
		'post_type'      => 'sluzba',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	) );
	foreach ( $old as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

/**
 * @return array legacy_id (z Department.sql) => nové WP post ID
 */
function kolf_seed_run_departments() {
	$map        = array();
	$highlights = kolf_department_highlight_overrides();

	foreach ( kolf_seed_departments() as $dept ) {
		$existing = get_posts( array(
			'post_type'      => 'oddeleni',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $dept['legacy_id'],
		) );
		if ( $existing ) {
			$map[ $dept['legacy_id'] ] = $existing[0]->ID;
			kolf_seed_apply_highlight( $existing[0], $dept['legacy_id'], $highlights );
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

		kolf_seed_apply_highlight( get_post( $post_id ), $dept['legacy_id'], $highlights );

		$map[ $dept['legacy_id'] ] = $post_id;
	}

	return $map;
}

/**
 * Nastaví "Zvýraznit" a připojí marketingový popis (viz kolf_department_highlight_overrides())
 * pod stávající obsah oddělení. Pozná se podle 'kolf_highlight_seeded', jestli už proběhlo —
 * díky tomu funguje i jako dodatečný reseed na instalaci, kde oddělení už dřív vznikla
 * (v3/v4), a zároveň nepřepíše pozdější ruční úpravu příznaku "Zvýraznit" v adminu.
 */
function kolf_seed_apply_highlight( $post, $legacy_id, $highlights ) {
	if ( ! isset( $highlights[ $legacy_id ] ) || get_post_meta( $post->ID, 'kolf_highlight_seeded', true ) ) {
		return;
	}

	$content = trim( $post->post_content . ( $post->post_content ? "\n\n" : '' ) . $highlights[ $legacy_id ] );
	wp_update_post( array( 'ID' => $post->ID, 'post_content' => $content ) );

	update_post_meta( $post->ID, 'kolf_highlight', 1 );
	update_post_meta( $post->ID, 'kolf_highlight_seeded', 1 );
}

/**
 * legacy_id (Phone.Id) => pořadí a popisek pro box "Rychlá čísla" na úvodní
 * stránce. Editorial volba, v produkční DB tohle není.
 */
function kolf_phone_highlight_overrides() {
	return array(
		1162 => array( 'order' => 1, 'label' => __( 'Lékárna', 'kolf' ) ),               // 466 753 332
		81   => array( 'order' => 2, 'label' => __( 'Rentgen', 'kolf' ) ),               // 466 753 264
		85   => array( 'order' => 3, 'label' => __( 'Zdravotnické potřeby', 'kolf' ) ),  // 466 753 272
	);
}

/**
 * Nastaví "Zvýraznit" + pořadí/popisek u telefonu (viz kolf_phone_highlight_overrides()).
 * Stejná logika jako kolf_seed_apply_highlight() u oddělení — funguje i jako
 * dodatečný reseed a nepřepíše pozdější ruční úpravu v adminu.
 */
function kolf_seed_apply_phone_highlight( $phone_id, $legacy_id, $highlights ) {
	if ( ! isset( $highlights[ $legacy_id ] ) || get_post_meta( $phone_id, 'kolf_highlight_seeded', true ) ) {
		return;
	}

	update_post_meta( $phone_id, 'kolf_highlight', 1 );
	update_post_meta( $phone_id, 'kolf_quick_order', $highlights[ $legacy_id ]['order'] );
	update_post_meta( $phone_id, 'kolf_quick_label', $highlights[ $legacy_id ]['label'] );
	update_post_meta( $phone_id, 'kolf_highlight_seeded', 1 );
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
	$highlights = kolf_phone_highlight_overrides();

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
			kolf_seed_apply_phone_highlight( $existing[0]->ID, $phone['legacy_id'], $highlights );
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

		kolf_seed_apply_phone_highlight( $post_id, $phone['legacy_id'], $highlights );
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

/**
 * Obyčejné WordPress stránky (post_type "page") z Page.sql + ContentItem.sql.
 * @return array legacy_id (z Page.sql) => nové WP post ID
 */
function kolf_seed_run_pages() {
	$map = array();

	foreach ( kolf_seed_pages() as $page ) {
		$existing = get_posts( array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'meta_key'       => 'kolf_legacy_id',
			'meta_value'     => $page['legacy_id'],
		) );
		if ( $existing ) {
			$map[ $page['legacy_id'] ] = $existing[0]->ID;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['content'] ? $page['content'] : '',
			'post_status'  => 'publish',
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_legacy_id', $page['legacy_id'] );
		$map[ $page['legacy_id'] ] = $post_id;
	}

	return $map;
}

/**
 * Postaví nativní WP menu (Vzhled → Menu) ze stromu MenuItem.sql. Technický kořen
 * "MenuTop" se do dat vůbec nedostal (viz inc/data/menu.php) — jeho přímé děti tak
 * tvoří nejvyšší úroveň menu. "Organizační" položky (bez odkazu na stránku, jen
 * nadpis pro podnabídku) se vytvoří jako vlastní odkaz na "#".
 */
function kolf_seed_run_menu( $page_map ) {
	$menu_name = __( 'CMS stránky (import)', 'kolf' );
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		return;
	}

	$items     = kolf_seed_menu();
	$id_map    = array(); // legacy MenuItem.Id => nové ID nav_menu_item postu
	$remaining = $items;
	$safety    = 0;

	// Položky se musí vytvářet v pořadí rodič → dítě, ale v datech nejsou takhle
	// seřazené — v několika průchodech zpracujeme vždy jen ty, jejichž rodič už
	// existuje (nebo je top-level), dokud nezbyde nic.
	while ( $remaining && $safety < 10 ) {
		$safety++;
		$next_remaining = array();

		foreach ( $remaining as $item ) {
			$parent_ready = ( null === $item['parent_legacy_id'] ) || isset( $id_map[ $item['parent_legacy_id'] ] );
			if ( ! $parent_ready ) {
				$next_remaining[] = $item;
				continue;
			}

			$existing = get_posts( array(
				'post_type'      => 'nav_menu_item',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => 'kolf_legacy_id',
				'meta_value'     => $item['legacy_id'],
			) );
			if ( $existing ) {
				$id_map[ $item['legacy_id'] ] = $existing[0]->ID;
				continue;
			}

			$parent_wp_id = $item['parent_legacy_id'] ? ( $id_map[ $item['parent_legacy_id'] ] ?? 0 ) : 0;

			$args = array(
				'menu-item-title'     => $item['title'],
				'menu-item-parent-id' => $parent_wp_id,
				'menu-item-position'  => $item['order'],
				'menu-item-status'    => 'publish',
			);

			if ( $item['is_folder'] || ! $item['page_legacy_id'] || ! isset( $page_map[ $item['page_legacy_id'] ] ) ) {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = '#';
			} else {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $page_map[ $item['page_legacy_id'] ];
			}

			$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
			if ( is_wp_error( $item_id ) || ! $item_id ) {
				continue;
			}
			update_post_meta( $item_id, 'kolf_legacy_id', $item['legacy_id'] );
			$id_map[ $item['legacy_id'] ] = $item_id;
		}

		if ( count( $next_remaining ) === count( $remaining ) ) {
			break; // žádný posun — v čistých datech by se sem nemělo dojít
		}
		$remaining = $next_remaining;
	}

	// Ať je menu rovnou přiřazené k pozici a jde ho najít ve Vzhled → Menu,
	// aniž by ho bylo nutné nejdřív ručně přiřadit.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['legacy_pages'] ) ) {
		$locations['legacy_pages'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

function kolf_run_seed() {
	if ( get_option( 'kolf_seeded_v6' ) ) {
		return;
	}

	kolf_purge_placeholder_departments();
	kolf_purge_legacy_services();

	$department_map = kolf_seed_run_departments();
	$person_map      = kolf_seed_run_persons( $department_map );
	kolf_seed_run_phones( $department_map, $person_map );
	kolf_seed_run_hours( $department_map, $person_map );

	$page_map = kolf_seed_run_pages();
	kolf_seed_run_menu( $page_map );

	update_option( 'kolf_seeded_v6', 1 );
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
		delete_option( 'kolf_seeded_v6' );
		kolf_run_seed();
		wp_safe_redirect( remove_query_arg( 'kolf_reseed' ) );
		exit;
	}
}
add_action( 'admin_init', 'kolf_maybe_reseed' );
