<?php
/**
 * Jednoduché vlastní meta boxy — bez ACF nebo jiného pluginu.
 */

defined( 'ABSPATH' ) || exit;

function kolf_register_meta_boxes() {
	add_meta_box( 'kolf_oddeleni_meta', __( 'Údaje o oddělení', 'kolf' ), 'kolf_render_oddeleni_meta_box', 'oddeleni', 'side', 'default' );
	add_meta_box( 'kolf_sluzba_meta', __( 'Údaje o službě', 'kolf' ), 'kolf_render_sluzba_meta_box', 'sluzba', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'kolf_register_meta_boxes' );

function kolf_render_oddeleni_meta_box( $post ) {
	wp_nonce_field( 'kolf_save_oddeleni_meta', 'kolf_oddeleni_meta_nonce' );
	$floor       = get_post_meta( $post->ID, 'kolf_floor', true );
	$contact     = get_post_meta( $post->ID, 'kolf_contact', true );
	$quick_order = get_post_meta( $post->ID, 'kolf_quick_order', true );
	?>
	<p>
		<label for="kolf_floor"><strong><?php esc_html_e( 'Patro', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_floor" name="kolf_floor" value="<?php echo esc_attr( $floor ); ?>" step="1" style="width:100%" placeholder="-1 = suterén, 0 = přízemí">
	</p>
	<p>
		<label for="kolf_contact"><strong><?php esc_html_e( 'Kontakt (lékař / telefon)', 'kolf' ); ?></strong></label><br>
		<input type="text" id="kolf_contact" name="kolf_contact" value="<?php echo esc_attr( $contact ); ?>" style="width:100%" placeholder="MUDr. Jméno nebo telefonní číslo">
	</p>
	<p>
		<label for="kolf_quick_order"><strong><?php esc_html_e( 'Pořadí v „Rychlá čísla“', 'kolf' ); ?></strong></label><br>
		<input type="number" id="kolf_quick_order" name="kolf_quick_order" value="<?php echo esc_attr( $quick_order ); ?>" min="0" step="1" style="width:100%" placeholder="0 = nezobrazovat">
		<span class="description"><?php esc_html_e( 'Vyplňte jen u oddělení, jejichž číslo se má zobrazit v boxu na úvodní stránce.', 'kolf' ); ?></span>
	</p>
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

function kolf_save_meta_boxes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['kolf_oddeleni_meta_nonce'] ) && wp_verify_nonce( $_POST['kolf_oddeleni_meta_nonce'], 'kolf_save_oddeleni_meta' ) ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['kolf_floor'] ) ) {
			update_post_meta( $post_id, 'kolf_floor', intval( $_POST['kolf_floor'] ) );
		}
		if ( isset( $_POST['kolf_contact'] ) ) {
			update_post_meta( $post_id, 'kolf_contact', sanitize_text_field( $_POST['kolf_contact'] ) );
		}
		if ( isset( $_POST['kolf_quick_order'] ) ) {
			update_post_meta( $post_id, 'kolf_quick_order', intval( $_POST['kolf_quick_order'] ) );
		}
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
 * Sloupec "Patro" ve výpisu oddělení v adminu.
 */
function kolf_oddeleni_columns( $columns ) {
	$columns['kolf_floor']   = __( 'Patro', 'kolf' );
	$columns['kolf_contact'] = __( 'Kontakt', 'kolf' );
	return $columns;
}
add_filter( 'manage_oddeleni_posts_columns', 'kolf_oddeleni_columns' );

function kolf_oddeleni_column_content( $column, $post_id ) {
	if ( 'kolf_floor' === $column ) {
		echo esc_html( kolf_floor_label( get_post_meta( $post_id, 'kolf_floor', true ) ) );
	}
	if ( 'kolf_contact' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'kolf_contact', true ) );
	}
}
add_action( 'manage_oddeleni_posts_custom_column', 'kolf_oddeleni_column_content', 10, 2 );
