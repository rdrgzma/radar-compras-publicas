<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function perfis(): HasMany
    {
        return $this->hasMany(FornecedorPerfil::class);
    }

    public function selecoes(): HasMany
    {
        return $this->hasMany(OportunidadeSelecao::class);
    }
}
