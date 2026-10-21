<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\FornecedorPerfil;
use App\Models\OportunidadeSelecao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador Global do Radar
        $admin = User::firstOrCreate(
            ['email' => 'admin@radar.com.br'],
            [
                'name'     => 'Administrador Radar',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // 2. Empresa 1: Alpha Equipamentos de Proteção
        $empresaAlpha = Empresa::firstOrCreate(
            ['cnpj' => '12.345.678/0001-99'],
            [
                'razao_social'  => 'Alpha Equipamentos de Proteção e Segurança Ltda',
                'nome_fantasia' => 'Alpha EPI & Segurança',
                'ativo'         => true,
            ]
        );

        // Fornecedor vinculado à Empresa 1
        $userAlpha = User::firstOrCreate(
            ['email' => 'fornecedor@alphaepi.com.br'],
            [
                'empresa_id' => $empresaAlpha->id,
                'name'       => 'Carlos Fornecedor Alpha',
                'password'   => Hash::make('fornecedor123'),
                'role'       => 'fornecedor',
            ]
        );

        // Perfil de busca da Empresa 1
        $perfilAlpha = FornecedorPerfil::firstOrCreate(
            [
                'empresa_id' => $empresaAlpha->id,
                'nome'       => 'Proteção Individual e Segurança',
            ],
            [
                'termos_obrigatorios' => ['equipamentos de protecao', 'epi', 'seguranca', 'uniformes', 'botas'],
                'termos_excluidos'    => ['asfalto', 'pavimentacao', 'obras civis'],
                'ufs'                 => ['SP', 'RS', 'RJ', 'MG', 'PR', 'DF'],
                'ativo'               => true,
            ]
        );

        // Pré-seleção demonstrativa para a Empresa 1
        OportunidadeSelecao::firstOrCreate(
            [
                'empresa_id'           => $empresaAlpha->id,
                'numero_controle_pncp' => '46374500000194-1-099002/2026',
            ],
            [
                'status'            => 'selecionada',
                'observacoes'       => 'Excelente margem e especificações aderentes ao catálogo Alpha EPI.',
                'atualizado_por_id' => $userAlpha->id,
            ]
        );

        // 3. Empresa 2 (Para Validação Rigorosa de Isolamento entre Tenants)
        $empresaBeta = Empresa::firstOrCreate(
            ['cnpj' => '98.765.432/0001-11'],
            [
                'razao_social'  => 'Beta Soluções de Tecnologia Ltda',
                'nome_fantasia' => 'Beta Cloud & TI',
                'ativo'         => true,
            ]
        );

        $userBeta = User::firstOrCreate(
            ['email' => 'fornecedor@betati.com.br'],
            [
                'empresa_id' => $empresaBeta->id,
                'name'       => 'Mariana Fornecedora Beta',
                'password'   => Hash::make('fornecedor123'),
                'role'       => 'fornecedor',
            ]
        );

        FornecedorPerfil::firstOrCreate(
            [
                'empresa_id' => $empresaBeta->id,
                'nome'       => 'Serviços de Tecnologia da Informação',
            ],
            [
                'termos_obrigatorios' => ['tecnologia da informacao', 'ti', 'nuvem', 'seguranca da informacao'],
                'termos_excluidos'    => ['limpeza', 'alimentos'],
                'ufs'                 => ['DF', 'SP', 'RJ'],
                'ativo'               => true,
            ]
        );

        // 4. Empresa 3 Demonstrativa: Ecoclean (Caso Demonstrativo Personalizado)
        $this->call(EcocleanDemonstracaoSeeder::class);
    }
}
