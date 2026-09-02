<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_type')->index()->comment('Enum InquiryType');
            $table->string('service_type')->nullable()->index()->comment('Enum ServiceType');
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->foreignId('plot_id')->nullable()->constrained('plots')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('nouveau')->index()->comment('Enum InquiryStatus');
            $table->jsonb('meta')->nullable()->comment('Données additionnelles du wizard');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'inquiry_type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
