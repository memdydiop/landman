<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable()->comment('Taille en octets');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedSmallInteger('position')->default(0)->index();
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_media');
    }
};
