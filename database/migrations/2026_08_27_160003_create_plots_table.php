<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('reference')->unique()->comment('Ex: LOT-A12');
            $table->decimal('surface_m2', 10, 2);
            $table->decimal('price', 12, 2)->nullable();
            $table->string('status')->index()->comment('Enum PlotStatus');
            $table->boolean('is_viabilise')->default(true);
            $table->string('juridical_status')->nullable()->comment('Statut juridique / ACD');
            $table->string('plan_pdf_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'status']);
            $table->index(['status', 'is_viabilise']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};
