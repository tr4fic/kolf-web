<?php
/**
 * Customizer — kontaktní údaje, které se opakují na více místech webu,
 * ať je klient může měnit bez zásahu do kódu.
 */

defined( 'ABSPATH' ) || exit;

function kolf_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'kolf_contact_section', array(
		'title'    => __( 'Kontaktní údaje', 'kolf' ),
		'priority' => 30,
	) );

	$fields = array(
		'kolf_phone'      => array( 'label' => __( 'Telefon (ústředna)', 'kolf' ), 'default' => '466 753 111' ),
		'kolf_email'      => array( 'label' => __( 'E-mail', 'kolf' ), 'default' => 'info@kolf.cz' ),
		'kolf_address'    => array( 'label' => __( 'Adresa (jeden řádek)', 'kolf' ), 'default' => 'Masarykovo nám. 2667, Pardubice' ),
		'kolf_address_2'  => array( 'label' => __( 'Adresa (druhý řádek — název s.r.o., PSČ)', 'kolf' ), 'default' => 'Poliklinika KOLF s.r.o., Masarykovo nám. 2667, Pardubice, 53002' ),
		'kolf_since_year' => array( 'label' => __( 'Rok založení (patička)', 'kolf' ), 'default' => '1991' ),
		'kolf_maps_url'   => array( 'label' => __( 'Odkaz na mapu budovy', 'kolf' ), 'default' => 'https://maps.google.com/?q=Masarykovo+nám.+2667+Pardubice' ),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $field['default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $field['label'],
			'section' => 'kolf_contact_section',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'kolf_customize_register' );
