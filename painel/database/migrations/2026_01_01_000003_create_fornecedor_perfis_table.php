<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fornecedor_perfis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->json('termos_obrigatorios')->nullable(); // palavras que devem constar
            $table->json('termos_excluidos')->nullable();    // palavras que eliminam a oportunidade
            $table->json('ufs')->nullable();                 // UFs de interesse
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedor_perfis');
    }
};
