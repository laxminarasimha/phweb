# Prasanthi Hospitals — live theme snapshot

**Partial source snapshot**

These five files were read from the active Prasanthi Hospitals theme through the WordPress Theme File Editor on 7 October 2026. This folder is not a complete or installable theme. It excludes the remaining templates, uploads, plugins and database.

- `prasanthi-hospitals/front-page.php`: Home, including dynamic doctor cards.
- `prasanthi-hospitals/page.php`: About Us, Services and Contact Us text.
- `prasanthi-hospitals/footer.php`: shared footer.
- `prasanthi-hospitals/style.css`: original stylesheet plus appended image, spacing and responsive refinements; version 1.0.2.
- `prasanthi-hospitals/functions.php`: existing asset registration with the stylesheet version changed to 1.0.2.

**Review and publication**

Commit `54be0b2` preserves the three live content templates before the text update; `66cce7a` records the applied copy. Commit `3e3554a` preserves the live stylesheet and functions before the formatting pass. Subsequent formatting changes preserve the original CSS rules and append scoped refinements; the only PHP change is the stylesheet version argument.

Do not install this partial folder as a replacement theme. Doctor profiles are WordPress posts and metadata. Their portable editorial content lives in [the WordPress content pack](../content/wordpress/README.md). A Git push does not publish WordPress changes; the live dashboard requires separate save and public read-back. The [formatting record](../content/wordpress/formatting-record.md) describes the independently applied live changes.
