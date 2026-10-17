<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class SashQrCodeRenderer
{
    public function render(string $content): string
    {
        $renderer = new ImageRenderer(new RendererStyle(320, 4), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($content, Encoder::DEFAULT_BYTE_MODE_ENCODING, ErrorCorrectionLevel::Q());
    }

    public function renderPngDataUri(string $content): string
    {
        $png = (new Writer(new GDLibRenderer(320, 4)))->writeString(
            $content,
            Encoder::DEFAULT_BYTE_MODE_ENCODING,
            ErrorCorrectionLevel::Q(),
        );

        return 'data:image/png;base64,'.base64_encode($this->opaquePng($png));
    }

    private function opaquePng(string $png): string
    {
        $image = imagecreatefromstring($png);

        if ($image === false) {
            return $png;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $flat = imagecreatetruecolor($width, $height);

        if ($flat === false) {
            imagedestroy($image);

            return $png;
        }

        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, $width, $height, $white === false ? 0 : $white);
        imagecopy($flat, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        $encoded = imagepng($flat, null, 9);
        $bytes = ob_get_clean();
        imagedestroy($image);
        imagedestroy($flat);

        return $encoded && is_string($bytes) && $bytes !== '' ? $bytes : $png;
    }
}
