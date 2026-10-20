# 🛰️ Radar de Compras Públicas (PNCP)

> **Plataforma corporativa de inteligência, monitoramento e triagem de contratações públicas federais, estaduais e municipais do Portal Nacional de Contratações Públicas (PNCP - Lei 14.133/2021).**

---

## 🏗️ Stack Tecnológica

- **API & Motor de Coleta:** Go 1.24+ (Goroutines, roteamento de alta performance via `go-chi`, conector resiliente com retry exponencial e matching determinístico com motivo legível).
- **Painel & Interface:** PHP 8.4, Laravel 13 e FilamentPHP 5 (Multi-Panel com Administração em `/admin` e Portal do Fornecedor em `/portal`).
- **Banco de Dados:** PostgreSQL 16+ com isolamento estrito de schemas (`painel` e `api_go`).
- **Orquestração Local:** Docker Compose para Windows/Linux e script nativo `rodar-local.ps1`.
- **Fuso de Apresentação:** `America/Sao_Paulo`.
- **Idioma da Interface:** Português do Brasil (`pt-BR`).

---

## 🚀 Como Executar

### Opção 1: Via Docker Compose (Recomendado para Produção/Staging)

```bash
docker compose up -d --build
```

Em instantes, acesse:
- **Portal do Fornecedor:** [http://localhost:8080/portal](http://localhost:8080/portal)
- **Painel Administrativo:** [http://localhost:8080/admin](http://localhost:8080/admin)
- **Healthcheck & API Go:** [http://localhost:8000/saude](http://localhost:8000/saude)

---

### Opção 2: Local no Windows (Bare-Metal)

1. Execute o script de inicialização local:
```powershell
.\rodar-local.ps1
```

2. No terminal do Painel:
```bash
cd painel
composer install
php artisan migrate --seed
php artisan serve --port=8080
```

---

## 🔑 Credenciais Padrão de Acesso

| Painel | URL | Usuário / E-mail | Senha | Perfil |
| :--- | :--- | :--- | :--- | :--- |
| **Administração** | `/admin` | `admin@radar.com.br` | `admin123` | Administrador Global |
| **Portal do Fornecedor** | `/portal` | `fornecedor@alphaepi.com.br` | `fornecedor123` | Fornecedor Alpha (EPIs & Segurança) |
| **Portal do Fornecedor (Tenant 2)** | `/portal` | `fornecedor@betati.com.br` | `fornecedor123` | Fornecedor Beta (Tecnologia & Nuvem) |

---

## 🔄 Fluxo Completo Demonstrável

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

1. **Cadastrar Perfil:** O fornecedor define palavras-chave obrigatórias (`epi`, `segurança`, `uniformes`), palavras-chave proibidas (`asfalto`, `pavimentação`) e UFs de interesse.
2. **Sincronizar PNCP:** Execução assíncrona com controle de concorrência e retorno de `job_id`.
3. **Identificar Oportunidades:** Avaliação semântica das contratações com **motivo explícito da correspondência**.
4. **Revisar Detalhes:** Consulta de valores estimados, prazos de proposta (vigente vs. encerrado) e link direto ao edital oficial do PNCP.
5. **Salvar Seleção:** Triagem imediata com status `Em Análise`, `Selecionada` ou `Descartada` e anotações internas.
6. **Exportar CSV Seguro:** Download de planilha com **BOM UTF-8** e **sanitização de CSV Injection** (mitigando prefixos `=`, `+`, `-`, `@`, `\t`, `\r`).

---

## 🛡️ Segurança e Boas Práticas

- **Multi-Tenancy Estrito:** O fornecedor opera isolado no contexto de sua empresa autenticada na sessão do servidor.
- **Server-to-Server:** O Laravel comunica-se privadamente com Go via cabeçalho `X-Internal-Token`, sem expor credenciais ao navegador.
- **Imunidade a Injeção de Fórmulas:** Exportações CSV passam por sanitização rigorosa antes do download.
- **Resiliência do Conector:** Timeout de 15s, exponential backoff em 3 tentativas e tratamento de rate limit (HTTP 429) da API pública federal.

---

## 📚 Documentação Técnica

- [Plano do MVP](docs/PLANO-MVP.md)
- [Arquitetura Detalhada](docs/ARQUITETURA.md)
- [Especificação OpenAPI 3.1](docs/openapi.yaml)
- [Roteiro de Apresentação em 5 Minutos](docs/ROTEIRO-APRESENTACAO-5-MINUTOS.md)
