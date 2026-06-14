<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('subscribers', 'person_id')) {
                $table->dropForeign(['mailing_list_id']);
                $table->dropUnique(['mailing_list_id', 'email']);

                $table->dropColumn(['email', 'name']);

                $table->integer('person_id')->unsigned();
                $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

                $table->foreign('mailing_list_id')->references('id')->on('mailing_lists')->onDelete('cascade');

                $table->unique(['mailing_list_id', 'person_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            if (Schema::hasColumn('subscribers', 'person_id')) {
                $table->dropForeign(['mailing_list_id']);
                $table->dropForeign(['person_id']);
                $table->dropUnique(['mailing_list_id', 'person_id']);
                $table->dropColumn('person_id');

                $table->string('email');
                $table->string('name')->nullable();
                $table->unique(['mailing_list_id', 'email']);

                $table->foreign('mailing_list_id')->references('id')->on('mailing_lists')->onDelete('cascade');
            }
        });
    }
};
