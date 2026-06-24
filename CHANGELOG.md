# Changelog

All notable changes to the Tymeslot for WordPress plugin are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/), and the
project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-06-23

### Added
- Tymeslot Booking Gutenberg block with inline, popup, floating-button, and
  direct-link modes.
- `[tymeslot]` shortcode with `username`, `mode`, `theme`, `color`, `locale`,
  `layout`, `height`, `width`, and `label` attributes.
- Branded admin screen with a Setup tab, a live embed generator (server-rendered
  preview + copy-to-clipboard), and a Help tab.
- Live embedding check: a browser-side probe loads the real booking page in
  the Setup tab and reports whether this site’s domain is allow-listed.
- Embed guard: when a booking page can’t be embedded (the domain isn’t
  allow-listed), the front end shows a clear message in place of the booker
  instead of letting the Tymeslot homepage render inside the page — with an
  actionable hint for logged-in administrators. Covers inline, popup, and
  floating modes.
- Support for self-hosted Tymeslot instances via a configurable instance URL.
- Theme (Quill, Rhythm), primary colour, layout, language, height, and width
  options, with site-wide defaults.
- Snippet engine verified byte-identical to the Tymeslot Core dashboard
  generator.
