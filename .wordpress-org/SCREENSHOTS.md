# Listing screenshots

`readme.txt` references five screenshots. They must be captured from a real
WordPress install so the listing reflects the actual UI. Capture them at a
**1200px-wide** viewport, save as `screenshot-1.png` … `screenshot-5.png` in
this folder (PNG, no retina suffix — wordpress.org scales them).

The captions in `readme.txt` are, in order:

1. **Setup tab** — connect your Tymeslot account and confirm embedding is
   enabled for your domain. *(Admin → Tymeslot → Setup)*
2. **Embed generator** — choose a mode, customise the look, copy a snippet, see
   the live preview. *(Admin → Tymeslot → Embed generator)*
3. **Gutenberg block** — the Tymeslot Booking block selected, with the
   InspectorControls sidebar open. *(Edit any page, insert the block)*
4. **Inline embed on the front end** — a published page showing the booker
   rendered inline.
5. **Floating button** — the floating booking button on a published page.

## Quick capture with wp-env (Docker)

```bash
# from the plugin root
npm run build
npx @wordpress/env start          # boots WordPress with this plugin mounted
# visit http://localhost:8888/wp-admin (admin / password)
# activate "Tymeslot — Booking & Scheduling", configure Setup, then screenshot
```

For shots 1–2, the page is self-contained. For 4–5, point the plugin at a real
booking username whose account has `localhost` added under **Embed → Security**
so the iframe actually renders.

Crop to the content area (drop the wp-admin sidebar) for a cleaner listing.
