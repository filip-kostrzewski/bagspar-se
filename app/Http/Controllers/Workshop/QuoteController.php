<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Mail\QuoteSentMail;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\Quote;
use App\Models\User;
use App\Support\CustomerMailLimit;
use App\Support\Kronor;
use App\Support\PdfDocument;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class QuoteController extends Controller
{
    private const EMPTY_ROWS = 3;

    public function edit(Request $request, Project $project): View
    {
        $project = $this->authorizedProject($request, $project);
        $quote = $this->quoteFor($project);

        return view('app.projects.quote', [
            'workshop' => $project->workshop,
            'project' => $project,
            'quote' => $quote,
            'rows' => $this->formRows($quote),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project = $this->authorizedProject($request, $project);
        $quote = $this->quoteFor($project);

        if ($quote->isAccepted()) {
            return $this->lockedResponse();
        }

        $validated = $this->validatedQuote($request);

        DB::transaction(function () use ($quote, $validated): void {
            $quote->update([
                'introduction' => $validated['introduction'],
                'valid_until' => $validated['valid_until'],
            ]);
            $quote->lines()->delete();

            foreach ($validated['lines'] as $position => $line) {
                $quote->lines()->create($line + ['sort_order' => $position]);
            }
        });

        return redirect()->route('app.projects.quote.edit', $project)->with('status', 'Offerten har sparats.');
    }

    public function fill(Request $request, Project $project): RedirectResponse
    {
        $project = $this->authorizedProject($request, $project);
        $quote = $this->quoteFor($project);

        if ($quote->isAccepted()) {
            return $this->lockedResponse();
        }

        $existing = $quote->lines->pluck('description')->all();
        $nextOrder = (int) $quote->lines->max('sort_order') + 1;
        $added = 0;

        foreach ($project->windowSashes()->orderBy('code')->get() as $sash) {
            $description = 'Renovering av båge '.$sash->code;
            if (is_string($sash->room) && trim($sash->room) !== '') {
                $description .= ' · '.trim($sash->room);
            }
            $description = mb_substr($description, 0, 200);

            if (in_array($description, $existing, true)) {
                continue;
            }

            $quote->lines()->create([
                'description' => $description,
                'quantity' => 1,
                'unit_price_ore' => 0,
                'sort_order' => $nextOrder++,
            ]);
            $added++;
        }

        return redirect()->route('app.projects.quote.edit', $project)->with('status', match (true) {
            $added === 0 => 'Alla bågar har redan en rad.',
            $added === 1 => 'En rad lades till. Fyll i priset och spara.',
            default => $added.' rader lades till. Fyll i priserna och spara.',
        });
    }

    public function send(Request $request, Project $project): RedirectResponse
    {
        $project = $this->authorizedProject($request, $project);
        $quote = $this->quoteFor($project);
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        if ($quote->isAccepted()) {
            return $this->lockedResponse();
        }

        $back = redirect()->route('app.projects.quote.edit', $project);

        if ($quote->lines->isEmpty()) {
            return $back->withErrors(['quote' => 'Lägg till minst en rad innan du skickar offerten.']);
        }

        if ($quote->totalOre() <= 0) {
            return $back->withErrors(['quote' => 'Offerten har ingen summa. Fyll i priserna och spara innan du skickar.']);
        }

        $email = $project->customer?->email;

        if (! is_string($email) || trim($email) === '') {
            return $back->withErrors(['quote' => 'Kunden saknar e-postadress. Lägg till e-posten under Kunder och försök igen.']);
        }

        if (! CustomerMailLimit::allow($project->workshop()->firstOrFail())) {
            return $back->withErrors(['quote' => CustomerMailLimit::MESSAGE]);
        }

        try {
            DB::transaction(function () use ($actor, $email, $project, $quote): void {
                if (! is_string($quote->token) || $quote->token === '') {
                    $quote->issueToken();
                }

                $quote->status = Quote::STATUS_SENT;
                $quote->sent_at = now();
                $quote->save();
                $project->recordEvent($actor, ProjectEvent::KIND_QUOTE_SENT, 'Offert skickad till '.trim($email).'.');

                Mail::to(trim($email))->send(new QuoteSentMail(
                    $quote,
                    route('quote.show', ['token' => $quote->token]),
                ));
            });
        } catch (Throwable $exception) {
            Log::warning('Quote mail could not be sent.', [
                'project_id' => $project->getKey(),
                'exception' => $exception::class,
            ]);

            return $back->withErrors(['quote' => 'Offerten kunde inte skickas just nu. Försök igen om en stund.']);
        }

        return $back->with('status', 'Offerten har skickats till '.trim($email).'.');
    }

    public function pdf(Request $request, Project $project): Response
    {
        $project = $this->authorizedProject($request, $project);
        $quote = $this->quoteFor($project);
        $html = view('app.projects.quote-pdf', [
            'workshop' => $project->workshop()->firstOrFail(),
            'project' => $project,
            'quote' => $quote,
        ])->render();

        return PdfDocument::download($html, PdfDocument::filename($project->name.'-offert.pdf'));
    }

    private function authorizedProject(Request $request, Project $project): Project
    {
        $workshop = $this->currentWorkshop($request);
        $project = $this->projectForWorkshop($workshop, $project);
        Gate::authorize('update', $project);
        $project->load('customer');

        return $project;
    }

    private function quoteFor(Project $project): Quote
    {
        $quote = $project->quote()->firstOrCreate([], [
            'workshop_id' => $project->workshop_id,
            'status' => Quote::STATUS_DRAFT,
        ]);
        $quote->setRelation('project', $project);
        $quote->load('lines');

        return $quote;
    }

    private function lockedResponse(): RedirectResponse
    {
        return back()->withErrors(['quote' => 'Offerten är godkänd av kunden och kan inte ändras.']);
    }

    /**
     * @return list<array{description: string, quantity: string, unit_price: string}>
     */
    private function formRows(Quote $quote): array
    {
        $old = old('lines');

        if (is_array($old)) {
            return array_values(array_map(fn (mixed $row): array => [
                'description' => is_array($row) && is_string($row['description'] ?? null) ? $row['description'] : '',
                'quantity' => is_array($row) && is_string($row['quantity'] ?? null) ? $row['quantity'] : '',
                'unit_price' => is_array($row) && is_string($row['unit_price'] ?? null) ? $row['unit_price'] : '',
            ], $old));
        }

        $rows = $quote->lines->map(fn ($line): array => [
            'description' => $line->description,
            'quantity' => (string) $line->quantity,
            'unit_price' => Kronor::input($line->unit_price_ore),
        ])->all();

        for ($i = 0; $i < self::EMPTY_ROWS; $i++) {
            $rows[] = ['description' => '', 'quantity' => '', 'unit_price' => ''];
        }

        return $rows;
    }

    /**
     * @return array{introduction: ?string, valid_until: ?string, lines: list<array{description: string, quantity: int, unit_price_ore: int}>}
     */
    private function validatedQuote(Request $request): array
    {
        $rows = [];
        $submitted = $request->input('lines');

        if (is_array($submitted)) {
            foreach ($submitted as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $row = [
                    'description' => trim(is_string($row['description'] ?? null) ? $row['description'] : ''),
                    'quantity' => trim(is_string($row['quantity'] ?? null) ? $row['quantity'] : ''),
                    'unit_price' => trim(is_string($row['unit_price'] ?? null) ? $row['unit_price'] : ''),
                ];

                if ($row['description'] === '' && $row['unit_price'] === '') {
                    continue;
                }

                $rows[(int) $index] = $row;
            }
        }

        $introduction = $request->input('introduction');
        $validator = Validator::make([
            'introduction' => is_string($introduction) ? trim($introduction) : $introduction,
            'valid_until' => $request->input('valid_until'),
            'lines' => $rows,
        ], [
            'introduction' => ['nullable', 'string', 'max:5000'],
            'valid_until' => ['nullable', 'date_format:Y-m-d'],
            'lines' => ['array', 'max:100'],
            'lines.*.description' => ['required', 'string', 'max:200'],
            'lines.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'lines.*.unit_price' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, Closure $fail): void {
                try {
                    if (Kronor::parseOptional($value) === null) {
                        $fail('Rad :position: ange priset i kronor.');
                    }
                } catch (InvalidArgumentException) {
                    $fail('Rad :position: ange priset i kronor, till exempel 4500 eller 4500,50.');
                }
            }],
        ], [
            'introduction.max' => 'Inledningen får vara högst 5 000 tecken.',
            'valid_until.date_format' => 'Ange sista giltighetsdag som ett datum.',
            'lines.max' => 'Offerten får ha högst 100 rader.',
            'lines.*.description.required' => 'Rad :position: ange en beskrivning.',
            'lines.*.description.max' => 'Rad :position: beskrivningen får vara högst 200 tecken.',
            'lines.*.quantity.integer' => 'Rad :position: ange antalet som ett heltal.',
            'lines.*.quantity.min' => 'Rad :position: antalet måste vara minst 1.',
            'lines.*.quantity.max' => 'Rad :position: antalet får vara högst 9 999.',
            'lines.*.unit_price.required' => 'Rad :position: ange priset i kronor.',
            'lines.*.unit_price.max' => 'Rad :position: priset får vara högst 1 000 000 kr.',
        ]);

        $validated = $validator->validate();
        $lines = [];

        foreach ($rows as $row) {
            $lines[] = [
                'description' => $row['description'],
                'quantity' => $row['quantity'] === '' ? 1 : (int) $row['quantity'],
                'unit_price_ore' => (int) Kronor::parseOptional($row['unit_price']),
            ];
        }

        return [
            'introduction' => is_string($validated['introduction'] ?? null) && $validated['introduction'] !== '' ? $validated['introduction'] : null,
            'valid_until' => is_string($validated['valid_until'] ?? null) && $validated['valid_until'] !== '' ? $validated['valid_until'] : null,
            'lines' => $lines,
        ];
    }
}
