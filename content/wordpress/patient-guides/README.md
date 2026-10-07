# Prasanthi Hospitals — Patient Guides

Build a bilingual English and Telugu patient education section as the next website improvement. Start with questions people ask during consultations and practical explanations they can revisit at home. Preserve the hospital's personal-care identity and its modern medicine focus with an integrative approach.

**Prepared now**

The local [interactive preview](preview.html) provides topic filters, search, a language switch and a draft/outline reader. [articles.json](articles.json) contains six bilingual article ideas and one complete pilot. The [English rice-and-diabetes draft](rice-and-diabetes.en.md) and [Telugu draft](rice-and-diabetes.te.md) are for clinical review. They have no clinician author/reviewer assigned and are not live WordPress posts. Both languages must be checked before publication.

The preview is an editorial prototype. It does not establish working WordPress routes, publication, approvals or contact delivery. JSON is a portable content reference rather than a WordPress REST payload or importer. Site files and the existing live layout are unchanged by this feature branch.

**First six guides**

| Topic | English question | Telugu question |
|---|---|---|
| Diabetes & food | Can I eat rice if I have diabetes? | మధుమేహం ఉంటే అన్నం తినవచ్చా? |
| Blood pressure | My BP is high, but I feel fine. What next? | బీపీ ఎక్కువగా ఉంది, కానీ ఇబ్బంది లేదు. ఇప్పుడు ఏం చేయాలి? |
| Healthy weight | Trying to lose weight? Start with changes you can keep | బరువు తగ్గాలనుకుంటున్నారా? కొనసాగించగల చిన్న మార్పులతో మొదలుపెట్టండి |
| Thyroid health | Could my tiredness or weight change be related to my thyroid? | అలసట లేదా బరువు మార్పుకు థైరాయిడ్ కారణమా? |
| Joint health | Joint pain and morning stiffness: when should I get checked? | కీళ్ల నొప్పి, ఉదయం బిగుతు: ఎప్పుడు వైద్యుడిని కలవాలి? |
| Ayurveda & wellbeing | Thinking about Ayurvedic care? Questions to ask first | ఆయుర్వేద చికిత్స గురించి ఆలోచిస్తున్నారా? ముందుగా అడగాల్సిన ప్రశ్నలు |

Launch with the diabetes guide, then blood pressure and healthy weight. These are proposed priorities, not scheduled commitments. Add thyroid, joint health and Ayurveda as their drafts and clinical review are completed.

**How the writing should sound**

Use one familiar question, a plain answer, a relevant local example, a few useful actions and guidance on seeking care. Food examples can reflect Andhra meals; fixed portions and treatment instructions need the patient's own plan. Use supportive language about weight. For joint health, explain symptoms and timely assessment rather than promise “rheumatological remedies.” Describe Ayurvedic practice respectfully and distinguish traditional use, researched evidence and individual care.

Name an author and medical reviewer only when they actually contribute or review. Add a genuine review date after review, source links and a short general-information note. Do not present drafts as doctor-approved. No patient stories or photographs are included in this prototype.

**WordPress build next**

Use native Posts and Categories. Suggested index: `/patient-guides/`, with six categories and ordinary article URLs. These are proposed paths; they have not been created or verified live.

The captured `page.php` supports generic page content outside About, Services and Contact. Blog rendering is still unverified because the repository is only a partial theme snapshot. Before deployment, inspect the active `header.php`, `index.php`, `single.php`, any `home.php`, `single-post.php`, `archive.php` and `category.php`, plus doctor content-type registration and query filters. Check Reading, permalink and primary-menu settings in the dashboard.

Use `home.php` for the article index and `single-post.php` for ordinary articles so the doctor routes remain separate. Keep the current Home page. Use existing styles with compact cards and optional modest illustrations; avoid mandatory large featured photographs.

A language button must open the same article in the other language, with a stable translation relationship. Choose a maintained WordPress multilingual approach with Lakshmi Narasimha after inspecting the existing installation; no plugin has been selected or installed. Make English and Telugu publication states explicit. Include language-specific URLs and metadata when the production approach is known.

Publish the reviewed pilot first and add the Patient Guides menu link only when the index and article both work. Verify Home, Doctors, article navigation and mobile layouts after deployment.

**After the first guides**

Add a small library of reviewed printable handouts, first-visit FAQs and short doctor explanation videos with checked captions. Propose one resource at a time using the questions patients most often ask. The current priority is useful bilingual articles.

**Sources**

- [Live Prasanthi website](https://www.prasanthihospitals.com/)
- [WordPress — Template Hierarchy](https://developer.wordpress.org/themes/classic-themes/basics/template-hierarchy/)
- [CDC — Diabetes Meal Planning](https://www.cdc.gov/diabetes/healthy-eating/diabetes-meal-planning.html)
- [NIDDK — Healthy Living with Diabetes](https://www.niddk.nih.gov/health-information/diabetes/overview/healthy-living-with-diabetes)
- [WHO — Hypertension](https://www.who.int/news-room/fact-sheets/detail/hypertension)
- [WHO — Sodium reduction](https://www.who.int/news-room/fact-sheets/detail/sodium-reduction)
- [NIDDK — Changing Your Habits for Better Health](https://www.niddk.nih.gov/health-information/diet-nutrition/changing-habits-better-health)
- [NIDDK — Hypothyroidism](https://www.niddk.nih.gov/health-information/endocrine-diseases/hypothyroidism)
- [NHS — Rheumatoid arthritis treatment](https://www.nhs.uk/conditions/rheumatoid-arthritis/treatment/)
- [NCCIH — Ayurvedic Medicine: In Depth](https://www.nccih.nih.gov/health/ayurvedic-medicine-in-depth)

Sutradhara — Codex on MacBook.
