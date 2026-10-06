Prasanthi Hospitals — local website draft

Pages
  index.html         Home
  about.html         About
  doctors.html       Doctors
  services.html      Services
  appointments.html  Appointment contact and visit preparation
  contact.html       Address and contact details

The site uses plain HTML, shared styles.css and script.js, the hospital logo
and two exterior photos in assets/.
There is no build step, form backend or database. Each page works as a static file.
To preview locally: python3 -m http.server 8000 --bind 127.0.0.1
Then open http://127.0.0.1:8000/.

Appointment actions open the phone dialler or WhatsApp. The website does not
collect, send or store appointment or medical details. A clicked link is not a
confirmed appointment. The Maps link opens the verified Governorpet place
listing, checked against its location and hospital signage.

Content source
Public facts were drafted from Prasanthi Hospital — Setup.md (updated 30 May
2026), the Hospital identity notes, and HQ's recorded public calls/WhatsApp
number. No patient records are used. The three confirmed OPD profiles, roles, qualifications, morning hours
(10:30 AM–2:00 PM), evening hours (6:00–9:00 PM) and official email
PrashantiHospitals1985@Gmail.com were confirmed directly by Aditya on
6 October 2026. Doctor profiles use only recorded or supplied facts. No founding year, accreditation, treatment outcomes,
24-hour service, insurer empanelment or unverified specialties are claimed.

Confirm before publication
- Operating days and individual doctor schedules.
- Any additional consultant profiles and their qualifications.
- Current service availability and any insurance/cashless arrangements.
- Photo reuse permission or hospital-owned originals, doctor photographs and final text.

The old unverified info@prasanthihospitals.com address has been replaced with
the user-confirmed Gmail address. The email spelling is preserved as supplied.
Additional clinician profiles can be added after confirmation.

Positioning and schedules
The hospital is presented as a modern medicine hospital with personal care
and an integrative approach that includes Ayurvedic consultations, following
Aditya's correction on 6 October 2026. Dr. Nishteshwar is not available for
evening OPD. His exact consultation schedule remains to be confirmed.

Public listing and photographs
Google Maps: https://www.google.com/maps/place/Prasanthi+Hospitals/@16.5087785,80.6269991,17z/data=!3m1!4b1!4m6!3m5!1s0x3a35fab1403de667:0x22655d921ff62c2!8m2!3d16.5087785!4d80.6269991!16s%2Fg%2F1t_thqj0
The daytime exterior and nighttime entrance images were retrieved from this
listing on 6 October 2026 and visually inspected. Both show hospital signage;
neither shows patients or medical records. The daytime photo is labelled
April 2021 by Maps; it should not be treated as a current facilities survey.
Each website caption links to the source. Retrieval URLs and known source
particulars are recorded in assets/photo-sources.json. Reuse permission and
photographer details are not verified; confirm public reuse permission or
replace with hospital-owned originals before publication.

Additional doctor candidates from public sources (not published on the site)
- Dr. Ramireddy Krishna Chaitanya Reddy: a Justdial listing associates him with
  Prashanthi Hospitals on Help Hospital Road/Governorpet; confirm current role,
  display name, qualifications and consultation schedule directly.
  https://www.justdial.com/Vijayawada/Dr-Ramireddy-Krishna-Chaitanya-Reddy-Prashanthi-Hospitals-Near-Help-Hospital-Road-Governerpet/0866PX866-X866-220423233627-D9X8_BZDET/amp
- Dr. Sarath Chandrabhatla: a public CV lists Prasanthi Hospital/RK Hospital
  work beginning August 2021; confirm whether he currently consults here.
  https://in.bold.pro/my/sarath-chandrabhatla-231003145108
- Dr. Yelamanchili Prashanthi: a Practo profile links to a matching-road clinic
  listing; the current hospital association needs confirmation.
  https://www.practo.com/vijayawada/doctor/yelamanchili-prashanthi-general-physician?practice_id=1533328
Directory hours and rosters conflict, so the user-confirmed OPD information
takes precedence. The similarly named Labbipet hospital at prashanthospital.com
is a different hospital and was excluded.

Deployment
When the hospital approves the draft for publication, upload the six HTML
pages, styles.css, script.js and assets/ to BigRock public_html. Keep any
existing live site backed up first. README.txt is for maintainers and does not
need to be uploaded. Live hosting, HTTPS, domain control, phone/WhatsApp receipt
and mailbox delivery have not been tested. No deployment was performed.
