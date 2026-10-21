<x-filament-panels::page>
    <style>
        .comp-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .comp-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }
        .comp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            margin-top: 1rem;
        }
        @media (min-width: 1024px) {
            .comp-grid {
                grid-template-columns: 1.2fr 1fr;
            }
        }
        .comp-box-edital {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        .comp-box-catalogo {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        .comp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.55rem;
            border-radius: 0.375rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .comp-badge-possivel { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .comp-badge-confirmada { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .comp-badge-incompativel { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .comp-icon-sm { width: 1rem; height: 1rem; flex-shrink: 0; }
        .divergencia-box {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 0.65rem 0.85rem;
            margin-top: 0.75rem;
            border-radius: 0 0.375rem 0.375rem 0;
            font-size: 0.78rem;
            color: #92400e;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- Banner de Contexto e Metodologia -->
        <div style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); color: #ffffff; padding: 1.5rem; border-radius: 0.75rem;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(255,255,255,0.2); padding: 0.25rem 0.6rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                        <span>Caso Demonstrativo: Ecoclean</span>
                    </div>
                    <h2 style="font-size: 1.4rem; font-weight: 800; margin: 0.4rem 0 0.2rem 0;">
                        Comparativo Técnico de Itens do Edital vs. Catálogo da Empresa
                    </h2>
                    <p style="font-size: 0.85rem; opacity: 0.95; max-width: 800px; margin: 0;">
                        Análise de convergência e divergências entre itens da contratação pública de Canoas (Edital 0103/2026 - Banrisul 349814) e os produtos padronizados pela Ecoclean.
                    </p>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                    <div style="font-size: 0.75rem; opacity: 0.85;">Valor dos Itens Compatíveis:</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #a7f3d0;">
                        R$ {{ number_format($totalValorItens, 2, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.85;">
                        {{ $totalItensConfirmados }} de {{ $totalItens }} itens confirmados
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerta de Transparência e Rastreabilidade -->
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #3b82f6; padding: 0.85rem 1rem; border-radius: 0.5rem; font-size: 0.82rem; color: #1e40af;">
            <strong>Nota de Rastreabilidade e Transparência:</strong> Este certame é um <em>exemplo do histórico recente</em> (homologado em 2026), utilizado para validação da aderência técnica do catálogo. Os itens e valores foram extraídos diretamente do Termo de Referência oficial do Edital 0103/2026 da Prefeitura Municipal de Canoas/RS. Correspondências automáticas são sugestões e não convertem unidades ou afirmam equivalência sem regra explícita.
        </div>

        <!-- Barra de Filtros e Busca -->
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.85rem 1rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                <div>
                    <label style="font-size: 0.72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Contratação em Análise</label>
                    <select wire:model.live="filtroEdital" style="border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.35rem 0.65rem; font-size: 0.82rem; background: #fff;">
                        <option value="88577416000118-1-000066/2026">Canoas — Edital 0103/2026 (Materiais de Higiene e Limpeza)</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Classificação</label>
                    <select wire:model.live="filtroClassificacao" style="border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.35rem 0.65rem; font-size: 0.82rem; background: #fff;">
                        <option value="todos">Todas as Classificações</option>
                        <option value="confirmada">Confirmada pelo Usuário</option>
                        <option value="possivel">Possível (Sugestão Automática)</option>
                        <option value="incompativel">Incompatível</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">Buscar por Item / Palavra</label>
                    <input type="text" wire:model.live.debounce.300ms="buscaTermo" placeholder="Ex: água sanitária, balde, luva..." style="border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.35rem 0.65rem; font-size: 0.82rem; min-width: 220px;" />
                </div>
            </div>

            <div style="font-size: 0.78rem; color: #6b7280;">
                Exibindo <strong>{{ count($comparacoes) }}</strong> correspondência(s)
            </div>
        </div>

        <!-- Lista de Comparações de Itens -->
        <div style="display: flex; flex-direction: column;">
            @forelse ($comparacoes as $comp)
                @php
                    $item = $comp->editalItem;
                    $prod = $comp->produtoCatalogo;
                @endphp
                <div class="comp-card">
                    <!-- Cabeçalho do Item -->
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span style="background: #1e293b; color: #ffffff; padding: 0.2rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 800;">
                                Item {{ str_pad((string)$item->item_numero, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            @if ($item->lote_numero)
                                <span style="background: #e2e8f0; color: #475569; padding: 0.2rem 0.45rem; border-radius: 0.375rem; font-size: 0.72rem; font-weight: 600;">
                                    Lote {{ $item->lote_numero }}
                                </span>
                            @endif
                            <span style="font-size: 1rem; font-weight: 700; color: #0f172a;">
                                {{ $item->descricao_principal }}
                            </span>
                            @if ($item->catmat)
                                <span style="font-size: 0.72rem; color: #64748b;">CATMAT: {{ $item->catmat }}</span>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <!-- Badge de Classificação -->
                            <span class="comp-badge comp-badge-{{ $comp->classificacao }}">
                                @if ($comp->classificacao === 'confirmada')
                                    <x-heroicon-s-check-circle class="comp-icon-sm" />
                                    Confirmada
                                @elseif ($comp->classificacao === 'possivel')
                                    <x-heroicon-s-question-mark-circle class="comp-icon-sm" />
                                    Possível
                                @else
                                    <x-heroicon-s-x-circle class="comp-icon-sm" />
                                    Incompatível
                                @endif
                            </span>

                            <!-- Ações Rápidas de Classificação -->
                            <div style="display: inline-flex; border: 1px solid #d1d5db; border-radius: 0.375rem; overflow: hidden; font-size: 0.72rem;">
                                <button type="button" wire:click="atualizarClassificacao({{ $comp->id }}, 'confirmada')" style="padding: 0.25rem 0.5rem; background: {{ $comp->classificacao === 'confirmada' ? '#166534' : '#fff' }}; color: {{ $comp->classificacao === 'confirmada' ? '#fff' : '#374151' }}; border: none; cursor: pointer; font-weight: 600;">
                                    Confirmar
                                </button>
                                <button type="button" wire:click="atualizarClassificacao({{ $comp->id }}, 'possivel')" style="padding: 0.25rem 0.5rem; background: {{ $comp->classificacao === 'possivel' ? '#92400e' : '#fff' }}; color: {{ $comp->classificacao === 'possivel' ? '#fff' : '#374151' }}; border: none; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; cursor: pointer; font-weight: 600;">
                                    Possível
                                </button>
                                <button type="button" wire:click="atualizarClassificacao({{ $comp->id }}, 'incompativel')" style="padding: 0.25rem 0.5rem; background: {{ $comp->classificacao === 'incompativel' ? '#991b1b' : '#fff' }}; color: {{ $comp->classificacao === 'incompativel' ? '#fff' : '#374151' }}; border: none; cursor: pointer; font-weight: 600;">
                                    Incompatível
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grade Comparativa: Lado a Lado -->
                    <div class="comp-grid">
                        <!-- Coluna 1: Especificação Oficial do Edital -->
                        <div class="comp-box-edital">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                                    Especificação no Edital Oficial
                                </span>
                                <span style="font-size: 0.75rem; font-weight: 700; color: #0284c7;">
                                    Cota: {{ strtoupper($item->cota_tipo ?? 'Ampla') }}
                                </span>
                            </div>

                            <p style="font-size: 0.82rem; color: #1e293b; margin: 0 0 0.65rem 0; line-height: 1.45;">
                                {{ $item->descricao_detalhada ?? $item->descricao_principal }}
                            </p>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.78rem; background: #ffffff; padding: 0.5rem; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                                <div>
                                    <span style="color: #64748b; font-size: 0.7rem; display: block;">Quantidade & Unidade:</span>
                                    <strong>{{ number_format($item->quantidade, 0, ',', '.') }}</strong> {{ $item->unidade_medida }}
                                </div>
                                <div>
                                    <span style="color: #64748b; font-size: 0.7rem; display: block;">Valor Máximo Estimado:</span>
                                    <strong style="color: #0369a1;">R$ {{ number_format($item->valor_total_estimado, 2, ',', '.') }}</strong>
                                    <span style="font-size: 0.7rem; color: #64748b;">(R$ {{ number_format($item->valor_unitario_estimado, 2, ',', '.') }}/un)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna 2: Produto Correspondente no Catálogo Ecoclean -->
                        <div class="comp-box-catalogo">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #166534;">
                                    Produto Correspondente (Ecoclean)
                                </span>
                                @if ($prod)
                                    <span style="font-size: 0.7rem; background: #dcfce7; color: #15803d; padding: 0.15rem 0.45rem; border-radius: 0.25rem; font-weight: 700;">
                                        {{ $prod->categoria }}
                                    </span>
                                @endif
                            </div>

                            @if ($prod)
                                <div style="font-size: 0.9rem; font-weight: 700; color: #064e3b; margin-bottom: 0.25rem;">
                                    {{ $prod->nome }}
                                </div>
                                <div style="font-size: 0.78rem; color: #065f46; margin-bottom: 0.5rem;">
                                    <strong>Embalagem:</strong> {{ $prod->embalagem ?? 'Padrão' }} • <strong>Capacidade:</strong> {{ $prod->capacidade ?? 'N/I' }}
                                </div>
                                <p style="font-size: 0.78rem; color: #166534; margin: 0 0 0.5rem 0; line-height: 1.4;">
                                    {{ $prod->especificacao_tecnica ?? 'Especificação compatível com os padrões de saneamento e higiene.' }}
                                </p>
                                <div style="font-size: 0.75rem; color: #047857;">
                                    Preço Ref. Site: <strong>R$ {{ number_format($prod->preco_referencia_site, 2, ',', '.') }}</strong>
                                    <span style="font-size: 0.7rem; color: #6b7280; font-style: italic;">(Fonte: site oficial)</span>
                                </div>
                            @else
                                <div style="color: #6b7280; font-size: 0.8rem; font-style: italic; padding: 1rem 0;">
                                    Nenhum produto vinculado diretamente. Selecione um item do catálogo abaixo:
                                </div>
                                <select wire:change="vincularProduto({{ $comp->id }}, $event.target.value)" style="width: 100%; border: 1px solid #86efac; border-radius: 0.375rem; padding: 0.35rem; font-size: 0.78rem;">
                                    <option value="">-- Vincular Produto do Catálogo --</option>
                                    @foreach ($produtosDisponiveis as $pDisp)
                                        <option value="{{ $pDisp->id }}">{{ $pDisp->nome }} ({{ $pDisp->embalagem }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <!-- Divergências e Motivo Técnico -->
                    @if (!empty($comp->divergencias_especificacao))
                        <div class="divergencia-box">
                            <strong>Divergência / Ponto de Atenção Técnica:</strong>
                            {{ $comp->divergencias_especificacao }}
                        </div>
                    @endif

                    <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #475569; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 0.375rem;">
                        <strong>Critério de Correspondência:</strong> {{ $comp->motivo_correspondencia }}
                    </div>

                    <!-- Rodapé com Ações de Feedback e Origem -->
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; font-size: 0.75rem;">
                        <!-- Feedback de Interesse do Fornecedor -->
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="color: #64748b; font-weight: 600;">Status do Interesse:</span>
                            @php
                                $feedbacks = [
                                    'relevante'       => ['label' => 'Relevante', 'bg' => '#dbeafe', 'color' => '#1e40af'],
                                    'irrelevante'     => ['label' => 'Irrelevante', 'bg' => '#f3f4f6', 'color' => '#4b5563'],
                                    'ja_participo'    => ['label' => 'Já Participo', 'bg' => '#fef3c7', 'color' => '#92400e'],
                                    'tenho_interesse' => ['label' => 'Tenho Interesse', 'bg' => '#dcfce7', 'color' => '#166534'],
                                ];
                            @endphp
                            @foreach ($feedbacks as $chave => $meta)
                                <button type="button"
                                        wire:click="registrarFeedback({{ $comp->id }}, '{{ $chave }}')"
                                        style="border: 1px solid {{ $comp->feedback_usuario === $chave ? $meta['color'] : '#d1d5db' }}; background: {{ $comp->feedback_usuario === $chave ? $meta['bg'] : '#ffffff' }}; color: {{ $comp->feedback_usuario === $chave ? $meta['color'] : '#6b7280' }}; font-weight: {{ $comp->feedback_usuario === $chave ? '700' : '500' }}; padding: 0.2rem 0.5rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.72rem;">
                                    {{ $meta['label'] }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Links da Fonte e Data de Coleta -->
                        <div style="display: flex; align-items: center; gap: 0.85rem; color: #64748b;">
                            <span>Coleta: <strong>{{ $item->data_coleta?->format('d/m/Y') ?? '21/10/2026' }}</strong></span>
                            @if ($item->link_fonte)
                                <a href="{{ $item->link_fonte }}" target="_blank" rel="noopener noreferrer" style="color: #0284c7; text-decoration: underline; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    <span>Verificar no Banrisul</span>
                                    <x-heroicon-m-arrow-top-right-on-square class="comp-icon-sm" />
                                </a>
                            @endif
                            <a href="https://pncp.gov.br/app/editais/88577416000118/2026/66" target="_blank" rel="noopener noreferrer" style="color: #059669; text-decoration: underline; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;">
                                <span>Ver no PNCP</span>
                                <x-heroicon-m-arrow-top-right-on-square class="comp-icon-sm" />
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 2rem; text-align: center; color: #6b7280;">
                    Nenhum item encontrado para os filtros selecionados.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
