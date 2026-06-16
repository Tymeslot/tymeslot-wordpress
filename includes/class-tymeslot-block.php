<?php
/**
 * The Tymeslot booking Gutenberg block (dynamic / server-rendered).
 *
 * The block stores its attributes only; markup is produced server-side by
 * the shared snippet engine so the block and shortcode can never drift.
 *
 * @package Tymeslot
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the `tymeslot/booking` block.
 */
class Tymeslot_Block {

	/**
	 * Register the block type from compiled metadata in /build.
	 *
	 * @return void
	 */
	public static function register() {
		$metadata = TYMESLOT_PATH . 'build/block.json';

		if ( ! file_exists( $metadata ) ) {
			// Built assets missing (e.g. checked out without `npm run build`).
			return;
		}

		register_block_type(
			$metadata,
			array(
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);

		// Give the editor script the default appearance + instance context.
		self::localize_editor_defaults();
	}

	/**
	 * Server render callback. Reuses the snippet engine.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();

		$mode    = isset( $attributes['mode'] ) ? $attributes['mode'] : 'inline';
		$snippet = Tymeslot_Snippet::render(
			$mode,
			array(
				'username'       => isset( $attributes['username'] ) ? $attributes['username'] : '',
				'theme'          => isset( $attributes['theme'] ) ? $attributes['theme'] : '',
				'primary_color'  => isset( $attributes['primaryColor'] ) ? $attributes['primaryColor'] : '',
				'locale'         => isset( $attributes['locale'] ) ? $attributes['locale'] : '',
				'layout'         => isset( $attributes['layout'] ) ? $attributes['layout'] : '',
				'initial_height' => isset( $attributes['initialHeight'] ) ? $attributes['initialHeight'] : '',
				'max_width'      => isset( $attributes['maxWidth'] ) ? $attributes['maxWidth'] : '',
				'label'          => isset( $attributes['buttonLabel'] ) ? $attributes['buttonLabel'] : '',
			)
		);

		if ( '' === $snippet ) {
			return '';
		}

		if ( 'link' !== Tymeslot_Settings::sanitize_mode( $mode ) ) {
			Tymeslot_Assets::enqueue();
		}

		$wrapper = function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes( array( 'class' => 'tymeslot-block' ) )
			: 'class="tymeslot-block"';

		return '<div ' . $wrapper . '>' . $snippet . '</div>';
	}

	/**
	 * Expose saved defaults + the constant lookup tables to the editor
	 * script so the block controls match the admin generator exactly.
	 *
	 * @return void
	 */
	private static function localize_editor_defaults() {
		$handle = generate_block_asset_handle( 'tymeslot/booking', 'editorScript' );

		wp_localize_script(
			$handle,
			'TymeslotBlockData',
			array(
				'instanceUrl' => Tymeslot_Settings::instance_url(),
				'settingsUrl' => admin_url( 'admin.php?page=tymeslot' ),
				'defaults'    => array(
					'username'      => Tymeslot_Settings::get( 'username', '' ),
					'theme'         => Tymeslot_Settings::get( 'theme', '' ),
					'primaryColor'  => Tymeslot_Settings::get( 'primary_color', '' ),
					'locale'        => Tymeslot_Settings::get( 'locale', '' ),
					'layout'        => Tymeslot_Settings::get( 'layout', 'column' ),
					'initialHeight' => Tymeslot_Settings::get( 'initial_height', 700 ),
					'maxWidth'      => Tymeslot_Settings::get( 'max_width', 1000 ),
				),
				'themes'      => self::to_choices( Tymeslot_Settings::themes() ),
				'locales'     => self::to_choices( Tymeslot_Settings::locales() ),
				'layouts'     => self::to_choices( Tymeslot_Settings::layouts() ),
				'modes'       => self::to_choices( Tymeslot_Settings::modes() ),
				'brandColors' => array( '#14b8a6', '#06b6d4', '#3b82f6', '#0d9488', '#2dd4bf' ),
			)
		);
	}

	/**
	 * Convert a value=>label map into [{value,label}] for SelectControl.
	 *
	 * @param array<string,string> $map Source map.
	 * @return array<int,array<string,string>>
	 */
	private static function to_choices( $map ) {
		$choices = array();
		foreach ( $map as $value => $label ) {
			$choices[] = array(
				'value' => (string) $value,
				'label' => $label,
			);
		}

		return $choices;
	}
}
