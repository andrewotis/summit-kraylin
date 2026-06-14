<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->text('from')->nullable()->change();
            $table->text('sender')->nullable()->change();
            $table->text('reply_to')->nullable()->change();
            $table->text('cc')->nullable()->change();
            $table->text('bcc')->nullable()->change();
            $table->text('reference_ids')->nullable()->change();
            $table->text('subject')->nullable()->change();
            $table->text('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->json('from')->nullable()->change();
            $table->json('sender')->nullable()->change();
            $table->json('reply_to')->nullable()->change();
            $table->json('cc')->nullable()->change();
            $table->json('bcc')->nullable()->change();
            $table->json('reference_ids')->nullable()->change();
            $table->string('subject', 255)->nullable()->change();
            $table->string('name', 255)->nullable()->change();
        });
    }
};
