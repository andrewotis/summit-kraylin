<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('emergency_token')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('emergency_token_expires_at')->nullable()->after('emergency_token');
            $table->unsignedInteger('emergency_granted_by')->nullable()->after('emergency_token_expires_at');

            $table->foreign('emergency_granted_by', 'users_emergency_granted_by_fk')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_emergency_granted_by_fk');
            $table->dropColumn(['emergency_token', 'emergency_token_expires_at', 'emergency_granted_by']);
        });
    }
};
