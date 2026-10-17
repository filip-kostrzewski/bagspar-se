<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class CustomerBackfill
{
    public static function run(): void
    {
        /** @var array<string, int> $customers */
        $customers = [];

        foreach (DB::table('customers')->orderBy('id')->get(['id', 'workshop_id', 'name']) as $customer) {
            $key = $customer->workshop_id.'|'.mb_strtolower((string) $customer->name);
            $customers[$key] ??= (int) $customer->id;
        }

        $projects = DB::table('projects')
            ->whereNull('customer_id')
            ->whereNotNull('customer_name')
            ->orderBy('id')
            ->get(['id', 'workshop_id', 'customer_name']);

        foreach ($projects as $project) {
            $name = trim((string) $project->customer_name);

            if ($name === '') {
                continue;
            }

            $key = $project->workshop_id.'|'.mb_strtolower($name);

            if (! isset($customers[$key])) {
                $customers[$key] = DB::table('customers')->insertGetId([
                    'workshop_id' => $project->workshop_id,
                    'name' => mb_substr($name, 0, 160),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('projects')->where('id', $project->id)->update([
                'customer_id' => $customers[$key],
            ]);
        }
    }
}
