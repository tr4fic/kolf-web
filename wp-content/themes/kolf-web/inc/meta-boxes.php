<?php
/**
 * Jednoduché vlastní meta boxy — bez ACF nebo jiného pluginu.
 *
 * Telefonní čísla (CPT "telefon") se nikde nespravují samostatně — zadávají se
 * rovnou ve formuláři oddělení/osoby jako opakovatelný seznam a na pozadí se
 * ukládají/mažou jako vlastní záznamy propojené přes kolf_phone_department_id
 * / kolf_phone_person_id.
 */

defined( 'ABSPATH' ) || exit;

function kolf_register_meta_boxes() {
	add_meta_box( 'kolf_oddeleni_meta', __( 'Údaje o oddělení', 'kolf' ), 'kolf_render_oddeleni_meta_box', 'oddeleni', 'normal', 'default' );
	add_meta_box( 'kolf_osoba_meta', __( 'Údaje o osobě', 'kolf' ), 'kolf_render_osoba_meta_box', 'osoba', 'normal', 'default' );
	add_meta_box( 'kolf_sluzba_meta', __( 'Údaje o službě', 'kolf' ), 'kolf_render_sluzba_meta_box', 'sluzba', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'kolf_register_meta_boxes' );

function kolf_render_oddeleni_meta_box( $post ) {
	wp_nonce_field( 'kolf_save_oddeleni_meta', 'kolf_oddeleni_meta_nonce' );
	$is_ordination = get_post_meta( $post->ID, 'kolf_is_ordination', true );
	$floor         = get_post_meta( $post->ID, 'kolf_floor', true );
	$door_number   = get_post_meta( $post->ID, 'kolf_door_number', true );
	$location_spec = get_post_meta( $post->ID, 'kolf_location_specification', true );
	$web           = get_post_meta( $post->ID, 'kolf_web', true );
	$quick_order   = get_post_meta( $post->ID, 'kolf_quick_order', true );
	$quick_label   = get_post_meta( $post->ID, 'kolf_quick_label', true );
	?>
	<p>
		<label><input type="checkbox" name="kolf_is_ordination" value="1" <?php checked( $is_ordination, 1 ); ?>> <?php esc_html_e( 'Jde o ordinaci/ambulanci (ne provozní místnost typu lékárna, WC, ředitelství…)', 'kolf' ); ?></label>
	</p>
	<p>
		<label for="kolf_floor"><strong><?php esc_html_e( 'Patro', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_floor" name="kolf_floor" value="<?php echo esc_attr( $floor ); ?>" step="1" placeholder="-1 = suterén, 0 = přízemí">
	</p>
	<p>
		<label for="kolf_door_number"><strong><?php esc_html_e( 'Číslo dveří', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_door_number" name="kolf_door_number" value="<?php echo esc_attr( $door_number ); ?>" step="1">
	</p>
	<p>
		<label for="kolf_web"><strong><?php esc_html_e( 'Web', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_web" name="kolf_web" value="<?php echo esc_attr( $web ); ?>" style="width:100%" placeholder="https://…">
	</p>
	<p>
		<label for="kolf_location_specification"><strong><?php esc_html_e( 'Upřesnění / ordinační doba (může obsahovat HTML)', 'kolf' ); ?></strong></label><br>
		<textarea id="kolf_location_specification" name="kolf_location_specification" rows="4" style="width:100%"><?php echo esc_textarea( $location_spec ); ?></textarea>
	</p>
	<hr>
	<p>
		<label for="kolf_quick_order"><strong><?php esc_html_e( 'Pořadí v „Rychlá čísla“ na úvodní stránce', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_quick_order" name="kolf_quick_order" value="<?php echo esc_attr( $quick_order ); ?>" min="0" step="1" placeholder="0 = nezobrazovat">
	</p>
	<p>
		<label for="kolf_quick_label"><strong><?php esc_html_e( 'Krátký popisek pro „Rychlá čísla“ (nepovinné)', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_quick_label" name="kolf_quick_label" value="<?php echo esc_attr( $quick_label ); ?>" style="width:100%" placeholder="Jinak se použije název oddělení">
	</p>
	<hr>
	<p><strong><?php esc_html_e( 'Telefonní čísla oddělení', 'kolf' ); ?></strong></p>
	<?php kolf_render_phone_repeater( $post->ID, 'kolf_phone_department_id' ); ?>
	<hr>
	<p><strong><?php esc_html_e( 'Ordinační / provozní hodiny oddělení', 'kolf' ); ?></strong></p>
	<?php kolf_render_hours_repeater( $post->ID, 'kolf_hours_department_id' ); ?>
	<?php
}

function kolf_render_osoba_meta_box( $post ) {
	wp_nonce_field( 'kolf_save_osoba_meta', 'kolf_osoba_meta_nonce' );
	$honor_before   = get_post_meta( $post->ID, 'kolf_honor_before', true );
	$honor_after    = get_post_meta( $post->ID, 'kolf_honor_after', true );
	$specialization = get_post_meta( $post->ID, 'kolf_specialization', true );
	$floor          = get_post_meta( $post->ID, 'kolf_floor', true );
	$door_number    = get_post_meta( $post->ID, 'kolf_door_number', true );
	$ico            = get_post_meta( $post->ID, 'kolf_ico', true );
	$web            = get_post_meta( $post->ID, 'kolf_web', true );
	$department_id  = (int) get_post_meta( $post->ID, 'kolf_department_id', true );

	$departments = get_posts( array( 'post_type' => 'oddeleni', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	?>
	<p class="description"><?php esc_html_e( 'Jméno a příjmení se zadává do pole „Název“ nahoře.', 'kolf' ); ?></p>
	<p>
		<label for="kolf_honor_before"><strong><?php esc_html_e( 'Titul před jménem', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_honor_before" name="kolf_honor_before" value="<?php echo esc_attr( $honor_before ); ?>" placeholder="MUDr.">
	</p>
	<p>
		<label for="kolf_honor_after"><strong><?php esc_html_e( 'Titul za jménem', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_honor_after" name="kolf_honor_after" value="<?php echo esc_attr( $honor_after ); ?>" placeholder="Ph.D.">
	</p>
	<p>
		<label for="kolf_specialization"><strong><?php esc_html_e( 'Specializace', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_specialization" name="kolf_specialization" value="<?php echo esc_attr( $specialization ); ?>" style="width:100%">
	</p>
	<p>
		<label for="kolf_department_id"><strong><?php esc_html_e( 'Oddělení', 'kolf' ); ?></strong></label><br>
		<select id="kolf_department_id" name="kolf_department_id">
			<option value="0"><?php esc_html_e( '— Nepřiřazeno —', 'kolf' ); ?></option>
			<?php foreach ( $departments as $dept ) : ?>
				<option value="<?php echo esc_attr( $dept->ID ); ?>" <?php selected( $department_id, $dept->ID ); ?>><?php echo esc_html( $dept->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="kolf_floor"><strong><?php esc_html_e( 'Patro', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_floor" name="kolf_floor" value="<?php echo esc_attr( $floor ); ?>" step="1">
	</p>
	<p>
		<label for="kolf_door_number"><strong><?php esc_html_e( 'Číslo dveří', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_door_number" name="kolf_door_number" value="<?php echo esc_attr( $door_number ); ?>">
	</p>
	<p>
		<label for="kolf_ico"><strong><?php esc_html_e( 'IČO', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_ico" name="kolf_ico" value="<?php echo esc_attr( $ico ); ?>">
	</p>
	<p>
		<label for="kolf_web"><strong><?php esc_html_e( 'Web', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_web" name="kolf_web" value="<?php echo esc_attr( $web ); ?>" style="width:100%" placeholder="https://…">
	</p>
	<hr>
	<p><strong><?php esc_html_e( 'Telefonní čísla osoby', 'kolf' ); ?></strong></p>
	<?php kolf_render_phone_repeater( $post->ID, 'kolf_phone_person_id' ); ?>
	<hr>
	<p><strong><?php esc_html_e( 'Ordinační / provozní hodiny osoby', 'kolf' ); ?></strong></p>
	<?php kolf_render_hours_repeater( $post->ID, 'kolf_hours_person_id' ); ?>
	<?php
}

function kolf_render_sluzba_meta_box( $post ) {
	wp_nonce_field( 'kolf_save_sluzba_meta', 'kolf_sluzba_meta_nonce' );
	$subtitle = get_post_meta( $post->ID, 'kolf_subtitle', true );
	$meta     = get_post_meta( $post->ID, 'kolf_meta', true );
	?>
	<p>
		<label for="kolf_subtitle"><strong><?php esc_html_e( 'Podtitul', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_subtitle" name="kolf_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" style="width:100%">
	</p>
	<p>
		<label for="kolf_meta"><strong><?php esc_html_e( 'Doplňující řádek (patro, telefon, web)', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_meta" name="kolf_meta" value="<?php echo esc_attr( $meta ); ?>" style="width:100%" placeholder="Přízemí · 800 420 420">
	</p>
	<?php
}

/**
 * Opakovatelný seznam telefonních čísel — společný pro oddělení i osobu.
 * $meta_key je buď 'kolf_phone_department_id', nebo 'kolf_phone_person_id'.
 */
function kolf_render_phone_repeater( $parent_id, $meta_key ) {
	$posts = $parent_id ? get_posts( array(
		'post_type'      => 'telefon',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
	) ) : array();
	?>
	<div class="kolf-phone-repeater">
		<?php foreach ( $posts as $phone ) : ?>
			<p class="kolf-phone-row">
				<input type="hidden" name="kolf_phone_id[]" value="<?php echo esc_attr( $phone->ID ); ?>">
				<input type="text" name="kolf_phone_number[]" value="<?php echo esc_attr( get_post_meta( $phone->ID, 'kolf_phone_number', true ) ); ?>" placeholder="466753111">
			</p>
		<?php endforeach; ?>
		<p class="kolf-phone-row">
			<input type="hidden" name="kolf_phone_id[]" value="">
			<input type="text" name="kolf_phone_number[]" value="" placeholder="466753111">
		</p>
	</div>
	<button type="button" class="button kolf-add-phone"><?php esc_html_e( '+ Přidat číslo', 'kolf' ); ?></button>
	<?php
}

function kolf_save_meta_boxes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['kolf_oddeleni_meta_nonce'] ) && wp_verify_nonce( $_POST['kolf_oddeleni_meta_nonce'], 'kolf_save_oddeleni_meta' ) ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'kolf_is_ordination', isset( $_POST['kolf_is_ordination'] ) ? 1 : 0 );
		update_post_meta( $post_id, 'kolf_floor', isset( $_POST['kolf_floor'] ) && '' !== $_POST['kolf_floor'] ? intval( $_POST['kolf_floor'] ) : '' );
		update_post_meta( $post_id, 'kolf_door_number', isset( $_POST['kolf_door_number'] ) && '' !== $_POST['kolf_door_number'] ? intval( $_POST['kolf_door_number'] ) : '' );
		update_post_meta( $post_id, 'kolf_web', isset( $_POST['kolf_web'] ) ? esc_url_raw( $_POST['kolf_web'] ) : '' );
		update_post_meta( $post_id, 'kolf_location_specification', isset( $_POST['kolf_location_specification'] ) ? wp_kses_post( $_POST['kolf_location_specification'] ) : '' );
		update_post_meta( $post_id, 'kolf_quick_order', isset( $_POST['kolf_quick_order'] ) ? intval( $_POST['kolf_quick_order'] ) : 0 );
		update_post_meta( $post_id, 'kolf_quick_label', isset( $_POST['kolf_quick_label'] ) ? sanitize_text_field( $_POST['kolf_quick_label'] ) : '' );

		kolf_save_phone_repeater( $post_id, 'kolf_phone_department_id' );
		kolf_save_hours_repeater( $post_id, 'kolf_hours_department_id' );
	}

	if ( isset( $_POST['kolf_osoba_meta_nonce'] ) && wp_verify_nonce( $_POST['kolf_osoba_meta_nonce'], 'kolf_save_osoba_meta' ) ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'kolf_honor_before', isset( $_POST['kolf_honor_before'] ) ? sanitize_text_field( $_POST['kolf_honor_before'] ) : '' );
		update_post_meta( $post_id, 'kolf_honor_after', isset( $_POST['kolf_honor_after'] ) ? sanitize_text_field( $_POST['kolf_honor_after'] ) : '' );
		update_post_meta( $post_id, 'kolf_specialization', isset( $_POST['kolf_specialization'] ) ? sanitize_text_field( $_POST['kolf_specialization'] ) : '' );
		update_post_meta( $post_id, 'kolf_floor', isset( $_POST['kolf_floor'] ) && '' !== $_POST['kolf_floor'] ? intval( $_POST['kolf_floor'] ) : '' );
		update_post_meta( $post_id, 'kolf_door_number', isset( $_POST['kolf_door_number'] ) ? sanitize_text_field( $_POST['kolf_door_number'] ) : '' );
		update_post_meta( $post_id, 'kolf_ico', isset( $_POST['kolf_ico'] ) ? sanitize_text_field( $_POST['kolf_ico'] ) : '' );
		update_post_meta( $post_id, 'kolf_web', isset( $_POST['kolf_web'] ) ? esc_url_raw( $_POST['kolf_web'] ) : '' );
		update_post_meta( $post_id, 'kolf_department_id', isset( $_POST['kolf_department_id'] ) ? intval( $_POST['kolf_department_id'] ) : 0 );

		kolf_save_phone_repeater( $post_id, 'kolf_phone_person_id' );
		kolf_save_hours_repeater( $post_id, 'kolf_hours_person_id' );
	}

	if ( isset( $_POST['kolf_sluzba_meta_nonce'] ) && wp_verify_nonce( $_POST['kolf_sluzba_meta_nonce'], 'kolf_save_sluzba_meta' ) ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['kolf_subtitle'] ) ) {
			update_post_meta( $post_id, 'kolf_subtitle', sanitize_text_field( $_POST['kolf_subtitle'] ) );
		}
		if ( isset( $_POST['kolf_meta'] ) ) {
			update_post_meta( $post_id, 'kolf_meta', sanitize_text_field( $_POST['kolf_meta'] ) );
		}
	}
}
add_action( 'save_post', 'kolf_save_meta_boxes' );

/**
 * Uloží opakovatelný seznam telefonních čísel z $_POST['kolf_phone_id'] / ['kolf_phone_number']
 * jako samostatné "telefon" posty propojené s $parent_id přes $meta_key. Prázdné řádky se
 * přeskočí, smazané řádky (existující v DB, ale už neodeslané ve formuláři) se odstraní.
 */
function kolf_save_phone_repeater( $parent_id, $meta_key ) {
	$ids     = isset( $_POST['kolf_phone_id'] ) ? (array) $_POST['kolf_phone_id'] : array();
	$numbers = isset( $_POST['kolf_phone_number'] ) ? (array) $_POST['kolf_phone_number'] : array();

	$existing = get_posts( array(
		'post_type'      => 'telefon',
		'posts_per_page' => -1,
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
		'fields'         => 'ids',
	) );

	$keep = array();
	foreach ( $numbers as $i => $raw_number ) {
		$number = preg_replace( '/\s+/', '', sanitize_text_field( $raw_number ) );
		if ( '' === $number ) {
			continue;
		}

		$phone_id = isset( $ids[ $i ] ) ? intval( $ids[ $i ] ) : 0;
		if ( $phone_id && in_array( $phone_id, $existing, true ) ) {
			wp_update_post( array( 'ID' => $phone_id, 'post_title' => $number ) );
			update_post_meta( $phone_id, 'kolf_phone_number', $number );
			$keep[] = $phone_id;
		} else {
			$new_id = wp_insert_post( array(
				'post_type'   => 'telefon',
				'post_title'  => $number,
				'post_status' => 'publish',
			) );
			if ( $new_id && ! is_wp_error( $new_id ) ) {
				update_post_meta( $new_id, 'kolf_phone_number', $number );
				update_post_meta( $new_id, $meta_key, $parent_id );
				$keep[] = $new_id;
			}
		}
	}

	foreach ( $existing as $id ) {
		if ( ! in_array( $id, $keep, true ) ) {
			wp_delete_post( $id, true );
		}
	}
}

/**
 * Opakovatelný seznam rozvrhů (ordinační/provozní hodiny) — společný pro oddělení i osobu.
 * Na rozdíl od telefonu má každý řádek vlastní podformulář (typ, 5× dopoledne/odpoledne, poznámka),
 * proto se nerenderuje jako jednoduchý input, ale jako celý blok s unikátním klíčem v názvu polí.
 */
function kolf_render_hours_repeater( $parent_id, $meta_key ) {
	$posts = $parent_id ? get_posts( array(
		'post_type'      => 'hodiny',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
	) ) : array();
	?>
	<div class="kolf-hours-repeater">
		<?php foreach ( $posts as $hodiny ) :
			kolf_render_one_hours_block( (string) $hodiny->ID, array(
				'type' => get_post_meta( $hodiny->ID, 'kolf_hours_type', true ),
				'note' => get_post_meta( $hodiny->ID, 'kolf_hours_note', true ),
				'days' => get_post_meta( $hodiny->ID, 'kolf_hours_days', true ),
			) );
		endforeach; ?>
		<?php kolf_render_one_hours_block( 'new0', null ); ?>
	</div>
	<button type="button" class="button kolf-add-hours"><?php esc_html_e( '+ Přidat rozvrh', 'kolf' ); ?></button>
	<?php
}

function kolf_render_one_hours_block( $key, $data ) {
	$type = $data['type'] ?? 'ordinacni';
	$note = $data['note'] ?? '';
	$days = $data['days'] ?? array();
	?>
	<fieldset class="kolf-hours-block">
		<input type="hidden" name="kolf_hours[<?php echo esc_attr( $key ); ?>][id]" value="<?php echo esc_attr( ctype_digit( $key ) ? $key : '' ); ?>">
		<p>
			<label><?php esc_html_e( 'Typ', 'kolf' ); ?><br>
				<select name="kolf_hours[<?php echo esc_attr( $key ); ?>][type]">
					<option value="ordinacni" <?php selected( $type, 'ordinacni' ); ?>><?php esc_html_e( 'Ordinační hodiny', 'kolf' ); ?></option>
					<option value="provozni" <?php selected( $type, 'provozni' ); ?>><?php esc_html_e( 'Provozní hodiny', 'kolf' ); ?></option>
				</select>
			</label>
		</p>
		<table class="widefat kolf-hours-table">
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'Dopoledne od', 'kolf' ); ?></th>
					<th><?php esc_html_e( 'Dopoledne do', 'kolf' ); ?></th>
					<th><?php esc_html_e( 'Odpoledne od', 'kolf' ); ?></th>
					<th><?php esc_html_e( 'Odpoledne do', 'kolf' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( kolf_hours_days() as $day_key => $day_label ) :
					$day = $days[ $day_key ] ?? array();
					?>
					<tr>
						<th><?php echo esc_html( $day_label ); ?></th>
						<td><input type="time" name="kolf_hours[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $day_key ); ?>][from1]" value="<?php echo esc_attr( $day['from1'] ?? '' ); ?>"></td>
						<td><input type="time" name="kolf_hours[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $day_key ); ?>][to1]" value="<?php echo esc_attr( $day['to1'] ?? '' ); ?>"></td>
						<td><input type="time" name="kolf_hours[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $day_key ); ?>][from2]" value="<?php echo esc_attr( $day['from2'] ?? '' ); ?>"></td>
						<td><input type="time" name="kolf_hours[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $day_key ); ?>][to2]" value="<?php echo esc_attr( $day['to2'] ?? '' ); ?>"></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<label><?php esc_html_e( 'Poznámka', 'kolf' ); ?><br>
				<textarea name="kolf_hours[<?php echo esc_attr( $key ); ?>][note]" rows="2" style="width:100%"><?php echo esc_textarea( $note ); ?></textarea>
			</label>
		</p>
	</fieldset>
	<?php
}

/**
 * "HH:MM" nebo prázdno — cokoliv jiného (chybný formát z JS/uživatele) se zahodí.
 */
function kolf_sanitize_time( $value ) {
	$value = trim( (string) $value );
	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
}

/**
 * Uloží opakovatelné rozvrhy z $_POST['kolf_hours'][klíč][...] jako samostatné "hodiny" posty
 * propojené s $parent_id přes $meta_key. Blok bez jediné vyplněné hodiny/poznámky se přeskočí
 * (a pokud předtím existoval, smaže se) — stejná logika jako u telefonů.
 */
function kolf_save_hours_repeater( $parent_id, $meta_key ) {
	$submitted = isset( $_POST['kolf_hours'] ) ? (array) $_POST['kolf_hours'] : array();

	$existing = get_posts( array(
		'post_type'      => 'hodiny',
		'posts_per_page' => -1,
		'meta_key'       => $meta_key,
		'meta_value'     => $parent_id,
		'fields'         => 'ids',
	) );

	$keep = array();
	foreach ( $submitted as $block ) {
		$days_data = array();
		$has_data  = false;
		foreach ( array_keys( kolf_hours_days() ) as $day_key ) {
			$raw = isset( $block[ $day_key ] ) ? (array) $block[ $day_key ] : array();
			$day = array(
				'from1' => kolf_sanitize_time( $raw['from1'] ?? '' ),
				'to1'   => kolf_sanitize_time( $raw['to1'] ?? '' ),
				'from2' => kolf_sanitize_time( $raw['from2'] ?? '' ),
				'to2'   => kolf_sanitize_time( $raw['to2'] ?? '' ),
			);
			if ( $day['from1'] || $day['to1'] || $day['from2'] || $day['to2'] ) {
				$has_data = true;
			}
			$days_data[ $day_key ] = $day;
		}

		$note = isset( $block['note'] ) ? sanitize_textarea_field( $block['note'] ) : '';
		if ( '' !== $note ) {
			$has_data = true;
		}

		if ( ! $has_data ) {
			continue;
		}

		$type       = ( isset( $block['type'] ) && 'provozni' === $block['type'] ) ? 'provozni' : 'ordinacni';
		$hodiny_id  = isset( $block['id'] ) ? intval( $block['id'] ) : 0;
		$is_existing = $hodiny_id && in_array( $hodiny_id, $existing, true );

		if ( ! $is_existing ) {
			$hodiny_id = wp_insert_post( array(
				'post_type'   => 'hodiny',
				'post_title'  => __( 'Rozvrh', 'kolf' ),
				'post_status' => 'publish',
			) );
		}
		if ( ! $hodiny_id || is_wp_error( $hodiny_id ) ) {
			continue;
		}

		update_post_meta( $hodiny_id, $meta_key, $parent_id );
		update_post_meta( $hodiny_id, 'kolf_hours_type', $type );
		update_post_meta( $hodiny_id, 'kolf_hours_note', $note );
		update_post_meta( $hodiny_id, 'kolf_hours_days', $days_data );

		$keep[] = $hodiny_id;
	}

	foreach ( $existing as $id ) {
		if ( ! in_array( $id, $keep, true ) ) {
			wp_delete_post( $id, true );
		}
	}
}

/**
 * JS pro tlačítka "+ Přidat číslo" / "+ Přidat rozvrh" — jen na editačních
 * obrazovkách oddělení a osoby.
 */
function kolf_admin_phone_repeater_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'oddeleni', 'osoba' ), true ) ) {
		return;
	}
	wp_enqueue_script( 'kolf-admin-phone-repeater', KOLF_URI . '/assets/js/admin-phone-repeater.js', array(), KOLF_VERSION, true );
	wp_enqueue_script( 'kolf-admin-hours-repeater', KOLF_URI . '/assets/js/admin-hours-repeater.js', array(), KOLF_VERSION, true );
	wp_enqueue_style( 'kolf-admin-repeaters', KOLF_URI . '/assets/css/admin-repeaters.css', array(), KOLF_VERSION );
}
add_action( 'admin_enqueue_scripts', 'kolf_admin_phone_repeater_assets' );

/**
 * Sloupce "Patro" / "Oddělení" ve výpisu v adminu.
 */
function kolf_oddeleni_columns( $columns ) {
	$columns['kolf_floor'] = __( 'Patro', 'kolf' );
	return $columns;
}
add_filter( 'manage_oddeleni_posts_columns', 'kolf_oddeleni_columns' );

function kolf_oddeleni_column_content( $column, $post_id ) {
	if ( 'kolf_floor' === $column ) {
		echo esc_html( kolf_floor_label( get_post_meta( $post_id, 'kolf_floor', true ) ) );
	}
}
add_action( 'manage_oddeleni_posts_custom_column', 'kolf_oddeleni_column_content', 10, 2 );

function kolf_osoba_columns( $columns ) {
	$columns['kolf_department'] = __( 'Oddělení', 'kolf' );
	return $columns;
}
add_filter( 'manage_osoba_posts_columns', 'kolf_osoba_columns' );

function kolf_osoba_column_content( $column, $post_id ) {
	if ( 'kolf_department' === $column ) {
		$dept_id = (int) get_post_meta( $post_id, 'kolf_department_id', true );
		echo $dept_id ? esc_html( get_the_title( $dept_id ) ) : '—';
	}
}
add_action( 'manage_osoba_posts_custom_column', 'kolf_osoba_column_content', 10, 2 );
