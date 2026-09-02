<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('page_visits', function (Blueprint $table) {
                $table->index('created_at');
            });
        } catch (Throwable $e) {
        }
        try {
            Schema::table('page_visits', function (Blueprint $table) {
                $table->index(['route', 'created_at']);
            });
        } catch (Throwable $e) {
        }

        // pg_trgm for title search (Postgres only)
        if (DB::connection()->getDriverName() === 'pgsql') {
            try {
                DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
                DB::statement('CREATE INDEX IF NOT EXISTS programs_title_trgm ON programs USING gin (title gin_trgm_ops)');
                DB::statement('CREATE INDEX IF NOT EXISTS projects_title_trgm ON projects USING gin (title gin_trgm_ops)');
            } catch (Throwable $e) {
                // Ignore if insufficient privileges (CI)
            }
        }
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            try {
                $table->dropIndex(['created_at']);
            } catch (Throwable $e) {
            }
            try {
                $table->dropIndex(['route', 'created_at']);
            } catch (Throwable $e) {
            }
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            try {
                DB::statement('DROP INDEX IF EXISTS programs_title_trgm');
                DB::statement('DROP INDEX IF EXISTS projects_title_trgm');
            } catch (Throwable $e) {
            }
        }
    }
};
