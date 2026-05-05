<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_passwords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->string('url', 2048);
            $table->text('password');
            $table->text('notes')->nullable();
            $table->string('favicon_url', 2048)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'service_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_passwords');
    }
};
