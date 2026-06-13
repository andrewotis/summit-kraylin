<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE '.DB::getTablePrefix().'attribute_values MODIFY json_value TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE '.DB::getTablePrefix().'attribute_values MODIFY json_value JSON NULL');
    }
};
