# Bågspår — design and content direction

Owner: root, 2026-10-07. Implementation: Luna Max.
User requested a new website in `/Users/filipkostrzewski/Herd/bagspar`, everything on Laravel. Website only: validate demand before building the proposed SaaS. Do not commit or publish. Do not alter other projects.

## Product and conversion

Swedish landing page for small workshops that renovate wooden windows. The primary visitor is the workshop owner, not a homeowner seeking renovation. Primary conversion: a genuine interest submission for a future pilot. Collect email, company and optional short description of current workflow. Do not collect unnecessary phone numbers or sensitive details. A first-person CTA can say `Jag vill testa Bågspår`; submission means interest, not a purchase. Keep the proposed price visible. No fabricated customer counts, testimonials, savings percentages, launch dates, urgency or working-product claims. Mock work cards must visibly say `Exempel på arbetskort` or `Konceptbild`.

Working brand `Bågspår`, local hostname `bagspar.test`. Brand/domain availability is not verified; do not register a domain or use a made-up public canonical. Recipient: `filip.kostrzewski@wisegent.se`. Local leads stored independently of mail provider availability; local log mail must never be presented as inbox delivery.

## Art direction

Editorial workshop journal: precise typesetting, tangible wood and paint texture, quiet practical interface previews. The memorable motif is an old four-pane wooden sash and a numbered brass-yellow workshop tag. Avoid a generic blue SaaS gradient, stock laptop, balloons, decorative blobs or a wall of identical feature cards.

Palette:
- Paper background `#F7F6F1`.
- Dark ink / deep navy `#17313B`.
- Brass / action accent `#E7B65A` with ink text.
- Secondary ink `#516269`.
- Rule `#D6DAD6`.
- White card `#FFFFFF`.
- Quiet green only for a real illustrative completed step `#35634F`.

Type: `Fraunces` for hero and editorial section headings (use a stable serif fallback); `Manrope` for UI/body (system sans fallback). Self-host fonts when straightforward; no font-dependent invisible rendering. Hero 72–88px at desktop (fluid clamp), 45–52px at ~390px and ~40px at 320px; headings weight 500/600, serif naturally. Body 17–18px with ~1.6 line height. Labels 14px minimum and secondary metadata never below 12px. Do not put critical disclosures in tiny text.

Layout max width 1240px, desktop gutters 48–64px, mobile 20px (16px at 320). Main section spacing 88–112px desktop, 56–72 mobile. Corners restrained: buttons 6px, image 4–8px, cards 10px. Shadows only on the overlaid illustrative work card, very light elsewhere. Use rules and spacing instead of many boxed panels. All text and buttons must remain usable at narrow widths and increased text size.

## Homepage structure and exact Swedish copy

### Masthead

Simple four-pane window mark plus Bågspår wordmark. Window mark should be vector, recognisable at favicon size, avoid external icon packs for this one symbol. Nav `Så fungerar idén` (#arbetsflode), `Pilotprojektet` (#pilot), compact primary `Anmäl intresse` (#intresse). On mobile keep brand and interest button; do not invent a complex menu for three anchors. No icon arrows on CTA buttons. Keyboard focus must be visible.

### Hero

Two columns, text ~48%, photograph ~52%. Page opens with a strong, legible text block and tall workshop photo. On mobile text first, photo below. Hero photo supplied by root at public/images/workshop-hero.webp (with original/source notes in docs). Photo shows an old window sash on a real wood workshop bench, editorial, not a fabricated customer's facility. Do not imply partnership or use it as customer evidence.

Eyebrow: `FÖR VERKSTÄDER SOM RENOVERAR FÖNSTER`
H1: `Ordning på varje båge.` (natural linebreaks, ~three lines at desktop if elegant; never broken words)
Body: `Vi utvecklar ett enkelt verktyg som samlar märkning, bilder och arbetssteg — från första fotot till återmontering.`
Primary: `Jag vill testa Bågspår` -> #intresse
Secondary text link: `Se hur idén fungerar` -> #arbetsflode
Supporting disclosure: `Planerat pilotpris: 790 kr/mån exkl. moms. Intresseanmälan är inte bindande.`

Photo overlay at lower left, contained within its visual column: white work card, 280–320px desktop, fit mobile width. Caption clearly `Exempel på arbetskort`; name `Båge B-04`; secondary `Våning 2 · Kök`; status chip `Redo för målning`. Three rows with checkmarks or phase indicators: `Märkt och fotograferad`, `Trä lagat`, `Glas och kitt klart`. This is illustrative; no fake live activity, dates, tenant identity or real customer names. A small brass tag B-04 near upper right can echo physical tagging without covering the window. A bottom caption may read `Från verkstaden till rätt fönsteröppning.` Use layout geometry with subtle shadow, not giant tilted dashboard screenshot.

### Problem / proposition (unboxed editorial row)

Section eyebrow `EN PLATS FÖR HELA ARBETET`
Heading `Låt hantverket ta plats. Inte letandet.`
Body `När flera projekt pågår samtidigt behöver varje båge, bild och anteckning höra ihop. Bågspår är tänkt att ge verkstaden en enkel överblick — och ett tydligt underlag när arbetet är klart.`
Three concise items with line icons or numbered separators, not big cards:
- `Rätt båge, rätt plats` — `Koppla varje båge till projekt, rum och fönsteröppning.`
- `Bilder där de hör hemma` — `Samla före- och efterbilder tillsammans med arbetsanteckningarna.`
- `Tydligt vad som återstår` — `Följ arbetsstegen och se vad som är redo för nästa moment.`

### Workflow, id arbetsflode

A deep-navy section, headings in paper and brass accents. Desktop left editorial heading ~40%, right vertical three-step workflow ~60% with fine connecting line. Mobile one column. This section should vary composition and provide concrete value, not repeat hero copy. Tiny two-pane outline motif is okay if quiet.
Eyebrow `SÅ FUNGERAR IDÉN`
Heading `En båge. Ett arbetskort. Hela vägen.`
Intro `Det här är arbetsflödet vi vill utveckla tillsammans med de första verkstäderna.`
Step 01 `Märk och fotografera` — `Skapa ett projekt och ge varje båge ett nummer. Lägg till bilder, plats och det som behöver göras.`
Step 02 `Följ arbetet i mobilen` — `Öppna arbetskortet via en QR-kod och uppdatera steget när arbetet går vidare.`
Step 03 `Samla dokumentationen` — `Tanken är att bilder och utförda moment ska kunna sammanställas till ett enkelt PDF-underlag.`
No mock browser login links or fake interaction that implies a deployed SaaS. A static concept preview is enough; interactive demo is not necessary.

### Pilot, id pilot

Editorial two-column section with strong price number, thin separator, no tier grid.
Eyebrow `VAR MED FRÅN BÖRJAN`
Heading `Byggt tillsammans med verkstäder som din.`
Body `Vi söker verkstäder som vill påverka den första versionen. Berätta hur ni arbetar i dag och vad som brukar vara svårt att hålla ordning på.`
Price: `790` + `kr/mån` + `exkl. moms`
Price label: `Planerat pris per verkstad`
Disclosures: `Bågspår är under utveckling. Vi tar emot intresseanmälningar inför ett framtida pilotprojekt. Du betalar ingenting genom att anmäla intresse.`
No claimed number of available spots, launch date, contract or discount promise. No payments or product subscriptions on this website.

### Interest, id intresse

A large paper/light section with heading and compact form alongside. Can be slightly darker paper than page, no extreme rounded card. Left heading `Vill du få bättre ordning i verkstaden?`
Body `Lämna din email och berätta gärna lite om ert arbetssätt. Vi hör av oss personligen om pilotprojektet.`
Inputs:
- Required `Företag / verkstad` (company)
- Required `Email` (email, autocomplete=email, native type=email)
- Optional textarea `Vad vill ni få bättre ordning på?` with helpful placeholder `Till exempel märkning, bilder eller dokumentation.`
- Required unchecked checkbox `Jag godkänner att ni kontaktar mig om Bågspår och har läst integritetspolicyn.` (linked privacy)
Submit `Anmäl intresse`.
Under button `Ingen betalning. Inget bindande avtal.`
Server validation errors in Swedish, input values preserved after errors, clearly associated labels/errors, focus accessible. Browser-native invalid states acceptable; server must independently validate. Success view or message must accurately confirm stored interest; not that an email was delivered. Suggested success: `Tack! Din intresseanmälan är mottagen.` and `Vi återkommer till din email om pilotprojektet.` A useful back-to-home action. Disable double submits gracefully if JS enabled; progressive enhancement must still work without JS. No localStorage-only lead collection.

### FAQ

Four simple accessible disclosures (`details`/`summary`), quiet typography and horizontal rules. Heading `Några saker att veta`.
- `Kan vi använda Bågspår redan nu?` — `Inte ännu. Vi tar emot intresseanmälningar och vill förstå verkstädernas behov innan vi bygger den första versionen.`
- `Behöver vi byta vårt nuvarande ekonomisystem?` — `Idén är ett separat verktyg för bågar och arbetsdokumentation. Vi planerar inte att ersätta ert ekonomisystem.`
- `Kommer det att fungera på telefonen?` — `Den planerade första versionen ska kunna användas i mobilens webbläsare.`
- `Vad händer när jag anmäler intresse?` — `Vi kontaktar dig via email för att förstå ert arbetssätt och berätta mer om ett möjligt pilotprojekt. Anmälan är inte bindande.`

### Footer

Mark + Bågspår, one sentence `Ett enklare arbetsflöde för fönsterrenovering.`
Links `Integritet` and `Kontakt` (mailto user-provided recipient). Short local origin `En idé med utgångspunkt i Märsta.` without inventing a registered address/legal company. Copyright current year + working brand. No fictitious company number or SaaS availability promise.

## Privacy and SEO

Short Swedish privacy page: honest explanation of company/email/message/consent/date collected, use limited to pilot contact and managing interest, no third-party sales, contact address provided, simple deletion request through email, actual storage including infrastructure/email processor only if known. Do not invent legal entity, registration number, vendors or a guarantee of GDPR compliance. Avoid tracking cookies and analytics at this stage. No unnecessary cookie banner.

Home title `Bågspår — arbetskort för fönsterrenovering`.
Meta description `Ett planerat verktyg för fönsterverkstäder: märkning, bilder, arbetssteg och dokumentation på ett ställe. Anmäl intresse för Bågspårs pilotprojekt.`
Use lang=sv, one H1, logical heading hierarchy, custom favicon, good image alt text, correct labels and contrast, reachable anchors without header overlap. No fake reviews/schema ratings, no public canonical for bagspar.test. Configure a canonical only from a real deployment APP_URL. Local development should not pretend to be public/indexed. A future deployment can include legitimate WebSite metadata without invented product ratings/stock/reviews.

## Generated photograph provenance

Mode: built-in imagegen. The final photograph is saved in the Laravel project at `public/images/workshop-hero.webp`; WebP conversion only, with the original generated photograph preserved. This is an illustration of the industry, not a customer's workshop. Generation prompt:

> Use case: photorealistic-natural. Asset type: editorial hero photograph for Bågspår, a Swedish landing page for a proposed workshop tool for wooden-window renovation. Create one premium, believable editorial photograph, portrait 4:5 composition, of a traditional old wooden window sash resting upright at a slight angle on a well-used pale oak carpenter's workbench in a small Scandinavian window-restoration workshop. Main subject is one intact four-pane sash: white painted outer rails with authentic small weathered paint chips, warm wood grain visible on a narrow repaired edge, thin wooden muntins, aged clean glass, accurate joinery and simple glazing. Show the whole sash with room around its silhouette, roughly centered; the upper two-thirds are clear and compelling, and the bottom-left can accept a small HTML work-card overlay later. Behind it, a softly out-of-focus row of a few similar wooden sashes and modest workshop tools on a shelf. Soft natural daylight from the side, cool Scandinavian ambient light with warm tactile wood, quiet craftsmanship and careful order. Photorealistic medium-format editorial photography, physically convincing proportions and fine paint and timber textures, calm tonal contrast. No people, no hands, no laptop or phone, no logos, no legible labels or text, no website UI, no watermark, no exaggerated dirt, no glossy 3D render. The photograph is a concept illustration of the industry, not a depiction of a claimed customer. Opaque background.

## Implementation and QA acceptance

Everything Laravel. Root owns visuals and final review. Agent implements durable lead storage, robust form, privacy and notification configuration; independent email failure should not erase a stored lead or present a false failure after successful storage. Keep raw lead data and mail logs outside public. Use appropriate CSRF, field limits, honeypot/rate limiting and safe DB writes. No admin dashboard or authentication project, no SaaS business functionality. A simple developer CLI export is okay for reviewing leads.

Verify desktop ~1440, mobile ~390 and narrow ~320, hero order, legibility, no overflow, keyboard form/FAQ, interest action, actual stored lead, invalid submission, successful submission, privacy. Root will inspect real browser screenshots and source. Only meaningful focused tests, no generic new test suite matching markup. Distinguish stored leads, local mail logs and actual mail delivery in report. Leave all changes uncommitted.
