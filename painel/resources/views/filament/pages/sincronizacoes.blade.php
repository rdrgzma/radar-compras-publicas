<x-filament-panels::page>
    <style>
        .sync-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .sync-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .sync-card:hover {
            border-color: #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }
        .sync-card-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }
        .sync-card-value {
            font-size: 1.35rem;
            font-weight: 800;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sync-card-meta {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }
        .sync-table-container {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .sync-table-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fafafa;
        }
        .sync-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.825rem;
            text-align: left;
        }
        .sync-table th {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .sync-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            white-space: nowrap;
        }
        .sync-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .sync-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            line-height: 1rem;
            text-transform: capitalize;
        }
        .sync-badge-sucesso {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .sync-badge-parcial {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .sync-badge-processando {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .sync-badge-erro {
            background-color: #fff1f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }
        .sync-badge-modo {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .sync-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 50%;
            display: inline-block;
        }
        .sync-dot-green {
            background-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        .sync-dot-red {
            background-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        /* Suporte ao Dark Mode */
        .dark .sync-card,
        .dark .sync-table-container {
            background-color: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .dark .sync-table-header {
            background-color: #111827;
            border-color: #374151;
        }
        .dark .sync-table th {
            background-color: #111827;
            color: #9ca3af;
            border-color: #374151;
        }
        .dark .sync-table td {
            border-color: #374151;
            color: #d1d5db;
        }
        .dark .sync-table tbody tr:hover {
            background-color: #374151;
        }
    </style>

    <!-- Cards de Indicadores de Saúde -->
    <div class="sync-grid">
        <div class="sync-card">
            <div class="sync-card-title">Status da API Go</div>
            <div class="sync-card-value">
                @if (($saudeApi['status'] ?? '') === 'ok')
                    <span class="sync-dot sync-dot-green"></span>
                    <span style="color: #059669;">Operacional</span>
                @else
                    <span class="sync-dot sync-dot-red"></span>
                    <span style="color: #dc2626;">Indisponível</span>
                @endif
            </div>
            <div class="sync-card-meta">
                Conector PNCP: <strong>{{ $saudeApi['conector_pncp'] ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="sync-card">
            <div class="sync-card-title">Banco de Dados (Schema Go)</div>
            <div class="sync-card-value" style="color: #111827;">
                {{ ucfirst($saudeApi['banco_de_dados'] ?? 'Não informado') }}
            </div>
            <div class="sync-card-meta">
                Fuso Horário: <strong>{{ $saudeApi['fuso_horario'] ?? 'America/Sao_Paulo' }}</strong>
            </div>
        </div>

        <div class="sync-card">
            <div class="sync-card-title">Total de Execuções Registradas</div>
            <div class="sync-card-value" style="color: #2563eb;">
                {{ $totalRegistros }}
            </div>
            <div class="sync-card-meta">
                Histórico persistido para auditoria
            </div>
        </div>
    </div>

    <!-- Tabela de Histórico de Sincronizações -->
    <div class="sync-table-container">
        <div class="sync-table-header">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; color: #111827; margin: 0;">
                    Histórico de Execuções e Coletas
                </h3>
                <span style="font-size: 0.75rem; color: #6b7280;">
                    Sincronização assíncrona gerenciada via API Go
                </span>
            </div>
            <div>
                <span style="font-size: 0.75rem; font-weight: 600; color: #4b5563; background: #e5e7eb; padding: 0.25rem 0.6rem; border-radius: 0.375rem;">
                    Exibindo {{ count($historico) }} registro(s)
                </span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="sync-table">
                <thead>
                    <tr>
                        <th>Iniciado Em</th>
                        <th>Janela Consultada</th>
                        <th>Modo</th>
                        <th>Páginas</th>
                        <th>Coletados</th>
                        <th>Novos / Atualizados</th>
                        <th>Falhas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historico as $s)
                        @php
                            $st = $s['status'] ?? 'pendente';
                            $badgeClass = match($st) {
                                'sucesso'     => 'sync-badge-sucesso',
                                'parcial'     => 'sync-badge-parcial',
                                'processando' => 'sync-badge-processando',
                                'erro'        => 'sync-badge-erro',
                                default       => 'sync-badge-modo',
                            };
                            $dtIni = !empty($s['iniciado_em']) ? \Carbon\Carbon::parse($s['iniciado_em'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'N/I';
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">
                                {{ $dtIni }}
                            </td>
                            <td style="font-family: monospace; font-size: 0.75rem;">
                                {{ $s['janela_inicial'] }} até {{ $s['janela_final'] }}
                            </td>
                            <td>
                                <span class="sync-badge sync-badge-modo" style="{{ ($s['modo'] ?? 'real') === 'demonstrativo' ? 'background: #faf5ff; color: #6b21a8; border-color: #e9d5ff;' : '' }}">
                                    {{ ucfirst($s['modo'] ?? 'real') }}
                                </span>
                            </td>
                            <td style="font-weight: 600;">
                                {{ $s['paginas_processadas'] ?? 0 }}
                            </td>
                            <td style="font-weight: 700; color: #111827;">
                                {{ $s['total_registros_fonte'] ?? 0 }}
                            </td>
                            <td>
                                <span style="color: #059669; font-weight: 700;">+{{ $s['novos_registros'] ?? 0 }}</span>
                                <span style="color: #9ca3af; margin: 0 0.15rem;">/</span>
                                <span style="color: #6b7280; font-weight: 600;">{{ $s['registros_atualizados'] ?? 0 }}</span>
                            </td>
                            <td>
                                @if (($s['erros_encontrados'] ?? 0) > 0)
                                    <span style="color: #d97706; font-weight: 700;" title="{{ $s['mensagem_erro'] ?? '' }}">
                                        {{ $s['erros_encontrados'] }} aviso(s)
                                    </span>
                                @else
                                    <span style="color: #9ca3af;">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="sync-badge {{ $badgeClass }}">
                                    {{ ucfirst($st) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Nenhuma execução registrada até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
