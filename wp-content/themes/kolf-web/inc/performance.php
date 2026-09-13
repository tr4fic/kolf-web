<?php
/**
 * Drobný úklid výstupu, ať web zůstane svižný — bez zásahu do jádra, jen odregistrování
 * věcí, které tenhle web nepoužívá (emoji skripty, embed skript, XML-RPC odkazy…).
 */

defined( 'ABSPATH' ) || exit;

function kolf_performance_cleanup() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_embed_scripts' );

	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'xmlrpc_enabled', '__return_false' );
}
add_action( 'init', 'kolf_performance_cleanup' );
