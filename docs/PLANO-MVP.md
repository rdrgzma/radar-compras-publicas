# Radar de Compras Públicas — Plano do MVP

> **Documento Diretor de Implementação e Validação do MVP**  
> Fuso horário de referência: `America/Sao_Paulo`  
> Idioma oficial da aplicação: `Português Brasileiro (pt-BR)`  
> Repositório: `radar-compras-publicas`

---

## 1. Visão Geral e Objetivos do Produto

O **Radar de Compras Públicas** é um sistema voltado para fornecedores e gestores monitorarem, filtrarem e selecionarem oportunidades de licitações públicas disponibilizadas no **Portal Nacional de Contratações Públicas (PNCP)** da Lei 14.133/2021.

### 1.1 Fluxo Essencial Demonstrável
```
┌─────────────────────────┐     ┌─────────────────────────┐     ┌─────────────────────────┐
│  1. Cadastrar Perfil de │ ──► │  2. Sincronizar PNCP   │ ──► │ 3. Identificar           │
│     Fornecedor (Filtros)│     │     (Assíncrono Go)     │     │    Oportunidades (Match)│
└─────────────────────────┘     └─────────────────────────┘     └─────────────────────────┘
                                                                             │
┌─────────────────────────┐     ┌─────────────────────────┐                  ▼
│  6. Exportar Seleção em │ ◄── │  5. Salvar Seleção      │ ◄── ┌─────────────────────────┐
│     CSV Seguro          │     │     (Status + Notas)    │     │ 4. Revisar Detalhes e   │
└─────────────────────────┘     └─────────────────────────┘     │    Link Oficial Fonte   │
                                                                └─────────────────────────┘
```

---

## 2. Stack Tecnológica Obrigatória

| Componente | Tecnologia | Responsabilidade |
| :--- | :--- | :--- |
| **API & Coleta** | **Go 1.24+** | Conector resiliente PNCP, normalização, motor de matching, armazenamento bruto/normalizado, API interna REST autenticada. |
| **Painel & Interface** | **PHP 8.4 + Laravel 13 + FilamentPHP 5** | Multi-Panel (`/admin` e `/portal`), autenticação, empresas/tenants, perfis de fornecedor, seleções, auditoria, exportação CSV segura, scheduler. |
| **Banco de Dados** | **PostgreSQL 16+** | Schemas separados (`painel` e `api_go`), com permissões e migrações isoladas por aplicação. |
| **Ambiente Local** | **Docker Compose** | Orquestração contêineres (`radar-db`, `radar-api-go`, `radar-painel`), healthchecks integrados e redes isoladas. |

---

## 3. Escopo Funcional da Primeira Versão

### 3.1 Painel do Fornecedor (`/portal`)
1. **Autenticação e Multi-Tenancy Estrito:**
   - O fornecedor acessa apenas os dados da sua empresa vinculada.
   - Qualquer operação de busca, seleção ou edição valida o `empresa_id` autenticado na sessão do servidor.
2. **Perfil de Fornecedor:**
   - Cadastro de palavras-chave obrigatórias/incluídas (ex: `"serviços de ti"`, `"segurança"`).
   - Cadastro de palavras-chave proibidas/excluídas (ex: `"obras civis"`, `"asfalto"`).
   - Filtro por Unidades Federativas (UFs) de interesse (ou abrangência nacional).
3. **Mural de Oportunidades & Motivo Explícito:**
   - Listagem com paginação e ordenação por data de publicação / encerramento.
   - Apresentação do **motivo explícito da correspondência** (ex.: *«Identificado termo 'vigilância' no objeto; UF 'SP' compatível com perfil; nenhuma palavra excluída encontrada»*).
   - Exibição de órgão licitante, CNPJ, modalidade, valor estimado (ou "não informado"), prazo de envio de propostas e link oficial no PNCP.
   - Indicador visual claro do status da licitação: Vigente (aberto a propostas), Prazo Encerrado, etc.
4. **Seleção e Triagem:**
   - Marcar oportunidade como: `Em Análise`, `Selecionada` ou `Descartada`.
   - Adicionar observações operacionais e táticas por oportunidade.
5. **Exportação CSV Protegida:**
   - Exportar oportunidades e seleção em formato CSV.
   - **Sanitização de CSV Injection:** prefixação de aspas simples (`'`) caso os valores iniciem com caracteres executáveis em planilhas (`=`, `+`, `-`, `@`, `\t`, `\r`).
6. **Disparo Manual de Sincronização:**
   - Botão para solicitar sincronização imediata do PNCP à API Go, com feedback de status assíncrono via polling visual.

### 3.2 Painel de Administração (`/admin`)
1. **Gestão de Empresas & Usuários:**
   - Cadastro e ativação de empresas contratantes e operadores do sistema.
2. **Monitor de Execuções e Coletas:**
   - Histórico das sincronizações disparadas (janela de datas, total de registros, novas oportunidades, falhas, duração).
   - Visibilidade de erros e avisos de indisponibilidade da fonte.
3. **Modo Demonstrativo (Fixtures):**
   - Disponibilização de botão/seeder para carregar cenários simulados de licitações reais com rótulo indelével `[DEMONSTRAÇÃO]`, permitindo testes sem depender da conectividade externa do PNCP.

### 3.3 Motor em Go (`api-go`)
1. **Conector PNCP Oficial:**
   - Endpoints `/api/consulta/v1/contratacoes/publicacao`.
   - Paginação com limite mínimo de 10 registros por página exigido pela API.
   - Filtro local em memória/SQL para termos e UF, sem assumir que a API externa ofereça busca textual avançada.
   - Resiliência: HTTP client com timeout (15s), até 3 retries com exponential backoff (`2^n * 500ms`).
   - Lock atômico de sincronização em andamento para impedir execuções simultâneas duplicadas.
   - Idempotência total através da chave única oficial `numeroControlePNCP`.
2. **Motor de Match:**
   - Algoritmo determinístico de pontuação e justificativa de aderência das oportunidades frente aos critérios do perfil.
3. **Armazenamento de Histórico:**
   - Preservação das oportunidades prévias válidas quando a fonte falha.
   - Registro detalhado de logs operacionais.

---

## 4. Matriz de Entregáveis e Fases

| Etapa | Foco | Entregáveis |
| :--- | :--- | :--- |
| **Etapa 1** | Especificação & Contrato | `docs/PLANO-MVP.md`, `docs/ARQUITETURA.md`, `docs/openapi.yaml`. |
| **Etapa 2** | Infraestrutura & Baseline | `docker-compose.yml`, Dockerfile Go, Dockerfile PHP/Laravel, migrations do PostgreSQL, healthchecks. |
| **Etapa 3** | Fluxo Demonstrativo | Seeders demonstrativos, telas Filament `/admin` e `/portal`, exportação CSV com proteção de injeção. |
| **Etapa 4** | Conector PNCP Real | Implementação Go com backoff, retry, paginação real e proteção de concorrência. |
| **Etapa 5** | Integração Completa | Disparo assíncrono pelo Laravel Scheduler e manual, polling de status, enriquecimento do motivo de match. |
| **Etapa 6** | Documentação & Roteiro | Manual de execução local, verificação ponta a ponta e roteiro de apresentação de 5 minutos. |

---

## 5. Critérios de Aceite e Validação Rigorosa
- [ ] Isolamento total entre empresas (tenants).
- [ ] Sincronizações repetidas na mesma janela não geram duplicidades de licitação.
- [ ] Recuperação graciosa e preservação dos dados vigentes em caso de queda do PNCP ou da API Go.
- [ ] Tratamento estrito de valores nulos (exibição padrão "não informado").
- [ ] Nenhuma credencial interna exposta ao frontend (comunicação server-to-server).
- [ ] CSV exportado imune a execução de fórmulas no Excel/Calc.
