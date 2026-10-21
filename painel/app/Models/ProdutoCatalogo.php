<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoCatalogo extends Model
{
    use HasFactory;

    protected $table = 'produtos_catalogo';

    protected $fillable = [
        'empresa_id',
        'nome',
        'categoria',
        'embalagem',
        'capacidade',
        'unidade',
        'preco_referencia_site',
        'url_origem',
        'data_coleta',
        'especificacao_tecnica',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'preco_referencia_site' => 'decimal:2',
        'data_coleta'           => 'datetime',
        'ativo'                 => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function comparacoes(): HasMany
    {
        return $this->hasMany(ItemComparacao::class, 'produto_catalogo_id');
    }
}
