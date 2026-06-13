<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->text('subject')->nullable()->change();
            $table->text('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->string('subject', 255)->nullable()->change();
            $table->string('name', 255)->nullable()->change();
        });
    }
};
