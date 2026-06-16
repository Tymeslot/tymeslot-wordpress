/**
 * Tymeslot Booking block — registration entry point.
 */
import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit';
import { TymeslotMark } from './icon';
import './style.scss';
import './editor.scss';

registerBlockType( metadata.name, {
	icon: TymeslotMark,
	edit: Edit,
	// Dynamic block: markup is produced by the PHP render_callback so the
	// block and shortcode share one source of truth.
	save: () => null,
} );
