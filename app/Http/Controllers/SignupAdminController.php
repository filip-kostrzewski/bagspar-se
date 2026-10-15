<?php

namespace App\Http\Controllers;

use App\Models\PilotLead;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignupAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.signups', [
            'signups' => $this->signups(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $signups = $this->signups();

        return response()->streamDownload(function () use ($signups): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Företag', 'E-post', 'Datum'], escape: '\\');

            foreach ($signups as $signup) {
                fputcsv($handle, [
                    $this->csvCell($signup['company']),
                    $this->csvCell($signup['email']),
                    $this->csvCell($signup['submitted_at']),
                ], escape: '\\');
            }

            fclose($handle);
        }, 'anmalningar.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Collection<int, array{company: string, email: string, submitted_at: string}>
     */
    private function signups(): Collection
    {
        return PilotLead::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (PilotLead $lead): array {
                return [
                    'company' => $lead->company,
                    'email' => $lead->email,
                    'submitted_at' => $lead->created_at->timezone('Europe/Stockholm')->format('Y-m-d H:i'),
                ];
            });
    }

    /**
     * Prefix cells that a spreadsheet would treat as a formula.
     */
    private function csvCell(string $value): string
    {
        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
