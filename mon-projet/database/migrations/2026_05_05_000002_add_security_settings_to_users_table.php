<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mfa_method')->nullable()->after('password');
            $table->string('mfa_email_code_hash')->nullable()->after('mfa_method');
            $table->timestamp('mfa_email_code_expires_at')->nullable()->after('mfa_email_code_hash');
            $table->string('mfa_token_hash')->nullable()->after('mfa_email_code_expires_at');
        });

        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->text('public_key');
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passkeys');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mfa_method',
                'mfa_email_code_hash',
                'mfa_email_code_expires_at',
                'mfa_token_hash',
            ]);
        });
    }
};
