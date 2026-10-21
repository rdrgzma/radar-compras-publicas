<x-filament-panels::page>
    <style>
        .rel-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .rel-section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #065f46;
            margin: 0 0 0.85rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .rel-metric-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        .rel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        .rel-table th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.65rem 0.85rem;
            text-align: left;
            font-weight: 700;
            color: #475569;
        }
        .rel-table td {
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .rel-badge {
            display: inline-flex;
            padding: 0.15rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- Cabeçalho do Dossiê Comercial -->
        <div style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); color: #ffffff; padding: 2rem; border-radius: 0.75rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1.5rem;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(255,255,255,0.15); padding: 0.25rem 0.65rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                    <span>Dossiê Comercial Estratégico</span>
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0.5rem 0 0.3rem 0;">
                    Oportunidades Identificadas no Mercado Público
                </h1>
                <p style="font-size: 0.88rem; opacity: 0.95; margin: 0; max-width: 650px;">
                    Demonstração personalizada baseada no catálogo público de produtos e histórico recente de compras públicas da Grande Porto Alegre / Canoas.
                </p>
                <div style="margin-top: 0.75rem; font-size: 0.78rem; opacity: 0.85;">
                    Empresa Alvo: <strong>Ecoclean — demonstração não vinculada à empresa</strong> • Data de Emissão: {{ now('America/Sao_Paulo')->format('d/m/Y') }}
                </div>
            </div>

            <div>
                <button type="button" wire:click="exportarCsv" style="background: #ffffff; color: #065f46; border: none; padding: 0.75rem 1.25rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <x-heroicon-m-arrow-down-tray style="width: 1.1rem; height: 1.1rem;" />
                    <span>Exportar Relatório CSV</span>
                </button>
            </div>
        </div>

        <!-- Métricas Executivas -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div class="rel-metric-box">
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Licitações Analisadas</span>
                <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0;">3</div>
                <span style="font-size: 0.72rem; color: #64748b;">Canoas e FMSC (Exercício 2026)</span>
            </div>

            <div class="rel-metric-box">
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Valor Global das Contratações</span>
                <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0;">R$ 61.267.486,09</div>
                <span style="font-size: 0.72rem; color: #64748b;">Total orçado pelos órgãos</span>
            </div>

            <div class="rel-metric-box" style="background: #f0fdf4; border-color: #bbf7d0;">
                <span style="font-size: 0.72rem; color: #166534; font-weight: 700; text-transform: uppercase;">Valor dos Itens Compatíveis</span>
                <div style="font-size: 1.6rem; font-weight: 800; color: #059669; margin: 0.2rem 0;">
                    R$ {{ number_format($totalValorItensCompativeis, 2, ',', '.') }}
                </div>
                <span style="font-size: 0.72rem; color: #166534;">Volume aderente ao catálogo Ecoclean</span>
            </div>

            <div class="rel-metric-box">
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Itens com Convergência</span>
                <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0;">
                    {{ $totalItensConfirmados }} / {{ $totalItensAnalisados }}
                </div>
                <span style="font-size: 0.72rem; color: #64748b;">Itens técnicos validados</span>
            </div>
        </div>

        <!-- Metodologia e Escopo Pesquisado -->
        <div class="rel-card">
            <h3 class="rel-section-title">
                <x-heroicon-o-information-circle style="width: 1.25rem; height: 1.25rem;" />
                Metodologia da Pesquisa e Fontes Oficiais
            </h3>
            <div style="font-size: 0.85rem; color: #475569; line-height: 1.5; display: flex; flex-direction: column; gap: 0.5rem;">
                <p style="margin: 0;">
                    <strong>Fontes Consultadas:</strong> Portal Nacional de Contratações Públicas (PNCP - API REST v1), Pregão Online Banrisul (Procergs) e Editais Públicos da Prefeitura Municipal de Canoas / Fundação Municipal de Saúde de Canoas.
                </p>
                <p style="margin: 0;">
                    <strong>Filtros e Regras Aplicadas:</strong> Foco exclusivo em compras de suprimentos de higiene, limpeza e descartáveis. Exclusão explícita de serviços terceirizados com mão de obra, roçada, desassoreamento e transporte/coleta de lixo urbano. Licitações mistas ou com lotes múltiplos foram avaliadas item a item.
                </p>
                <p style="margin: 0;">
                    <strong>Abrangência Geográfica:</strong> Conforme informação pública do site da Ecoclean, a empresa atende a Grande Porto Alegre e o Vale dos Sinos (pendente de confirmação comercial formal). O município de Canoas situa-se diretamente na área de conurbação primária de Porto Alegre.
                </p>
            </div>
        </div>

        <!-- Tabela 1: Publicações e Contratações Identificadas -->
        <div class="rel-card">
            <h3 class="rel-section-title">
                <x-heroicon-o-building-office-2 style="width: 1.25rem; height: 1.25rem;" />
                Contratações Públicas com Oportunidades Identificadas
            </h3>
            <div style="overflow-x: auto;">
                <table class="rel-table">
                    <thead>
                        <tr>
                            <th>Órgão Licitante</th>
                            <th>Edital / Modalidade</th>
                            <th>Objeto Sintético</th>
                            <th>Valor Global do Edital</th>
                            <th>Valor dos Itens Aderentes</th>
                            <th>Local de Entrega Informado</th>
                            <th>Situação na Fonte</th>
                            <th>Fonte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($publicacoes as $pub)
                            <tr>
                                <td>
                                    <strong>{{ $pub['orgao'] }}</strong>
                                    <div style="font-size: 0.7rem; color: #64748b;">PNCP: {{ $pub['pncp'] }}</div>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: #065f46;">{{ $pub['edital'] }}</span>
                                    <div style="font-size: 0.7rem; color: #64748b;">{{ $pub['modalidade'] }}</div>
                                </td>
                                <td style="max-width: 250px;">
                                    {{ $pub['objeto'] }}
                                </td>
                                <td>
                                    R$ {{ number_format($pub['valor_global'], 2, ',', '.') }}
                                </td>
                                <td>
                                    <strong style="color: #059669;">R$ {{ number_format($pub['valor_compativeis'], 2, ',', '.') }}</strong>
                                    <div style="font-size: 0.7rem; color: #64748b;">({{ $pub['itens_relacionados'] }} itens mapeados)</div>
                                </td>
                                <td style="font-size: 0.75rem; color: #475569;">
                                    {{ $pub['local_entrega'] }}
                                </td>
                                <td>
                                    <span class="rel-badge" style="background: #e0f2fe; color: #0369a1;">
                                        {{ $pub['situacao'] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $pub['link_fonte'] }}" target="_blank" rel="noopener noreferrer" style="color: #0284c7; text-decoration: underline; font-weight: 600;">
                                        Banrisul
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabela 2: Detalhamento dos Itens e Divergências Técnicas (Edital 0103/2026) -->
        <div class="rel-card">
            <h3 class="rel-section-title">
                <x-heroicon-o-clipboard-document-check style="width: 1.25rem; height: 1.25rem;" />
                Detalhamento dos Itens Técnicos e Compatibilidade com o Catálogo
            </h3>
            <div style="overflow-x: auto;">
                <table class="rel-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Descrição no Edital</th>
                            <th>Quantidade & Unidade</th>
                            <th>Valor Unit. Máx.</th>
                            <th>Valor Total</th>
                            <th>Produto Ecoclean Correspondente</th>
                            <th>Convergência / Divergência de Embalagem</th>
                            <th>Classificação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comparacoes as $c)
                            @php
                                $item = $c->editalItem;
                                $prod = $c->produtoCatalogo;
                            @endphp
                            <tr>
                                <td style="font-weight: 700; text-align: center;">
                                    {{ str_pad((string)$item->item_numero, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>
                                    <strong>{{ $item->descricao_principal }}</strong>
                                    <div style="font-size: 0.7rem; color: #64748b;">Cota: {{ strtoupper($item->cota_tipo ?? 'Ampla') }}</div>
                                </td>
                                <td>
                                    {{ number_format($item->quantidade, 0, ',', '.') }} {{ $item->unidade_medida }}
                                </td>
                                <td>
                                    R$ {{ number_format($item->valor_unitario_estimado, 2, ',', '.') }}
                                </td>
                                <td>
                                    <strong>R$ {{ number_format($item->valor_total_estimado, 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <span style="color: #065f46; font-weight: 600;">{{ $prod?->nome ?? 'Pendente' }}</span>
                                    <div style="font-size: 0.7rem; color: #64748b;">{{ $prod?->embalagem ?? '' }}</div>
                                </td>
                                <td style="font-size: 0.75rem; color: #92400e; max-width: 260px;">
                                    {{ $c->divergencias_especificacao ?? 'Especificação equivalente.' }}
                                </td>
                                <td>
                                    <span class="rel-badge" style="background: {{ $c->classificacao === 'confirmada' ? '#dcfce7' : '#fef3c7' }}; color: {{ $c->classificacao === 'confirmada' ? '#166534' : '#92400e' }};">
                                        {{ ucfirst($c->classificacao) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notas e Limitações Estratégicas -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1.25rem; font-size: 0.8rem; color: #64748b; line-height: 1.5;">
            <div style="font-weight: 700; color: #334155; margin-bottom: 0.35rem; text-transform: uppercase; font-size: 0.75rem;">
                Premissas e Limitações da Análise Comercial:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                <li><strong>Oportunidades Identificadas:</strong> Os números apresentados representam o valor estimado dos lotes técnicos compatíveis com a linha de produtos da empresa no histórico recente de compras públicas de Canoas/RS. Não afirmamos que a empresa participou ou deixou de participar de certames passados, pois atas e propostas dependem de validação de documentos históricos específicos.</li>
                <li><strong>Valores Totais vs. Itens Selecionados:</strong> O valor total do Edital 0103/2026 (R$ 61,2 milhões) contempla a totalidade do Registro de Preços do Município para 118 lotes ao longo de 12 meses. Os itens compatíveis com o portfólio da Ecoclean somam R$ 32,8 milhões e não devem ser tratados como faturamento ou lucro perdido, mas como dimensão de mercado potencial.</li>
                <li><strong>Preços do Catálogo:</strong> Os preços indicados nos produtos da empresa foram extraídos da tabela pública do site institucional para fins demonstrativos e não representam proposta comercial vinculante da Ecoclean.</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
