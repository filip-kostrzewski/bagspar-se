<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
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
}
