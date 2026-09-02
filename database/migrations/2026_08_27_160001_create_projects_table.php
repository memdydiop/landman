<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('service_type')->index()->comment('Enum ServiceType');
            $table->string('status')->index()->comment('Enum ProjectStatus');
            $table->string('location')->nullable()->index();
            $table->decimal('surface_m2', 10, 2)->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('technical_sheet')->nullable()->comment('Fiche technique JSON');
            $table->string('cover_path')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_type', 'status', 'is_published']);
            $table->index(['is_featured', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
