<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('window_sashes', function (Blueprint $table): void {
            $table->text('intake_notes')->nullable();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->timestamp('intake_approved_at')->nullable();
            $table->string('intake_approved_name', 150)->nullable();
            $table->string('intake_approved_via', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['intake_approved_at', 'intake_approved_name', 'intake_approved_via']);
        });

        Schema::table('window_sashes', function (Blueprint $table): void {
            $table->dropColumn('intake_notes');
        });
    }
};
