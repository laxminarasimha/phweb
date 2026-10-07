# Prasanthi Hospitals — live formatting

Aditya requested neater pages and smaller images. The active WordPress stylesheet and its existing enqueue version were saved on 7 October 2026 and checked on the public site. Brand colours, navigation, page sections, copy, approved portraits and contact destinations are retained.

**Applied changes**

- Doctor-card photo areas are 220 pixels tall on desktop and 230 on phones; images use containment so faces are not stretched or cut off.
- Profile portraits are 240 × 300 pixels on desktop and 220 × 275 on narrower screens. A cached Chrome profile previously stretched the portrait to 320 × 900 pixels.
- The Home hero logo is 220 × 220 pixels. Heading sizes, section padding and profile spacing are more compact and consistent.
- Profiles without photographs use a single text column. Existing empty photo areas remain in directory cards to align the text while approved portraits are pending.
- Footer columns stack on phones. Shrinkable text columns and wrapping prevent the address and official emails from extending the page width.

**Saved source and cache refresh**

Commit `3e3554a` preserves the original live `style.css` and `functions.php`. The applied stylesheet keeps every original rule and appends scoped formatting overrides; only its header version changes. The sole PHP change is the existing `wp_enqueue_style` version argument from `1.0.0` to `1.0.2`. Both WordPress saves returned “File edited successfully,” and saved editor text exactly matched the prepared source. Independent source review passed.

A stale browser stylesheet was part of the oversized-portrait problem. WordPress's version parameter changes the stylesheet URL used for caching; see [wp_enqueue_style documentation](https://developer.wordpress.org/reference/functions/wp_enqueue_style/). The site's normal Caching → Purge All control was used, and public reloads confirmed `style.css?ver=1.0.2` and the new dimensions. No cache/security settings were changed.

**Public read-back**

Home, About Us, Services, Contact Us and Doctors were checked signed out at 1280 × 720 and 390 × 844 viewports. At desktop, document and client width were both 1265 pixels; at mobile, both were 375 pixels, allowing for the browser scrollbar. No horizontal overflow was present in these checks. The directory retained five doctors and all three existing photographs loaded. A directory View Profile action opened Aditya's correct profile; its image measured 240 × 300 desktop and 220 × 275 mobile. Sarath's profile showed a 780-pixel single text column with its empty image column hidden.

Public screenshots were inspected. No framework error overlay was visible and browser console checks reported no relevant errors. This pass does not test enquiry receipt, telephone handling or WhatsApp delivery. No test enquiry was submitted.

**Remaining inputs**

Approved photographs for Sarath and Sreya, individual schedules, operating days, first-visit details and the basis of the existing experience figure still need confirmation. The captured five-file source folder remains a partial snapshot, not an installable theme or a complete backup. Git records the changes for Lakshmi Narasimha; a Git push or merge does not deploy WordPress.

**Sources**

- [Live website](https://www.prasanthihospitals.com/)
- [Partial theme source](../../wordpress-theme/README.md)
- [Content publication record](publication-record.md)
- [WordPress stylesheet registration](https://developer.wordpress.org/reference/functions/wp_enqueue_style/)

Sutradhara — Codex on MacBook.
