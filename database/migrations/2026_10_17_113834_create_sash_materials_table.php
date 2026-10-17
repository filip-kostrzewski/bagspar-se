<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sash_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sash_id')->constrained('window_sashes')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('description', 160);
            $table->decimal('quantity', 8, 2);
            $table->string('unit', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sash_materials');
    }
};
