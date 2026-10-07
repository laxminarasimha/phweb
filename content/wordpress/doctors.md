# Prasanthi WordPress doctor content

Status: published and publicly read back on 7 October 2026.

All five doctor profiles and their content fields have been saved and published in WordPress using Aditya’s confirmed facts. The current doctor-card and profile layout is retained. Individual schedules and portraits for the two new clinicians remain content gaps; the published timing text asks patients to call for availability.

**Doctors directory introduction — live copy unchanged**

Experienced doctors providing personal, accessible care.

The directory's existing introduction in `archive-doctor.php` was retained. Its proposed replacement below has not been applied.

**Proposed directory introduction — not applied**

Meet the doctors you can speak with at Prasanthi. Call us before your visit so we can confirm who is available and help you plan your consultation.

**Dr. C.N. Murthy**

Qualification: BAMS

Role: Founder · Family Physician

Card copy: Dr. Murthy founded Prasanthi Hospitals and continues to provide family physician consultations, working alongside Dr. Aditya in the hospital’s day-to-day care.

Profile copy:

Prasanthi Hospitals began with Dr. C.N. Murthy. He remains part of its everyday care, seeing patients as a family physician and working alongside Dr. C.S.K. Aditya.

For patients and families returning to Prasanthi, that continuity is part of the hospital’s personal approach to care.

If you would like to consult Dr. Murthy, call the hospital team to confirm his availability. Bring earlier prescriptions and reports, if you have them, so they can be reviewed during your visit.

Consultation timing: Please call the hospital to confirm Dr. Murthy’s consultation time before visiting.

**Dr. C.S.K. Aditya**

Qualification: M.D. (General Medicine)

Role: General Physician

Card copy: Dr. Aditya provides general medicine consultations and coordinates medical daycare and inpatient care at Prasanthi Hospitals.

Profile copy:

Whether you are feeling unwell or returning for follow-up, you can consult Dr. C.S.K. Aditya for general medical care at Prasanthi Hospitals.

He provides outpatient consultations and coordinates medical daycare and inpatient care, working alongside Dr. C.N. Murthy and the hospital team.

Your visit is an opportunity to discuss your symptoms, previous treatment and questions about your care. Bring earlier prescriptions and test reports, if you have them.

Please call the hospital before visiting to confirm Dr. Aditya’s consultation time.

Consultation timing: Please call the hospital to confirm Dr. Aditya’s consultation time before visiting.

**Dr. Nishteshwar**

Qualification: M.D. (Ayurveda)

Role: Ayurveda Physician

Card copy: Dr. Nishteshwar provides Ayurvedic consultations as part of Prasanthi’s integrative approach. Please call to confirm his consultation time; he is not available for evening OPD.

Profile copy:

Dr. Nishteshwar provides Ayurvedic outpatient consultations at Prasanthi Hospitals. These consultations are part of the hospital’s integrative approach, alongside its modern medical care.

If you would like to consult him, contact the hospital team before travelling. You can ask about his availability and what to bring to your visit.

Dr. Nishteshwar is not available for evening OPD.

Consultation timing: Not available for evening OPD. Please call the hospital to confirm his consultation time.

**Dr. Sarath Chandrabhatla**

Qualification: M.S. (General Surgery)

Role: General Surgeon

Training: Currently pursuing M.Ch. in Surgical Gastroenterology.

Card copy: Dr. Sarath Chandrabhatla is a general surgeon with an M.S. in General Surgery. He is currently pursuing M.Ch. in Surgical Gastroenterology. Call the hospital to enquire about consultation availability.

Profile copy:

Dr. Sarath Chandrabhatla holds an M.S. in General Surgery and is currently pursuing M.Ch. in Surgical Gastroenterology.

If you would like to discuss a general surgical concern with him, contact Prasanthi Hospitals to enquire about a consultation. The team can confirm his availability before you travel.

Bring previous prescriptions, scans and reports, if you have them, for your consultation.

Consultation timing: Please call the hospital to confirm Dr. Sarath Chandrabhatla’s availability before visiting.

**Dr. Sreya Bhuvanagiri**

Qualification: M.S. (Gynaecology)

Role: Gynaecologist

Card copy: Dr. Sreya Bhuvanagiri holds an M.S. in Gynaecology. Contact the hospital to enquire about a gynaecology consultation and confirm her availability.

Profile copy:

Dr. Sreya Bhuvanagiri is a gynaecologist with an M.S. in Gynaecology.

If you have a concern about your gynaecological health or would like to arrange a consultation, contact the Prasanthi Hospitals team. They can help you confirm her availability before your visit.

Bring previous prescriptions and relevant reports, if you have them, along with any questions you would like to discuss.

Consultation timing: Please call the hospital to confirm Dr. Sreya Bhuvanagiri’s availability before visiting.

**WordPress implementation notes**

The `doctor` content type and its editor fields have been verified and used. The title supplies the name, the main editor supplies the full profile, Featured Image supplies the portrait, and Doctor Details supplies qualification, role, short introduction and consultation timing. The [CMS editor map](cms-editor-map.md) records the observed UI names, verified Home metadata reads and published post IDs. The [portable JSON](doctor-content.json) records the actual public URLs and publication status; it is an editorial reference rather than an API/import schema.

Aditya’s latest display spellings are Dr. Sarath Chandrabhatla and Dr. Sreya Bhuvanagiri. Their consultation days and times have not been supplied. Use a call-to-confirm message rather than the hospital’s general OPD hours as their personal schedule. M.Ch. in Surgical Gastroenterology must remain labelled as training in progress. New portraits still need selection or confirmation; do not reuse another doctor’s photograph.

No claims about seniority, outcomes, accreditations, specific procedures or hospital services beyond the confirmed scope have been added. The former “30+ years” experience claim in Dr. Murthy's profile was omitted from the published replacement pending direct confirmation.

**Sources**

- [Live doctors directory](https://www.prasanthihospitals.com/doctor/)
- [Dr. C.N. Murthy’s current profile](https://www.prasanthihospitals.com/doctor/dr-cn-murthy/)
- [Dr. C.S.K. Aditya’s current profile](https://www.prasanthihospitals.com/doctor/dr-csk-aditya/)
- [Dr. Nishteshwar’s current profile](https://www.prasanthihospitals.com/doctor/dr-nishteshwar/)
- [Dr. Sarath Chandrabhatla’s published profile](https://www.prasanthihospitals.com/doctor/dr-sarath-chandrabhatla/)
- [Dr. Sreya Bhuvanagiri’s published profile](https://www.prasanthihospitals.com/doctor/dr-sreya-bhuvanagiri/)
- Aditya’s direct OPD, role and qualification confirmations in this conversation, including the two additions on 7 October 2026.
