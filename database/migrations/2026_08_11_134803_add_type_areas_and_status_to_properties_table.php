<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('type', 20)->default('land')->after('person_id');
            $table->decimal('land_area', 12, 2)->nullable()->after('type');
            $table->decimal('building_area', 12, 2)->nullable()->after('land_area');
            $table->string('status', 20)->default('active')->after('complement');

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn(['type', 'land_area', 'building_area', 'status']);
        });
    }
};
