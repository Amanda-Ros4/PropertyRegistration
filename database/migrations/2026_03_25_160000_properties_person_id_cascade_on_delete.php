<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ao excluir uma pessoa (ou o usuário dono em cascata), remove imóveis vinculados.
     * Sem isso, o MySQL pode tentar apagar `people` antes de `properties` e falhar com 1451.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')
                ->on('people')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')
                ->on('people');
        });
    }
};
