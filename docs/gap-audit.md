# Gap audit

17 October 2026. Public pages checked: home, demo, registration, terms, privacy, the guide index, and the four guide articles, including the FAQ blocks. The price in `config/bagspar.php` and the terms is 790 kr/mån exkl. moms after a free pilot through 31 December 2026. Registration collects no card. There is no Stripe.

## Promises and the code

- A workshop account is how a shop starts. The owner invites coworkers by email. A coworker can work on projects and sashes and cannot change workshop settings or delete projects. The role in the app is Medarbetare. The FAQ, terms, settings, and invitation page say this, and the `owner` / `member` roles do it.
- After the pilot the price is 790 kr/mån exkl. moms, with no binding period and no automatic charge. The terms say payment starts only when the workshop chooses to continue. That matches the registration form.
- The demo is fictional, works without a network, and does not write workshop data. A real account needs a connection.
- Each sash has a work card: place, notes, photos, and the stages Mottagen through Klar. Photos are private, up to 20 per sash. The sash card shows the photo caption on one line, `Före · 17 okt 2026`, and shows a time-entry note in the sash time list.
- A printable QR label is generated from the sash card and as an A4 sheet. Each cell is 62 × 29 mm and shows the QR code, sash code, room, and project. Scanning opens the private sash card after login. The customer status link is a different address and does not include work notes. Public copy does not promise other label sizes.
- The project report can be downloaded as a PDF file or printed. The home page, the documentation guide, and the terms say that. The file is produced in the app. Photos in the PDF are embedded, not loaded from a remote address.
- The contact form does not create an account. A confirmation email is sent only when outbound mail is configured.
- “Klar” on a sash means the sash can leave the workshop. It is not a customer approval. A customer can approve delivery on the status page. The name and time are stored on the project and shown on the report. The owner can clear that approval. Public pages do not advertise it. The privacy policy says the name and time are saved.

## Competitors

These are general tools a Swedish renovation shop might already use. None of them is a per-sash window card.

- **Bygglet** — time, projects, quotes, and invoicing for small construction firms.
- **Fieldly** — project time, documents, and project economy for construction.
- **SmartDok** — hours, machines, and checklists for Nordic contractors.
- **Dalux** — drawings, photos, and quality on larger building sites.
- **Fortnox** — bookkeeping. ROT there is an invoice deduction. Bågspår does not calculate a deduction.

## Built in this round

1. Customer sign-off on the status page, with the name and time on the project and the report.
2. ROT-underlag from the workshop’s own time and material prices. It is labeled as an underlag, not tax advice, not a quote, and not an invoice. Unpriced material rows are counted and left out of the sum. There is no 30 percent calculation.
3. A real PDF download of the project report, plus a PDF of the ROT-underlag.

QR labels were already shipped, so they were not rebuilt. Quotes, card payments, and the 790 kr price were left unchanged. There is still no role named Hantverkare and no workshop-wide activity feed. The label sheet is still one size.

Public pages do not mention ROT, sign-off, quotes, or payments. The PDF sentences describe the file the app now downloads.
