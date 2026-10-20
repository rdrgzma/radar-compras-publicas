<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FornecedorPerfil extends Model
{
    use HasFactory;

    protected $table = 'fornecedor_perfis';

    protected $fillable = [
        'empresa_id',
        'nome',
        'termos_obrigatorios',
        'termos_excluidos',
        'ufs',
        'ativo',
    ];

    protected $casts = [
        'termos_obrigatorios' => 'array',
        'termos_excluidos' => 'array',
        'ufs' => 'array',
        'ativo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
