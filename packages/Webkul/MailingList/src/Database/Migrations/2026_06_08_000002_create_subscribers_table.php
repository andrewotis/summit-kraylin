<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mailing_list_id')->unsigned();
            $table->string('email');
            $table->string('name')->nullable();
            $table->boolean('is_subscribed')->default(true);
            $table->timestamps();

            $table->foreign('mailing_list_id')->references('id')->on('mailing_lists')->onDelete('cascade');
            $table->unique(['mailing_list_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
