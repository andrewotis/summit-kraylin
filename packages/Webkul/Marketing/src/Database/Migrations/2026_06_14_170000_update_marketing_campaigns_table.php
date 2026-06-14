<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('mail_to')->nullable()->change();
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_campaigns', 'mailing_list_id')) {
                $table->unsignedInteger('mailing_list_id')->nullable()->after('marketing_event_id');
                $table->foreign('mailing_list_id')
                    ->references('id')
                    ->on('mailing_lists')
                    ->onDelete('set null');
            }
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_campaigns', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
            $table->string('mail_to')->nullable(false)->change();
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_campaigns', 'mailing_list_id')) {
                $table->dropForeign(['mailing_list_id']);
                $table->dropColumn('mailing_list_id');
            }
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_campaigns', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
        });
    }
};
