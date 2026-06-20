<?php
/**
 * Admin page shell: brand header + tab navigation + active tab body.
 *
 * @package Tymeslot
 *
 * @var array  $tabs     Tab slug => label.
 * @var string $active   Active tab slug.
 * @var array  $settings Merged settings.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap tymeslot-admin">
	<header class="tymeslot-admin__header">
		<span class="tymeslot-admin__logo">
			<?php
			// Inline the wordmark SVG (already trusted, shipped with the plugin).
			$wordmark = TYMESLOT_PATH . 'assets/img/wordmark.svg';
			if ( file_exists( $wordmark ) ) {
				echo wp_kses(
					file_get_contents( $wordmark ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					Tymeslot_Admin::svg_kses()
				);
			} else {
				echo '<strong>Tymeslot</strong>';
			}
			?>
		</span>
		<p class="tymeslot-admin__tagline"><?php esc_html_e( 'Add your booking page to WordPress — no code required.', 'tymeslot' ); ?></p>
	</header>

	<nav class="tymeslot-admin__tabs nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a
				href="<?php echo esc_url( admin_url( 'admin.php?page=' . Tymeslot_Admin::SLUG . '&tab=' . $slug ) ); ?>"
				class="nav-tab <?php echo $slug === $active ? 'nav-tab-active' : ''; ?>"
			><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<div class="tymeslot-admin__body">
		<?php
		$view = TYMESLOT_PATH . 'admin/views/tab-' . $active . '.php';
		if ( file_exists( $view ) ) {
			require $view;
		}
		?>
	</div>

	<footer class="tymeslot-admin__footer">
		<?php
		printf(
			/* translators: 1: docs link, 2: GitHub link. */
			wp_kses_post( __( 'Tymeslot is open source. Read the %1$s or view the %2$s.', 'tymeslot' ) ),
			'<a href="' . esc_url( Tymeslot_Settings::instance_url() . '/docs' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'documentation', 'tymeslot' ) . '</a>',
			'<a href="https://github.com/tymeslot/tymeslot" target="_blank" rel="noopener noreferrer">' . esc_html__( 'source on GitHub', 'tymeslot' ) . '</a>'
		);
		?>
	</footer>
</div>
