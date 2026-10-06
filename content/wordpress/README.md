# Prasanthi Hospitals — WordPress content draft

Replacement copy for the existing live WordPress pages, prepared for review on 7 October 2026. The focus is personal care, useful consultation information and a consistent description of hospital services. The current layout remains the baseline.

**Read the draft**

- [Improvement ideas and priorities](improvement-notes.md)
- [Home, About Us, Services and Contact copy](core-pages.md)
- [Doctor directory and five individual profiles](doctors.md)
- [Structured doctor copy](doctor-content.json)
- [Confirmed facts and open content questions](confirmed-facts.md)

**Repository and publication status**

This content-only branch starts from `main` at `2c01bc8`. At the time of review, `main` and `basicbranch1` contain static HTML, CSS and JavaScript; neither contains the theme source for the deployed WordPress website. Existing site files are untouched. Merging this pack into Git does not update WordPress.

The live site is [prasanthihospitals.com](https://www.prasanthihospitals.com/). These files are editorial drafts for its current sections. The JSON is a portable content reference, not a verified WordPress API payload, importer or database schema. It contains no CMS IDs or credentials.

**What the content changes**

The opening invites patients to discuss their concerns, rather than repeating generic praise. Doctor profiles describe each clinician's confirmed role and consultation information. The two additions use the corrected names **Dr. Sarath Chandrabhatla** and **Dr. Sreya Bhuvanagiri**; Sarath's M.Ch. remains training in progress. General hospital OPD hours are distinguished from individual doctor availability.

The Services draft proposes consistent descriptions within the existing six cards. Check the proposed labels against the current icons and editor fields before applying them. Both confirmed email addresses are retained, with `info@prasanthihospitals.com` as the current primary address.

**Applying the draft in WordPress**

1. Review the wording with Dr. Aditya and inspect the existing page and doctor editors. Identify the actual content type, fields and template boundaries.
2. Preserve the current text through an available WordPress revision or an export before replacing it.
3. Paste the agreed copy into the existing fields. Create the two new profiles as drafts and map their fields to the existing doctor template. The proposed slugs are suggestions only.
4. Confirm the new clinicians' availability and photographs. Use the drafted call-to-confirm wording where an individual schedule is unknown; do not assign general OPD hours to a doctor.
5. Preview Home, About Us, Services, Doctors, Contact and each affected profile on desktop and mobile. Check names, qualifications, availability, contact links, service wording and layout.
6. Publish when ready, then read the public pages back. Change the About doctor count from three to five only after both new profiles are public. Git status alone is not publication evidence.

**Sources**

- [Live website](https://www.prasanthihospitals.com/), reviewed 7 October 2026
- Dr. Aditya's confirmed hospital details, credentials and corrected doctor names
- [WordPress revision documentation](https://wordpress.org/documentation/article/revisions/)
