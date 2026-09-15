<?php
/**
 * SEO: meta popisky, Open Graph / Twitter Card a strukturovaná data (JSON-LD).
 * Web dřív neměl žádné z tohoto — vlastní řešení bez pluginu (Yoast apod.),
 * v duchu filozofie "minimum pluginů" z README.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Textový popisek (meta description / og:description) pro aktuální stránku,
 * oříznutý na ~160 znaků. Vždy čistý text bez HTML.
 */
function kolf_meta_description() {
	if ( is_front_page() ) {
		$desc = sprintf(
			/* translators: 1: adresa, 2: telefon ústředny */
			__( 'Poliklinika KOLF Pardubice — více než 40 ambulancí a oddělení pod jednou střechou. Najděte lékaře, oddělení nebo telefonní číslo. %1$s, ústředna %2$s.', 'kolf' ),
			get_theme_mod( 'kolf_address', 'Masarykovo nám. 2667, Pardubice' ),
			get_theme_mod( 'kolf_phone', '466 753 111' )
		);
	} elseif ( is_post_type_archive( 'oddeleni' ) ) {
		$desc = __( 'Kompletní seznam oddělení a ambulancí Polikliniky KOLF Pardubice podle pater, s telefonními čísly a ordinačními hodinami.', 'kolf' );
	} elseif ( is_post_type_archive( 'osoba' ) ) {
		$desc = __( 'Seznam lékařů a zdravotnického personálu Polikliniky KOLF Pardubice — vyhledejte svého lékaře podle jména nebo oddělení.', 'kolf' );
	} elseif ( is_singular( 'oddeleni' ) ) {
		$id     = get_the_ID();
		$bits   = array( get_the_title( $id ), kolf_floor_label( get_post_meta( $id, 'kolf_floor', true ) ) );
		$phones = kolf_get_department_phones( $id );
		if ( $phones ) {
			$bits[] = sprintf( __( 'telefon %s', 'kolf' ), reset( $phones ) );
		}
		$bits[] = __( 'Poliklinika KOLF Pardubice', 'kolf' );
		$desc   = implode( ' — ', array_filter( $bits ) );
	} elseif ( is_singular( 'osoba' ) ) {
		$id      = get_the_ID();
		$spec    = get_post_meta( $id, 'kolf_specialization', true );
		$dept_id = (int) get_post_meta( $id, 'kolf_department_id', true );
		$bits    = array( kolf_person_display_name( $id ) );
		if ( $spec ) {
			$bits[] = $spec;
		} elseif ( $dept_id ) {
			$bits[] = get_the_title( $dept_id );
		}
		$phones = kolf_get_person_phones( $id );
		if ( $phones ) {
			$bits[] = sprintf( __( 'telefon %s', 'kolf' ), reset( $phones ) );
		}
		$bits[] = __( 'Poliklinika KOLF Pardubice', 'kolf' );
		$desc   = implode( ' — ', array_filter( $bits ) );
	} elseif ( is_singular() ) {
		$excerpt = get_the_excerpt();
		$desc    = $excerpt ? $excerpt : wp_strip_all_tags( get_the_content() );
	} else {
		$desc = get_bloginfo( 'description' );
	}

	$desc = wp_strip_all_tags( (string) $desc );
	$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
	if ( '' === $desc ) {
		return '';
	}
	if ( mb_strlen( $desc ) > 160 ) {
		$desc = mb_substr( $desc, 0, 157 ) . '…';
	}
	return $desc;
}

/**
 * Absolutní URL aktuální stránky (pro og:url) — pro singuláry a archivy
 * oddělení/osob přesně, jinak z aktuálního requestu.
 */
function kolf_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_post_type_archive( 'oddeleni' ) ) {
		return get_post_type_archive_link( 'oddeleni' );
	}
	if ( is_post_type_archive( 'osoba' ) ) {
		return get_post_type_archive_link( 'osoba' );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Obrázek pro og:image / twitter:image — vyobrazený obrázek stránky, jinak
 * logo webu, jinak obrázek u domovské stránky (foto/mapa budovy v Kontaktu).
 */
function kolf_og_image_url() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $src ) {
			return $src[0];
		}
	}

	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$src = wp_get_attachment_image_src( $custom_logo_id, 'large' );
		if ( $src ) {
			return $src[0];
		}
	}

	$front_page_id = (int) get_option( 'page_on_front' );
	if ( $front_page_id && has_post_thumbnail( $front_page_id ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $front_page_id ), 'large' );
		if ( $src ) {
			return $src[0];
		}
	}

	return '';
}

/**
 * <meta name="description">, Open Graph a Twitter Card — na každé veřejné stránce.
 */
function kolf_output_meta_tags() {
	if ( is_admin() ) {
		return;
	}

	$description = kolf_meta_description();
	if ( '' === $description ) {
		return;
	}

	$title = wp_get_document_title();
	$type  = is_singular() ? 'article' : 'website';
	$image = kolf_og_image_url();
	$url   = kolf_current_url();

	echo "\n" . '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";

	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:locale" content="cs_CZ">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	if ( $url ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	}
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	echo '<meta name="twitter:card" content="' . esc_attr( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'kolf_output_meta_tags', 1 );

/**
 * Vlastní <title> podle typu stránky — konkrétnější a bohatší na klíčová
 * slova než holý název příspěvku (obor, "ordinační hodiny", "Pardubice"…).
 * Nahrazuje celé pole, aby výsledek byl vždy jeden předvídatelný řetězec
 * bez závislosti na výchozí WP logice pro statickou úvodní stránku.
 */
function kolf_document_title_parts( $title ) {
	$site = get_bloginfo( 'name' );

	if ( is_front_page() ) {
		return array( 'title' => $site . ' Pardubice – ambulance, lékaři a kontakty' );
	}

	if ( is_post_type_archive( 'oddeleni' ) ) {
		return array( 'title' => __( 'Oddělení a ambulance', 'kolf' ) . ' | ' . $site );
	}

	if ( is_post_type_archive( 'osoba' ) ) {
		return array( 'title' => __( 'Lékaři a personál', 'kolf' ) . ' | ' . $site );
	}

	if ( is_singular( 'oddeleni' ) ) {
		return array( 'title' => get_the_title() . ' – ' . __( 'ordinační hodiny a kontakt', 'kolf' ) . ' | ' . $site );
	}

	if ( is_singular( 'osoba' ) ) {
		$id   = get_the_ID();
		$spec = get_post_meta( $id, 'kolf_specialization', true );
		$name = kolf_person_display_name( $id ) . ( $spec ? ' – ' . $spec : '' );
		return array( 'title' => $name . ' | ' . $site );
	}

	return $title;
}
add_filter( 'document_title_parts', 'kolf_document_title_parts' );

/**
 * Adresa Polikliniky jako schema.org PostalAddress — sdílená pro organizaci
 * i jednotlivá oddělení (budova je jedna, adresa stejná).
 */
function kolf_schema_address() {
	$street    = trim( strtok( get_theme_mod( 'kolf_address', 'Masarykovo nám. 2667, Pardubice' ), ',' ) );
	$address_2 = get_theme_mod( 'kolf_address_2', 'Poliklinika KOLF s.r.o., Masarykovo nám. 2667, Pardubice, 53002' );

	$postal = '';
	if ( preg_match( '/(\d{3})\s?(\d{2})\s*$/', $address_2, $m ) ) {
		$postal = $m[1] . ' ' . $m[2];
	}

	return array_filter( array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $street,
		'addressLocality' => 'Pardubice',
		'postalCode'      => $postal,
		'addressCountry'  => 'CZ',
	) );
}

/**
 * Organizace jako celek (MedicalClinic) — vypisuje se na každé stránce,
 * ostatní schémata se na ni odkazují přes @id, ať Google pozná, že jde
 * o stejný subjekt všude na webu.
 */
function kolf_organization_schema() {
	$schema = array_filter( array(
		'@context'  => 'https://schema.org',
		'@type'     => 'MedicalClinic',
		'@id'       => home_url( '/#organization' ),
		'name'      => get_bloginfo( 'name' ),
		'url'       => home_url( '/' ),
		'telephone' => get_theme_mod( 'kolf_phone', '466 753 111' ),
		'email'     => get_theme_mod( 'kolf_email', 'info@kolf.cz' ),
		'address'   => kolf_schema_address(),
	) );

	$image = kolf_og_image_url();
	if ( $image ) {
		$schema['image'] = $image;
		$schema['logo']  = $image;
	}

	return $schema;
}

/**
 * Rozvrh (ordinační/provozní hodiny) v našem formátu -> pole
 * OpeningHoursSpecification. Dny/hodiny bez vyplněných časů se přeskočí.
 */
function kolf_hours_to_schema( $hours_blocks ) {
	$day_map = array(
		'monday'    => 'Monday',
		'tuesday'   => 'Tuesday',
		'wednesday' => 'Wednesday',
		'thursday'  => 'Thursday',
		'friday'    => 'Friday',
	);

	$specs = array();
	foreach ( $hours_blocks as $block ) {
		$days = is_array( $block['days'] ) ? $block['days'] : array();
		foreach ( $day_map as $key => $schema_day ) {
			$day = $days[ $key ] ?? array();
			foreach ( array( array( 'from1', 'to1' ), array( 'from2', 'to2' ) ) as $pair ) {
				$from = $day[ $pair[0] ] ?? '';
				$to   = $day[ $pair[1] ] ?? '';
				if ( $from && $to ) {
					$specs[] = array(
						'@type'     => 'OpeningHoursSpecification',
						'dayOfWeek' => 'https://schema.org/' . $schema_day,
						'opens'     => $from,
						'closes'    => $to,
					);
				}
			}
		}
	}
	return $specs;
}

/**
 * Detail oddělení jako podřízená MedicalClinic (stejná budova/adresa,
 * vlastní telefon a ordinační hodiny, odkaz na hlavní organizaci).
 */
function kolf_department_schema( $post_id ) {
	$phones = kolf_get_department_phones( $post_id );
	$hours  = kolf_hours_to_schema( kolf_get_department_hours( $post_id ) );

	$schema = array_filter( array(
		'@context'           => 'https://schema.org',
		'@type'              => 'MedicalClinic',
		'name'               => get_the_title( $post_id ),
		'url'                => get_permalink( $post_id ),
		'telephone'          => $phones ? reset( $phones ) : '',
		'address'            => kolf_schema_address(),
		'parentOrganization' => array( '@id' => home_url( '/#organization' ) ),
	) );

	if ( $hours ) {
		$schema['openingHoursSpecification'] = $hours;
	}

	return $schema;
}

/**
 * Detail osoby (lékaře/personálu) jako Person — obor jako jobTitle,
 * pracoviště (oddělení, nebo celá poliklinika, pokud oddělení nemá).
 */
function kolf_person_schema( $post_id ) {
	$phones  = kolf_get_person_phones( $post_id );
	$dept_id = (int) get_post_meta( $post_id, 'kolf_department_id', true );
	$spec    = get_post_meta( $post_id, 'kolf_specialization', true );

	$schema = array_filter( array(
		'@context'  => 'https://schema.org',
		'@type'     => 'Person',
		'name'      => kolf_person_display_name( $post_id ),
		'url'       => get_permalink( $post_id ),
		'jobTitle'  => $spec,
		'telephone' => $phones ? reset( $phones ) : '',
	) );

	if ( $dept_id && get_post( $dept_id ) ) {
		$schema['worksFor'] = array(
			'@type' => 'MedicalClinic',
			'name'  => get_the_title( $dept_id ),
			'url'   => get_permalink( $dept_id ),
		);
	} else {
		$schema['worksFor'] = array( '@id' => home_url( '/#organization' ) );
	}

	return $schema;
}

/**
 * BreadcrumbList (Domů > Oddělení > konkrétní oddělení, obdobně Osoby) —
 * na archivech i detailu; Google to umí zobrazit přímo ve výsledcích hledání.
 */
function kolf_breadcrumb_schema() {
	$items = array(
		array( 'name' => get_bloginfo( 'name' ), 'item' => home_url( '/' ) ),
	);

	if ( is_post_type_archive( 'oddeleni' ) || is_singular( 'oddeleni' ) ) {
		$items[] = array( 'name' => __( 'Oddělení', 'kolf' ), 'item' => get_post_type_archive_link( 'oddeleni' ) );
		if ( is_singular( 'oddeleni' ) ) {
			$items[] = array( 'name' => get_the_title(), 'item' => get_permalink() );
		}
	} elseif ( is_post_type_archive( 'osoba' ) || is_singular( 'osoba' ) ) {
		$items[] = array( 'name' => __( 'Osoby', 'kolf' ), 'item' => get_post_type_archive_link( 'osoba' ) );
		if ( is_singular( 'osoba' ) ) {
			$items[] = array( 'name' => kolf_person_display_name( get_the_ID() ), 'item' => get_permalink() );
		}
	} else {
		return null;
	}

	$list_items = array();
	foreach ( $items as $i => $item ) {
		$list_items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $item['name'],
			'item'     => $item['item'],
		);
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list_items,
	);
}

/**
 * Vypíše všechna JSON-LD schémata pro aktuální stránku do patičky.
 */
function kolf_output_structured_data() {
	if ( is_admin() ) {
		return;
	}

	$schemas = array( kolf_organization_schema() );

	if ( is_singular( 'oddeleni' ) ) {
		$schemas[] = kolf_department_schema( get_the_ID() );
	} elseif ( is_singular( 'osoba' ) ) {
		$schemas[] = kolf_person_schema( get_the_ID() );
	}

	$breadcrumbs = kolf_breadcrumb_schema();
	if ( $breadcrumbs ) {
		$schemas[] = $breadcrumbs;
	}

	foreach ( $schemas as $schema ) {
		if ( empty( $schema ) ) {
			continue;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}
add_action( 'wp_footer', 'kolf_output_structured_data', 20 );
