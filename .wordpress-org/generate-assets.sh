#!/usr/bin/env bash
#
# Generate the wordpress.org listing PNGs from the branded SVG masters.
#
# Required outputs (https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/):
#   icon-128x128.png, icon-256x256.png
#   banner-772x250.png, banner-1544x500.png
#
# Prefers Inkscape (best text rendering); falls back to ImageMagick or
# rsvg-convert. Idempotent — safe to re-run.

set -euo pipefail
cd "$(dirname "$0")"

render() {
	local src="$1" out="$2" w="$3" h="$4"
	if command -v inkscape >/dev/null 2>&1; then
		inkscape "$src" --export-type=png --export-filename="$out" \
			--export-width="$w" --export-height="$h" >/dev/null 2>&1
	elif command -v rsvg-convert >/dev/null 2>&1; then
		rsvg-convert -w "$w" -h "$h" "$src" -o "$out"
	elif command -v convert >/dev/null 2>&1; then
		convert -background none -density 300 -resize "${w}x${h}" "$src" "$out"
	else
		echo "No SVG renderer found (need inkscape, rsvg-convert, or ImageMagick)." >&2
		exit 1
	fi
	echo "  ✓ $out (${w}x${h})"
}

echo "Rendering icons…"
render icon.svg   icon-128x128.png   128  128
render icon.svg   icon-256x256.png   256  256

echo "Rendering banners…"
render banner.svg banner-772x250.png  772  250
render banner.svg banner-1544x500.png 1544 500

echo "Done."
