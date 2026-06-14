<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->longText('address')->nullable()->change();
        });

        DB::table('organizations')->whereNotNull('address')->lazyById()->each(function ($org) {
            try {
                decrypt($org->address);
            } catch (Exception $e) {
                DB::table('organizations')
                    ->where('id', $org->id)
                    ->update(['address' => encrypt($org->address)]);
            }
        });
    }

    public function down(): void
    {
        DB::table('organizations')->whereNotNull('address')->lazyById()->each(function ($org) {
            try {
                $decrypted = decrypt($org->address);
                DB::table('organizations')
                    ->where('id', $org->id)
                    ->update(['address' => $decrypted]);
            } catch (Exception $e) {
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->json('address')->nullable(false)->change();
        });
    }
};
