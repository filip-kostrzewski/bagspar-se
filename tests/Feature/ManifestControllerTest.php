<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManifestControllerTest extends TestCase
{
    public function test_manifest_describes_an_installable_demo(): void
    {
        $response = $this->get(route('manifest'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json; charset=UTF-8');

        $manifest = $response->json();
        $this->assertIsArray($manifest);
        $this->assertSame('Bågspår', $manifest['name']);
        $this->assertSame('Bågspår', $manifest['short_name']);
        $this->assertSame('/demo', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#F7F6F1', $manifest['background_color']);
        $this->assertSame('#F7F6F1', $manifest['theme_color']);
        $this->assertSame('sv', $manifest['lang']);
        $this->assertSame([
            [
                'src' => '/icons/icon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ], $manifest['icons']);

        $body = $response->getContent();
        $this->assertStringContainsString('"name":"Bågspår"', $body);
        $this->assertStringNotContainsString('\\u00e5', $body);
        $this->assertStringNotContainsString('\\/', $body);
    }

    public function test_layout_links_the_manifest_and_apple_touch_icon(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<meta name="theme-color" content="#F7F6F1">', false);
        $response->assertSee('<link rel="manifest" href="'.route('manifest').'">', false);
        $response->assertSee(
            '<link rel="apple-touch-icon" href="'.asset('icons/apple-touch-icon.png').'">',
            false,
        );
    }

    public function test_png_icons_match_the_manifest_and_use_the_paper_background(): void
    {
        $this->assertIcon('icons/icon-192.png', 192, 18, 18, 23, 49, 59);
        $this->assertIcon('icons/icon-512.png', 512, 48, 48, 23, 49, 59);
        $this->assertIcon('icons/apple-touch-icon.png', 180, 17, 17, 23, 49, 59);
    }

    private function assertIcon(
        string $path,
        int $size,
        int $inkX,
        int $inkY,
        int $red,
        int $green,
        int $blue,
    ): void {
        $absolutePath = public_path($path);
        $info = getimagesize($absolutePath);

        $this->assertIsArray($info);
        $this->assertSame($size, $info[0]);
        $this->assertSame($size, $info[1]);
        $this->assertSame(IMAGETYPE_PNG, $info[2]);

        $image = imagecreatefrompng($absolutePath);
        $this->assertNotFalse($image);

        $corner = imagecolorsforindex($image, imagecolorat($image, 0, 0));
        $this->assertSame(247, $corner['red']);
        $this->assertSame(246, $corner['green']);
        $this->assertSame(241, $corner['blue']);

        $ink = imagecolorsforindex($image, imagecolorat($image, $inkX, $inkY));
        $this->assertSame($red, $ink['red']);
        $this->assertSame($green, $ink['green']);
        $this->assertSame($blue, $ink['blue']);

        imagedestroy($image);
    }
}
