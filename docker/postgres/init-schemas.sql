-- ==============================================================================
-- Inicialização do Banco de Dados - Radar de Compras Públicas
-- Criação de Schemas Lógicos Separados: painel e api_go
-- ==============================================================================

CREATE SCHEMA IF NOT EXISTS painel;
CREATE SCHEMA IF NOT EXISTS api_go;

-- Configuração de fuso horário padrão da sessão
SET timezone = 'America/Sao_Paulo';

-- Comentários informativos
COMMENT ON SCHEMA painel IS 'Tabelas do Painel Web Laravel / Filament (usuários, empresas, perfis, seleções)';
COMMENT ON SCHEMA api_go IS 'Tabelas da API Go (oportunidades coletadas do PNCP, execuções de sincronização, logs)';
