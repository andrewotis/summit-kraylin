<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('mailing_list_id')->nullable()->after('marketing_event_id');

            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('mailing_lists')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropForeign(['mailing_list_id']);
            $table->dropColumn('mailing_list_id');
        });
    }
};
