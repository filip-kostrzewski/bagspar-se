<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Token-protected list of workshops with their trial, order, and paid period.
 *
 * Invoices are issued by hand. This page is where a paid period is recorded
 * once the invoice is sent, which is what keeps the workshop's account open.
 */
class WorkshopAdminController extends Controller
{
    public function index(): View
    {
        $workshops = Workshop::query()
            ->withCount('users')
            ->orderByDesc('subscription_ordered_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.workshops', [
            'workshops' => $workshops,
        ]);
    }

    public function update(Request $request, Workshop $workshop): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan' => ['nullable', Rule::in([Workshop::PLAN_MONTHLY, Workshop::PLAN_YEARLY])],
            'paid_until' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $paidUntil = $validated['paid_until'] ?? null;

        $workshop->fill([
            'subscription_plan' => $validated['subscription_plan'] ?? null,
            'paid_until' => is_string($paidUntil) && $paidUntil !== ''
                ? Carbon::parse($paidUntil.' 23:59:59', 'Europe/Stockholm')->utc()
                : null,
        ]);

        if ($workshop->paid_until !== null && $workshop->subscription_ordered_at === null) {
            $workshop->subscription_ordered_at = now();
        }

        if ($workshop->isDirty('paid_until')) {
            $workshop->access_reminder_sent_at = null;
        }

        $workshop->save();

        return redirect()->route('admin.workshops')->with('status', $workshop->name.' är uppdaterad.');
    }
}
