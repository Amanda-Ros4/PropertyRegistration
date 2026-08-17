<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->char('event', 1);
            $table->decimal('measure', 12, 2)->nullable();
            $table->text('description');
            $table->date('occurred_on');
            $table->timestamps();
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_endorsements');
    }
};
