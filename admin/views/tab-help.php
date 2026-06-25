<?php
/**
 * Help tab: shortcode + block usage and useful links.
 *
 * @package Tymeslot
 *
 * @var array $settings Merged settings.
 */

defined( 'ABSPATH' ) || exit;

// Template partial required into Tymeslot_Admin::render_page(); its variables
// are function-local, not globals, so the global-prefix rule does not apply.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$docs = Tymeslot_Settings::instance_url() . '/docs/embed';
?>

<div class="tymeslot-grid">
	<section class="tymeslot-card">
		<h2><?php esc_html_e( 'Three ways to embed', 'tymeslot' ); ?></h2>

		<h3><?php esc_html_e( '1. Gutenberg block', 'tymeslot' ); ?></h3>
		<p><?php esc_html_e( 'In the editor, add the “Tymeslot Booking” block and configure it in the sidebar. Best for most pages.', 'tymeslot' ); ?></p>

		<h3><?php esc_html_e( '2. Shortcode', 'tymeslot' ); ?></h3>
		<p><?php esc_html_e( 'Paste a shortcode into any post, page, or widget:', 'tymeslot' ); ?></p>
		<pre class="tymeslot-snippet__code"><code>[tymeslot username="<?php echo esc_html( $settings['username'] ? $settings['username'] : 'your-handle' ); ?>" mode="inline"]</code></pre>
		<p><?php esc_html_e( 'Supported attributes:', 'tymeslot' ); ?></p>
		<ul class="tymeslot-list">
			<li><code>username</code> — <?php esc_html_e( 'your booking handle (defaults to the one saved in Setup)', 'tymeslot' ); ?></li>
			<li><code>mode</code> — <code>inline</code>, <code>popup</code>, <code>floating</code>, <?php esc_html_e( 'or', 'tymeslot' ); ?> <code>link</code></li>
			<li><code>theme</code> — <code>1</code> (Quill) <?php esc_html_e( 'or', 'tymeslot' ); ?> <code>2</code> (Rhythm)</li>
			<li><code>locale</code> — <code>en</code>, <code>de</code>, <code>uk</code>, <code>fr</code>, <code>it</code></li>
			<li><code>layout</code> — <code>column</code> <?php esc_html_e( 'or', 'tymeslot' ); ?> <code>default</code></li>
			<li><code>height</code>, <code>width</code> — <?php esc_html_e( 'pixels (200–2000)', 'tymeslot' ); ?></li>
			<li><code>label</code> — <?php esc_html_e( 'custom button / link text', 'tymeslot' ); ?></li>
		</ul>

		<h3><?php esc_html_e( '3. Embed generator', 'tymeslot' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %s: link to the generator tab. */
				wp_kses_post( __( 'Use the %s to build and copy a ready-made snippet for any other theme or page builder.', 'tymeslot' ) ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=' . Tymeslot_Admin::SLUG . '&tab=generator' ) ) . '">' . esc_html__( 'Embed generator', 'tymeslot' ) . '</a>'
			);
			?>
		</p>
	</section>

	<aside class="tymeslot-card">
		<h2><?php esc_html_e( 'Troubleshooting', 'tymeslot' ); ?></h2>
		<h3><?php esc_html_e( 'My booking page is blank', 'tymeslot' ); ?></h3>
		<p><?php esc_html_e( 'Almost always the domain allowlist. Tymeslot blocks embedding until you add this site’s domain under Embed → Security in your dashboard. Use “Test connection” on the Setup tab to confirm.', 'tymeslot' ); ?></p>

		<h3><?php esc_html_e( 'Self-hosting Tymeslot?', 'tymeslot' ); ?></h3>
		<p><?php esc_html_e( 'Set your own instance URL on the Setup tab. Everything else works the same.', 'tymeslot' ); ?></p>

		<h2><?php esc_html_e( 'Links', 'tymeslot' ); ?></h2>
		<ul class="tymeslot-list">
			<li><a href="<?php echo esc_url( $docs ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Embedding guide', 'tymeslot' ); ?></a></li>
			<li><a href="<?php echo esc_url( Tymeslot_Settings::instance_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Tymeslot home', 'tymeslot' ); ?></a></li>
			<li><a href="https://github.com/tymeslot/tymeslot" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Source on GitHub', 'tymeslot' ); ?></a></li>
		</ul>
	</aside>
</div>
