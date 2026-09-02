<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table): void {
            // Supprime unique global sur reference
            try {
                $table->dropUnique(['reference']);
            } catch (Throwable $e) {
                // Peut déjà être supprimé ou nom différent selon driver
                try {
                    $table->dropUnique('plots_reference_unique');
                } catch (Throwable $e2) {
                }
            }
            // Ajoute unique composite par programme (M7 audit)
            $table->unique(['program_id', 'reference'], 'plots_program_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table): void {
            try {
                $table->dropUnique('plots_program_reference_unique');
            } catch (Throwable $e) {
            }
            $table->unique('reference');
        });
    }
};
