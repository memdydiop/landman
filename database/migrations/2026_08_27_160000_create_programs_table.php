<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('city')->index();
            $table->string('address')->nullable();
            $table->decimal('total_area', 10, 2)->nullable()->comment('Surface totale en m2');
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
