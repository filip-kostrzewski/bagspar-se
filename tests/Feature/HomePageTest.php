<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_describes_the_product_and_shows_the_price(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Prova exempel', false);
        $response->assertSee('href="'.route('demo').'"', false);
        $response->assertSee('Prova gratis i 30 dagar', false);
        $response->assertSee('href="'.route('register').'"', false);
        $response->assertSee('Logga in', false);
        $response->assertSee('href="'.route('login').'"', false);
        $response->assertSee('590', false);
        $response->assertSee('kr/mån', false);
        $response->assertSee('id="pris"', false);
        $response->assertSee('Därefter 590 kr/mån exkl. moms per verkstad. Tre användare ingår. Faktura varje månad, ingen bindningstid.', false);
        $response->assertSee('Betala per år: 5 900 kr/år exkl. moms, vilket motsvarar två månader gratis.', false);
        $response->assertSee('Vad kostar Bågspår?', false);
        $response->assertSee('Vad händer när provperioden tar slut?', false);
        $response->assertDontSee('3 månader', false);
        $response->assertDontSee('tre månader', false);
        $response->assertSee('Mottagen', false);
        $response->assertSee('Skrapning', false);
        $response->assertSee('Trälagning', false);
        $response->assertSee('Glasning', false);
        $response->assertSee('Målning', false);
        $response->assertSee('>Klar</span>', false);
        $response->assertDontSee('Renovera', false);
        $response->assertDontSee('Återmontera', false);
        $response->assertSee('Ägaren kan bjuda in medarbetare till samma verkstad.', false);
        $response->assertDontSee('Ett konto per verkstad.', false);
        $response->assertDontSee('En inloggning, utan medarbetarinbjudningar.', false);
        $response->assertDontSee('medarbetare kan inte bjudas in', false);
        $response->assertDontSee('medarbetarinbjudningar', false);
        $response->assertSee('Utskrivbar QR-etikett', false);
        $response->assertSee('Öppnar det privata arbetskortet efter inloggning.', false);
        $response->assertSee('Kunden får en separat statuslänk.', false);
        $response->assertSee('Upp till 20 per båge · högst 5 MB per foto.', false);
        $response->assertSee('Ladda ner som PDF eller skriv ut.', false);
        $response->assertSee('Samma uppgifter, nedladdade som PDF eller utskrivna.', false);
        $response->assertDontSee('i webbläsaren', false);
        $response->assertSee('ROT-underlag', false);
        $response->assertSee('Hjälper Bågspår med ROT?', false);
        $response->assertSee('Statuslänk till kunden', false);
        $response->assertSee('kan godkänna leveransen med namn och tid', false);
        $response->assertSee('Offert med godkännande via länk', false);
        $response->assertSee('Skriv offerten per båge, skicka länken, och kunden godkänner med sitt namn.', false);
        $response->assertSee('Mottagningsprotokoll', false);
        $response->assertSee('så ingen tvistar om sprickor i efterhand.', false);
        $response->assertSee('Kan jag skicka offerter?', false);
        $response->assertSee('Bågspår räknar inte ut något ROT-avdrag', false);
        $response->assertSee('inte skatterådgivning', false);
        $response->assertDontSee('Godkänn leverans', false);
        $response->assertDontSee('Fortsättning avtalas separat', false);
        $response->assertSee('2-KÖK-F1-B2', false);
        $response->assertSee('Ett exempel med testuppgifter är öppet utan konto.', false);
        $response->assertSee('Det här formuläret skapar inget konto.', false);
        $response->assertSee('Verkstadskontot behöver internetanslutning.', false);
        $response->assertSee('Prodi Digital AB, org.nr 559214-9370, Tegelbrukets väg 41, 195 59 Märsta', false);
        $response->assertSee('name="contact_name"', false);
        $response->assertSee('name="contact_company"', false);
        $response->assertSee('name="contact_email"', false);
        $response->assertSee('name="contact_message"', false);
        $response->assertDontSee('name="company"', false);
        $response->assertDontSee('name="email"', false);
        $response->assertSee('href="'.route('privacy').'"', false);
        $response->assertSee('href="'.route('guides.documentation').'"', false);
        $response->assertSee('href="'.route('guides.work-card').'"', false);
        $response->assertSee('href="'.route('guides.marking').'"', false);
        $response->assertSee('Korta guider om märkning och dokumentation.', false);
        $response->assertSee('href="'.route('guides.index').'"', false);
        $response->assertSee('href="'.route('guides.document-renovation').'"', false);
        $response->assertSee('href="'.route('terms').'"', false);
        $response->assertSee('>Villkor</a>', false);
        $response->assertSee('href="'.route('dpa').'"', false);
        $response->assertSee('>Personuppgiftsbiträdesavtal</a>', false);
        $response->assertSee('images/product/arbetskort.webp', false);
        $response->assertSee('alt="Arbetskort för båge 2-KÖK-F1-B2 i exempelverkstaden, med steget Glasning och stegen Mottagen till Klar"', false);
        $response->assertSee('width="2560"', false);
        $response->assertSee('height="1800"', false);
        $response->assertSee('images/product/rot-underlag.webp', false);
        $response->assertSee('alt="ROT-underlag för Exempelprojekt i exempelverkstaden, med summerad arbetskostnad och material samt knapparna Ladda ner CSV och Ladda ner PDF"', false);
        $response->assertSee('<strong>ROT-underlag</strong> Tid och material summerade per projekt, som PDF eller CSV.', false);
        $response->assertSee('images/product/statussida.webp', false);
        $response->assertSee('alt="Statussida för Exempelprojekt som kunden ser utan inloggning, med fyra bågar och deras steg samt ett godkännande av leveransen. Exempeldata."', false);
        $response->assertSee('<strong>Statussida för kunden</strong> Etapp per båge utan inloggning, med godkännande av leveransen.', false);
        $response->assertSee('height="2640"', false);
        $response->assertSee('images/product/qr-etikett.webp', false);
        $response->assertSee('alt="QR-etikett för båge 2-KÖK-F1-B2, rummet Kök, i Exempelprojekt"', false);
        $response->assertSee('width="468"', false);
        $response->assertSee('height="218"', false);
        $response->assertSee('images/product/projektrapport.webp', false);
        $response->assertSee('alt="Projektrapport för Exempelprojekt från Exempelverkstaden, med logotyp och bildtexterna Före och Efter för båge V2-H"', false);
        $response->assertSee('width="2200"', false);
        $response->assertSee('height="3066"', false);
        $response->assertSee('Skärmbilder från ett exempel med testuppgifter.', false);
        $response->assertDontSee('wisegent', false);
        $response->assertDontSee('Wisegent', false);
        $response->assertDontSee('Driver ni också städverksamhet?', false);
        $response->assertSee('<link rel="manifest" href="'.route('manifest').'">', false);
        $response->assertSee('rel="apple-touch-icon"', false);
        $response->assertDontSee('name="message"', false);
        $response->assertDontSee('name="contact_consent"', false);
        $response->assertDontSee('tänkt', false);
        $response->assertDontSee('planerat', false);
        $response->assertDontSee('tanken är', false);
        $response->assertDontSee('Så fungerar idén', false);
        $response->assertDontSee('första 10', false);
        $response->assertDontSee('tre verkstäder', false);
        $response->assertDontSee('prototyp', false);
        $response->assertDontSee('B-04', false);
        $response->assertSee('mailto:'.config('bagspar.contact_email'), false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"price":"590"', false);
        $response->assertDontSee('aggregateRating', false);
        $response->assertDontSee('tel:', false);
        $response->assertDontSee('bekräftelse', false);
        $response->assertDontSee('skickar en bekräftelse', false);
        $response->assertSee('Namn, e-post och ett meddelande räcker, också om du vill ha en genomgång innan verkstaden börjar.', false);
        $response->assertDontSee('Be oss kontakta dig', false);
    }

    public function test_homepage_promises_an_email_confirmation_only_when_mail_can_be_sent(): void
    {
        config([
            'mail.default' => 'postmark',
            'services.postmark.key' => 'server-token-test',
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('Du får en bekräftelse via e-post.', false);
        $response->assertSee('Namn, e-post och ett meddelande räcker, också om du vill ha en genomgång innan verkstaden börjar.', false);
        $response->assertDontSee('Be oss kontakta dig', false);
        $response->assertDontSee('skickar en bekräftelse', false);
    }

    public function test_contact_email_is_shown_in_the_footer(): void
    {
        config(['bagspar.contact_email' => 'kontakt@example.test']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('mailto:kontakt@example.test', false);
    }

    public function test_each_workflow_step_maps_to_a_unique_photo(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        preg_match_all(
            '/data-workflow-step\b[^>]*data-image="([^"]+)"[^>]*data-image-small="([^"]+)"[^>]*data-image-alt="([^"]+)"/',
            $html,
            $matches,
            PREG_SET_ORDER,
        );

        $this->assertCount(6, $matches);

        $full = array_column($matches, 1);
        $small = array_column($matches, 2);
        $alts = array_column($matches, 3);

        $this->assertSame($full, array_values(array_unique($full)));
        $this->assertSame($small, array_values(array_unique($small)));
        $this->assertSame($alts, array_values(array_unique($alts)));

        $hashes = [];

        foreach ([...$full, ...$small] as $url) {
            $path = public_path((string) parse_url($url, PHP_URL_PATH));
            $this->assertFileExists($path);
            $hashes[] = hash_file('sha256', $path);
        }

        $this->assertSame($hashes, array_values(array_unique($hashes)));
        $this->assertCount(12, $hashes);
    }

    public function test_pricing_section_keeps_the_pilot_anchor_without_an_empty_grid_child(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertIsString($html);
        $this->assertMatchesRegularExpression(
            '/<div id="pilot">\s*<section id="pris" class="pilot-pricing page-shell"[^>]*>\s*<div class="pilot-pricing__copy"/',
            $html,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<div id="pilot"><\/div>/',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/<div class="pilot-pricing__copy"[\s\S]*?<div class="pilot-pricing__price"[\s\S]*?<ul class="pilot-features"[\s\S]*?<figure class="pilot-pricing__shot"/',
            $html,
        );
        $this->assertStringContainsString('images/product/arbetskort-pris.webp', $html);
        $this->assertStringContainsString('alt="Arbetskort för båge 2-KÖK-F1-B2 i exempelverkstaden, med steget Glasning, stegen Mottagen till Klar och foton före och efter. Exempeldata."', $html);
        $this->assertStringContainsString('width="1600"', $html);
        $this->assertStringContainsString('height="1702"', $html);
        $pricingShot = getimagesize(public_path('images/product/arbetskort-pris.webp'));
        $this->assertIsArray($pricingShot);
        $this->assertSame(1600, $pricingShot[0]);
        $this->assertSame(1702, $pricingShot[1]);
        $this->assertStringContainsString('loading="lazy"', $html);

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertIsString($css);
        $this->assertMatchesRegularExpression('/\.pilot-pricing\s*\{[^}]*grid-template-areas:\s*"shot copy"/', $css);
        $this->assertMatchesRegularExpression('/grid-template-areas:\s*"copy"\s*"shot"/', $css);
    }

    public function test_account_screenshots_open_full_size_in_a_lightbox(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertIsString($html);
        $this->assertSame(5, preg_match_all('/data-lightbox-open/', $html));
        $this->assertStringContainsString('<dialog class="lightbox" data-lightbox aria-labelledby="lightbox-title">', $html);
        $this->assertStringContainsString('data-lightbox-close>Stäng</button>', $html);
        $this->assertStringContainsString('Visa i full storlek.', $html);
        $this->assertStringContainsString('<a class="lightbox__full" data-lightbox-full target="_blank" rel="noopener">Visa i full storlek', $html);
        $this->assertStringContainsString('<div class="lightbox__frame" data-lightbox-frame>', $html);
        $this->assertStringNotContainsString('user-scalable', $html);
        $this->assertStringNotContainsString('maximum-scale', $html);

        $script = file_get_contents(public_path('js/site.js'));
        $this->assertIsString($script);
        $this->assertStringContainsString("event.key === 'Escape'", $script);
        $this->assertStringContainsString("event.key !== 'Tab'", $script);
        $this->assertStringContainsString('lightbox.showModal()', $script);
        $this->assertStringContainsString('lightboxOpener.focus()', $script);
        $this->assertStringContainsString('lightboxFull.href = lightboxImage.src', $script);
        $this->assertStringNotContainsString('touchmove', $script);
        $this->assertStringContainsString('const resetLightboxScroll = () =>', $script);
        $this->assertStringContainsString("lightboxImage.addEventListener('load', resetLightboxScroll)", $script);
        $this->assertStringContainsString('lightboxFrame.scrollTop = 0', $script);
        $this->assertStringContainsString('lightboxFrame.scrollLeft = 0', $script);
        $this->assertStringContainsString('lightbox.scrollTop = 0', $script);
        $this->assertStringContainsString('lightbox.scrollLeft = 0', $script);

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertIsString($css);
        $this->assertMatchesRegularExpression(
            '/\.lightbox img\s*\{[^}]*width:\s*auto;[^}]*max-width:\s*90vw;[^}]*height:\s*auto;[^}]*max-height:\s*calc\(100vh - 160px\);[^}]*max-height:\s*calc\(100dvh - 160px\);[^}]*object-fit:\s*contain;/',
            $css,
        );
        $this->assertMatchesRegularExpression('/\.lightbox\s*\{[^}]*overflow-x:\s*hidden;/', $css);
        $this->assertMatchesRegularExpression('/\.lightbox\s*\{[^}]*box-sizing:\s*border-box;/', $css);
        $this->assertMatchesRegularExpression('/\.lightbox\s*\{[^}]*max-height:\s*100vh;[^}]*max-height:\s*100dvh;/', $css);
        $this->assertMatchesRegularExpression('/\.lightbox\s*\{[^}]*overflow-y:\s*hidden;/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.lightbox img\s*\{[^}]*max-width:\s*none/', $css);
        $this->assertMatchesRegularExpression('/\.lightbox__bar\s*\{[^}]*position:\s*fixed;/', $css);
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 768px\), \(pointer: coarse\) \{.*?\.lightbox__frame\s*\{[^}]*overflow:\s*auto;[^}]*-webkit-overflow-scrolling:\s*touch;[^}]*touch-action:\s*pan-x pan-y pinch-zoom;.*?\.lightbox__frame img\s*\{[^}]*max-height:\s*none;/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression('/touch-action:\s*none/', $css);
    }

    public function test_renovation_guide_uses_a_workshop_photo_thumbnail(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertIsString($html);
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('guides.document-renovation'), '/').'"[^>]*>\s*<span class="guide-teaser__visual"><img [^>]*src="[^"]*workshop-painting-small\.webp"[^>]*srcset="[^"]*workshop-painting\.webp 1200w"[^>]*alt="Färg stryks på en äldre träbåge"[^>]*width="1200"[^>]*height="800"/',
            $html,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="'.preg_quote(route('guides.document-renovation'), '/').'"[\s\S]{0,500}projektrapport\.webp/',
            $html,
        );
        $this->assertFileExists(public_path('images/workshop-painting.webp'));
        $painting = getimagesize(public_path('images/workshop-painting.webp'));
        $glazing = getimagesize(public_path('images/workshop-glazing.webp'));
        $this->assertIsArray($painting);
        $this->assertIsArray($glazing);
        $this->assertSame($glazing[0], $painting[0]);
        $this->assertSame($glazing[1], $painting[1]);
    }

    public function test_the_workflow_photo_keeps_its_frame_while_the_page_loads(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertIsString($html);
        $this->assertDoesNotMatchRegularExpression('/class="atelier__visual"[^>]*data-reveal/', $html);
        $this->assertMatchesRegularExpression('/class="atelier-photo__image"[\s\S]*?width="1200"[\s\S]*?height="800"[\s\S]*?loading="eager"/', $html);
        $this->assertStringContainsString('Kitt stryks mot glaset i en äldre träbåge', $html);
        $this->assertStringContainsString('Färg skrapas av en äldre träbåge', $html);

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertIsString($css);
        $this->assertMatchesRegularExpression('/\.atelier-photo__image\s*\{[^}]*position:\s*absolute/', $css);
    }

    public function test_homepage_shows_the_bunny_video_when_an_id_is_configured(): void
    {
        config([
            'bagspar.video.id' => 'test-video-guid',
            'bagspar.video.library_id' => '785183',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="se-video"', false);
        $response->assertSee('<iframe', false);
        $response->assertSee('https://player.mediadelivery.net/embed/785183/test-video-guid?autoplay=false&amp;loop=false&amp;muted=false&amp;preload=false&amp;responsive=true', false);
        $response->assertSee('title="Film: Bågspår i praktiken"', false);
        $response->assertSee('href="https://player.mediadelivery.net/play/785183/test-video-guid"', false);
        $response->assertSee('"@type":"VideoObject"', false);
        $response->assertSee('"embedUrl":"https://iframe.mediadelivery.net/embed/785183/test-video-guid"', false);
        $response->assertSee('/images/video-poster.jpg', false);
        $response->assertDontSee('autoplay=true', false);
    }

    public function test_homepage_hides_the_video_section_without_an_id(): void
    {
        config(['bagspar.video.id' => null]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('id="se-video"', false);
        $response->assertDontSee('mediadelivery.net', false);
        $response->assertDontSee('VideoObject', false);
    }

    public function test_video_defaults_to_the_uploaded_bunny_guid(): void
    {
        $this->assertSame('459e5746-38a0-4b21-ac4d-c6ad46731cb0', config('bagspar.video.id'));
        $this->assertSame('785183', config('bagspar.video.library_id'));
    }
}
