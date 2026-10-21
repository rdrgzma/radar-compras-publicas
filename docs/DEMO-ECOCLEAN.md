# Roteiro de Demonstração Personalizada — Ecoclean

> **Status do Caso Demonstrativo:** Homologado e Validado  
> **Identificação Obrigatória:** *Ecoclean — demonstração não vinculada à empresa*  
> **Fuso de Apresentação:** `America/Sao_Paulo` (Horário de Brasília)  
> **Ambiente Local:** Painel Laravel 13 / FilamentPHP 5 em `http://localhost:8080` | API Go em `http://localhost:8000`

---

## 1. Visão Geral e Objetivos da Demonstração

Esta demonstração apresenta o valor do **Radar de Compras Públicas** para a empresa gaúcha **Ecoclean**, especializada em produtos de limpeza, higiene e descartáveis. 

O fluxo guiado demonstra como o sistema resolve os 3 maiores gargalos comerciais de um fornecedor:
1. **Ruído em licitações mistas:** separar contratações com serviços terceirizados (ex.: mão de obra de limpeza) de editais que contenham fornecimento real de mercadorias.
2. **Comparação técnica item a item:** comparar automaticamente as exigências do edital com a linha padronizada da empresa, apontando divergências de embalagem (ex.: 2L vs. 5L) ou especificação (ex.: micras de sacos de lixo).
3. **Visibilidade do volume financeiro aderente:** distinguir o valor total do edital (que pode passar de dezenas de milhões de reais) do valor específico dos itens que a Ecoclean pode fornecer.

---

## 2. Acessos do Sistema

| Usuário / Perfil | URL de Acesso | E-mail de Login | Senha |
| :--- | :--- | :--- | :--- |
| **Fornecedor Ecoclean (Demo)** | `http://localhost:8080/portal` | `fornecedor@ecoclean.com.br` | `fornecedor123` |
| **Fornecedor Alpha EPI (Isolamento)** | `http://localhost:8080/portal` | `fornecedor@alphaepi.com.br` | `fornecedor123` |
| **Administrador Geral** | `http://localhost:8080/admin` | `admin@radar.com.br` | `admin123` |
| **API Go Healthcheck** | `http://localhost:8000/saude` | *Endpoint público* | — |

---

## 3. Roteiro de Demonstração (5 a 7 Minutos)

### Passo 1: Acesso ao Portal do Fornecedor
1. Acesse `http://localhost:8080/portal` e autentique-se com `fornecedor@ecoclean.com.br` / `fornecedor123`.
2. Observe a identificação da empresa no menu e o painel inicial com navegação em português brasileiro.

### Passo 2: Catálogo de Produtos da Empresa
1. Acesse o menu lateral: **Catálogo da Empresa** (`/portal/produto-catalogos`).
2. Demonstre os produtos cadastrados, organizados nas **7 famílias oficiais** extraídas do catálogo público:
   - *Água sanitária, detergentes e desinfetantes* (Água Sanitária 5L, Cloro Concentrado, Detergentes, Desinfetante Pinho, Álcool 70% Líquido e Gel);
   - *Desengordurantes e limpa-vidros* (Desengordurante 5L, Limpa-Vidros com ação antiestática 500ml, Ajax, Cera Autobrilho);
   - *Sabonetes líquidos* (Galão 5L Erva Doce e Floral, Sabonetes em barra glicerinados 90g);
   - *Papel higiênico e papel-toalha* (Folha dupla 30m pacote com 4, Rolão institucional 300m fardo com 8, Papel toalha interfolhado 1000 folhas, Guardanapos 24x24cm);
   - *Sacos para lixo* (30L, 50L, 100L e 150L reforçados com solda contínua NBR 9191, e saco branco leitoso hospitalar para saúde);
   - *Rodos, baldes, esponjas, panos e escovas* (Balde 20L alça reforçada, balde espremedor 14L, rodo madeira 60cm, mops, esponjas dupla face, panos alvejados com bainha, vassouras de náilon);
   - *Copos descartáveis e luvas* (Copos 50ml e 200ml ABNT NBR 14865, luvas de borracha P/M/G cano longo, luvas de procedimento com pó e sem pó).
3. Demonstre a capacidade de **Editar** um produto (alterando observações técnicas ou embalagem) e utilize o botão de **Reimportar Catálogo Demonstrativo** (operação idempotente que atualiza os dados sem duplicar registros).

### Passo 3: Perfil de Monitoramento Estratégico
1. Acesse o menu: **Perfil de Fornecimento** (`/portal/perfil-fornecedors`).
2. Mostre as palavras-chave obrigatórias configuradas (`agua sanitaria`, `detergente`, `saco de lixo`, `papel toalha`, etc.).
3. Mostre as **exclusões ativas** que eliminam o ruído de empresas de serviços:
   - Exclusões: `prestacao de servicos de limpeza`, `mão de obra`, `roçada`, `desassoreamento`, `coleta de lixo urbano`.
4. Destaque a abrangência geográfica configurada: `RS`. Explique que a informação pública do site indica atuação prioritária na Grande Porto Alegre e Vale dos Sinos (pendente de confirmação comercial), razão pela qual o sistema não descarta municípios vizinhos como Canoas.

### Passo 4: Oportunidades Identificadas no Radar
1. Acesse o menu: **Radar de Oportunidades** (`/portal/radar-oportunidades`).
2. Observe as publicações encontradas pelo conector, com pontuação de relevância e **motivo explícito da correspondência**.
3. Alterne o filtro *"Apenas Prazos Vigentes"* para inspecionar oportunidades com prazo aberto e o histórico recente.
4. Demonstre a ação de **Salvar Seleção** com notas de análise para acompanhamento interno.

### Passo 5: Comparativo Técnico de Itens do Edital
1. Acesse o menu: **Comparação de Itens** (`/portal/comparacao-itens`).
2. Mostre o caso do **Edital 0103/2026 da Prefeitura de Canoas** (Pregão Eletrônico SRP - Banrisul 349814).
3. Mostre a comparação lado a lado:
   - **Item 02 / 04 (Água Sanitária):** O edital pede garrafa de 2 Litros (Item 2) e garrafa de 5 Litros (Item 4). O sistema correlaciona com a *Água Sanitária Ecoclean 5L*, identificando convergência na formulação química (hipoclorito 2.0-2.5%) e apontando a divergência de fracionamento de embalagem.
   - **Item 10 (Álcool Gel):** Edital pede frasco de 500ml; Ecoclean padroniza galão de 5L para dispensers institucionais.
   - **Item 24 (Copos 200ml):** Convergência total de norma ABNT NBR 14865 e tiras de 100 unidades.
   - **Item 79 (Papel Toalha Interfolhado):** Convergência em 100% celulose virgem, 2 dobras e pacote com 1000 folhas (R$ 7,1 milhões neste lote).
   - **Item 93 / 97 / 100 (Sacos de Lixo):** Convergência nas espessuras (6 a 10 micras), solda contínua e padrão hospitalar branco leitoso.
4. Demonstre a alteração de status do item: `Confirmada`, `Possível` ou `Incompatível`.
5. Demonstre o registro do feedback do fornecedor: `Tenho Interesse`, `Relevante`, `Já Participo` ou `Irrelevante`.

### Passo 6: Central de Alertas em Modo Prévia
1. Acesse o menu: **Central de Alertas** (`/portal/central-alertas`).
2. Mostre o **banner de segurança:** o envio de e-mails para a Ecoclean está categoricamente bloqueado no modo demo.
3. Clique em **Prévia do E-mail**:
   - Exiba o template profissional com cabeçalho institucional, dados do órgão, valor global vs. valor dos itens da Ecoclean, e links diretos para análise.
4. Clique em **Testar Bloqueio de Envio**: o sistema dispara notificação de proteção impedindo transmissões externas não autorizadas.
5. Acesse a aba **Resumo Diário Simulado** para visualizar como o diretor comercial da Ecoclean receberia o resumo matinal de novas compras.
6. Acesse a aba **Histórico & Anti-Duplicação** comprovando que o mesmo edital não gera alertas repetidos indevidamente.

### Passo 7: Relatório Comercial e Exportação
1. Acesse o menu: **Relatório Comercial** (`/portal/relatorio-comercial`).
2. Mostre o dossiê executivo pronto para apresentação à diretoria da Ecoclean:
   - Diferenciação cristalina entre **R$ 61.203.954,72** (valor total do edital) e **R$ 32.863.953,53** (valor estimado apenas dos itens compatíveis com a Ecoclean).
   - Uso rigoroso da redação *"oportunidades identificadas"* (evitando afirmações sobre participação passada sem ata confirmada).
3. Clique em **Exportar Relatório CSV**: o arquivo estruturado é gerado instantaneamente com sanitização contra injeção de fórmulas e compatível com Excel.

---

## 4. Fontes Oficiais e Rastreabilidade das Licitações

| Contratação | Órgão | Link Oficial do Pregão | Número PNCP | Situação Verificada |
| :--- | :--- | :--- | :--- | :--- |
| **Edital 0103/2026** | Prefeitura de Canoas / RS | [Banrisul 349814](https://pregaobanrisul.com.br/editais/0103_2026/349814) | `88577416000118-1-000066/2026` | Homologado em 16/10/2026 |
| **Edital 0212/2026** | Prefeitura de Canoas / RS | [Banrisul 352670](https://pregaobanrisul.com.br/editais/0212_2026/352670) | `88577416000118-1-000112/2026` | Homologado em 16/07/2026 |
| **Edital 0002/2026** | FMSC (Saúde Canoas) | [Banrisul 347529](https://pregaobanrisul.com.br/editais/0002_2026/347529) | `14885499000176-1-000003/2026` | Homologado em 29/04/2026 |

*Origem dos dados do catálogo público:* `https://www.produtosecoclean.com.br/produtos/` (Coleta em 21/10/2026).

---

## 5. Limitações e Declarações Técnicas

1. **Atendimento Geográfico:** A informação pública do site institucional da Ecoclean cita atendimento na Grande Porto Alegre e Vale dos Sinos. Trata-se de dado preliminar pendente de validação comercial formal; o sistema não assume entrega em todo o território gaúcho.
2. **Preços do Catálogo:** Os valores constantes no catálogo público serviram unicamente como referência comparativa do porte dos produtos e não configuram proposta vinculante de fornecimento.
3. **Exemplos Históricos:** As contratações de Canoas constam como referências do histórico recente para demonstrar demanda reprimida e aderência técnica, não como editais abertos para lances atuais.
4. **Docker Compose:** A sintaxe dos arquivos de implantação foi validada e aprovada via comando `docker compose config`. Conforme diretriz do projeto, declaramos explicitamente: **“Docker Compose ainda não validado em execução”**, visto que o serviço daemon do Docker Desktop Linux Engine não estava inicializado no ambiente hospedeiro Windows. Nenhuma alteração invasiva no sistema operacional foi realizada.
