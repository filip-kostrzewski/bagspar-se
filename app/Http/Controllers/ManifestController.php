<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ManifestController extends Controller
{
    public function __invoke(): Response
    {
        $manifest = [
            'name' => 'Bågspår',
            'short_name' => 'Bågspår',
            'start_url' => '/demo',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#F7F6F1',
            'theme_color' => '#F7F6F1',
            'lang' => 'sv',
            'icons' => [
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
            ],
        ];

        return response(
            json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            200,
            ['Content-Type' => 'application/manifest+json; charset=UTF-8'],
        );
    }
}
