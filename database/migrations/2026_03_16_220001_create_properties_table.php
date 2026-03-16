<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); // auto-increment = municipal registration ID
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people');
            $table->string('street');
            $table->string('number', 20);
            $table->string('neighborhood');
            $table->string('complement')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'street']);
            $table->index(['user_id', 'person_id']);
            $table->index(['user_id', 'neighborhood']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
