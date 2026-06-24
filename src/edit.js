/**
 * Tymeslot Booking block — editor UI.
 *
 * The canvas shows a branded placeholder (not a live iframe): the booking
 * page won't frame inside wp-admin unless the site is allowlisted, and a
 * static card keeps the editor fast and predictable. All configuration is
 * in the Inspector sidebar, mirroring the admin embed generator.
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RangeControl,
	ExternalLink,
	Notice,
} from '@wordpress/components';

import { TymeslotMark } from './icon';

const DATA =
	typeof window !== 'undefined' && window.TymeslotBlockData
		? window.TymeslotBlockData
		: {
				instanceUrl: 'https://tymeslot.app',
				settingsUrl: '',
				defaults: {},
				themes: [],
				locales: [],
				layouts: [],
				modes: [],
		  };

const optionField = ( choices, includeBlank, blankLabel ) => {
	const list = ( choices || [] ).map( ( c ) => ( {
		label: c.label,
		value: c.value,
	} ) );
	if ( includeBlank ) {
		return [ { label: blankLabel, value: '' }, ...list ];
	}
	return list;
};

export default function Edit( { attributes, setAttributes } ) {
	const {
		username,
		mode,
		theme,
		locale,
		layout,
		initialHeight,
		maxWidth,
		buttonLabel,
	} = attributes;

	const blockProps = useBlockProps( { className: 'tymeslot-block-editor' } );
	const effectiveUsername = username || DATA.defaults.username || '';
	const modeLabel =
		( DATA.modes.find( ( m ) => m.value === mode ) || {} ).label || mode;

	const isInline = mode === 'inline';
	const isLink = mode === 'link';
	const usesButtonLabel = mode === 'popup' || mode === 'floating' || mode === 'link';
	const usesWidth = mode !== 'link';

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Booking', 'tymeslot' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Tymeslot username', 'tymeslot' ) }
						value={ username }
						onChange={ ( value ) => setAttributes( { username: value } ) }
						placeholder={ DATA.defaults.username || 'your-handle' }
						help={
							DATA.defaults.username
								? __( 'Leave blank to use the site default.', 'tymeslot' )
								: __( 'The handle in your booking page URL.', 'tymeslot' )
						}
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Display as', 'tymeslot' ) }
						value={ mode }
						options={ optionField( DATA.modes, false ) }
						onChange={ ( value ) => setAttributes( { mode: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Appearance', 'tymeslot' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Theme', 'tymeslot' ) }
						value={ theme }
						options={ optionField(
							DATA.themes,
							true,
							__( 'Account default', 'tymeslot' )
						) }
						onChange={ ( value ) => setAttributes( { theme: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Layout', 'tymeslot' ) }
						value={ layout }
						options={ optionField(
							DATA.layouts,
							true,
							__( 'Account default', 'tymeslot' )
						) }
						onChange={ ( value ) => setAttributes( { layout: value } ) }
						help={ __(
							'Column fills the container width; Default is a centred card.',
							'tymeslot'
						) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Language', 'tymeslot' ) }
						value={ locale }
						options={ optionField(
							DATA.locales,
							true,
							__( 'Visitor / account default', 'tymeslot' )
						) }
						onChange={ ( value ) => setAttributes( { locale: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Size', 'tymeslot' ) } initialOpen={ false }>
					{ isInline && (
						<RangeControl
							label={ __( 'Initial height (px)', 'tymeslot' ) }
							value={ initialHeight }
							onChange={ ( value ) =>
								setAttributes( { initialHeight: value } )
							}
							min={ 200 }
							max={ 2000 }
							step={ 10 }
							allowReset
							help={ __(
								'Placeholder height before the widget auto-resizes.',
								'tymeslot'
							) }
							__nextHasNoMarginBottom
						/>
					) }
					{ usesWidth && (
						<RangeControl
							label={ __( 'Max width (px)', 'tymeslot' ) }
							value={ maxWidth }
							onChange={ ( value ) => setAttributes( { maxWidth: value } ) }
							min={ 200 }
							max={ 2000 }
							step={ 10 }
							allowReset
							__nextHasNoMarginBottom
						/>
					) }
					{ ! isInline && ! usesWidth && (
						<p className="tymeslot-help">
							{ __(
								'The direct link inherits the booking page size.',
								'tymeslot'
							) }
						</p>
					) }
				</PanelBody>

				{ usesButtonLabel && (
					<PanelBody
						title={ __( 'Button & link', 'tymeslot' ) }
						initialOpen={ false }
					>
						<TextControl
							label={
								isLink
									? __( 'Link text', 'tymeslot' )
									: __( 'Button label', 'tymeslot' )
							}
							value={ buttonLabel }
							onChange={ ( value ) =>
								setAttributes( { buttonLabel: value } )
							}
							placeholder={
								isLink
									? __( 'Schedule a meeting', 'tymeslot' )
									: __( 'Book a Meeting', 'tymeslot' )
							}
							__nextHasNoMarginBottom
						/>
					</PanelBody>
				) }
			</InspectorControls>

			<div className="tymeslot-placeholder">
				<div className="tymeslot-placeholder__mark">{ TymeslotMark }</div>
				<div className="tymeslot-placeholder__body">
					<span className="tymeslot-placeholder__eyebrow">
						{ __( 'Tymeslot booking', 'tymeslot' ) }
					</span>
					<strong className="tymeslot-placeholder__title">
						{ effectiveUsername
							? /* translators: 1: mode label, 2: username. */
							  `${ modeLabel } · ${ effectiveUsername }`
							: modeLabel }
					</strong>
					{ ! effectiveUsername && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'Set your Tymeslot username in the block settings to display your booking page.',
								'tymeslot'
							) }
							{ DATA.settingsUrl && (
								<>
									{ ' ' }
									<ExternalLink href={ DATA.settingsUrl }>
										{ __( 'Plugin settings', 'tymeslot' ) }
									</ExternalLink>
								</>
							) }
						</Notice>
					) }
					{ effectiveUsername && (
						<span className="tymeslot-placeholder__hint">
							{ __(
								'Your booking page renders here on the published page.',
								'tymeslot'
							) }
						</span>
					) }
				</div>
			</div>
		</div>
	);
}
