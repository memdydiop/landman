<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_setting_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_setting_id')->nullable()->constrained('site_settings')->nullOnDelete();
            $table->string('key')->index();
            $table->string('group')->index();
            $table->jsonb('old_value')->nullable();
            $table->jsonb('new_value')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->default('update')->comment('create/update/restore');
            $table->timestamps();

            $table->index(['key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_setting_histories');
    }
};
