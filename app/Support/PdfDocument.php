<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PdfDocument
{
    public static function download(string $html, string $filename): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('isJavascriptEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = self::filename($filename);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public static function filename(string $filename): string
    {
        $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        if ($base === '') {
            $base = 'dokument';
        }

        return $base.'.pdf';
    }
}
