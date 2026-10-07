# Prasanthi Hospitals — WordPress content

The warmer copy was applied to live WordPress on 7 October 2026. Home, About Us, Services, Contact Us and the shared footer were updated; three existing doctor profiles were revised and two new profiles published. A subsequent live formatting pass reduced image sizes, tightened spacing and fixed phone-width overflow while retaining the brand, navigation and template structure.

**Current content and evidence**

- [Publication record and checks](publication-record.md)
- [Live formatting changes](formatting-record.md)
- [Home, About Us, Services and Contact copy](core-pages.md)
- [Doctor directory and five profiles](doctors.md)
- [Structured doctor content](doctor-content.json)
- [Confirmed facts and open questions](confirmed-facts.md)
- [Verified editor and theme mapping](cms-editor-map.md)
- [Improvement ideas and priorities](improvement-notes.md)
- [Captured live content templates](../../wordpress-theme/README.md)

**Git and WordPress**

The `codex/wordpress-content-improvements` branch is in the actual shared `laxminarasimha/phweb` repository. It starts from static `main` at `2c01bc8`. Existing static HTML, CSS, JavaScript and assets are untouched. The first template snapshot commit records three deployed WordPress templates before the text update; the following commit records the applied copy. A separate baseline commit preserves the live stylesheet and functions before the formatting changes.

The captured folder is a partial source snapshot, not a complete or installable theme. WordPress posts and metadata are stored separately. The JSON is a portable editorial reference with verified public URLs and post IDs, not a verified REST payload, importer or database export. Git pushes and merges do not publish WordPress content.

**Content decisions**

Lead with personal care and modern medicine, with Ayurvedic consultations as part of the integrative approach. Use the corrected names Dr. Sarath Chandrabhatla and Dr. Sreya Bhuvanagiri; Sarath's M.Ch. remains training in progress. General OPD hours are distinct from individual doctor availability, and Dr. Nishteshwar's evening restriction is explicit.

The Services page retains its six existing icons. Daycare and pharmacy share one card, Ayurvedic consultations retain the plant icon, and further care/referral explains surgical assessment. Both official email addresses are present on Contact; the existing primary email link remains unchanged.

**Next content**

Approved portraits and individual schedules for the two new doctors are still needed. Operating days, first-visit arrangements and the basis of the existing experience figure need confirmation. The mobile footer and Contact email overflow have been fixed in the authorised formatting pass; all five primary pages fit the checked 390-pixel phone viewport.

**Sources**

- [Live website](https://www.prasanthihospitals.com/), checked after saving on 7 October 2026
- Dr. Aditya's direct confirmations of hospital details, qualifications and corrected names
