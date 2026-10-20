<x-filament-panels::page>
    @if ($avisoApi)
        <div class="p-4 mb-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600" />
                <span>{{ $avisoApi }}</span>
            </div>
            <x-filament::button size="xs" color="gray" wire:click="carregarOportunidades">Tentar Novamente</x-filament::button>
        </div>
    @endif

    @if (!$perfilAtivo)
        <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 text-center shadow-sm">
            <x-heroicon-o-adjustments-horizontal class="w-12 h-12 text-emerald-500 mx-auto mb-3" />
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Nenhum perfil de fornecimento configurado</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                Para que o radar identifique licitações compatíveis, cadastre seu perfil com as palavras-chave dos produtos/serviços que sua empresa fornece.
            </p>
            <div class="mt-4">
                <x-filament::button tag="a" href="{{ url('/portal/perfil-fornecedors/create') }}" color="primary">
                    Configurar Perfil de Fornecimento
                </x-filament::button>
            </div>
        </div>
    @else
        <!-- Barra de Contexto do Perfil Ativo -->
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="text-xs uppercase font-bold text-gray-400 tracking-wider">Perfil Ativo</div>
                <div class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ $perfilAtivo->nome }}</div>
                <div class="flex flex-wrap gap-1 mt-1 text-xs">
                    <span class="text-gray-500">Inclui:</span>
                    @foreach ($perfilAtivo->termos_obrigatorios ?? [] as $t)
                        <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded font-medium">{{ $t }}</span>
                    @endforeach
                    @if (!empty($perfilAtivo->termos_excluidos))
                        <span class="text-gray-500 ml-2">Exclui:</span>
                        @foreach ($perfilAtivo->termos_excluidos as $e)
                            <span class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 rounded font-medium">{{ $e }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-filament::button size="sm" color="{{ $apenasVigentes ? 'primary' : 'gray' }}" wire:click="alternarFiltroVigentes">
                    {{ $apenasVigentes ? 'Apenas Propostas Abertas' : 'Exibindo Todas (Incluindo Encerradas)' }}
                </x-filament::button>
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    {{ $totalEncontradas }} encontrada(s)
                </div>
            </div>
        </div>

        <!-- Grade de Oportunidades -->
        <div class="space-y-4">
            @forelse ($oportunidades as $op)
                @php
                    $isEncerrado = $op['prazo_encerrado'] ?? false;
                    $valor = $op['valor_total_estimado'] ?? null;
                    $valorFmt = is_numeric($valor) ? 'R$ ' . number_format($valor, 2, ',', '.') : 'não informado';
                    $dtPub = !empty($op['data_publicacao_pncp']) ? \Carbon\Carbon::parse($op['data_publicacao_pncp'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'não informado';
                    $dtEnc = !empty($op['data_encerramento_proposta']) ? \Carbon\Carbon::parse($op['data_encerramento_proposta'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') : 'não informado';
                @endphp

                <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition hover:shadow-md">
                    <!-- Cabeçalho do Card -->
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-2 pb-3 border-b border-gray-100 dark:border-gray-750">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs text-gray-500 font-semibold">{{ $op['numero_controle_pncp'] }}</span>
                                @if ($op['is_demonstrativo'] ?? false)
                                    <span class="px-2 py-0.5 text-xs font-bold rounded bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                        [DEMONSTRAÇÃO]
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    {{ $op['modalidade_nome'] ?? 'Modalidade não informada' }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $isEncerrado ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' }}">
                                    {{ $isEncerrado ? 'Prazo Encerrado' : 'Propostas Abertas' }}
                                </span>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 mt-1">
                                {{ $op['orgao_razao_social'] ?? 'Órgão não informado' }}
                            </h4>
                            <div class="text-xs text-gray-500">
                                CNPJ: {{ $op['orgao_cnpj'] ?? 'não informado' }} | UF: <strong class="text-gray-700 dark:text-gray-300">{{ $op['unidade_uf_sigla'] ?? 'N/I' }}</strong> - {{ $op['unidade_municipio_nome'] ?? '' }}
                            </div>
                        </div>

                        <div class="text-right sm:self-start">
                            <div class="text-xs text-gray-400">Valor Estimado</div>
                            <div class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                                {{ $valorFmt }}
                            </div>
                        </div>
                    </div>

                    <!-- Objeto da Licitação -->
                    <div class="py-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $op['objeto'] }}
                        </p>
                        @if (!empty($op['informacao_complementar']) && $op['informacao_complementar'] !== 'não informado')
                            <p class="text-xs text-gray-500 mt-1 italic">
                                Obs: {{ $op['informacao_complementar'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Motivo Explícito da Correspondência -->
                    <div class="p-3 mb-3 bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-lg">
                        <div class="flex items-start gap-2">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Motivo da Correspondência</span>
                                <p class="text-xs text-emerald-900 dark:text-emerald-200 mt-0.5">
                                    {{ $op['motivo_correspondencia'] ?? 'Correspondência identificada com os critérios do perfil.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé com Prazos e Ações -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2 text-xs text-gray-500">
                        <div class="flex items-center gap-4 flex-wrap">
                            <span>Publicado: <strong>{{ $dtPub }}</strong></span>
                            <span>Encerramento: <strong class="{{ $isEncerrado ? 'text-rose-600' : 'text-emerald-700' }}">{{ $dtEnc }}</strong></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ $op['link_pncp'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition">
                                <span>Ver no PNCP</span>
                                <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                            </a>

                            <x-filament::button size="xs" color="success" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'selecionada', 'Interesse confirmado pelo Radar')">
                                Selecionar
                            </x-filament::button>

                            <x-filament::button size="xs" color="warning" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'em_analise', 'Aguardando parecer da equipe')">
                                Analisar
                            </x-filament::button>

                            <x-filament::button size="xs" color="danger" wire:click="salvarSelecao('{{ $op['numero_controle_pncp'] }}', 'descartada', 'Descartada pelo fornecedor')">
                                Descartar
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-gray-500">Nenhuma oportunidade encontrada para os filtros configurados.</p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
