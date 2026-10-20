<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oportunidade_selecoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('numero_controle_pncp', 80);
            $table->string('status', 30)->default('em_analise'); // 'em_analise', 'selecionada', 'descartada'
            $table->text('observacoes')->nullable();
            $table->foreignId('atualizado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Uma empresa tem exatamente um registro de status/anotação por oportunidade
            $table->unique(['empresa_id', 'numero_controle_pncp'], 'unique_empresa_oportunidade');
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oportunidade_selecoes');
    }
};
