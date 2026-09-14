	<footer class="kolf-footer">
		<?php if ( has_nav_menu( 'legacy_pages' ) ) : ?>
		<nav class="kolf-footer-nav" aria-label="<?php esc_attr_e( 'Další stránky', 'kolf' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'legacy_pages',
				'container'      => false,
				'items_wrap'     => '<ul class="kolf-footer-nav__list">%3$s</ul>',
				'depth'          => 2,
			) );
			?>
		</nav>
		<?php endif; ?>
		<div class="kolf-footer__meta">
			<span>&copy; <?php echo esc_html( get_theme_mod( 'kolf_since_year', '1991' ) ); ?> – <?php echo esc_html( date_i18n( 'Y' ) ); ?> Kolf s.r.o.</span>
			<span><?php bloginfo( 'name' ); ?> Pardubice</span>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
