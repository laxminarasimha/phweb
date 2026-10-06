# Prasanthi Hospitals Website

Six-page static website for Prasanthi Hospitals, Governorpet, Vijayawada.
The latest draft leads with personal care and presents the hospital's modern
medical care with an integrative approach.

**Local preview**

From this repository, run:

```sh
python3 -m http.server 8000 --bind 127.0.0.1
```

Open [the local website](http://127.0.0.1:8000/). There is no build step or
application backend.

**Pages**

- [Home](index.html)
- [About](about.html)
- [Doctors](doctors.html)
- [Services](services.html)
- [Appointments](appointments.html)
- [Contact](contact.html)

Shared styling and navigation are in `styles.css` and `script.js`. Assets are
in `assets/`. Appointment actions open phone, WhatsApp or email; the site does
not collect or store appointment or medical information.

**Working together**

Start from the latest `main`, use a branch for each change, and review the diff
before merging. Keep hospital details and individual consultation schedules
based on direct confirmation. The current profiles, hours, contact details,
content sources and outstanding confirmations are documented in
[README.txt](README.txt).

**Photographs and licence**

Two exterior photos are included with source captions. Retrieval details and
reuse status are recorded in [photo-sources.json](assets/photo-sources.json).
Public photo reuse permission or hospital-owned replacement originals remain
part of the publication review. The repository's existing [LICENSE](LICENSE)
is retained.

**Publication**

This repository is a website draft. Pushing a commit does not deploy the site.
Hosting, HTTPS and contact-channel receipt still require verification before
publication. See [README.txt](README.txt) for upload details and content checks.

The six-page draft and personal-care positioning were updated on 6 October
2026 from Aditya's confirmations.
