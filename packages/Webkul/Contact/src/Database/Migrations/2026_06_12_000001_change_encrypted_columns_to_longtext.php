<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->longText('emails')->change();
            $table->longText('contact_numbers')->change();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->longText('address')->change();
        });

        DB::table('persons')->whereNotNull('emails')->lazyById()->each(function ($person) {
            try {
                decrypt($person->emails);
            } catch (\Exception $e) {
                DB::table('persons')
                    ->where('id', $person->id)
                    ->update(['emails' => encrypt($person->emails)]);
            }
        });

        DB::table('persons')->whereNotNull('contact_numbers')->lazyById()->each(function ($person) {
            try {
                decrypt($person->contact_numbers);
            } catch (\Exception $e) {
                DB::table('persons')
                    ->where('id', $person->id)
                    ->update(['contact_numbers' => encrypt($person->contact_numbers)]);
            }
        });

        DB::table('organizations')->whereNotNull('address')->lazyById()->each(function ($org) {
            try {
                decrypt($org->address);
            } catch (\Exception $e) {
                DB::table('organizations')
                    ->where('id', $org->id)
                    ->update(['address' => encrypt($org->address)]);
            }
        });
    }

    public function down(): void
    {
        DB::table('persons')->whereNotNull('emails')->lazyById()->each(function ($person) {
            try {
                $decrypted = decrypt($person->emails);
                DB::table('persons')
                    ->where('id', $person->id)
                    ->update(['emails' => $decrypted]);
            } catch (\Exception $e) {}
        });

        DB::table('persons')->whereNotNull('contact_numbers')->lazyById()->each(function ($person) {
            try {
                $decrypted = decrypt($person->contact_numbers);
                DB::table('persons')
                    ->where('id', $person->id)
                    ->update(['contact_numbers' => $decrypted]);
            } catch (\Exception $e) {}
        });

        DB::table('organizations')->whereNotNull('address')->lazyById()->each(function ($org) {
            try {
                $decrypted = decrypt($org->address);
                DB::table('organizations')
                    ->where('id', $org->id)
                    ->update(['address' => $decrypted]);
            } catch (\Exception $e) {}
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->json('emails')->change();
            $table->json('contact_numbers')->change();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->json('address')->change();
        });
    }
};
