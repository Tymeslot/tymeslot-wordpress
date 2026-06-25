=== Tymeslot ===
Contributors: luka113
Tags: booking, appointments, scheduling, calendar, meetings
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add your Tymeslot booking page to WordPress with a block, a shortcode, or a floating button. Open source. Cloud or self-hosted.

== Description ==

**Stop the back-and-forth. Start booking.**

Tymeslot is the open-source scheduling platform that respects your time and your data. This official plugin puts your Tymeslot booking page anywhere on your WordPress site — no code, no iframe wrangling, no copy-pasting snippets by hand.

Pick how visitors book:

* **Inline** — the calendar renders right inside your page.
* **Popup** — a button opens the booker in a clean overlay.
* **Floating button** — a booking bubble follows visitors as they scroll, like a chat widget.
* **Direct link** — a simple link for menus, buttons, and footers.

Everything is driven by your real Tymeslot booking page, so availability, time zones, calendar sync, video links, and confirmation emails all just work.

= Why Tymeslot =

* **Open source** — self-host the whole platform, or use the managed cloud at tymeslot.app. This plugin works with both.
* **Privacy-first** — your booking data stays in your Tymeslot account. The plugin stores only your settings.
* **Beautiful booking themes** — choose Quill (elegant glassmorphism) or Rhythm (immersive video background). The booker keeps the brand colours you set in your Tymeslot account.
* **Auto-resizing** — the embedded booker grows and shrinks to fit its content, with no awkward scrollbars.
* **Multilingual** — render the booker in English, German, Ukrainian, French, or Italian.

= Three ways to add it =

1. **Block** — insert the “Tymeslot Booking” block and configure it in the sidebar.
2. **Shortcode** — `[tymeslot username="you" mode="inline"]` in any post, page, or widget.
3. **Embed generator** — build and copy a ready-made snippet from the plugin’s settings screen for any page builder or custom theme.

= Connecting to Tymeslot (third-party service) =

This plugin displays booking pages served by **Tymeslot** — the managed cloud at tymeslot.app, or a Tymeslot instance you host yourself. To show a booker, the visitor’s browser loads your booking page and its `embed.js` from the Tymeslot instance you choose. On the settings screen, an optional connection check loads your booking page in a preview so you can confirm embedding works. The plugin makes no external request until you set a booking username, and it never sends your data anywhere other than the Tymeslot instance you configure.

If you use the managed cloud, your use is subject to Tymeslot’s terms and privacy policy:

* Terms & Conditions: https://tymeslot.app/legal/terms-and-conditions
* Privacy Policy: https://tymeslot.app/legal/privacy-policy

= An alternative to Calendly and Cal.com =

If you’re looking for an open-source booking and appointment tool you can fully control — a Calendly or Cal.com alternative — Tymeslot is built for exactly that, and this plugin brings it natively into WordPress.

Tymeslot is not affiliated with Calendly or Cal.com.

== Installation ==

1. Install and activate the plugin.
2. Go to **Tymeslot** in the WordPress admin menu.
3. Choose **Cloud** (tymeslot.app — the default, nothing to configure) or **Self-hosted** (enter your own instance URL), then set your **booking username**.
4. **Important:** Tymeslot blocks embedding by default. In your Tymeslot dashboard, open **Embed → Security** and add your WordPress site’s domain to the allowed embed domains. The Setup tab’s live embedding status then confirms, right in the browser, that it’s working.
5. Add the **Tymeslot Booking** block to a page, or drop in the `[tymeslot]` shortcode.

== Frequently Asked Questions ==

= My booking page shows up blank. What’s wrong? =

Almost always the domain allowlist. For security, Tymeslot only allows your booking page to be embedded on domains you approve. In your Tymeslot dashboard go to **Embed → Security**, add your WordPress site’s domain, and save. The live embedding status on the plugin’s Setup tab loads your real booking page and tells you, in the browser, exactly whether this site is allowed.

Until the domain is allow-listed, the plugin shows a short “booking is currently unavailable” message in place of the booker — it deliberately never lets the Tymeslot homepage render inside your page. Logged-in administrators also see a hint pointing to **Embed → Security**.

= Do I need a paid Tymeslot account? =

No. Core scheduling on Tymeslot is free, and this plugin works with free and Pro accounts alike.

= Does it work with a self-hosted Tymeslot instance? =

Yes. On the Setup tab choose **Self-hosted** and enter your instance URL. Switch back to **Cloud** any time — everything else behaves the same.

= Where is my booking data stored? =

In your Tymeslot account — never in WordPress. The plugin only stores your local settings (instance URL, username, and default appearance).

= Can I change the look of the booker? =

Yes. Choose a theme (Quill or Rhythm), pick a layout (column or centred), and a language. Set defaults once on the Setup tab, or override them per block/shortcode. Your brand colour is set in your Tymeslot account and is applied to the booker automatically.

= Does it slow down my site? =

No. The lightweight embed runtime loads only on pages that actually contain a Tymeslot block or shortcode.

= Does it work with a strict Content-Security-Policy? =

The **inline** embed and the **direct link** work under a strict CSP — they only add a `<div>`/`<a>` plus an external script you already allow via `script-src`. The **popup** and **floating button** modes use a small inline `onclick`/`<script>` to open the booker, so they need `'unsafe-inline'` in your `script-src` (or a matching nonce/hash) to run. If you enforce a strict CSP without `'unsafe-inline'`, prefer the inline or link mode.

= Where is the plugin’s source code? =

Development happens in the open. The full, un-minified source — including the Gutenberg block sources under `src/` and the build tooling — lives at https://github.com/Tymeslot/tymeslot-wordpress. The compiled files under `build/` shipped here are generated from it with `@wordpress/scripts` (`npm run build`).

== Screenshots ==

1. The Setup tab — connect your Tymeslot account and confirm embedding is enabled for your domain.
2. The embed generator — choose a mode, customise the look, and copy a ready-made snippet with a live preview.
3. The Tymeslot Booking block in the editor.

== Changelog ==

= 1.0.0 =
* Initial release.
* Tymeslot Booking Gutenberg block (inline, popup, floating, link).
* `[tymeslot]` shortcode.
* Branded settings screen with an embed generator and live preview.
* Live embedding check that loads your real booking page and shows whether this site is allow-listed.
* Embed guard that shows a clear message instead of the Tymeslot homepage when a booking page can’t be embedded (inline, popup, and floating).
* Support for self-hosted Tymeslot instances.
* Themes (Quill, Rhythm), layout, and language options.

== Upgrade Notice ==

= 1.0.0 =
First release of the official Tymeslot plugin for WordPress.
