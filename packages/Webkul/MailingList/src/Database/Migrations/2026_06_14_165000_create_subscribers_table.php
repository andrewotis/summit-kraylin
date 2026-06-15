<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscribers')) {
            Schema::create('subscribers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('mailing_list_id')->unsigned();
                $table->integer('person_id')->unsigned();
                $table->boolean('is_subscribed')->default(true);
                $table->timestamps();

                $table->foreign('mailing_list_id')->references('id')->on('mailing_lists')->onDelete('cascade');
                $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
                $table->unique(['mailing_list_id', 'person_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
