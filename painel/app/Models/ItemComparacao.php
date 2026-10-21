<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemComparacao extends Model
{
    use HasFactory;

    protected $table = 'item_comparacoes';

    protected $fillable = [
        'empresa_id',
        'edital_item_id',
        'produto_catalogo_id',
        'classificacao',
        'motivo_correspondencia',
        'divergencias_especificacao',
        'feedback_usuario',
        'revisado_por_id',
        'revisado_em',
    ];

    protected $casts = [
        'revisado_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function editalItem(): BelongsTo
    {
        return $this->belongsTo(EditalItem::class, 'edital_item_id');
    }

    public function produtoCatalogo(): BelongsTo
    {
        return $this->belongsTo(ProdutoCatalogo::class, 'produto_catalogo_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_id');
    }
}
