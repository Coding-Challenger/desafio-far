<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_evento', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedBigInteger('tipo_evento_id');
            $table->unsignedBigInteger('user_id');
            $table->text('descricao');
            $table->date('data_inicio')->nullable();
            $table->date('data_prazo')->nullable();
            $table->date('data_conclusao')->nullable();
            $table->string('status', 15)->default('aberto');
            $table->timestamps();

            $table->foreign('tipo_evento_id')->references('id')->on('tipo_evento');
            $table->foreign('user_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_evento');
        Schema::dropIfExists('eventos');
    }
};
