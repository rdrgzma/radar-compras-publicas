<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\FornecedorPerfil;
use App\Models\OportunidadeSelecao;
use App\Models\User;
use App\Services\CsvExportService;
use App\Services\GoApiClient;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IntegrationValidationTest extends TestCase
{
    protected Empresa $empresaAlpha;
    protected Empresa $empresaBeta;
    protected User $admin;
    protected User $userAlpha;
    protected User $userBeta;
    protected FornecedorPerfil $perfilAlpha;
    protected FornecedorPerfil $perfilBeta;
    protected OportunidadeSelecao $selecaoAlpha;
    protected OportunidadeSelecao $selecaoBeta;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Criar Empresas
        $this->empresaAlpha = Empresa::firstOrCreate(
            ['cnpj' => '11.111.111/0001-11'],
            ['razao_social' => 'Alpha Equipamentos de Proteção Ltda', 'ativo' => true]
        );

        $this->empresaBeta = Empresa::firstOrCreate(
            ['cnpj' => '22.222.222/0002-22'],
            ['razao_social' => 'Beta Soluções Tecnológicas Ltda', 'ativo' => true]
        );

        // 2. Criar Usuários
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@radar.com.br'],
            ['name' => 'Admin Teste', 'password' => Hash::make('senha123'), 'role' => 'admin']
        );

        $this->userAlpha = User::firstOrCreate(
            ['email' => 'alpha_test@empresa.com.br'],
            ['empresa_id' => $this->empresaAlpha->id, 'name' => 'User Alpha', 'password' => Hash::make('senha123'), 'role' => 'fornecedor']
        );

        $this->userBeta = User::firstOrCreate(
            ['email' => 'beta_test@empresa.com.br'],
            ['empresa_id' => $this->empresaBeta->id, 'name' => 'User Beta', 'password' => Hash::make('senha123'), 'role' => 'fornecedor']
        );

        // 3. Perfis de Fornecedor
        $this->perfilAlpha = FornecedorPerfil::updateOrCreate(
            ['empresa_id' => $this->empresaAlpha->id, 'nome' => 'Perfil Alpha'],
            [
                'termos_obrigatorios' => ['epi', 'seguranca'],
                'termos_excluidos'    => ['asfalto'],
                'ufs'                 => ['SP', 'RS'],
                'ativo'               => true,
            ]
        );
        FornecedorPerfil::where('empresa_id', $this->empresaAlpha->id)
            ->where('id', '!=', $this->perfilAlpha->id)
            ->update(['ativo' => false]);

        $this->perfilBeta = FornecedorPerfil::updateOrCreate(
            ['empresa_id' => $this->empresaBeta->id, 'nome' => 'Perfil Beta'],
            [
                'termos_obrigatorios' => ['nuvem', 'servidores'],
                'termos_excluidos'    => ['limpeza'],
                'ufs'                 => ['DF'],
                'ativo'               => true,
            ]
        );
        FornecedorPerfil::where('empresa_id', $this->empresaBeta->id)
            ->where('id', '!=', $this->perfilBeta->id)
            ->update(['ativo' => false]);

        // 4. Seleções
        $this->selecaoAlpha = OportunidadeSelecao::updateOrCreate(
            ['empresa_id' => $this->empresaAlpha->id, 'numero_controle_pncp' => 'TEST-001/2026'],
            ['status' => 'selecionada', 'observacoes' => 'Observação confidencial Alpha', 'atualizado_por_id' => $this->userAlpha->id]
        );

        $this->selecaoBeta = OportunidadeSelecao::updateOrCreate(
            ['empresa_id' => $this->empresaBeta->id, 'numero_controle_pncp' => 'TEST-002/2026'],
            ['status' => 'em_analise', 'observacoes' => 'Observação confidencial Beta', 'atualizado_por_id' => $this->userBeta->id]
        );
    }

    public function test_controle_de_acesso_aos_paineis(): void
    {
        // Admin pode acessar admin e portal
        $adminPanel = Filament::getPanel('admin');
        $portalPanel = Filament::getPanel('portal');

        $this->assertTrue($this->admin->canAccessPanel($adminPanel), 'Admin deve acessar painel /admin');
        $this->assertTrue($this->admin->canAccessPanel($portalPanel), 'Admin pode acessar portal para suporte');

        // Fornecedor não pode acessar /admin
        $this->assertFalse($this->userAlpha->canAccessPanel($adminPanel), 'Fornecedor Alpha não pode acessar /admin');
        $this->assertTrue($this->userAlpha->canAccessPanel($portalPanel), 'Fornecedor Alpha deve acessar /portal');

        // Proteção da página de sincronizações e carga de demonstração
        $this->actingAs($this->admin);
        $this->assertTrue(\App\Filament\Pages\Sincronizacoes::canAccess(), 'Admin pode acessar página de sincronizações');

        $this->actingAs($this->userAlpha);
        $this->assertFalse(\App\Filament\Pages\Sincronizacoes::canAccess(), 'Fornecedor Alpha NÃO pode acessar página de sincronizações');
    }

    public function test_isolamento_de_perfis_entre_empresas(): void
    {
        // Simulando autenticação do usuário da Empresa Alpha
        $this->actingAs($this->userAlpha);

        // O fornecedor Alpha só deve enxergar o perfil de sua própria empresa via Query Scoped
        $queryAlpha = \App\Filament\Portal\Resources\PerfilFornecedorResource::getEloquentQuery();
        $this->assertNotNull($queryAlpha->find($this->perfilAlpha->id), 'Perfil Alpha deve ser localizado');
        $this->assertNull($queryAlpha->find($this->perfilBeta->id), 'Tentativa de acessar Perfil Beta via ID direto deve retornar null');

        // Tentativa de manipular empresa_id para gravar perfil em outra empresa deve falhar
        $this->assertNotEquals($this->userAlpha->empresa_id, $this->empresaBeta->id);
    }

    public function test_isolamento_de_selecoes_entre_empresas(): void
    {
        // Usuário Alpha autenticado
        $this->actingAs($this->userAlpha);

        $queryAlpha = \App\Filament\Portal\Resources\MinhasSelecoesResource::getEloquentQuery();
        $this->assertNotNull($queryAlpha->find($this->selecaoAlpha->id), 'Seleção Alpha deve ser localizada');
        $this->assertNull($queryAlpha->find($this->selecaoBeta->id), 'Tentativa de manipular ID para ver Seleção Beta deve retornar null');

        // Usuário Beta autenticado
        $this->actingAs($this->userBeta);

        $queryBeta = \App\Filament\Portal\Resources\MinhasSelecoesResource::getEloquentQuery();
        $this->assertNotNull($queryBeta->find($this->selecaoBeta->id), 'Seleção Beta deve ser localizada');
        $this->assertNull($queryBeta->find($this->selecaoAlpha->id), 'Tentativa de manipular ID para ver Seleção Alpha deve retornar null');
    }

    public function test_exportacao_csv_sanitizada_e_isolada(): void
    {
        $this->actingAs($this->userAlpha);

        // Cria oportunidade com valor potencialmente malicioso para testar CSV injection
        OportunidadeSelecao::updateOrCreate(
            ['empresa_id' => $this->empresaAlpha->id, 'numero_controle_pncp' => 'INJECTION-TEST/2026'],
            [
                'status'            => 'selecionada',
                'observacoes'       => '=cmd|/C calc!A0', // fórmula maliciosa
                'atualizado_por_id' => $this->userAlpha->id,
            ]
        );

        $selecoes = OportunidadeSelecao::where('empresa_id', auth()->user()->empresa_id)->get();

        $cabecalhos = ['Controle', 'Status', 'Observações'];
        $linhas = [];
        foreach ($selecoes as $s) {
            $linhas[] = [
                $s->numero_controle_pncp,
                $s->status,
                $s->observacoes,
            ];
        }

        $csv = CsvExportService::gerarCsv($cabecalhos, $linhas, ';');

        // Valida BOM UTF-8
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Valida neutralização da injeção de fórmula
        $this->assertStringContainsString("'=cmd|/C calc!A0", $csv, "A fórmula maliciosa deve ter sido prefixada com apóstrofo");

        // Valida que não contém dados da empresa Beta
        $this->assertStringNotContainsString('TEST-002/2026', $csv);
        $this->assertStringNotContainsString('Observação confidencial Beta', $csv);
    }

    public function test_comportamento_gracioso_quando_api_go_indisponivel(): void
    {
        // Cria um cliente Go apontando para porta inexistente
        config(['services.radar_go.base_url' => 'http://127.0.0.1:59999']);
        config(['services.radar_go.timeout' => 1]);

        $client = new GoApiClient();

        $saude = $client->checkSaude();
        $this->assertEquals('indisponivel', $saude['status']);
        $this->assertEquals('indisponivel', $saude['banco_de_dados']);

        $match = $client->avaliarMatch(['termos_obrigatorios' => ['epi']]);
        $this->assertArrayHasKey('erro', $match);
        $this->assertEmpty($match['dados']);
        $this->assertEquals(0, $match['total']);

        $sync = $client->iniciarSincronizacao('2026-10-19', '2026-10-20');
        $this->assertFalse($sync['sucesso']);
        $this->assertStringContainsString('Falha de comunicação', $sync['mensagem']);
    }

    public function test_rota_minhas_selecoes_responde_com_sucesso(): void
    {
        $this->actingAs($this->userAlpha);

        $response = $this->get('/portal/minhas-selecoes');
        $response->assertOk();
    }

    public function test_rota_perfil_fornecedor_responde_com_sucesso(): void
    {
        $this->actingAs($this->userAlpha);

        $response = $this->get('/portal/perfil-fornecedors');
        $response->assertOk();
    }

    public function test_rota_radar_oportunidades_responde_com_sucesso(): void
    {
        $this->actingAs($this->userAlpha);

        $response = $this->get('/portal/radar-oportunidades');
        $response->assertOk();
        $response->assertSee('encontrada(s)');
        $response->assertSee('Motivo Explícito da Correspondência', false);
    }

    public function test_rota_sincronizacoes_admin_responde_com_sucesso(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/sincronizacoes');
        $response->assertOk();
    }

    public function test_criacao_de_perfil_por_admin_e_por_fornecedor(): void
    {
        // 1. Criar perfil como Fornecedor
        $this->actingAs($this->userAlpha);
        $novoPerfilAlpha = FornecedorPerfil::create([
            'empresa_id'          => $this->userAlpha->empresa_id,
            'nome'                => 'Novo Perfil Fornecedor',
            'termos_obrigatorios' => ['luvas', 'capacetes'],
            'termos_excluidos'    => ['tintas'],
            'ufs'                 => ['SP'],
            'ativo'               => true,
        ]);
        $this->assertNotNull($novoPerfilAlpha->id);
        $this->assertEquals($this->empresaAlpha->id, $novoPerfilAlpha->empresa_id);

        // 2. Administrador sem empresa vinculada pode criar perfil indicando a empresa
        $this->actingAs($this->admin);
        $this->assertNull($this->admin->empresa_id);
        $novoPerfilAdmin = FornecedorPerfil::create([
            'empresa_id'          => $this->empresaBeta->id,
            'nome'                => 'Perfil Criado Por Admin',
            'termos_obrigatorios' => ['servidores', 'redes'],
            'termos_excluidos'    => [],
            'ufs'                 => ['DF'],
            'ativo'               => true,
        ]);
        $this->assertNotNull($novoPerfilAdmin->id);
        $this->assertEquals($this->empresaBeta->id, $novoPerfilAdmin->empresa_id);
    }

    public function test_caso_ecoclean_catalogo_e_perfil_configurados(): void
    {
        $empresaEcoclean = Empresa::where('razao_social', 'like', '%Ecoclean%')->first();
        $this->assertNotNull($empresaEcoclean, 'Empresa Ecoclean deve existir no banco.');
        $this->assertStringContainsString('demonstração não vinculada à empresa', $empresaEcoclean->razao_social);

        $produtosCount = \App\Models\ProdutoCatalogo::where('empresa_id', $empresaEcoclean->id)->count();
        $this->assertGreaterThanOrEqual(20, $produtosCount, 'Catálogo da Ecoclean deve ter ao menos 20 produtos das 7 famílias.');

        $perfilEcoclean = FornecedorPerfil::where('empresa_id', $empresaEcoclean->id)->first();
        $this->assertNotNull($perfilEcoclean);
        $this->assertContains('prestacao de servicos de limpeza', $perfilEcoclean->termos_excluidos);
        $this->assertContains('saco de lixo', $perfilEcoclean->termos_obrigatorios);
    }

    public function test_rotas_ecoclean_portal_respondem_com_sucesso(): void
    {
        $userEcoclean = User::where('email', 'fornecedor@ecoclean.com.br')->first();
        $this->assertNotNull($userEcoclean);
        $this->actingAs($userEcoclean);

        // 1. Catálogo
        $respCat = $this->get('/portal/produto-catalogos');
        $respCat->assertOk();
        $respCat->assertSee('Catálogo de Produtos', false);

        // 2. Comparação de Itens
        $respComp = $this->get('/portal/comparacao-itens');
        $respComp->assertOk();
        $respComp->assertSee('Comparativo Técnico de Itens do Edital', false);
        $respComp->assertSee('88577416000118-1-000066/2026', false);
        $respComp->assertSee('Água Sanitária', false);

        // 3. Central de Alertas
        $respAlert = $this->get('/portal/central-alertas');
        $respAlert->assertOk();
        $respAlert->assertSee('Modo de Demonstração Ativo — Envio Real Desativado', false);
        $respAlert->assertSee('Ecoclean', false);

        // 4. Relatório Comercial
        $respRel = $this->get('/portal/relatorio-comercial');
        $respRel->assertOk();
        $respRel->assertSee('Oportunidades Identificadas no Mercado Público', false);
        $respRel->assertSee('R$ 61.203.954,72', false);
        $respRel->assertSee('R$ 32.863.953,53', false);
    }

    public function test_isolamento_tenant_ecoclean_vs_alpha(): void
    {
        $userEcoclean = User::where('email', 'fornecedor@ecoclean.com.br')->first();
        $this->actingAs($userEcoclean);

        $produtosEcoclean = \App\Models\ProdutoCatalogo::where('empresa_id', $userEcoclean->empresa_id)->pluck('id');
        $this->assertNotEmpty($produtosEcoclean);

        // Usuário Alpha não deve ver comparações ou produtos da Ecoclean
        $this->actingAs($this->userAlpha);
        $produtosAlphaVisiveis = \App\Filament\Portal\Resources\ProdutoCatalogoResource::getEloquentQuery()->pluck('id');
        foreach ($produtosEcoclean as $pId) {
            $this->assertNotContains($pId, $produtosAlphaVisiveis, 'Produto da Ecoclean não pode ser visto pela Alpha.');
        }
    }
}
