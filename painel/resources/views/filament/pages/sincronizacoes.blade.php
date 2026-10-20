<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Status da API Go</div>
            <div class="text-xl font-bold mt-1 flex items-center gap-2">
                @if (($saudeApi['status'] ?? '') === 'ok')
                    <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-emerald-600 dark:text-emerald-400">Operacional</span>
                @else
                    <span class="inline-block w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-red-600 dark:text-red-400">Indisponível</span>
                @endif
            </div>
            <div class="text-xs text-gray-400 mt-1">Conector: {{ $saudeApi['conector_pncp'] ?? 'N/A' }}</div>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Banco de Dados (Schema Go)</div>
            <div class="text-xl font-bold mt-1 text-gray-800 dark:text-gray-100">
                {{ ucfirst($saudeApi['banco_de_dados'] ?? 'Não informado') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Fuso: {{ $saudeApi['fuso_horario'] ?? 'America/Sao_Paulo' }}</div>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total de Execuções Registradas</div>
            <div class="text-xl font-bold mt-1 text-gray-800 dark:text-gray-100">
                {{ $totalRegistros }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Histórico completo de auditoria</div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Histórico de Execuções e Coletas</h3>
            <span class="text-xs text-gray-500">Atualizado via API Go</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-400 text-xs uppercase font-medium">
                    <tr>
                        <th class="px-4 py-3">Iniciado Em</th>
                        <th class="px-4 py-3">Janela Consultada</th>
                        <th class="px-4 py-3">Modo</th>
                        <th class="px-4 py-3">Páginas</th>
                        <th class="px-4 py-3">Coletados</th>
                        <th class="px-4 py-3">Novos / Atualizados</th>
                        <th class="px-4 py-3">Falhas</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($historico as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($s['iniciado_em'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">
                                {{ $s['janela_inicial'] }} até {{ $s['janela_final'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ ($s['modo'] ?? 'real') === 'demonstrativo' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' }}">
                                    {{ ucfirst($s['modo'] ?? 'real') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $s['paginas_processadas'] ?? 0 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-semibold">{{ $s['total_registros_fonte'] ?? 0 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-emerald-600 font-medium">+{{ $s['novos_registros'] ?? 0 }}</span> / 
                                <span class="text-gray-500 font-medium">{{ $s['registros_atualizados'] ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if (($s['erros_encontrados'] ?? 0) > 0)
                                    <span class="text-amber-600 font-bold" title="{{ $s['mensagem_erro'] ?? '' }}">
                                        {{ $s['erros_encontrados'] }} aviso(s)
                                    </span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $st = $s['status'] ?? 'pendente';
                                    $corBadge = match($st) {
                                        'sucesso' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
                                        'parcial' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
                                        'processando' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 animate-pulse',
                                        'erro' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $corBadge }}">
                                    {{ ucfirst($st) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                Nenhuma sincronização realizada até o momento. Utilize os botões acima para disparar a primeira coleta ou carregar o modo demonstrativo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
