<?php

namespace Database\Seeders;

use App\Models\AlertaHistorico;
use App\Models\EditalItem;
use App\Models\Empresa;
use App\Models\FornecedorPerfil;
use App\Models\ItemComparacao;
use App\Models\ProdutoCatalogo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EcocleanDemonstracaoSeeder extends Seeder
{
    public function run(): void
    {
        $hoje = Carbon::now('America/Sao_Paulo');

        // 1. Cadastrar a Empresa Demonstrativa
        $empresa = Empresa::updateOrCreate(
            ['cnpj' => '00.000.000/0001-91'],
            [
                'razao_social'  => 'Ecoclean — demonstração não vinculada à empresa',
                'nome_fantasia' => 'Ecoclean Produtos (Demonstração)',
                'ativo'         => true,
            ]
        );

        // 2. Criar Usuário do Portal para Ecoclean
        $usuario = User::updateOrCreate(
            ['email' => 'fornecedor@ecoclean.com.br'],
            [
                'empresa_id' => $empresa->id,
                'name'       => 'Representante Comercial Ecoclean',
                'password'   => Hash::make('fornecedor123'),
                'role'       => 'fornecedor',
            ]
        );

        // 3. Cadastrar Catálogo das 7 Famílias Extraídas do Catálogo Público
        $produtos = [
            // Família 1: Água sanitária, detergentes e desinfetantes
            [
                'nome'                   => 'Água Sanitária e Alvejante Ecoclean 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona / Garrafa 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Galão / Bombona',
                'preco_referencia_site'  => 19.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Hipoclorito de sódio ativo 2.0% a 2.5% p/p. Alvejante e desinfetante para uso geral.',
                'observacoes'            => 'Preço de referência do site institucional; não constitui cotação final.',
            ],
            [
                'nome'                   => 'Cloro Concentrado 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 55.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Teor de cloro ativo concentrado 5.0% a 6.0% para desinfecção pesada e sanitização.',
                'observacoes'            => 'Preço de referência do site institucional.',
            ],
            [
                'nome'                   => 'Detergente Neutro Líquido Concentrado 500ml',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Frasco 500ml',
                'capacidade'             => '500 ml',
                'unidade'                => 'Frasco',
                'preco_referencia_site'  => 2.30,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Linear alquilbenzeno sulfonato de sódio, tensoativo aniônico biodegradável, neutro.',
                'observacoes'            => 'Disponível também em embalagens institucionais de 5L.',
            ],
            [
                'nome'                   => 'Detergente Neutro Líquido Concentrado 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 22.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Tensoativo biodegradável concentrado para higienização profissional de cozinhas e utensílios.',
                'observacoes'            => 'Preço de referência institucional.',
            ],
            [
                'nome'                   => 'Desinfetante Floral / Pinho 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 18.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Cloreto de alquil dimetil benzil amônio (quaternário de amônio), ação bactericida comprovada.',
                'observacoes'            => 'Disponível nas fragrâncias pinho, floral e lavanda.',
            ],
            [
                'nome'                   => 'Álcool Líquido 70% INPM 1L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Frasco 1 Litro',
                'capacidade'             => '1 Litro',
                'unidade'                => 'Frasco',
                'preco_referencia_site'  => 11.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Álcool etílico hidratado 70% INPM (77°GL) para assepsia e desinfecção de superfícies.',
                'observacoes'            => 'Em conformidade com normas ANVISA.',
            ],
            [
                'nome'                   => 'Álcool Líquido 70% INPM 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 58.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Álcool etílico 70% INPM hospitalar/institucional.',
                'observacoes'            => 'Preço de tabela pública do site.',
            ],
            [
                'nome'                   => 'Álcool Gel 70% INPM 5L',
                'categoria'              => 'Água sanitária, detergentes e desinfetantes',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 68.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Álcool etílico em gel 70% INPM com emoliente protetor da pele.',
                'observacoes'            => 'Uso em dispensers institucionais de recarga.',
            ],

            // Família 2: Desengordurantes e limpa-vidros
            [
                'nome'                   => 'Desengordurante e Desengraxante Multiuso 5L',
                'categoria'              => 'Desengordurantes e limpa-vidros',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 55.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Solução desengordurante alcalina à base de tensoativos aniônicos e solventes biodegradáveis.',
                'observacoes'            => 'Eficaz contra óleos, graxas e gorduras pesadas.',
            ],
            [
                'nome'                   => 'Limpa-Vidros com Ação Anti-Estática 500ml',
                'categoria'              => 'Desengordurantes e limpa-vidros',
                'embalagem'              => 'Frasco com pulverizador / tampa 500ml',
                'capacidade'             => '500 ml',
                'unidade'                => 'Frasco',
                'preco_referencia_site'  => 9.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Solvente glicólico, álcool isopropílico, tensoativo e ação anti-embaçante.',
                'observacoes'            => 'Secagem rápida sem manchas em espelhos, vidros e cristais.',
            ],
            [
                'nome'                   => 'Limpador Ajax Multiuso 1L',
                'categoria'              => 'Desengordurantes e limpa-vidros',
                'embalagem'              => 'Frasco 1000ml',
                'capacidade'             => '1 Litro',
                'unidade'                => 'Frasco',
                'preco_referencia_site'  => 19.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Limpador geral para superfícies laváveis, pisos, bancadas e azulejos.',
                'observacoes'            => 'Preço de referência do site.',
            ],
            [
                'nome'                   => 'Cera Líquida Autobrilho Incolor 5L',
                'categoria'              => 'Desengordurantes e limpa-vidros',
                'embalagem'              => 'Bombona 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Bombona',
                'preco_referencia_site'  => 140.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Dispersões acrílicas, resinas fumáticas, acabamento antiderrapante com película protetora.',
                'observacoes'            => 'Aplicação em pisos cerâmicos, lajotas e cimento polido.',
            ],

            // Família 3: Sabonetes líquidos
            [
                'nome'                   => 'Sabonete Líquido Erva Doce Galão 5L',
                'categoria'              => 'Sabonetes líquidos',
                'embalagem'              => 'Galão 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Galão',
                'preco_referencia_site'  => 28.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Sabonete neutro com pH balanceado 6.0 a 7.0, perolizado, hidratante para higienização frequente das mãos.',
                'observacoes'            => 'Ideal para ambientes corporativos, escolas e unidades de saúde.',
            ],
            [
                'nome'                   => 'Sabonete Líquido Floral Galão 5L',
                'categoria'              => 'Sabonetes líquidos',
                'embalagem'              => 'Galão 5 Litros',
                'capacidade'             => '5 Litros',
                'unidade'                => 'Galão',
                'preco_referencia_site'  => 28.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Sabonete perfumado com agentes condicionantes e emolientes.',
                'observacoes'            => 'Preço de tabela pública.',
            ],
            [
                'nome'                   => 'Sabonete em Barra Glicerinado 90g',
                'categoria'              => 'Sabonetes líquidos',
                'embalagem'              => 'Unidade 90g',
                'capacidade'             => '90 g',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 2.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Sabonete sólido à base de sais de ácidos graxos e glicerina vegetal pura.',
                'observacoes'            => 'Suave e hipoalergênico.',
            ],

            // Família 4: Papel higiênico e papel-toalha
            [
                'nome'                   => 'Papel Higiênico Folha Dupla 30m (Pacote com 4 rolos)',
                'categoria'              => 'Papel higiênico e papel-toalha',
                'embalagem'              => 'Fardo / Pacote 4 rolos',
                'capacidade'             => '4 rolos de 30m x 10cm',
                'unidade'                => 'Pacote',
                'preco_referencia_site'  => 5.80,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => '100% Celulose virgem, picotado, folha dupla, maciez superior, branco.',
                'observacoes'            => 'Em conformidade com normas ABNT.',
            ],
            [
                'nome'                   => 'Papel Higiênico Rolão Institucional 300m (Fardo com 8 rolos)',
                'categoria'              => 'Papel higiênico e papel-toalha',
                'embalagem'              => 'Fardo com 8 rolos de 300m',
                'capacidade'             => '8 rolos de 300m x 10cm',
                'unidade'                => 'Fardo',
                'preco_referencia_site'  => 44.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Celulose virgem e fibras vegetais de alta resistência, picotado, gramatura balanceada.',
                'observacoes'            => 'Para uso em dispensers de papel higiênico industrial de alta rotatividade.',
            ],
            [
                'nome'                   => 'Papel Toalha Interfolhado 2 Dobras 100% Celulose (1000 folhas)',
                'categoria'              => 'Papel higiênico e papel-toalha',
                'embalagem'              => 'Pacote 1000 folhas (2 dobras)',
                'capacidade'             => '1000 folhas 20x21cm',
                'unidade'                => 'Pacote',
                'preco_referencia_site'  => 12.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => '100% celulose virgem, 2 dobras interfolhadas, alta absorção e resistência a úmido.',
                'observacoes'            => 'Item com alta demanda em órgãos públicos e escolas municipais.',
            ],
            [
                'nome'                   => 'Guardanapo de Papel 24x24cm (Pacote com 50 unidades)',
                'categoria'              => 'Papel higiênico e papel-toalha',
                'embalagem'              => 'Pacote 50 unidades',
                'capacidade'             => '50 unidades 24x24cm',
                'unidade'                => 'Pacote',
                'preco_referencia_site'  => 4.20,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Folha dupla macia, 100% fibras celulósicas puras, cor branca.',
                'observacoes'            => 'Preço de tabela de descartáveis.',
            ],

            // Família 5: Sacos para lixo
            [
                'nome'                   => 'Saco para Lixo 30 Litros Preto Reforçado (Pacote com 100)',
                'categoria'              => 'Sacos para lixo',
                'embalagem'              => 'Pacote 100 unidades',
                'capacidade'             => '30 Litros (espessura 6 micras)',
                'unidade'                => 'Pacote 100un',
                'preco_referencia_site'  => 13.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polietileno reciclado de alta qualidade com solda contínua estanque ABNT NBR 9191 Classe I Tipo B.',
                'observacoes'            => 'Indicado para lixeiras de escritórios e banheiros.',
            ],
            [
                'nome'                   => 'Saco para Lixo 50 Litros Preto Reforçado (Pacote com 100)',
                'categoria'              => 'Sacos para lixo',
                'embalagem'              => 'Pacote 100 unidades',
                'capacidade'             => '50 Litros (espessura 6 micras)',
                'unidade'                => 'Pacote 100un',
                'preco_referencia_site'  => 19.80,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polietileno de alta densidade reforçado, solda de fundo estanque.',
                'observacoes'            => 'Preço público de referência.',
            ],
            [
                'nome'                   => 'Saco para Lixo 100 Litros Preto Reforçado (Pacote com 100)',
                'categoria'              => 'Sacos para lixo',
                'embalagem'              => 'Pacote 100 unidades',
                'capacidade'             => '100 Litros (espessura 8 a 12 micras)',
                'unidade'                => 'Pacote 100un',
                'preco_referencia_site'  => 48.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Capacidade 100L, soldado no fundo, resistente a perfurações e umidade para coletas gerais.',
                'observacoes'            => 'Grande volume de contratação no setor público.',
            ],
            [
                'nome'                   => 'Saco para Resíduos de Serviços de Saúde 100L Branco Leitoso (Pacote com 100)',
                'categoria'              => 'Sacos para lixo',
                'embalagem'              => 'Pacote 100 unidades',
                'capacidade'             => '100 Litros (10 micras)',
                'unidade'                => 'Pacote 100un',
                'preco_referencia_site'  => 44.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polietileno de alta densidade virgem, branco leitoso com símbolo de infectante conforme NBR 9191.',
                'observacoes'            => 'Destinado a unidades de saúde, pronto atendimentos e postos municipais.',
            ],

            // Família 6: Rodos, baldes, esponjas, panos e escovas
            [
                'nome'                   => 'Balde Plástico Reforçado 20 Litros com Alça',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade',
                'capacidade'             => '20 Litros',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 28.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polipropileno de alta resistência, borda reforçada e alça de arame galvanizado.',
                'observacoes'            => 'Item presente no catálogo e no site.',
            ],
            [
                'nome'                   => 'Balde com Espremedor 14 Litros',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade',
                'capacidade'             => '14 Litros',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 45.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Balde profissional com espremedor de mop manual incorporado.',
                'observacoes'            => 'Uso para limpeza de pisos de grandes áreas.',
            ],
            [
                'nome'                   => 'Rodo de Madeira com Borracha Dupla 60cm e Cabo',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade (suporte + cabo 1,20m)',
                'capacidade'             => '60 cm',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 14.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Cepa de madeira reflorestada 60cm, borracha dupla flexível e cabo rosqueado 1,20m.',
                'observacoes'            => 'Alta durabilidade.',
            ],
            [
                'nome'                   => 'Esponja Dupla Face Multiuso',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Pacote / Unidade',
                'capacidade'             => '100x70x20mm',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 0.90,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Espuma de poliuretano com manta abrasiva sintética aderida.',
                'observacoes'            => 'Preço do catálogo de descartáveis.',
            ],
            [
                'nome'                   => 'Esponja de Lã de Aço (Pacote com 8 unidades)',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Pacote 8 unidades (60g)',
                'capacidade'             => '60 g',
                'unidade'                => 'Pacote 8un',
                'preco_referencia_site'  => 3.90,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Fibras de aço carbono extrafinas biodegradáveis (tipo Bombril).',
                'observacoes'            => 'Item tradicional do catálogo.',
            ],
            [
                'nome'                   => 'Flanela de Algodão Laranja 40x30cm',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade',
                'capacidade'             => '40 x 30 cm',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 1.90,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => '100% algodão felpudo com costura reforçada de acabamento nas bordas.',
                'observacoes'            => 'Não risca superfícies móveis e vidros.',
            ],
            [
                'nome'                   => 'Pano de Limpeza Alvejado com Bainha 70x40cm',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade',
                'capacidade'             => '70 x 40 cm',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 3.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => '100% algodão alvejado de alta gramatura com bainha reforçada.',
                'observacoes'            => 'Pano de chão de alta resistência para lavagens contínuas.',
            ],
            [
                'nome'                   => 'Vassoura Cerdas de Náilon com Cabo 1,20m',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade completa',
                'capacidade'             => 'Cepa 25cm / Cabo 1,20m',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 12.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Cerdas sintéticas plumadas de náilon, cepa plástica e cabo rosqueável plastificado.',
                'observacoes'            => 'Varrição de pisos internos.',
            ],
            [
                'nome'                   => 'Pá Coletora de Lixo de Plástico com Cabo Longo',
                'categoria'              => 'Rodos, baldes, esponjas, panos e escovas',
                'embalagem'              => 'Unidade',
                'capacidade'             => 'Cabo 60cm',
                'unidade'                => 'Unidade',
                'preco_referencia_site'  => 6.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polipropileno com perfil em ângulo reto de 90° e cabo ergonômico.',
                'observacoes'            => 'Preço de tabela de utensílios.',
            ],

            // Família 7: Copos descartáveis e luvas
            [
                'nome'                   => 'Copos Descartáveis 50ml Cafezinho (Tira com 100)',
                'categoria'              => 'Copos descartáveis e luvas',
                'embalagem'              => 'Tira com 100 unidades',
                'capacidade'             => '50 ml',
                'unidade'                => 'Tira 100un',
                'preco_referencia_site'  => 2.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polipropileno atóxico branco, conforme ABNT NBR 14865.',
                'observacoes'            => 'Uso em cafezinho corporativo.',
            ],
            [
                'nome'                   => 'Copos Descartáveis 200ml Água (Tira com 100)',
                'categoria'              => 'Copos descartáveis e luvas',
                'embalagem'              => 'Tira com 100 unidades',
                'capacidade'             => '200 ml',
                'unidade'                => 'Tira 100un',
                'preco_referencia_site'  => 4.50,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Polipropileno atóxico frisado branco para bebidas frias e quentes conforme NBR 14865.',
                'observacoes'            => 'Altíssima rotatividade em todas as secretarias públicas.',
            ],
            [
                'nome'                   => 'Luva de Borracha Látex Amarela Cano Longo (Par - Tam M)',
                'categoria'              => 'Copos descartáveis e luvas',
                'embalagem'              => 'Par em embalagem individual',
                'capacidade'             => 'Tamanho M',
                'unidade'                => 'Par',
                'preco_referencia_site'  => 4.80,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Látex natural com acabamento antiderrapante na palma e ponta dos dedos, forrada com algodão.',
                'observacoes'            => 'Para serviços gerais de limpeza pesada.',
            ],
            [
                'nome'                   => 'Luva de Borracha Látex Amarela Cano Longo (Par - Tam G)',
                'categoria'              => 'Copos descartáveis e luvas',
                'embalagem'              => 'Par em embalagem individual',
                'capacidade'             => 'Tamanho G',
                'unidade'                => 'Par',
                'preco_referencia_site'  => 4.80,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Látex natural antiderrapante cano longo para proteção manual.',
                'observacoes'            => 'Preço de catálogo.',
            ],
            [
                'nome'                   => 'Luva de Procedimento Látex com Pó (Caixa com 100 - Tam M)',
                'categoria'              => 'Copos descartáveis e luvas',
                'embalagem'              => 'Caixa com 100 unidades',
                'capacidade'             => 'Tamanho M',
                'unidade'                => 'Caixa 100un',
                'preco_referencia_site'  => 25.00,
                'url_origem'             => 'https://www.produtosecoclean.com.br/produtos/',
                'especificacao_tecnica'  => 'Látex de borracha natural, ambidestra, com pó bioabsorvível grau farmacêutico, não estéril.',
                'observacoes'            => 'Indicação para atendimento ambulatorial e manipulação higiênica.',
            ],
        ];

        foreach ($produtos as $p) {
            ProdutoCatalogo::updateOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'nome'       => $p['nome'],
                    'embalagem'  => $p['embalagem'],
                ],
                array_merge($p, [
                    'data_coleta' => $hoje,
                    'ativo'       => true,
                ])
            );
        }

        // 4. Configurar Perfil de Monitoramento da Ecoclean
        $perfil = FornecedorPerfil::updateOrCreate(
            [
                'empresa_id' => $empresa->id,
                'nome'       => 'Fornecimento de Materiais de Limpeza e Higiene',
            ],
            [
                'termos_obrigatorios' => [
                    'agua sanitaria',
                    'alvejante',
                    'detergente',
                    'desinfetante',
                    'limpa vidros',
                    'desengordurante',
                    'sabonete liquido',
                    'papel higienico',
                    'papel toalha',
                    'saco de lixo',
                    'rodo',
                    'balde',
                    'esponja',
                    'pano de limpeza',
                    'vassoura',
                    'copo descartavel',
                    'luva de borracha',
                    'alcool',
                    'cloro concentrado',
                    'material de limpeza',
                    'materiais de higiene',
                ],
                'termos_excluidos' => [
                    'prestacao de servicos de limpeza',
                    'prestacao de servicos',
                    'mao de obra',
                    'mão de obra',
                    'rocada',
                    'roçada',
                    'desassoreamento',
                    'coleta de residuos',
                    'coleta de lixo urbano',
                ],
                'ufs'                 => ['RS'],
                'ativo'               => true,
            ]
        );

        // 5. Inserir Contratações Históricas na API Go (se não existirem)
        $historicas = [
            [
                'numero_controle_pncp'       => '88577416000118-1-000066/2026',
                'ano_compra'                 => 2026,
                'sequencial_compra'          => 66,
                'numero_compra'              => '0103/2026',
                'processo'                   => '25.0.000068035-7',
                'objeto'                     => 'Aquisição de materiais de higiene e limpeza, para atender as demandas das Unidades Administrativas, do Município de Canoas, através dos Almoxarifados das Secretarias de Administração - SMA, Secretaria da Educação - SME e Secretaria da Saúde - SMS',
                'modalidade_id'              => 6,
                'modalidade_nome'            => 'Pregão - Eletrônico',
                'modo_disputa_id'            => 1,
                'modo_disputa_nome'          => 'Aberto',
                'situacao_id'                => 1,
                'situacao_nome'              => 'Divulgada no PNCP',
                'orgao_cnpj'                 => '88577416000118',
                'orgao_razao_social'         => 'MUNICIPIO DE CANOAS',
                'orgao_esfera'               => 'M',
                'orgao_poder'                => 'N',
                'unidade_nome'               => 'PREFEITURA MUNICIPAL DE CANOAS',
                'unidade_uf_sigla'           => 'RS',
                'unidade_uf_nome'            => 'Rio Grande do Sul',
                'unidade_municipio_nome'     => 'Canoas',
                'unidade_codigo_ibge'        => '4304606',
                'valor_total_estimado'       => 61203954.72,
                'valor_total_homologado'     => 31956221.32,
                'data_publicacao_pncp'       => '2026-04-15 14:20:44',
                'data_abertura_proposta'     => '2026-04-15 12:00:00',
                'data_encerramento_proposta' => '2026-04-29 12:01:00',
                'link_sistema_origem'        => 'https://pregaobanrisul.com.br/editais/0103_2026/349814',
                'link_pncp'                  => 'https://pncp.gov.br/app/editais/88577416000118/2026/66',
            ],
            [
                'numero_controle_pncp'       => '88577416000118-1-000112/2026',
                'ano_compra'                 => 2026,
                'sequencial_compra'          => 112,
                'numero_compra'              => '0212/2026',
                'processo'                   => '260000024841-9',
                'objeto'                     => 'Aquisição de materiais de higiene e limpeza para manutenção de estoque crítico da Secretaria Municipal de Administração (SMA), conforme condições, quantidades e exigências estabelecidas neste instrumento.',
                'modalidade_id'              => 8,
                'modalidade_nome'            => 'Dispensa',
                'modo_disputa_id'            => 4,
                'modo_disputa_nome'          => 'Dispensa Com Disputa',
                'situacao_id'                => 1,
                'situacao_nome'              => 'Divulgada no PNCP',
                'orgao_cnpj'                 => '88577416000118',
                'orgao_razao_social'         => 'MUNICIPIO DE CANOAS',
                'orgao_esfera'               => 'M',
                'orgao_poder'                => 'N',
                'unidade_nome'               => 'PREFEITURA MUNICIPAL DE CANOAS',
                'unidade_uf_sigla'           => 'RS',
                'unidade_uf_nome'            => 'Rio Grande do Sul',
                'unidade_municipio_nome'     => 'Canoas',
                'unidade_codigo_ibge'        => '4304606',
                'valor_total_estimado'       => 47273.95,
                'valor_total_homologado'     => 30123.14,
                'data_publicacao_pncp'       => '2026-06-18 08:50:43',
                'data_abertura_proposta'     => '2026-06-18 09:00:00',
                'data_encerramento_proposta' => '2026-06-23 09:30:00',
                'link_sistema_origem'        => 'https://pregaobanrisul.com.br/editais/0212_2026/352670',
                'link_pncp'                  => 'https://pncp.gov.br/app/editais/88577416000118/2026/112',
            ],
            [
                'numero_controle_pncp'       => '14885499000176-1-000003/2026',
                'ano_compra'                 => 2026,
                'sequencial_compra'          => 3,
                'numero_compra'              => '0002/2026',
                'processo'                   => '26.1.000000005-8',
                'objeto'                     => 'Registro de preços para aquisição de materiais de higiene e limpeza em atendimento às demandas da Fundação Municipal de Saúde de Canoas (FMSC).',
                'modalidade_id'              => 6,
                'modalidade_nome'            => 'Pregão - Eletrônico',
                'modo_disputa_id'            => 1,
                'modo_disputa_nome'          => 'Aberto',
                'situacao_id'                => 1,
                'situacao_nome'              => 'Divulgada no PNCP',
                'orgao_cnpj'                 => '14885499000176',
                'orgao_razao_social'         => 'FUNDACAO MUNICIPAL DE SAUDE DE CANOAS',
                'orgao_esfera'               => 'M',
                'orgao_poder'                => 'E',
                'unidade_nome'               => 'FUNDAÇÃO MUNICIPAL DE SAÚDE DE CANOAS',
                'unidade_uf_sigla'           => 'RS',
                'unidade_uf_nome'            => 'Rio Grande do Sul',
                'unidade_municipio_nome'     => 'Canoas',
                'unidade_codigo_ibge'        => '4304606',
                'valor_total_estimado'       => 16257.42,
                'valor_total_homologado'     => 13396.11,
                'data_publicacao_pncp'       => '2026-02-20 08:20:40',
                'data_abertura_proposta'     => '2026-02-20 08:00:00',
                'data_encerramento_proposta' => '2026-03-05 09:05:00',
                'link_sistema_origem'        => 'https://pregaobanrisul.com.br/editais/0002_2026/347529',
                'link_pncp'                  => 'https://pncp.gov.br/app/editais/14885499000176/2026/3',
            ],
        ];

        foreach ($historicas as $h) {
            DB::statement("
                INSERT INTO api_go.oportunidades (
                    id, numero_controle_pncp, ano_compra, sequencial_compra, numero_compra,
                    processo, objeto, informacao_complementar, modalidade_id, modalidade_nome,
                    modo_disputa_id, modo_disputa_nome, situacao_id, situacao_nome,
                    orgao_cnpj, orgao_razao_social, orgao_esfera, orgao_poder,
                    unidade_nome, unidade_uf_sigla, unidade_uf_nome, unidade_municipio_nome, unidade_codigo_ibge,
                    valor_total_estimado, valor_total_homologado, data_publicacao_pncp,
                    data_abertura_proposta, data_encerramento_proposta, data_atualizacao_pncp,
                    link_sistema_origem, link_processo_eletronico, link_pncp, data_coleta,
                    is_demonstrativo
                ) VALUES (
                    :id, :num, :ano, :seq, :num_c,
                    :proc, :obj, '', :mod_id, :mod_nm,
                    :disp_id, :disp_nm, :sit_id, :sit_nm,
                    :cnpj, :razao, :esfera, :poder,
                    :unid_nm, :uf, :uf_nm, :muni, :ibge,
                    :v_est, :v_homo, :dt_pub::timestamp,
                    :dt_ab::timestamp, :dt_enc::timestamp, :dt_pub::timestamp,
                    :link_origem, '', :link_pncp, NOW(),
                    false
                )
                ON CONFLICT (numero_controle_pncp) DO UPDATE SET
                    objeto = EXCLUDED.objeto,
                    valor_total_estimado = EXCLUDED.valor_total_estimado,
                    valor_total_homologado = EXCLUDED.valor_total_homologado,
                    link_sistema_origem = EXCLUDED.link_sistema_origem,
                    link_pncp = EXCLUDED.link_pncp
            ", [
                'id'          => \Illuminate\Support\Str::uuid()->toString(),
                'num'         => $h['numero_controle_pncp'],
                'ano'         => $h['ano_compra'],
                'seq'         => $h['sequencial_compra'],
                'num_c'       => $h['numero_compra'],
                'proc'        => $h['processo'],
                'obj'         => $h['objeto'],
                'mod_id'      => $h['modalidade_id'],
                'mod_nm'      => $h['modalidade_nome'],
                'disp_id'     => $h['modo_disputa_id'],
                'disp_nm'     => $h['modo_disputa_nome'],
                'sit_id'      => $h['situacao_id'],
                'sit_nm'      => $h['situacao_nome'],
                'cnpj'        => $h['orgao_cnpj'],
                'razao'       => $h['orgao_razao_social'],
                'esfera'      => $h['orgao_esfera'],
                'poder'       => $h['orgao_poder'],
                'unid_nm'     => $h['unidade_nome'],
                'uf'          => $h['unidade_uf_sigla'],
                'uf_nm'       => $h['unidade_uf_nome'],
                'muni'        => $h['unidade_municipio_nome'],
                'ibge'        => $h['unidade_codigo_ibge'],
                'v_est'       => $h['valor_total_estimado'],
                'v_homo'      => $h['valor_total_homologado'],
                'dt_pub'      => $h['data_publicacao_pncp'],
                'dt_ab'       => $h['data_abertura_proposta'],
                'dt_enc'      => $h['data_encerramento_proposta'],
                'link_origem' => $h['link_sistema_origem'],
                'link_pncp'   => $h['link_pncp'],
            ]);
        }

        // 6. Cadastrar os Itens Oficiais do Edital 0103/2026 (Canoas) para Comparação
        $itensEdital = [
            [
                'item_numero'             => 2,
                'lote_numero'             => 2,
                'catmat'                  => '310507',
                'descricao_principal'     => 'Água Sanitária',
                'descricao_detalhada'     => 'Composição Química: Hipoclorito de Sódio, Hidróxido de Sódio, Cloreto. Aplicação: Lavagem e Alvejante de Roupas, Banheiras, Pias. Cor: Incolor. Tipo: Comum. Garrafa 2 Litros. Cota Principal 80%.',
                'quantidade'              => 21334,
                'unidade_medida'          => 'Garrafa 2 Litros',
                'valor_unitario_estimado' => 3.75,
                'valor_total_estimado'    => 80002.50,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Água Sanitária e Alvejante Ecoclean 5L',
                'divergencia'             => 'Embalagem do Edital é Garrafa de 2 Litros; Ecoclean padroniza Bombona de 5 Litros. A formulação química (Hipoclorito de Sódio 2.0-2.5%) é equivalente.',
            ],
            [
                'item_numero'             => 4,
                'lote_numero'             => 4,
                'catmat'                  => '310507',
                'descricao_principal'     => 'Água Sanitária',
                'descricao_detalhada'     => 'Composição Química: Hipoclorito de Sódio, Hidróxido de Sódio, Cloreto. Aplicação: Lavagem e Alvejante de Roupas, Banheiras, Pias. Cor: Incolor. Tipo: Comum. Garrafa 5 Litros. Cota Principal 75%.',
                'quantidade'              => 16660,
                'unidade_medida'          => 'Garrafa 5 Litros',
                'valor_unitario_estimado' => 7.90,
                'valor_total_estimado'    => 131614.00,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Água Sanitária e Alvejante Ecoclean 5L',
                'divergencia'             => 'Convergência total de embalagem (5 Litros) e especificação de alvejante/desinfetante.',
            ],
            [
                'item_numero'             => 6,
                'lote_numero'             => 6,
                'catmat'                  => '269941',
                'descricao_principal'     => 'Álcool Etílico Líquido 70%',
                'descricao_detalhada'     => 'Tipo: Hidratado. Teor Alcoólico: 70% (70°GL). Apresentação: Líquido. Cota Principal 75%.',
                'quantidade'              => 99456,
                'unidade_medida'          => 'Litro',
                'valor_unitario_estimado' => 7.74,
                'valor_total_estimado'    => 769789.44,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Álcool Líquido 70% INPM 1L',
                'divergencia'             => 'Convergência direta de embalagem (1 Litro) e teor alcoólico 70% INPM.',
            ],
            [
                'item_numero'             => 10,
                'lote_numero'             => 10,
                'catmat'                  => '380018',
                'descricao_principal'     => 'Álcool Etílico em Gel 70%',
                'descricao_detalhada'     => 'Concentração: 70°INPM. Composição Básica: Com Emoliente. Forma Farmacêutica: Gel. Frasco 500ml. Cota Principal 75%.',
                'quantidade'              => 28624,
                'unidade_medida'          => 'Frasco 500ml',
                'valor_unitario_estimado' => 5.51,
                'valor_total_estimado'    => 157718.24,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Álcool Gel 70% INPM 5L',
                'divergencia'             => 'Edital solicita frasco 500ml; Ecoclean comercializa galão institucional de 5 Litros (requer fracionamento ou cotação em frasco).',
            ],
            [
                'item_numero'             => 16,
                'lote_numero'             => 16,
                'catmat'                  => '325229',
                'descricao_principal'     => 'Balde Plástico 20 Litros',
                'descricao_detalhada'     => 'Capacidade: 20 L. Material: Plástico. Material Alça: Arame Galvanizado. Características Adicionais: Reforço Fundo e Borda. Exclusivo ME/EPP.',
                'quantidade'              => 5414,
                'unidade_medida'          => 'Unidade',
                'valor_unitario_estimado' => 14.28,
                'valor_total_estimado'    => 77311.92,
                'cota_tipo'               => 'exclusiva_me_epp',
                'correspondente_sugerido' => 'Balde Plástico Reforçado 20 Litros com Alça',
                'divergencia'             => 'Convergência total de capacidade (20 Litros), alça de arame galvanizado e reforço.',
            ],
            [
                'item_numero'             => 24,
                'lote_numero'             => 24,
                'catmat'                  => '332641',
                'descricao_principal'     => 'Copo Descartável 200 ML',
                'descricao_detalhada'     => 'Capacidade: 200 ML. Material: Plástico. Características Adicionais: Atóxico, De Acordo C/ Norma ABNT NBR 14865. Cor: Branco. Pacote 100 unidades. Cota Principal 75%.',
                'quantidade'              => 222196,
                'unidade_medida'          => 'Pacote 100 unidades',
                'valor_unitario_estimado' => 4.20,
                'valor_total_estimado'    => 933223.20,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Copos Descartáveis 200ml Água (Tira com 100)',
                'divergencia'             => 'Convergência total de material (plástico atóxico), capacidade (200ml), norma ABNT e embalagem com 100.',
            ],
            [
                'item_numero'             => 27,
                'lote_numero'             => 27,
                'catmat'                  => '408649',
                'descricao_principal'     => 'Desinfetante Aroma Pinho',
                'descricao_detalhada'     => 'Princípio Ativo: Cloreto Alquil Dimetil Benzil Amônio + Tensioativos. Aroma: Pinho. Solução Aquosa, Quaternário de Amônio. Litro. Cota Principal 75%.',
                'quantidade'              => 35435,
                'unidade_medida'          => 'Litro',
                'valor_unitario_estimado' => 4.89,
                'valor_total_estimado'    => 173277.15,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Desinfetante Floral / Pinho 5L',
                'divergencia'             => 'Edital requisita frasco de 1 Litro; Ecoclean fornece bombona de 5 Litros. Formulação ativa (quaternário de amônio pinho) idêntica.',
            ],
            [
                'item_numero'             => 30,
                'lote_numero'             => 30,
                'catmat'                  => '226698',
                'descricao_principal'     => 'Detergente Neutro',
                'descricao_detalhada'     => 'Aplicação: Remoção de gorduras de louças e utensílios. Aroma: Neutro. Componente Ativo: Linear Alquilbenzeno Sulfonato de Sódio Biodegradável. Frasco 500ml. Exclusivo ME/EPP.',
                'quantidade'              => 28725,
                'unidade_medida'          => 'Frasco 500ml',
                'valor_unitario_estimado' => 2.32,
                'valor_total_estimado'    => 66642.00,
                'cota_tipo'               => 'exclusiva_me_epp',
                'correspondente_sugerido' => 'Detergente Neutro Líquido Concentrado 500ml',
                'divergencia'             => 'Convergência total de volume (500ml), tensoativo neutro biodegradável e finalidade.',
            ],
            [
                'item_numero'             => 46,
                'lote_numero'             => 46,
                'catmat'                  => '479016',
                'descricao_principal'     => 'Limpador Limpeza Pesada / Desengordurante',
                'descricao_detalhada'     => 'Solução Limpeza Pesada. Composição Básica: Ácidos Graxos e Desengordurante. Frasco 500ml. Cota Principal 75%.',
                'quantidade'              => 27985,
                'unidade_medida'          => 'Frasco 500ml',
                'valor_unitario_estimado' => 5.39,
                'valor_total_estimado'    => 150839.15,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Desengordurante e Desengraxante Multiuso 5L',
                'divergencia'             => 'Edital cota frasco de 500ml; Ecoclean tem bombona de 5L. Mesma base desengordurante.',
            ],
            [
                'item_numero'             => 51,
                'lote_numero'             => 51,
                'catmat'                  => '372469',
                'descricao_principal'     => 'Limpa-Vidro',
                'descricao_detalhada'     => 'Aspecto Físico: Líquido. Tensoativo Catiônico/Fragrância, Ação Anti Estática. Frasco 500ml. Exclusivo ME/EPP.',
                'quantidade'              => 13200,
                'unidade_medida'          => 'Frasco 500ml',
                'valor_unitario_estimado' => 4.16,
                'valor_total_estimado'    => 54912.00,
                'cota_tipo'               => 'exclusiva_me_epp',
                'correspondente_sugerido' => 'Limpa-Vidros com Ação Anti-Estática 500ml',
                'divergencia'             => 'Convergência total de volume (500ml), ação antiestática e solventes glicólicos.',
            ],
            [
                'item_numero'             => 60,
                'lote_numero'             => 60,
                'catmat'                  => '366699',
                'descricao_principal'     => 'Luva de Borracha Látex Tamanho M',
                'descricao_detalhada'     => 'Material: Látex. Tamanho: Média. Características Adicionais: Anatômica, Antiderrapante, Cano Longo. Par. Cota Principal 75%.',
                'quantidade'              => 20481,
                'unidade_medida'          => 'Par',
                'valor_unitario_estimado' => 4.49,
                'valor_total_estimado'    => 91959.69,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Luva de Borracha Látex Amarela Cano Longo (Par - Tam M)',
                'divergencia'             => 'Convergência total de material (látex), tamanho M, formato anatômico e cano longo.',
            ],
            [
                'item_numero'             => 73,
                'lote_numero'             => 73,
                'catmat'                  => '238338',
                'descricao_principal'     => 'Papel Higiênico Folha Dupla 30M',
                'descricao_detalhada'     => 'Material: Celulose Virgem. Características Adicionais: Picotado e Dupla. Comprimento: 30 M. Largura: 10 CM. Cor: Branca. Pacote 4 unidades. Cota Principal 75%.',
                'quantidade'              => 344462,
                'unidade_medida'          => 'Pacote 4 unidades',
                'valor_unitario_estimado' => 5.43,
                'valor_total_estimado'    => 1870428.66,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Papel Higiênico Folha Dupla 30m (Pacote com 4 rolos)',
                'divergencia'             => 'Convergência total de metragem (30m), folha dupla picotada, 100% celulose e pacote com 4.',
            ],
            [
                'item_numero'             => 75,
                'lote_numero'             => 75,
                'catmat'                  => '296845',
                'descricao_principal'     => 'Papel Higiênico Rolão 300M',
                'descricao_detalhada'     => 'Material: Celulose Vegetal. Características Adicionais: Picotado e Simples. Comprimento: 300 M. Largura: 8 a 10 CM. Pacote 8 unidades. Cota Principal 75%.',
                'quantidade'              => 48165,
                'unidade_medida'          => 'Pacote 8 unidades',
                'valor_unitario_estimado' => 42.18,
                'valor_total_estimado'    => 2031599.70,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Papel Higiênico Rolão Institucional 300m (Fardo com 8 rolos)',
                'divergencia'             => 'Convergência total de especificação (rolos de 300 metros, fardo com 8 rolos).',
            ],
            [
                'item_numero'             => 79,
                'lote_numero'             => 79,
                'catmat'                  => '436328',
                'descricao_principal'     => 'Papel Toalha Interfolhado 2 Dobras',
                'descricao_detalhada'     => 'Material: 100% Celulose Virgem. Comprimento: 20 CM. Largura: 21 CM. Características Adicionais: Interfolhada. Tipo Folha: 2 Dobras. Pacote 1000 folhas. Cota Principal 75%.',
                'quantidade'              => 612647,
                'unidade_medida'          => 'Pacote 1000 folhas',
                'valor_unitario_estimado' => 11.67,
                'valor_total_estimado'    => 7149590.49,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Papel Toalha Interfolhado 2 Dobras 100% Celulose (1000 folhas)',
                'divergencia'             => 'Convergência total: 100% celulose virgem, interfolhado 2 dobras, pacote com 1000 folhas.',
            ],
            [
                'item_numero'             => 82,
                'lote_numero'             => 82,
                'catmat'                  => '601243',
                'descricao_principal'     => 'Rodo de Madeira 60cm',
                'descricao_detalhada'     => 'Material Suporte: Madeira. Comprimento Suporte: 60 CM. Quantidade Borrachas: 2 UN. Cabo aproximadamente 1,20 M com rosca. Exclusivo ME/EPP.',
                'quantidade'              => 6115,
                'unidade_medida'          => 'Unidade',
                'valor_unitario_estimado' => 7.22,
                'valor_total_estimado'    => 66653.50,
                'cota_tipo'               => 'exclusiva_me_epp',
                'correspondente_sugerido' => 'Rodo de Madeira com Borracha Dupla 60cm e Cabo',
                'divergencia'             => 'Convergência total: suporte de madeira 60cm, borracha dupla e cabo com rosca 1,20m.',
            ],
            [
                'item_numero'             => 89,
                'lote_numero'             => 89,
                'catmat'                  => '417309',
                'descricao_principal'     => 'Sabonete Líquido Galão 5 Litros',
                'descricao_detalhada'     => 'Aspecto Físico Líquido Perfumado. Acidez: Neutro. Galão 5 litros. Cota Principal 75%.',
                'quantidade'              => 19726,
                'unidade_medida'          => 'Galão 5 litros',
                'valor_unitario_estimado' => 18.02,
                'valor_total_estimado'    => 355462.52,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Sabonete Líquido Erva Doce Galão 5L',
                'divergencia'             => 'Convergência total: galão de 5 Litros, sabonete líquido neutro perfumado.',
            ],
            [
                'item_numero'             => 93,
                'lote_numero'             => 93,
                'catmat'                  => '615864',
                'descricao_principal'     => 'Saco para Lixo 30 Litros',
                'descricao_detalhada'     => 'Material: Resina Termoplástica Reciclada. Capacidade: 30 L. Espessura: 6 micras. Cor: Preta. Com solda contínua. Classe I Tipo B. Pacote 100 unidades. Cota Principal 75%.',
                'quantidade'              => 127906,
                'unidade_medida'          => 'Pacote 100 unidade',
                'valor_unitario_estimado' => 12.99,
                'valor_total_estimado'    => 1661498.94,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Saco para Lixo 30 Litros Preto Reforçado (Pacote com 100)',
                'divergencia'             => 'Convergência total de capacidade (30L), espessura (6 micras), NBR 9191 e embalagem com 100.',
            ],
            [
                'item_numero'             => 95,
                'lote_numero'             => 95,
                'catmat'                  => '398561',
                'descricao_principal'     => 'Saco para Lixo 50 Litros',
                'descricao_detalhada'     => 'Material: Polietileno Alta Densidade. Capacidade: 50 L. Espessura: 6 micras. Cor: Preta. Pacote 100 unidades. Cota Principal 75%.',
                'quantidade'              => 129506,
                'unidade_medida'          => 'Pacote 100 unidade',
                'valor_unitario_estimado' => 19.20,
                'valor_total_estimado'    => 2486515.20,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Saco para Lixo 50 Litros Preto Reforçado (Pacote com 100)',
                'divergencia'             => 'Convergência total de capacidade (50L), espessura (6 micras) e pacote com 100.',
            ],
            [
                'item_numero'             => 97,
                'lote_numero'             => 97,
                'catmat'                  => '345596',
                'descricao_principal'     => 'Saco para Lixo 100 Litros',
                'descricao_detalhada'     => 'Capacidade: 100 L. Espessura: 8 micras. Cor: Preta. Solda Contínua. Coleta de Lixo. Pacote 100 unidades. Cota Principal 75%.',
                'quantidade'              => 197678,
                'unidade_medida'          => 'Pacote 100 unidade',
                'valor_unitario_estimado' => 47.30,
                'valor_total_estimado'    => 9350169.40,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Saco para Lixo 100 Litros Preto Reforçado (Pacote com 100)',
                'divergencia'             => 'Convergência total de capacidade (100L), solda reforçada e pacote com 100.',
            ],
            [
                'item_numero'             => 100,
                'lote_numero'             => 100,
                'catmat'                  => '345589',
                'descricao_principal'     => 'Saco de Lixo Branco Leitoso Hospitalar 100L',
                'descricao_detalhada'     => 'Capacidade: 100 L. Dimensões: 75x105 cm. Espessura: 10 Micra. Cor: Branco Leitoso. Inscrição e Solda Contínua. Coleta Resíduos Serviços de Saúde. Pacote 100 unidades. Cota Principal 75%.',
                'quantidade'              => 183660,
                'unidade_medida'          => 'Pacote 100 Unidades',
                'valor_unitario_estimado' => 42.55,
                'valor_total_estimado'    => 7814733.00,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Saco para Resíduos de Serviços de Saúde 100L Branco Leitoso (Pacote com 100)',
                'divergencia'             => 'Convergência total de capacidade (100L), espessura (10 micras), cor branca leitosa e uso em resíduos de saúde.',
            ],
            [
                'item_numero'             => 110,
                'lote_numero'             => 110,
                'catmat'                  => '406214',
                'descricao_principal'     => 'Vassoura Cerdas de Náilon',
                'descricao_detalhada'     => 'Material Cerdas: Náilon. Material Cepa: Polipropileno 25cm. Cabo rosqueado 1,20m com ponteira alça. Cota Principal 80%.',
                'quantidade'              => 8588,
                'unidade_medida'          => 'Unidade',
                'valor_unitario_estimado' => 9.19,
                'valor_total_estimado'    => 80008.14,
                'cota_tipo'               => 'ampla',
                'correspondente_sugerido' => 'Vassoura Cerdas de Náilon com Cabo 1,20m',
                'divergencia'             => 'Convergência total de material de cerda (náilon), cepa plástica 25cm e cabo 1,20m.',
            ],
        ];

        $numeroControleCanoas = '88577416000118-1-000066/2026';
        $linkFonteBanrisul = 'https://pregaobanrisul.com.br/editais/0103_2026/349814';

        foreach ($itensEdital as $item) {
            $editalItem = EditalItem::updateOrCreate(
                [
                    'numero_controle_pncp' => $numeroControleCanoas,
                    'item_numero'          => $item['item_numero'],
                ],
                [
                    'numero_edital'           => '0103/2026',
                    'orgao_razao_social'      => 'MUNICIPIO DE CANOAS',
                    'lote_numero'             => $item['lote_numero'],
                    'catmat'                  => $item['catmat'],
                    'descricao_principal'     => $item['descricao_principal'],
                    'descricao_detalhada'     => $item['descricao_detalhada'],
                    'quantidade'              => $item['quantidade'],
                    'unidade_medida'          => $item['unidade_medida'],
                    'valor_unitario_estimado' => $item['valor_unitario_estimado'],
                    'valor_total_estimado'    => $item['valor_total_estimado'],
                    'cota_tipo'               => $item['cota_tipo'],
                    'link_fonte'              => $linkFonteBanrisul,
                    'data_coleta'             => $hoje,
                ]
            );

            // Buscar produto correspondente no catálogo da Ecoclean
            $produto = ProdutoCatalogo::where('empresa_id', $empresa->id)
                ->where('nome', $item['correspondente_sugerido'])
                ->first();

            if ($produto) {
                ItemComparacao::updateOrCreate(
                    [
                        'empresa_id'          => $empresa->id,
                        'edital_item_id'      => $editalItem->id,
                        'produto_catalogo_id' => $produto->id,
                    ],
                    [
                        'classificacao'             => 'confirmada',
                        'motivo_correspondencia'    => "Correspondência identificada com o item do catálogo '{$produto->nome}' por afinidade de descrição técnica e categoria.",
                        'divergencias_especificacao' => $item['divergencia'],
                        'feedback_usuario'          => 'tenho_interesse',
                        'revisado_por_id'           => $usuario->id,
                        'revisado_em'               => $hoje,
                    ]
                );
            }
        }

        // 7. Gerar Prévia do Alerta Institucional no Histórico (Modo Demonstração)
        $itensAderentesValor = 32863953.53; // Soma dos itens compatíveis no Edital 0103/2026
        $corpoHtml = <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; color: #1f2937; line-height: 1.5;">
    <div style="background: #065f46; color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0; font-size: 1.3rem;">Radar de Compras Públicas — Alerta de Oportunidade</h2>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; opacity: 0.9;">Prévia de Alerta Comercial • Perfil: Fornecimento de Materiais de Limpeza e Higiene</p>
    </div>
    <div style="border: 1px solid #e5e7eb; border-top: none; padding: 1.5rem; border-radius: 0 0 8px 8px; background: #ffffff;">
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #92400e;">
            <strong>Modo Demonstração:</strong> Esta é uma simulação interna do alerta gerado pelo sistema. Nenhum e-mail foi disparado para destinatários externos.
        </div>
        <p style="font-size: 0.95rem;">Prezado(a) <strong>Ecoclean</strong>,</p>
        <p style="font-size: 0.9rem; color: #4b5563;">
            Identificamos uma publicação no histórico recente de compras públicas com elevada compatibilidade com o catálogo da sua empresa:
        </p>
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin: 1.25rem 0;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #065f46; text-transform: uppercase;">MUNICIPIO DE CANOAS / RS</div>
            <div style="font-size: 1rem; font-weight: 700; color: #111827; margin: 0.25rem 0;">Pregão Eletrônico SRP — Edital 0103/2026</div>
            <div style="font-size: 0.82rem; color: #4b5563; margin-bottom: 0.75rem;">
                <strong>Objeto:</strong> Aquisição de materiais de higiene e limpeza, para atender as demandas das Unidades Administrativas do Município de Canoas.
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-top: 0.5rem;">
                <tr>
                    <td style="padding: 0.35rem 0; color: #6b7280;">Valor Global da Licitação:</td>
                    <td style="padding: 0.35rem 0; font-weight: 700; text-align: right; color: #111827;">R$ 61.203.954,72</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #6b7280;">Valor dos Itens Compatíveis com Ecoclean:</td>
                    <td style="padding: 0.35rem 0; font-weight: 700; text-align: right; color: #059669;">R$ 32.863.953,53</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #6b7280;">Total de Itens com Convergência:</td>
                    <td style="padding: 0.35rem 0; font-weight: 700; text-align: right; color: #111827;">21 itens analisados</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0; color: #6b7280;">Local de Entrega Informado:</td>
                    <td style="padding: 0.35rem 0; font-weight: 700; text-align: right; color: #111827;">Almoxarifados de Canoas/RS</td>
                </tr>
            </table>
        </div>
        <p style="font-size: 0.85rem; color: #4b5563;">
            Para revisar a correspondência item a item e conferir divergências de embalagem (ex.: galão de 5L vs. 2L), acesse a central no Portal do Fornecedor.
        </p>
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="http://localhost:8080/portal/comparacao-itens" style="background: #059669; color: #ffffff; padding: 0.65rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: inline-block;">
                Acessar Comparativo de Itens no Portal
            </a>
        </div>
    </div>
</div>
HTML;

        AlertaHistorico::updateOrCreate(
            [
                'empresa_id'           => $empresa->id,
                'numero_controle_pncp' => $numeroControleCanoas,
                'tipo_alerta'          => 'oportunidade_perfil',
            ],
            [
                'status'                => 'previa_gerada',
                'destinatario_simulado' => 'contato@produtosecoclean.com.br (Simulado)',
                'assunto'               => '[PRÉVIA] Oportunidade Identificada: Edital 0103/2026 — Prefeitura de Canoas',
                'conteudo_html'         => $corpoHtml,
                'feedback_alerta'       => 'tenho_interesse',
                'observacoes_feedback'  => 'Excelente alinhamento de itens com sacos de lixo, sabonetes e saneantes.',
            ]
        );
    }
}
