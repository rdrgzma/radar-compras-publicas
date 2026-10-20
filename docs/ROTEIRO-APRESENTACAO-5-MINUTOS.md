# Roteiro de Apresentação de 5 Minutos — Radar de Compras Públicas

> **Guia Executivo e Prático para Demonstração de Impacto**  
> Fuso horário: `America/Sao_Paulo` | Idioma: `Português Brasileiro (pt-BR)`  
> Duração estimada: **5 minutos**

---

## ⏱️ Linha do Tempo da Apresentação

```
┌──────────────┬────────────────────────────────────────────────────────┐
│ Minuto       │ Ação e Destaque Visual                                  │
├──────────────┼────────────────────────────────────────────────────────┤
│ 00:00 - 01:00│ Abertura, Arquitetura Dual e Monitor de Coleta         │
│ 01:00 - 02:00│ Perfil de Fornecimento & Regras de Filtro             │
│ 02:00 - 03:00│ Sincronização Assíncrona e Matching com Motivo Explícito│
│ 03:00 - 04:00│ Revisão de Detalhes, Link Oficial e Triagem da Seleção │
│ 04:00 - 05:00│ Exportação CSV Segura (Anti-Injeção) & Multi-Tenancy  │
└──────────────┴────────────────────────────────────────────────────────┘
```

---

## 1. Minuto 1: Contexto e Monitoramento da Infraestrutura

1. **Acesso:**
   - Acesse o Painel de Administração em [http://localhost:8080/admin](http://localhost:8080/admin).
   - Efetue login com as credenciais:
     - **E-mail:** `admin@radar.com.br`
     - **Senha:** `admin123`
2. **O que mostrar:**
   - Vá ao menu **Operações & Coleta → Monitor de Sincronizações**.
   - Mostre os cards de saúde:
     - Status da API Go: `Operacional`
     - Conector PNCP: `Disponível`
     - Fuso Horário de Apresentação: `America/Sao_Paulo`
   - Destaque a separação de schemas no PostgreSQL: `painel` (gerenciado por Laravel) e `api_go` (gerenciado por Go).
   - Explique que o agendador periódido roda via **Laravel Scheduler** chamando assincronamente a API Go via token interno seguro (`X-Internal-Token`).

---

## 2. Minuto 2: O Portal do Fornecedor e Configuração do Perfil

1. **Acesso:**
   - Abra uma aba anônima (ou faça logout) e acesse [http://localhost:8080/portal](http://localhost:8080/portal).
   - Efetue login com a empresa fornecedora de EPIs:
     - **E-mail:** `fornecedor@alphaepi.com.br`
     - **Senha:** `fornecedor123`
2. **O que mostrar:**
   - Acesse **Perfil de Fornecimento**.
   - Demonstre a configuração:
     - **Termos Incluídos:** `equipamentos de protecao`, `epi`, `seguranca`, `uniforme`, `botas`
     - **Termos Excluídos:** `asfalto`, `pavimentacao`, `obras civis`
     - **UFs de Interesse:** `SP`, `RS`, `RJ`, `MG`, `PR`, `DF`
   - Explique que o fornecedor não precisa ver termos técnicos ou consultar manualmente milhares de páginas no site federal: o radar faz o trabalho pesado.

---

## 3. Minuto 3: Radar de Oportunidades & Motivo Explícito da Correspondência

1. **Acesso:**
   - Clique em **Radar de Oportunidades**.
2. **O que mostrar:**
   - Destaque a badge roxa `[DEMONSTRAÇÃO]` e as licitações reais coletadas da fonte oficial.
   - Mostre a caixa em destaque com o **Motivo Explícito da Correspondência**:
     > *"Termo(s) correspondente(s) identificado(s): 'epi', 'seguranca'; UF 'SP' compatível com a abrangência configurada; Nenhum termo de exclusão detectado."*
   - Explique aos avaliadores que o fornecedor entende exatamente a razão pela qual cada oportunidade foi trazida à sua tela, eliminando a "caixa preta".
   - Demonstre a distinção de prazos: **Propostas Abertas** (verde) vs. **Prazo Encerrado** (cinza/vermelho).
   - Demonstre o tratamento de valores nulos (exibição padrão "não informado" em vez de quebrar ou exibir zero).

---

## 4. Minuto 4: Revisão de Detalhes, Link Oficial e Triagem

1. **O que mostrar:**
   - Clique no botão **"Ver no PNCP"**.
   - O navegador abre diretamente a página oficial do edital no portal federal (`https://pncp.gov.br/app/editais/...`), comprovando a autenticidade e proveniência dos dados.
2. **Ação de Triagem:**
   - No card da oportunidade desejada, clique em **Selecionar**.
   - O sistema atualiza o status para `Selecionada` e registra as observações operacionais.
   - Mostre como outra oportunidade pode ser marcada como **Analisar** ou **Descartar**.

---

## 5. Minuto 5: Minhas Seleções e Exportação CSV Segura (Anti-Injeção)

1. **Acesso:**
   - Vá ao menu **Minhas Seleções**.
   - Veja a lista das contratações salvas pela empresa Alpha EPI.
2. **Ação:**
   - Clique no botão **"Exportar Seleção em CSV"**.
   - O arquivo `radar-compras-selecao-YYYY-MM-DD.csv` é baixado instantaneamente.
3. **O grande diferencial de segurança:**
   - Abra o arquivo ou mostre a implementação de `CsvExportService`:
     - O arquivo possui **BOM UTF-8** para abrir perfeitamente no Excel sem distorcer caracteres (`ç`, `ã`).
     - **Mitigação contra CSV Injection (Formula Injection):** qualquer valor malicioso que inicie com `=`, `+`, `-`, `@`, `\t` ou `\r` é neutralizado com um apóstrofo `'`, impedindo execução remota de código no Excel/Calc.
4. **Isolamento de Tenants (Multi-Tenancy):**
   - Acesse com a segunda empresa (`fornecedor@betati.com.br` / `fornecedor123`).
   - Mostre que a empresa Beta vê apenas oportunidades de TI e suas próprias seleções, com isolamento estrito de dados validado no servidor.

---

## 6. Conclusão da Apresentação
> *"O Radar de Compras Públicas une a velocidade e resiliência do Go com a produtividade e elegância do Laravel Filament, garantindo alta conformidade com o PNCP, dados confiáveis e segurança de nível corporativo para fornecedores públicos."*
