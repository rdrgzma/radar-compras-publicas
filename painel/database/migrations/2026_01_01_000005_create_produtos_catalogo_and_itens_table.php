<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('categoria');
            $table->string('embalagem')->nullable();
            $table->string('capacidade')->nullable();
            $table->string('unidade')->nullable();
            $table->decimal('preco_referencia_site', 10, 2)->nullable();
            $table->string('url_origem')->nullable();
            $table->timestamp('data_coleta')->nullable();
            $table->text('especificacao_tecnica')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'nome', 'embalagem'], 'unique_empresa_produto_embalagem');
        });

        Schema::create('edital_itens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_controle_pncp');
            $table->string('numero_edital')->nullable();
            $table->string('orgao_razao_social')->nullable();
            $table->integer('item_numero');
            $table->integer('lote_numero')->nullable();
            $table->string('catmat')->nullable();
            $table->text('descricao_principal');
            $table->text('descricao_detalhada')->nullable();
            $table->decimal('quantidade', 12, 2);
            $table->string('unidade_medida');
            $table->decimal('valor_unitario_estimado', 12, 2)->nullable();
            $table->decimal('valor_total_estimado', 14, 2)->nullable();
            $table->string('cota_tipo')->nullable(); // ampla, me_epp, exclusiva
            $table->string('link_fonte')->nullable();
            $table->timestamp('data_coleta')->nullable();
            $table->timestamps();

            $table->unique(['numero_controle_pncp', 'item_numero'], 'unique_edital_item');
        });

        Schema::create('item_comparacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('edital_item_id')->constrained('edital_itens')->cascadeOnDelete();
            $table->foreignId('produto_catalogo_id')->nullable()->constrained('produtos_catalogo')->nullOnDelete();
            $table->string('classificacao')->default('possivel'); // possivel, confirmada, incompativel
            $table->text('motivo_correspondencia');
            $table->text('divergencias_especificacao')->nullable();
            $table->string('feedback_usuario')->nullable(); // relevante, irrelevante, ja_participo, tenho_interesse
            $table->foreignId('revisado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_em')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'edital_item_id', 'produto_catalogo_id'], 'unique_empresa_item_produto');
        });

        Schema::create('alertas_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('numero_controle_pncp');
            $table->string('tipo_alerta')->default('oportunidade_perfil'); // oportunidade_perfil, resumo_diario
            $table->string('status')->default('previa_gerada'); // previa_gerada, capturado_local, bloqueado_demo
            $table->string('destinatario_simulado')->nullable();
            $table->string('assunto');
            $table->longText('conteudo_html');
            $table->string('feedback_alerta')->nullable(); // relevante, irrelevante, ja_participo, tenho_interesse
            $table->text('observacoes_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_historico');
        Schema::dropIfExists('item_comparacoes');
        Schema::dropIfExists('edital_itens');
        Schema::dropIfExists('produtos_catalogo');
    }
};
