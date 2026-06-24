<?php
/**
 * Embed generator tab: mirrors the in-app dashboard generator. Controls
 * drive a live, server-rendered snippet (via REST) and a live preview.
 *
 * @package Tymeslot
 *
 * @var array $settings Merged settings.
 */

defined( 'ABSPATH' ) || exit;

$modes   = Tymeslot_Settings::modes();
$themes  = Tymeslot_Settings::themes();
$locales = Tymeslot_Settings::locales();
$layouts = Tymeslot_Settings::layouts();

$mode_meta = array(
	'inline'   => array( __( 'Embed the calendar directly in your page.', 'tymeslot' ), __( 'Recommended', 'tymeslot' ) ),
	'popup'    => array( __( 'A button opens the booker in an overlay.', 'tymeslot' ), __( 'Popular', 'tymeslot' ) ),
	'floating' => array( __( 'A button floats in the corner as visitors scroll.', 'tymeslot' ), __( 'Pro', 'tymeslot' ) ),
	'link'     => array( __( 'A plain link to your booking page.', 'tymeslot' ), __( 'Easiest', 'tymeslot' ) ),
);
?>

<div class="tymeslot-generator" id="tymeslot-generator">
	<section class="tymeslot-card">
		<h2><?php esc_html_e( 'Choose how to embed', 'tymeslot' ); ?></h2>

		<div class="tymeslot-modes" role="radiogroup" aria-label="<?php esc_attr_e( 'Embed type', 'tymeslot' ); ?>">
			<?php
			$first = true;
			foreach ( $modes as $value => $label ) :
				$meta = isset( $mode_meta[ $value ] ) ? $mode_meta[ $value ] : array( '', '' );
				?>
				<label class="tymeslot-mode<?php echo $first ? ' is-active' : ''; ?>">
					<input type="radio" name="tymeslot-mode" value="<?php echo esc_attr( $value ); ?>"<?php checked( $first ); ?> />
					<span class="tymeslot-mode__badge"><?php echo esc_html( $meta[1] ); ?></span>
					<strong class="tymeslot-mode__title"><?php echo esc_html( $label ); ?></strong>
					<span class="tymeslot-mode__desc"><?php echo esc_html( $meta[0] ); ?></span>
				</label>
				<?php
				$first = false;
			endforeach;
			?>
		</div>

		<div class="tymeslot-controls">
			<div class="tymeslot-field">
				<label for="tymeslot-gen-username"><?php esc_html_e( 'Username', 'tymeslot' ); ?></label>
				<input type="text" id="tymeslot-gen-username" value="<?php echo esc_attr( $settings['username'] ); ?>" placeholder="your-handle" />
			</div>

			<div class="tymeslot-field">
				<label for="tymeslot-gen-theme"><?php esc_html_e( 'Theme', 'tymeslot' ); ?></label>
				<select id="tymeslot-gen-theme">
					<option value=""><?php esc_html_e( 'Account default', 'tymeslot' ); ?></option>
					<?php foreach ( $themes as $id => $label ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $settings['theme'] ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="tymeslot-field">
				<label for="tymeslot-gen-layout"><?php esc_html_e( 'Layout', 'tymeslot' ); ?></label>
				<select id="tymeslot-gen-layout">
					<?php foreach ( $layouts as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $value, $settings['layout'] ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="tymeslot-field">
				<label for="tymeslot-gen-locale"><?php esc_html_e( 'Language', 'tymeslot' ); ?></label>
				<select id="tymeslot-gen-locale">
					<option value=""><?php esc_html_e( 'Default', 'tymeslot' ); ?></option>
					<?php foreach ( $locales as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $settings['locale'] ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="tymeslot-field" data-mode-only="inline">
				<label for="tymeslot-gen-height"><?php esc_html_e( 'Initial height (px)', 'tymeslot' ); ?></label>
				<input type="number" id="tymeslot-gen-height" min="200" max="2000" value="<?php echo esc_attr( $settings['initial_height'] ); ?>" />
			</div>

			<div class="tymeslot-field" data-mode-hide="link">
				<label for="tymeslot-gen-width"><?php esc_html_e( 'Max width (px)', 'tymeslot' ); ?></label>
				<input type="number" id="tymeslot-gen-width" min="200" max="2000" value="<?php echo esc_attr( $settings['max_width'] ); ?>" />
			</div>

			<div class="tymeslot-field" data-mode-show="popup floating link">
				<label for="tymeslot-gen-label"><?php esc_html_e( 'Button / link text', 'tymeslot' ); ?></label>
				<input type="text" id="tymeslot-gen-label" placeholder="<?php esc_attr_e( 'Book a Meeting', 'tymeslot' ); ?>" />
			</div>
		</div>
	</section>

	<section class="tymeslot-card">
		<div class="tymeslot-snippet">
			<div class="tymeslot-snippet__head">
				<h2><?php esc_html_e( 'Your embed code', 'tymeslot' ); ?></h2>
				<button type="button" class="button" id="tymeslot-copy-btn" data-default-label="<?php esc_attr_e( 'Copy code', 'tymeslot' ); ?>">
					<?php esc_html_e( 'Copy code', 'tymeslot' ); ?>
				</button>
			</div>
			<pre class="tymeslot-snippet__code"><code id="tymeslot-snippet-code"></code></pre>
			<p class="tymeslot-snippet__hint">
				<?php esc_html_e( 'Prefer no code? Insert the “Tymeslot Booking” block, or use the [tymeslot] shortcode — both produce this same embed.', 'tymeslot' ); ?>
			</p>
		</div>

		<div class="tymeslot-preview">
			<h3><?php esc_html_e( 'Live preview', 'tymeslot' ); ?></h3>
			<p class="tymeslot-field__hint">
				<?php esc_html_e( 'Renders your real booking page. If it stays blank, this site’s domain is not yet allow-listed — see the Setup tab.', 'tymeslot' ); ?>
			</p>
			<div class="tymeslot-preview__frame" id="tymeslot-preview-frame"></div>
		</div>
	</section>
</div>
