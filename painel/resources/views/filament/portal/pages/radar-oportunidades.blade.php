<x-filament-panels::page>
    <style>
        .radar-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
            transition: all 0.2s ease;
        }
        .radar-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.04);
            border-color: #d1d5db;
        }
        .radar-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            line-height: 1rem;
        }
        .radar-badge-emerald {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .radar-badge-rose {
            background-color: #fff1f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }
        .radar-badge-blue {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .radar-badge-purple {
            background-color: #faf5ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
        }
        .radar-badge-gray {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .radar-match-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin: 0.75rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .radar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.45rem 0.85rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .radar-btn-outline {
            background-color: #ffffff;
            border-color: #d1d5db;
            color: #374151;
        }
        .radar-btn-outline:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }
        .radar-btn-success {
            background-color: #10b981;
            color: #ffffff;
        }
        .radar-btn-success:hover {
            background-color: #059669;
        }
        .radar-btn-warning {
            background-color: #f59e0b;
            color: #ffffff;
        }
        .radar-btn-warning:hover {
            background-color: #d97706;
        }
        .radar-btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }
        .radar-btn-danger:hover {
            background-color: #dc2626;
        }
        .radar-icon-sm {
            width: 1.1rem;
            height: 1.1rem;
            max-width: 1.1rem;
            max-height: 1.1rem;
            flex-shrink: 0;
        }
        .radar-icon-lg {
            width: 3rem;
            height: 3rem;
            max-width: 3rem;
            max-height: 3rem;
            display: block;
            margin: 0 auto 0.75rem auto;
        }
        /* Suporte ao modo escuro do Filament */
        .dark .radar-card {
            background-color: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .dark .radar-match-box {
            background-color: rgba(6, 78, 59, 0.25);
            border-color: #065f46;
        }
        .dark .radar-btn-outline {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }
        .dark .radar-badge-gray {
            background-color: #374151;
            color: #d1d5db;
            border-color: #4b5563;
        }
    </style>

    @if ($avisoApi)
        <div style="background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; color: #92400e;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                <x-heroicon-o-exclamation-triangle class="radar-icon-sm" style="color: #d97706;" />
                <span>{{ $avisoApi }}</span>
            </div>
            <button type="button" class="radar-btn radar-btn-outline" wire:click="carregarOportunidades">
                Tentar Novamente
            </button>
        </div>
    @endif

    @if (!$perfilAtivo)
        <div class="radar-card" style="text-align: center; padding: 2.5rem 1.5rem; max-width: 36rem; margin: 1.5rem auto;">
            <x-heroicon-o-adjustments-horizontal class="radar-icon-lg" style="color: #10b981;" />
            <h3 style="font-size: 1.125rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0 0.25rem 0;">
                Nenhum perfil de fornecimento ativo encontrado
            </h3>
            <p style="font-size: 0.875rem; color: #6b7280; line-height: 1.5; margin-bottom: 1.25rem;">
                Para que o Radar identifique oportunidades no PNCP com justificativa explícita de correspondência, configure o perfil com suas palavras-chave e estados de atuação.
            </p>
            <div>
                <a href="{{ url('/portal/perfil-fornecedors/create') }}" class="radar-btn radar-btn-success" style="padding: 0.6rem 1.2rem; font-size: 0.875rem;">
                    Configurar Perfil de Fornecimento
                </a>
            </div>
        </div>
    @else
        <!-- Barra de Contexto do Perfil Ativo -->
        <div class="radar-card" style="margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">
                    Perfil de Busca Ativo
                </div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #111827;">
                    {{ $perfilAtivo->nome }}
                </div>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; margin-top: 0.4rem; font-size: 0.75rem;">
                    <span style="color: #6b7280; font-weight: 600;">Inclui:</span>
                    @foreach ($perfilAtivo->termos_obrigatorios ?? [] as $t)
                        <span class="radar-badge radar-badge-emerald">{{ $t }}</span>
                    @endforeach
                    @if (!empty($perfilAtivo->termos_excluidos))
                        <span style="color: #6b7280; font-weight: 600; margin-left: 0.5rem;">Exclui:</span>
                        @foreach ($perfilAtivo->termos_excluidos as $e)
                            <span class="radar-badge radar-badge-rose">{{ $e }}</span>
                        @endforeach
                    @endif
                    @if (!empty($perfilAtivo->ufs))
                        <span style="color: #6b7280; font-weight: 600; margin-left: 0.5rem;">UFs:</span>
                        @foreach ($perfilAtivo->ufs as $uf)
                            <span class="radar-badge radar-badge-blue">{{ $uf }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button type="button" class="radar-btn {{ $apenasVigentes ? 'radar-btn-success' : 'radar-btn-outline' }}" wire:click="alternarFiltroVigentes">
                    {{ $apenasVigentes ? 'Filtro: Propostas Abertas' : 'Filtro: Todas as Situações' }}
                </button>
                <div style="font-size: 0.875rem; font-weight: 700; color: #374151; white-space: nowrap; background: #f3f4f6; padding: 0.4rem 0.8rem; border-radius: 0.5rem;">
                    {{ $totalEncontradas }} encontrada(s)
                </div>
            </div>
        </div>

        <!-- Grade de Oportunidades -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse ($oportunidades as $op)
                @php
                    $isEncerrado = $op['prazo_encerrado'] ?? false;
                    $valor = $op['valor_total_estimado'] ?? null;
                    $valorFmt = is_numeric($valor) ? 'R$ ' . number_format($valor, 2, ',', '.') : 'não informado';
                    $dtPub = !empty($op['data_publicacao_pncp']) ? \Carbon\Carbon::parse($op['data_publicacao_pncp'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'não informado';
                    $dtEnc = !empty($op['data_encerramento_proposta']) ? \Carbon\Carbon::parse($op['data_encerramento_proposta'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'não informado';
                    $dtColeta = !empty($op['data_coleta']) ? \Carbon\Carbon::parse($op['data_coleta'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'não informado';
                @endphp

                <div class="radar-card">
                    <!-- Cabeçalho do Card -->
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f3f4f6;">
                        <div style="flex: 1; min-width: 280px;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <span style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #4b5563; background: #f3f4f6; padding: 0.15rem 0.45rem; border-radius: 0.25rem;">
                                    {{ $op['numero_controle_pncp'] }}
                                </span>
                                @if ($op['is_demonstrativo'] ?? false)
                                    <span class="radar-badge radar-badge-purple">[DEMONSTRAÇÃO]</span>
                                @endif
                                <span class="radar-badge radar-badge-blue">
                                    {{ $op['modalidade_nome'] ?? 'Modalidade N/I' }}
                                </span>
                                <span class="radar-badge {{ $isEncerrado ? 'radar-badge-gray' : 'radar-badge-emerald' }}">
                                    {{ $isEncerrado ? 'Prazo Encerrado' : 'Propostas Abertas' }}
                                </span>
                            </div>

                            <h4 style="font-size: 1rem; font-weight: 700; color: #111827; margin: 0.25rem 0;">
                                {{ $op['orgao_razao_social'] ?? 'Órgão não informado' }}
                            </h4>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                CNPJ: {{ $op['orgao_cnpj'] ?? 'não informado' }} &bull;
                                UF: <strong style="color: #1f2937;">{{ $op['unidade_uf_sigla'] ?? 'N/I' }}</strong> - {{ $op['unidade_municipio_nome'] ?? '' }} &bull;
                                Coletado em: {{ $dtColeta }}
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: #9ca3af;">Valor Estimado</div>
                            <div style="font-size: 1.25rem; font-weight: 800; color: #059669;">
                                {{ $valorFmt }}
                            </div>
                        </div>
                    </div>

                    <!-- Objeto da Licitação -->
                    <div style="padding: 0.85rem 0; font-size: 0.875rem; color: #374151; line-height: 1.5;">
                        {{ $op['objeto'] }}
                        @if (!empty($op['informacao_complementar']) && $op['informacao_complementar'] !== 'não informado')
                            <div style="font-size: 0.75rem; color: #6b7280; font-style: italic; margin-top: 0.35rem;">
                                Informação Complementar: {{ $op['informacao_complementar'] }}
                            </div>
                        @endif
                    </div>

                    <!-- Motivo Explícito da Correspondência -->
                    <div class="radar-match-box">
                        <x-heroicon-s-check-circle class="radar-icon-sm" style="color: #059669; margin-top: 0.1rem;" />
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #065f46;">
                                Motivo Explícito da Correspondência
                            </div>
                            <div style="font-size: 0.78rem; color: #047857; margin-top: 0.15rem; font-weight: 500;">
                                {{ $op['motivo_correspondencia'] ?? 'Correspondência identificada com os critérios do perfil.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé com Prazos e Ações -->
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f3f4f6; font-size: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; color: #6b7280;">
                            <span>Publicação: <strong style="color: #374151;">{{ $dtPub }}</strong></span>
                            <span>Abertura/Encerramento: <strong style="color: {{ $isEncerrado ? '#dc2626' : '#059669' }};">{{ $dtEnc }}</strong></span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ $op['link_pncp'] }}" target="_blank" rel="noopener noreferrer" class="radar-btn radar-btn-outline">
                                <span>Ver no PNCP</span>
                                <x-heroicon-m-arrow-top-right-on-square class="radar-icon-sm" />
                            </a>

                            <button type="button" class="radar-btn radar-btn-success" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'selecionada', 'Interesse confirmado pelo Radar')">
                                Selecionar
                            </button>

                            <button type="button" class="radar-btn radar-btn-warning" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'em_analise', 'Aguardando parecer da equipe')">
                                Analisar
                            </button>

                            <button type="button" class="radar-btn radar-btn-danger" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'descartada', 'Descartada pelo fornecedor')">
                                Descartar
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="radar-card" style="text-align: center; padding: 2rem; color: #6b7280;">
                    Nenhuma oportunidade encontrada no PNCP para os critérios e filtros atuais.
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
