<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaHistorico extends Model
{
    use HasFactory;

    protected $table = 'alertas_historico';

    protected $fillable = [
        'empresa_id',
        'numero_controle_pncp',
        'tipo_alerta',
        'status',
        'destinatario_simulado',
        'assunto',
        'conteudo_html',
        'feedback_alerta',
        'observacoes_feedback',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
