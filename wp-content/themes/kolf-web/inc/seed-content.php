<?php
/**
 * Počáteční naplnění obsahu (oddělení + zdravotnické služby) podle podkladu z návrhu.
 * Spouští se jen jednou, při aktivaci tématu — pokud obsah už existuje, nic nepřepisuje.
 * Vše je poté běžně editovatelné v adminu (Oddělení a ambulance / Zdravotnické služby).
 */

defined( 'ABSPATH' ) || exit;

function kolf_seed_departments() {
	// [ název, patro, kontakt (lékař/telefon), pořadí v "Rychlá čísla" (volitelné), krátký label pro "Rychlá čísla" (volitelný) ]
	return array(
		array( 'Praktický lékař', 0, 'MUDr. Hockeová, Junek, Kotasová, Miro, Psutka' ),
		array( 'Všeobecný praktický lékař', 2, 'MUDr. Martin Weber' ),
		array( 'Praktický lékař Ambulance PENTA', 2, 'MUDr. Slaninková, Rademacherová, Pěchoučková' ),
		array( 'Chirurgie', 1, 'MUDr. Jiří Široký' ),
		array( 'Chirurgická ambulance PROKTO', 3, 'MUDr. Flašar, Kasík' ),
		array( 'Dětská chirurgie a chirurgie DCH CHA s.r.o.', 1, '466 753 234' ),
		array( 'Ortopedie 1. patro', 1, 'MUDr. Erben, Haltuch, Haltuchová, Vávra' ),
		array( 'Ortopedie MP praxe', 1, '739 793 549' ),
		array( 'Urologie', 1, 'MUDr. Eliášová, Lucký' ),
		array( 'Gynekologie', 4, '777 021 111' ),
		array( 'Gynekologie MUDr. Robert Středa s.r.o.', 3, '608 656 967' ),
		array( 'Neurologie, Spánková poradna INSPAMED', 3, '466 753 315' ),
		array( 'Oční ambulance', 3, 'MUDr. Zbyněk Popelka' ),
		array( 'Oční ordinace', 2, 'MUDr. Lenka Niederhoferová' ),
		array( 'Oční optika LYNX Optik s.r.o.', 0, '466 753 240' ),
		array( 'Ušní, nosní, krční, audio', 2, 'ORL HRDLIČKA s.r.o.' ),
		array( 'Stomatologie 3. patro', 3, 'MDDr. Víšek, Kratochvílová' ),
		array( 'Stomatologie DentaLou s.r.o.', 1, 'MDDr. Jitka Loužilová' ),
		array( 'Dentální hygiena', 2, 'Lucie Weisová, Dis.' ),
		array( 'Ortodoncie', 2, 'MUDr. Švébišová/Klusoňová' ),
		array( 'Lékárna', 0, '800 420 420', 2, 'Lékárna' ),
		array( 'Rentgen', 1, '466 753 264', 3, 'Rentgen' ),
		array( 'Ultrazvuk', 1, 'MUDr. Aleš Machek' ),
		array( 'Denzitometrie', 3, '608 656 969' ),
		array( 'Odběry krve, MeDiLa s.r.o.', 2, 'laboratoř' ),
		array( 'Zdravotnické potřeby MEDESA care', 0, '466 753 272', 4, 'Zdravotnické potřeby' ),
		array( 'Nefrologie, hemodialýza – Fresenius', -1, '460 000 546' ),
		array( 'Kardiologické centrum AGEL', 4, '4. patro' ),
		array( 'Středisko očkování a cestovního lékařství', 4, 'MUDr. Lukešová, Slezák' ),
		array( 'Operační sály, lůžkový stacionář', 1, 'MUDr. Jan Vanáč' ),
		array( 'Gastroenterologie, interna, hepatologie', 1, 'MUDr. Josef Erben' ),
		array( 'Sportovní medicína – REHABIUM s.r.o.', 2, 'MUDr. Tomáš Vrbica' ),
		array( 'MASÁŽE', 2, 'Lenka Prachařová' ),
		array( 'Nutriční ambulance', 3, 'Bc. Erika Dykastová' ),
		array( 'PSYCHE s.r.o. Psychiatrie', 0, 'MUDr. Lenka Růžičková' ),
	);
}

function kolf_seed_services() {
	// [ název, podtitul, meta řádek, popis ]
	return array(
		array(
			'Lékárna', 'Lékárna na poliklinice', 'Přízemí · 800 420 420',
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

function kolf_run_seed() {
	if ( get_option( 'kolf_seeded_v1' ) ) {
		return;
	}

	foreach ( kolf_seed_departments() as $dept ) {
		list( $name, $floor, $contact ) = $dept;

		if ( get_page_by_title( $name, OBJECT, 'oddeleni' ) ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'oddeleni',
			'post_title'  => $name,
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, 'kolf_floor', $floor );
		update_post_meta( $post_id, 'kolf_contact', $contact );

		if ( isset( $dept[3] ) ) {
			update_post_meta( $post_id, 'kolf_quick_order', (int) $dept[3] );
		}
		if ( isset( $dept[4] ) ) {
			update_post_meta( $post_id, 'kolf_quick_label', $dept[4] );
		}
	}

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

	update_option( 'kolf_seeded_v1', 1 );
}
add_action( 'after_switch_theme', 'kolf_run_seed' );

/**
 * Bez tohohle by /oddeleni/ po aktivaci tématu vracelo 404, dokud by někdo
 * ručně neuložil Nastavení → Trvalé odkazy.
 */
function kolf_flush_rewrites_on_activation() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kolf_flush_rewrites_on_activation' );

/**
 * Ruční spuštění pro lokální vývoj: navštivte /wp-admin/?kolf_reseed=1 přihlášeni jako administrátor.
 */
function kolf_maybe_reseed() {
	if ( isset( $_GET['kolf_reseed'] ) && current_user_can( 'manage_options' ) ) {
		delete_option( 'kolf_seeded_v1' );
		kolf_run_seed();
		wp_safe_redirect( remove_query_arg( 'kolf_reseed' ) );
		exit;
	}
}
add_action( 'admin_init', 'kolf_maybe_reseed' );
