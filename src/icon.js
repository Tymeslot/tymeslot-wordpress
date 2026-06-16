/**
 * The Tymeslot brand mark as an inline SVG, for the block icon.
 * Derived from apps/tymeslot/priv/static/images/brand/logo.svg.
 */
const TymeslotMark = (
	<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
		<defs>
			<linearGradient id="tsPrimary" x1="0%" y1="0%" x2="100%" y2="100%">
				<stop offset="0%" stopColor="#14b8a6" />
				<stop offset="100%" stopColor="#06b6d4" />
			</linearGradient>
			<linearGradient id="tsSecondary" x1="0%" y1="0%" x2="100%" y2="100%">
				<stop offset="0%" stopColor="#06b6d4" />
				<stop offset="100%" stopColor="#3b82f6" />
			</linearGradient>
			<linearGradient id="tsAccent" x1="0%" y1="0%" x2="100%" y2="100%">
				<stop offset="0%" stopColor="#2dd4bf" />
				<stop offset="100%" stopColor="#22d3ee" />
			</linearGradient>
		</defs>
		<path
			d="M 60 60 L 125 60 Q 138 60 138 73 L 138 77 Q 138 90 125 90 L 85 90 Q 75 90 75 80 L 75 70 Q 75 60 85 60 Z"
			fill="url(#tsPrimary)"
		/>
		<path
			d="M 80 95 Q 68 95 68 107 L 68 113 Q 68 125 80 125 L 115 125 Q 128 125 128 112 L 128 108 Q 128 95 115 95 Z"
			fill="url(#tsSecondary)"
		/>
		<path
			d="M 115 130 Q 128 130 128 143 L 128 147 Q 128 160 115 160 L 65 160 Q 52 160 52 147 L 52 143 Q 52 130 65 130 Z"
			fill="url(#tsAccent)"
		/>
	</svg>
);

export { TymeslotMark };
