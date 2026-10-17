<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sash_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sash_id')->constrained('window_sashes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 150);
            $table->string('kind', 20);
            $table->string('stage', 20)->nullable();
            $table->text('body')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sash_events');
    }
};
