<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropUnique('people_user_id_cpf_unique');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->unique(['user_id', 'cpf'], 'people_user_id_cpf_unique');
        });
    }
};
