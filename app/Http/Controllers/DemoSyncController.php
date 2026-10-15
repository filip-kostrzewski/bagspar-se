<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemoSyncRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DemoSyncController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $demoId = strtolower($request->string('demo_id')->toString());

        if (! Str::isUuid($demoId)) {
            return response()->json([
                'message' => 'Ogiltigt exempel.',
            ], 422);
        }

        $card = Cache::get($this->cacheKey($demoId));

        return response()->json([
            'card' => is_array($card) ? $card : null,
        ]);
    }

    public function store(StoreDemoSyncRequest $request): JsonResponse
    {
        $demoId = strtolower($request->string('demo_id')->toString());

        $notes = array_map(fn (array $note): array => [
            'id' => $note['id'],
            'text' => $note['text'],
            'meta' => $note['meta'] ?? '',
        ], $request->validated('notes'));

        $card = [
            'step' => $request->string('step')->toString(),
            'notes' => $notes,
        ];

        Cache::put(
            $this->cacheKey($demoId),
            $card,
            now()->addDays((int) config('bagspar.demo_sync_days')),
        );

        return response()->json([
            'card' => $card,
        ]);
    }

    private function cacheKey(string $demoId): string
    {
        return 'bagspar.demo-card.'.$demoId;
    }
}
