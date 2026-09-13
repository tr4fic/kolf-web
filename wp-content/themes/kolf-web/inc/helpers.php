<?php
/**
 * Pomocné funkce pro práci s daty oddělení / osob / telefonů / hodin.
 */

defined( 'ABSPATH' ) || exit;

/**
 * "466753264" -> "466 753 264". Cokoliv, co nemá přesně 9 číslic, vrátí beze změny
 * (ať to nerozbije divné/zahraniční/staré formáty z historických dat).
 */
function kolf_format_phone( $number ) {
	$digits = preg_replace( '/\s+/', '', (string) $number );
	if ( 9 !== strlen( $digits ) || ! ctype_digit( $digits ) ) {
		return $digits;
	}
	return substr( $digits, 0, 3 ) . ' ' . substr( $digits, 3, 3 ) . ' ' . substr( $digits, 6, 3 );
}

/**
 * Jméno osoby ve tvaru "MUDr. Jméno Příjmení, Ph.D." pro zobrazení.
 */
function kolf_person_display_name( $person_id ) {
	$parts = array();
	$before = get_post_meta( $person_id, 'kolf_honor_before', true );
	if ( $before ) {
		$parts[] = $before;
	}
	$parts[] = get_the_title( $person_id );

	$name = implode( ' ', $parts );

	$after = get_post_meta( $person_id, 'kolf_honor_after', true );
	if ( $after ) {
		$name .= ', ' . $after;
	}
	return $name;
}

/**
 * Telefonní čísla patřící oddělení / osobě (formátovaná, viz kolf_format_phone()).
 */
function kolf_get_department_phones( $department_id ) {
	return kolf_get_phones_by_parent( 'kolf_phone_department_id', $department_id );
}

function kolf_get_person_phones( $person_id ) {
	return kolf_get_phones_by_parent( 'kolf_phone_person_id', $person_id );
}

function kolf_get_phones_by_parent( $meta_key, $parent_id ) {
	$posts = get_posts( array(
		'post_type'      => 'telefon',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
	) );

	return array_map( function ( $post ) {
		return kolf_format_phone( get_post_meta( $post->ID, 'kolf_phone_number', true ) );
	}, $posts );
}

/**
 * Všechna oddělení + osoby jako jeden index pro živé vyhledávání na úvodní stránce.
 */
function kolf_get_search_index() {
	$out = array();

	$departments = get_posts( array( 'post_type' => 'oddeleni', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	foreach ( $departments as $dept ) {
		$out[] = array(
			'name' => html_entity_decode( $dept->post_title, ENT_QUOTES ),
			'meta' => kolf_floor_label( get_post_meta( $dept->ID, 'kolf_floor', true ) ),
			'url'  => get_permalink( $dept ),
		);
	}

	$persons = get_posts( array( 'post_type' => 'osoba', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	foreach ( $persons as $person ) {
		$dept_id  = (int) get_post_meta( $person->ID, 'kolf_department_id', true );
		$dept_str = $dept_id ? get_the_title( $dept_id ) : get_post_meta( $person->ID, 'kolf_specialization', true );
		$out[]    = array(
			'name' => html_entity_decode( kolf_person_display_name( $person->ID ), ENT_QUOTES ),
			'meta' => $dept_str ? $dept_str : kolf_floor_label( get_post_meta( $person->ID, 'kolf_floor', true ) ),
			'url'  => get_permalink( $person ),
		);
	}

	return $out;
}

/**
 * Oddělení označená pro box "Rychlá čísla" na úvodní stránce, seřazená podle kolf_quick_order.
 * Číslo bere první telefon patřící danému oddělení.
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
		$phones = kolf_get_department_phones( $post->ID );
		if ( empty( $phones ) ) {
			continue;
		}
		$label = get_post_meta( $post->ID, 'kolf_quick_label', true );
		$out[] = array(
			'label'  => $label ? $label : $post->post_title,
			'number' => $phones[0],
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
		$floor_raw = get_post_meta( $post->ID, 'kolf_floor', true );
		$floor     = ( '' === $floor_raw ) ? 'none' : (int) $floor_raw;
		if ( ! isset( $groups[ $floor ] ) ) {
			$groups[ $floor ] = array();
		}
		$groups[ $floor ][] = $post;
	}
	uksort( $groups, function ( $a, $b ) {
		if ( 'none' === $a ) return 1;
		if ( 'none' === $b ) return -1;
		return $a <=> $b;
	} );
	return $groups;
}

/**
 * Osoby patřící danému oddělení, seřazené podle příjmení.
 */
function kolf_get_department_persons( $department_id ) {
	$posts = get_posts( array(
		'post_type'      => 'osoba',
		'posts_per_page' => -1,
		'meta_key'       => 'kolf_department_id',
		'meta_value'     => $department_id,
	) );
	usort( $posts, function ( $a, $b ) {
		return strcasecmp( get_post_meta( $a->ID, 'kolf_last_name', true ), get_post_meta( $b->ID, 'kolf_last_name', true ) );
	} );
	return $posts;
}

/**
 * Všechny osoby seskupené podle prvního písmene příjmení, pro archiv "Osoby".
 */
function kolf_get_persons_grouped_alpha() {
	$posts = get_posts( array( 'post_type' => 'osoba', 'posts_per_page' => -1 ) );
	usort( $posts, function ( $a, $b ) {
		return strcasecmp( get_post_meta( $a->ID, 'kolf_last_name', true ), get_post_meta( $b->ID, 'kolf_last_name', true ) );
	} );

	$groups = array();
	foreach ( $posts as $post ) {
		$last = get_post_meta( $post->ID, 'kolf_last_name', true );
		$letter = $last ? mb_strtoupper( mb_substr( $last, 0, 1 ) ) : '?';
		if ( ! isset( $groups[ $letter ] ) ) {
			$groups[ $letter ] = array();
		}
		$groups[ $letter ][] = $post;
	}
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

/**
 * Dny v týdnu, ve stejném pořadí, v jakém se ukládají a zobrazují všude v tématu.
 */
function kolf_hours_days() {
	return array(
		'monday'    => __( 'Pondělí', 'kolf' ),
		'tuesday'   => __( 'Úterý', 'kolf' ),
		'wednesday' => __( 'Středa', 'kolf' ),
		'thursday'  => __( 'Čtvrtek', 'kolf' ),
		'friday'    => __( 'Pátek', 'kolf' ),
	);
}

/**
 * Rozvrhy (ordinační/provozní hodiny) patřící oddělení / osobě. Vrací pole
 * [ 'type' => 'ordinacni'|'provozni', 'note' => string, 'days' => array( den => [from1,to1,from2,to2] ) ].
 */
function kolf_get_department_hours( $department_id ) {
	return kolf_get_hours_by_parent( 'kolf_hours_department_id', $department_id );
}

function kolf_get_person_hours( $person_id ) {
	return kolf_get_hours_by_parent( 'kolf_hours_person_id', $person_id );
}

function kolf_get_hours_by_parent( $meta_key, $parent_id ) {
	$posts = get_posts( array(
		'post_type'      => 'hodiny',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
	) );

	return array_map( function ( $post ) {
		return array(
			'type' => get_post_meta( $post->ID, 'kolf_hours_type', true ),
			'note' => get_post_meta( $post->ID, 'kolf_hours_note', true ),
			'days' => get_post_meta( $post->ID, 'kolf_hours_days', true ),
		);
	}, $posts );
}

/**
 * "07:30" + "11:30" -> "7:30–11:30" (bez zbytečné úvodní nuly, s pomlčkou).
 */
function kolf_format_hours_range( $from, $to ) {
	if ( ! $from || ! $to ) {
		return '';
	}
	$strip_zero = function ( $t ) {
		return preg_replace( '/^0(\d:)/', '$1', $t );
	};
	return $strip_zero( $from ) . '–' . $strip_zero( $to );
}

/**
 * Vypíše jeden rozvrh (nadpis podle typu, dny s hodinami, poznámka). Dny bez
 * vyplněných hodin se ve výpisu přeskočí.
 */
function kolf_render_hours_block( $hours ) {
	$title = 'provozni' === $hours['type'] ? __( 'Provozní hodiny', 'kolf' ) : __( 'Ordinační hodiny', 'kolf' );
	$days  = is_array( $hours['days'] ) ? $hours['days'] : array();
	?>
	<div class="kolf-hours">
		<h3 class="kolf-hours__title"><?php echo esc_html( $title ); ?></h3>
		<table class="kolf-hours__table">
			<?php foreach ( kolf_hours_days() as $day_key => $day_label ) :
				$day    = $days[ $day_key ] ?? array();
				$range1 = kolf_format_hours_range( $day['from1'] ?? '', $day['to1'] ?? '' );
				$range2 = kolf_format_hours_range( $day['from2'] ?? '', $day['to2'] ?? '' );
				$ranges = array_filter( array( $range1, $range2 ) );
				if ( empty( $ranges ) ) {
					continue;
				}
				?>
				<tr>
					<th><?php echo esc_html( $day_label ); ?></th>
					<td><?php echo esc_html( implode( ', ', $ranges ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php if ( ! empty( $hours['note'] ) ) : ?>
			<p class="kolf-hours__note"><?php echo esc_html( $hours['note'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
