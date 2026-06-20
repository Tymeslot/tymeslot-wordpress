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
				<label for="tymeslot-instance"><?php esc_html_e( 'Tymeslot instance URL', 'tymeslot' ); ?></label>
				<input
					type="url"
					id="tymeslot-instance"
					name="tymeslot_settings[instance_url]"
					class="regular-text"
					value="<?php echo esc_attr( $settings['instance_url'] ); ?>"
					placeholder="https://tymeslot.app"
				/>
				<p class="tymeslot-field__hint"><?php esc_html_e( 'No trailing slash. Self-hosters: enter your own domain.', 'tymeslot' ); ?></p>
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

				<div class="tymeslot-field">
					<label for="tymeslot-color"><?php esc_html_e( 'Primary colour', 'tymeslot' ); ?></label>
					<input
						type="text"
						id="tymeslot-color"
						name="tymeslot_settings[primary_color]"
						class="regular-text"
						value="<?php echo esc_attr( $settings['primary_color'] ); ?>"
						placeholder="#14b8a6"
					/>
					<p class="tymeslot-field__hint"><?php esc_html_e( 'Hex colour, e.g. #14b8a6. Leave blank to use the theme default.', 'tymeslot' ); ?></p>
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
		<h2><?php esc_html_e( 'Enable embedding on this site', 'tymeslot' ); ?></h2>
		<p class="tymeslot-card__lead">
			<?php esc_html_e( 'Tymeslot blocks embedding by default. Before your booking page can appear here, add this site’s domain to your account’s allowed embed domains.', 'tymeslot' ); ?>
		</p>

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
					wp_kses_post( __( 'Open %s in your Tymeslot dashboard.', 'tymeslot' ) ),
					'<a href="' . esc_url( Tymeslot_Settings::instance_url() . '/dashboard/embed' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Embed → Security', 'tymeslot' ) . '</a>'
				);
				?>
			</li>
			<li><?php esc_html_e( 'Add the domain, save, then run the check below.', 'tymeslot' ); ?></li>
		</ol>

		<button type="button" class="button button-primary" id="tymeslot-check-btn">
			<?php esc_html_e( 'Test connection', 'tymeslot' ); ?>
		</button>

		<div id="tymeslot-check-result" class="tymeslot-check-result" hidden></div>
	</aside>
</div>
