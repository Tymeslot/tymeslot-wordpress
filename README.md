# Tymeslot for WordPress

The official WordPress plugin for [Tymeslot](https://tymeslot.app) — the open-source meeting-scheduling platform.

Add your Tymeslot booking page to any WordPress site with a **Gutenberg block**, a **`[tymeslot]` shortcode**, or a **floating button** — no code required. Works with both Tymeslot Cloud (`tymeslot.app`) and self-hosted instances.

![Tymeslot for WordPress](.wordpress-org/banner-772x250.png)

## Features

- **Four embed modes** — inline, popup, floating button, and direct link.
- **Gutenberg block** — configure everything from the editor sidebar.
- **Shortcode** — `[tymeslot username="you" mode="inline"]`.
- **Embed generator** — a branded settings screen that builds, previews, and copies snippets, mirroring the in-app Tymeslot generator.
- **Connection check** — detects whether your site’s domain is allow-listed for embedding (Tymeslot blocks embedding by default) and tells you exactly what to fix.
- **Self-hosting friendly** — point the plugin at any Tymeslot instance.
- **On-brand & accessible** — booking themes (Quill, Rhythm), accent colour, layout, and language options.

## How it works

The plugin is a thin, well-behaved wrapper around Tymeslot’s existing embed runtime (`embed.js`). It generates markup **byte-identical** to the snippets produced by the Tymeslot dashboard’s own embed generator — the snippet engine in `includes/class-tymeslot-snippet.php` is a verified PHP port of the Core Elixir generator. The Gutenberg block and the shortcode both render through that single engine, so they can never drift apart.

No booking data touches WordPress: the booker runs in an iframe served by your Tymeslot instance. The plugin stores only your settings.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- A Tymeslot account (free or Pro), cloud or self-hosted.

## Installation (from source)

```bash
git clone <this-repo> tymeslot
cd tymeslot
npm install        # block tooling
npm run build      # compiles the Gutenberg block into /build (already committed)
```

Copy the folder into `wp-content/plugins/` and activate it, then open **Tymeslot** in the admin menu.

> **Enable embedding:** Tymeslot blocks embedding by default. In your Tymeslot dashboard go to **Embed → Security** and add your WordPress site’s domain, or the booker will render blank. The plugin’s **Test connection** button confirms this for you.

## Development

| Command | Purpose |
|---------|---------|
| `npm run start` | Watch-build the block during development |
| `npm run build` | Production build of the block (output committed to `/build`) |
| `npm run lint:js` | Lint block JavaScript |
| `composer lint` | PHP CodeSniffer against WordPress standards |

### Snippet parity

The snippet engine is verified byte-for-byte against the live Tymeslot Core generator (`apps/tymeslot/lib/tymeslot_web/live/dashboard/embed_settings/helpers.ex`). If the Core generator changes, re-run the parity check and update `includes/class-tymeslot-snippet.php` accordingly.

### Listing assets

Branded SVG masters live in `.wordpress-org/`. Regenerate the wordpress.org PNGs with:

```bash
./.wordpress-org/generate-assets.sh
```

## License

GPL-2.0-or-later. Tymeslot is open source.
