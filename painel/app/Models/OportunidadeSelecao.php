<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OportunidadeSelecao extends Model
{
    use HasFactory;

    protected $table = 'oportunidade_selecoes';

    protected $fillable = [
        'empresa_id',
        'numero_controle_pncp',
        'status',
        'observacoes',
        'atualizado_por_id',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selecionada' => 'Selecionada',
            'descartada'  => 'Descartada',
            default       => 'Em Análise',
        };
    }
}
