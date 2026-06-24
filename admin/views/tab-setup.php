<?php
/**
 * Setup tab: instance + username + default appearance, plus the
 * embeddability check that surfaces the domain-allowlist requirement.
 *
 * @package Tymeslot
 *
 * @var array $settings Merged settings.
 */

defined( 'ABSPATH' ) || exit;

$themes  = Tymeslot_Settings::themes();
$locales = Tymeslot_Settings::locales();
$layouts = Tymeslot_Settings::layouts();

// Cloud vs self-hosted is derived from the saved URL: the cloud default means
// "Cloud", anything else means "Self-hosted". No separate flag is persisted.
$instance_url = untrailingslashit( isset( $settings['instance_url'] ) ? $settings['instance_url'] : Tymeslot_Settings::DEFAULT_INSTANCE );
$is_cloud     = ( Tymeslot_Settings::DEFAULT_INSTANCE === $instance_url );
?>

<div class="tymeslot-grid">
	<section class="tymeslot-card">
		<h2><?php esc_html_e( 'Connect your booking page', 'tymeslot' ); ?></h2>
		<p class="tymeslot-card__lead">
			<?php esc_html_e( 'Point the plugin at your Tymeslot account. Use the cloud at tymeslot.app, or your own self-hosted instance.', 'tymeslot' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( Tymeslot_Settings::GROUP ); ?>

			<div class="tymeslot-field">
				<div class="tymeslot-modes" role="radiogroup" aria-label="<?php esc_attr_e( 'Tymeslot account location', 'tymeslot' ); ?>">
					<label class="tymeslot-mode<?php echo $is_cloud ? ' is-active' : ''; ?>">
						<input type="radio" name="tymeslot-instance-mode" value="cloud"<?php checked( $is_cloud ); ?> />
						<span class="tymeslot-mode__badge"><?php esc_html_e( 'Default', 'tymeslot' ); ?></span>
						<strong class="tymeslot-mode__title"><?php esc_html_e( 'Cloud', 'tymeslot' ); ?></strong>
						<span class="tymeslot-mode__desc"><?php esc_html_e( 'Hosted at tymeslot.app — nothing to configure.', 'tymeslot' ); ?></span>
					</label>
					<label class="tymeslot-mode<?php echo $is_cloud ? '' : ' is-active'; ?>">
						<input type="radio" name="tymeslot-instance-mode" value="self"<?php checked( ! $is_cloud ); ?> />
						<span class="tymeslot-mode__badge"><?php esc_html_e( 'Custom', 'tymeslot' ); ?></span>
						<strong class="tymeslot-mode__title"><?php esc_html_e( 'Self-hosted', 'tymeslot' ); ?></strong>
						<span class="tymeslot-mode__desc"><?php esc_html_e( 'Connect to your own Tymeslot instance.', 'tymeslot' ); ?></span>
					</label>
				</div>
			</div>

			<div class="tymeslot-field" id="tymeslot-instance-field"<?php echo $is_cloud ? ' hidden' : ''; ?>>
				<label for="tymeslot-instance"><?php esc_html_e( 'Your instance URL', 'tymeslot' ); ?></label>
				<input
					type="url"
					id="tymeslot-instance"
					name="tymeslot_settings[instance_url]"
					class="regular-text"
					value="<?php echo esc_attr( $is_cloud ? '' : $instance_url ); ?>"
					placeholder="https://book.example.com"
				/>
				<p class="tymeslot-field__hint"><?php esc_html_e( 'No trailing slash, e.g. https://book.example.com.', 'tymeslot' ); ?></p>
			</div>

			<div class="tymeslot-field">
				<label for="tymeslot-username"><?php esc_html_e( 'Default booking username', 'tymeslot' ); ?></label>
				<input
					type="text"
					id="tymeslot-username"
					name="tymeslot_settings[username]"
					class="regular-text"
					value="<?php echo esc_attr( $settings['username'] ); ?>"
					placeholder="your-handle"
				/>
				<p class="tymeslot-field__hint"><?php esc_html_e( 'The handle in your booking URL, e.g. tymeslot.app/your-handle. Blocks and shortcodes inherit this when no username is given.', 'tymeslot' ); ?></p>
			</div>

			<details class="tymeslot-advanced">
				<summary><?php esc_html_e( 'Default appearance (optional)', 'tymeslot' ); ?></summary>
				<p class="tymeslot-field__hint"><?php esc_html_e( 'These defaults pre-fill every new block and shortcode. You can override them per embed.', 'tymeslot' ); ?></p>

				<div class="tymeslot-field">
					<label for="tymeslot-theme"><?php esc_html_e( 'Theme', 'tymeslot' ); ?></label>
					<select id="tymeslot-theme" name="tymeslot_settings[theme]">
						<option value=""<?php selected( '', $settings['theme'] ); ?>><?php esc_html_e( 'Account default', 'tymeslot' ); ?></option>
						<?php foreach ( $themes as $id => $label ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $settings['theme'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="tymeslot-field">
					<label for="tymeslot-layout"><?php esc_html_e( 'Layout', 'tymeslot' ); ?></label>
					<select id="tymeslot-layout" name="tymeslot_settings[layout]">
						<?php foreach ( $layouts as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $settings['layout'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="tymeslot-field">
					<label for="tymeslot-locale"><?php esc_html_e( 'Language', 'tymeslot' ); ?></label>
					<select id="tymeslot-locale" name="tymeslot_settings[locale]">
						<option value=""<?php selected( '', $settings['locale'] ); ?>><?php esc_html_e( 'Visitor / account default', 'tymeslot' ); ?></option>
						<?php foreach ( $locales as $code => $label ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $settings['locale'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="tymeslot-field tymeslot-field--inline">
					<span>
						<label for="tymeslot-height"><?php esc_html_e( 'Initial height (px)', 'tymeslot' ); ?></label>
						<input type="number" id="tymeslot-height" name="tymeslot_settings[initial_height]" min="200" max="2000" value="<?php echo esc_attr( $settings['initial_height'] ); ?>" />
					</span>
					<span>
						<label for="tymeslot-width"><?php esc_html_e( 'Max width (px)', 'tymeslot' ); ?></label>
						<input type="number" id="tymeslot-width" name="tymeslot_settings[max_width]" min="200" max="2000" value="<?php echo esc_attr( $settings['max_width'] ); ?>" />
					</span>
				</div>
			</details>

			<?php submit_button( __( 'Save settings', 'tymeslot' ) ); ?>
		</form>
	</section>

	<aside class="tymeslot-card tymeslot-card--accent">
		<h2><?php esc_html_e( 'Embedding status on this site', 'tymeslot' ); ?></h2>
		<p class="tymeslot-card__lead">
			<?php esc_html_e( 'Tymeslot blocks embedding by default. This live test loads your real booking page from this page, so it shows exactly what your visitors would see.', 'tymeslot' ); ?>
		</p>

		<div
			id="tymeslot-embed-status"
			class="tymeslot-embed-status is-pending"
			data-host="<?php echo esc_attr( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?>"
		>
			<span class="tymeslot-embed-status__icon" aria-hidden="true"></span>
			<span class="tymeslot-embed-status__text"><?php esc_html_e( 'Checking…', 'tymeslot' ); ?></span>
		</div>

		<p class="tymeslot-embed-actions">
			<button type="button" class="button" id="tymeslot-check-btn">
				<?php esc_html_e( 'Re-check', 'tymeslot' ); ?>
			</button>
			<a
				href="<?php echo esc_url( Tymeslot_Settings::instance_url() . '/dashboard/embed' ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="button"
			><?php esc_html_e( 'Open Embed → Security', 'tymeslot' ); ?></a>
		</p>

		<details class="tymeslot-fix" id="tymeslot-fix-steps">
			<summary><?php esc_html_e( 'How to enable embedding on this domain', 'tymeslot' ); ?></summary>
			<ol class="tymeslot-steps">
				<li>
					<?php
					printf(
						/* translators: %s: site host. */
						esc_html__( 'Copy this site’s domain: %s', 'tymeslot' ),
						'<code>' . esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '</code>'
					);
					?>
				</li>
				<li>
					<?php
					printf(
						/* translators: %s: link to the embed security settings. */
						wp_kses_post( __( 'In your Tymeslot dashboard open %s and add the domain.', 'tymeslot' ) ),
						'<a href="' . esc_url( Tymeslot_Settings::instance_url() . '/dashboard/embed' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Embed → Security', 'tymeslot' ) . '</a>'
					);
					?>
				</li>
				<li><?php esc_html_e( 'Save, then press Re-check.', 'tymeslot' ); ?></li>
			</ol>
		</details>

		<div class="tymeslot-livetest">
			<span class="tymeslot-livetest__label"><?php esc_html_e( 'Live test', 'tymeslot' ); ?></span>
			<div id="tymeslot-livetest-frame" class="tymeslot-livetest__frame"></div>
		</div>
	</aside>
</div>
