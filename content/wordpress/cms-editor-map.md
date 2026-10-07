# Prasanthi Hospitals — WordPress editor map

Verified editor observations and publication status from 7 October 2026. This map identifies where the [doctor copy](doctors.md) and [core page copy](core-pages.md) were applied in the live WordPress site. Lakshmi Narasimha owns the design; the current templates, navigation, images and contact actions are retained.

**Doctor content type**

The editor identifies the content type as `doctor`. A doctor's display name is the post title. The full profile belongs in the main editor; its Code editor exposes the text area labelled “Type text or HTML.” The existing Featured Image supplies the portrait.

| Content | Observed editor location | Content-pack reference |
|---|---|---|
| Display name | Post title | `name` |
| Full profile | Main editor; Code editor text area “Type text or HTML” | `profile` |
| Portrait | Featured Image | Confirmed photograph; no image value is supplied in the JSON |
| Qualification | Doctor Details → Qualification; input name `doctor_qualification` | `qualification` |
| Role / speciality | Doctor Details → Role/Speciality; input name `doctor_role` | `role` |
| Short introduction | Doctor Details → Short introduction; textarea name `doctor_short_intro` | `excerpt` |
| Consultation timing | Doctor Details → Consultation timing; input name `doctor_consultation` | `availability` |

These are observed form names and editor labels. The captured Home template separately reads the following doctor post metadata:

| Observed UI form name | Metadata key read by Home |
|---|---|
| `doctor_qualification` | `_doctor_qualification` |
| `doctor_role` | `_doctor_role` |
| `doctor_short_intro` | `_doctor_short_intro` |

The Home template uses prefixed metadata keys while the editor exposes unprefixed form names. The consultation timing metadata key and its storage mapping remain unverified. These observations do not establish WordPress REST field names, REST exposure or an import schema. The keys in [doctor-content.json](doctor-content.json) organise editorial copy; an API or importer would need a separately verified mapping.

No separate training field has been verified. Keep Dr. Sarath Chandrabhatla's M.Ch. in Surgical Gastroenterology explicitly described as training in progress in the short introduction and full profile. His completed qualification remains M.S. (General Surgery).

**Published doctor records**

| Doctor | Observed post ID | Public profile |
|---|---|---|
| Dr. C.N. Murthy | `13` | [Dr. C.N. Murthy](https://www.prasanthihospitals.com/doctor/dr-cn-murthy/) |
| Dr. C.S.K. Aditya | `14` | [Dr. C.S.K. Aditya](https://www.prasanthihospitals.com/doctor/dr-csk-aditya/) |
| Dr. Nishteshwar | `15` | [Dr. Nishteshwar](https://www.prasanthihospitals.com/doctor/dr-nishteshwar/) |
| Dr. Sarath Chandrabhatla | `53` | [Dr. Sarath Chandrabhatla](https://www.prasanthihospitals.com/doctor/dr-sarath-chandrabhatla/) |
| Dr. Sreya Bhuvanagiri | `54` | [Dr. Sreya Bhuvanagiri](https://www.prasanthihospitals.com/doctor/dr-sreya-bhuvanagiri/) |

All five doctor posts have been saved, published and read back publicly. Their actual post IDs and profile URLs are verified above and recorded in the portable JSON. Dr. Sarath Chandrabhatla's and Dr. Sreya Bhuvanagiri's individual schedules and portraits remain unconfirmed content gaps; their published timing text directs patients to call for availability.

**Verified application status**

The original content and captured theme files have been preserved separately. All five doctor profiles have been saved, published and read back. Core text changes in `front-page.php`, `page.php` and `footer.php` each showed “File edited successfully”; the saved source exactly matched the prepared replacement text. Home, About Us, Services, Contact Us and the Doctors directory have also been read back publicly while signed out.

The Doctors directory retains its original introduction, “Experienced doctors providing personal, accessible care.” The proposed replacement introduction in [doctors.md](doctors.md) was not applied to `archive-doctor.php`.

**Core pages and template boundaries**

The live Theme Editor has revealed and supplied backups of these files:

| Public area | Observed theme file | Verified content boundary |
|---|---|---|
| [Home](https://www.prasanthihospitals.com/) | `front-page.php` | Home text is in the template; doctor summaries also read the metadata keys listed above |
| [About Us](https://www.prasanthihospitals.com/about-us/) | `page.php` | Page-specific copy is hardcoded in this template |
| [Services](https://www.prasanthihospitals.com/services/) | `page.php` | Page-specific copy is hardcoded in this template |
| [Contact Us](https://www.prasanthihospitals.com/contact-us/) | `page.php` | Page-specific copy is hardcoded in this template |
| Shared footer | `footer.php` | Shared footer copy belongs to the footer template |
| [Doctors directory](https://www.prasanthihospitals.com/doctor/) | `archive-doctor.php` | The original directory introduction is retained; its proposed replacement was not applied |

These sections are template text rather than page-editor content fields. The content changes preserve existing markup, template logic, styling and contact destinations. The original Git checkout did not contain the deployed WordPress theme; separately captured Theme Editor source established the template boundaries used for this update. A Git content change alone does not publish a WordPress update.

**Content checks while editing**

- Match display names, qualifications and roles to [confirmed-facts.md](confirmed-facts.md).
- Use the call-to-confirm timing copy where a doctor's individual schedule is unknown. General OPD hours are not each doctor's schedule; Dr. Nishteshwar is unavailable for evening OPD.
- Preserve current Featured Images for existing doctors. Select confirmed portraits for new doctors before filling their image fields.
- Preview changed fields in the existing layout and read the public page back after saving. Keep portable content status aligned with verified WordPress publication.

**Sources**

- Verified live WordPress editor observations, captured Theme Editor source and public read-back during the 7 October content update
- [Doctors directory](https://www.prasanthihospitals.com/doctor/)
- [Portable doctor content](doctor-content.json)
- [Confirmed facts](confirmed-facts.md)

Sutradhara — Codex on MacBook
