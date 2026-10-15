<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('customer_name', 160)->nullable();
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['workshop_id', 'status']);
        });

        Schema::create('window_sashes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('code', 120);
            $table->string('room', 120)->nullable();
            $table->string('location', 160)->nullable();
            $table->string('stage', 20)->default('received');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'code']);
            $table->index(['project_id', 'stage']);
        });

        Schema::create('sash_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sash_id')->constrained('window_sashes')->cascadeOnDelete();
            $table->string('path')->unique();
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->index(['sash_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sash_photos');
        Schema::dropIfExists('window_sashes');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('workshops');
    }
};
