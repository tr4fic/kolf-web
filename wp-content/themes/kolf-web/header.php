<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php $kolf_address_street = trim( strtok( get_theme_mod( 'kolf_address', 'Masarykovo nám. 2667, Pardubice' ), ',' ) ); ?>
<header class="kolf-header-bar">
	<div class="kolf-container kolf-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kolf-logo">
			<span class="kolf-logo__row">
				<span class="kolf-logo__name"><?php bloginfo( 'name' ); ?></span>
				<span class="kolf-logo__tag"><?php echo esc_html( get_bloginfo( 'description' ) ?: 'Pardubice' ); ?></span>
			</span>
			<span class="kolf-logo__address"><?php echo esc_html( $kolf_address_street ); ?></span>
		</a>
		<nav class="kolf-nav" aria-label="<?php esc_attr_e( 'Hlavní navigace', 'kolf' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
				) );
			} else {
				kolf_nav_fallback();
			}
			?>
			<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', get_theme_mod( 'kolf_phone', '466 753 111' ) ) ); ?>" class="kolf-nav__phone"><?php echo esc_html( get_theme_mod( 'kolf_phone', '466 753 111' ) ); ?></a>
		</nav>
	</div>
</header>

<div class="kolf-container">
