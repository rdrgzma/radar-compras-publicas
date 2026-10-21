<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EditalItem extends Model
{
    use HasFactory;

    protected $table = 'edital_itens';

    protected $fillable = [
        'numero_controle_pncp',
        'numero_edital',
        'orgao_razao_social',
        'item_numero',
        'lote_numero',
        'catmat',
        'descricao_principal',
        'descricao_detalhada',
        'quantidade',
        'unidade_medida',
        'valor_unitario_estimado',
        'valor_total_estimado',
        'cota_tipo',
        'link_fonte',
        'data_coleta',
    ];

    protected $casts = [
        'quantidade'              => 'decimal:2',
        'valor_unitario_estimado' => 'decimal:2',
        'valor_total_estimado'    => 'decimal:2',
        'data_coleta'             => 'datetime',
    ];

    public function comparacoes(): HasMany
    {
        return $this->hasMany(ItemComparacao::class, 'edital_item_id');
    }
}
