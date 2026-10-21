<x-filament-panels::page>
    <style>
        .alerta-nav-btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .alerta-nav-btn.ativo {
            background: #065f46;
            color: #ffffff;
        }
        .alerta-nav-btn.inativo {
            background: #ffffff;
            color: #4b5563;
            border-color: #e5e7eb;
        }
        .alerta-nav-btn.inativo:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        .email-preview-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .email-header-meta {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-size: 0.82rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        <!-- Banner de Segurança e Modo de Demonstração -->
        <div style="background: #fef3c7; border: 1px solid #fde68a; border-left: 5px solid #d97706; padding: 1rem 1.25rem; border-radius: 0.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: #92400e; font-size: 0.95rem;">
                    <x-heroicon-s-shield-check style="width: 1.25rem; height: 1.25rem; color: #d97706;" />
                    <span>Modo de Demonstração Ativo — Envio Real Desativado</span>
                </div>
                <p style="margin: 0.3rem 0 0 0; font-size: 0.82rem; color: #b45309; line-height: 1.4;">
                    Esta central opera em modo de prévia para a <strong>Ecoclean</strong>. Nenhum e-mail ou notificação externa é transmitida. O envio real dependerá de autorização expressa e configuração de SMTP de produção.
                </p>
            </div>
            <button type="button" wire:click="simularDisparoDemonstrativo" style="background: #ffffff; border: 1px solid #d97706; color: #92400e; padding: 0.4rem 0.85rem; border-radius: 0.375rem; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
                Testar Bloqueio de Envio
            </button>
        </div>

        <!-- Abas de Navegação -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.75rem;">
            <button type="button" wire:click="$set('abaAtiva', 'previa_email')" class="alerta-nav-btn {{ $abaAtiva === 'previa_email' ? 'ativo' : 'inativo' }}">
                Prévia do E-mail do Alerta
            </button>
            <button type="button" wire:click="$set('abaAtiva', 'resumo_diario')" class="alerta-nav-btn {{ $abaAtiva === 'resumo_diario' ? 'ativo' : 'inativo' }}">
                Resumo Diário Simulado
            </button>
            <button type="button" wire:click="$set('abaAtiva', 'historico')" class="alerta-nav-btn {{ $abaAtiva === 'historico' ? 'ativo' : 'inativo' }}">
                Histórico & Anti-Duplicação ({{ count($historicoAlertas) }})
            </button>
        </div>

        @if ($abaAtiva === 'previa_email')
            @if ($alertaSelecionado)
                <div class="email-preview-container">
                    <!-- Metadados do E-mail Simulado -->
                    <div class="email-header-meta">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <span style="color: #64748b;">Para:</span>
                                <strong style="color: #1e293b;">{{ $alertaSelecionado->destinatario_simulado }}</strong>
                            </div>
                            <span style="background: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                Status: {{ str_replace('_', ' ', $alertaSelecionado->status) }}
                            </span>
                        </div>
                        <div>
                            <span style="color: #64748b;">Assunto:</span>
                            <strong style="color: #065f46; font-size: 0.95rem;">{{ $alertaSelecionado->assunto }}</strong>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            Gerado em: {{ $alertaSelecionado->created_at?->format('d/m/Y H:i') ?? 'Agora' }} • Edital PNCP: {{ $alertaSelecionado->numero_controle_pncp }}
                        </div>
                    </div>

                    <!-- Renderização do Corpo do E-mail -->
                    <div style="padding: 1.5rem; background: #fafafa;">
                        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; max-width: 700px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                            {!! $alertaSelecionado->conteudo_html !!}
                        </div>
                    </div>

                    <!-- Barra de Feedback do Fornecedor -->
                    <div style="background: #ffffff; border-top: 1px solid #e5e7eb; padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #374151;">Registrar Feedback sobre este Alerta:</span>
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
                                        wire:click="registrarFeedback({{ $alertaSelecionado->id }}, '{{ $chave }}')"
                                        style="border: 1px solid {{ $alertaSelecionado->feedback_alerta === $chave ? $meta['color'] : '#d1d5db' }}; background: {{ $alertaSelecionado->feedback_alerta === $chave ? $meta['bg'] : '#ffffff' }}; color: {{ $alertaSelecionado->feedback_alerta === $chave ? $meta['color'] : '#6b7280' }}; font-weight: {{ $alertaSelecionado->feedback_alerta === $chave ? '700' : '500' }}; padding: 0.25rem 0.65rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.78rem;">
                                    {{ $meta['label'] }}
                                </button>
                            @endforeach
                        </div>

                        <div style="font-size: 0.75rem; color: #6b7280;">
                            Prevenção de Duplicidades: <strong>Ativa</strong> (1 registro por contratação/empresa)
                        </div>
                    </div>
                </div>
            @else
                <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 2.5rem; text-align: center; color: #6b7280;">
                    Nenhuma prévia de alerta gerada no momento.
                </div>
            @endif

        @elseif ($abaAtiva === 'resumo_diario')
            <!-- Resumo Diário Simulado -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #065f46; margin: 0;">Resumo Diário de Oportunidades — Ecoclean</h3>
                        <p style="font-size: 0.8rem; color: #6b7280; margin: 0.2rem 0 0 0;">Consolidado de oportunidades aderentes ao perfil nas últimas 24 horas</p>
                    </div>
                    <span style="background: #ecfdf5; color: #047857; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
                        Frequência: Diária (08h00)
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem;">
                        <span style="font-size: 0.75rem; color: #64748b; display: block;">Novas Licitações:</span>
                        <strong style="font-size: 1.5rem; color: #0f172a;">1</strong>
                        <span style="font-size: 0.72rem; color: #10b981; display: block;">Pregão Eletrônico SRP</span>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem;">
                        <span style="font-size: 0.75rem; color: #64748b; display: block;">Itens com Aderência Técnica:</span>
                        <strong style="font-size: 1.5rem; color: #0f172a;">{{ $totalComparacoes }}</strong>
                        <span style="font-size: 0.72rem; color: #64748b; display: block;">Mapeados com o catálogo</span>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1rem;">
                        <span style="font-size: 0.75rem; color: #64748b; display: block;">Valor Potencial dos Itens:</span>
                        <strong style="font-size: 1.5rem; color: #059669;">R$ {{ number_format($valorTotalItens, 2, ',', '.') }}</strong>
                        <span style="font-size: 0.72rem; color: #64748b; display: block;">Prefeitura de Canoas/RS</span>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 1rem; font-size: 0.85rem; color: #166534;">
                    <strong>Destaque do Dia:</strong> Pregão Eletrônico 0103/2026 de Canoas apresenta 100% de convergência em sacos para lixo (30L a 150L), água sanitária (5L) e sabonetes líquidos (5L). Ponto de atenção: exigência de apresentação de amostras em até 7 dias úteis após aceitação da proposta.
                </div>
            </div>

        @elseif ($abaAtiva === 'historico')
            <!-- Tabela de Histórico e Anti-Duplicação -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; color: #475569;">
                            <th style="padding: 0.75rem 1rem;">Assunto / Contratação</th>
                            <th style="padding: 0.75rem 1rem;">Tipo</th>
                            <th style="padding: 0.75rem 1rem;">Destinatário Simulado</th>
                            <th style="padding: 0.75rem 1rem;">Status</th>
                            <th style="padding: 0.75rem 1rem;">Feedback</th>
                            <th style="padding: 0.75rem 1rem;">Data/Hora</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historicoAlertas as $h)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #0f172a;">
                                    {{ $h->assunto }}
                                    <div style="font-size: 0.72rem; color: #64748b; font-weight: normal;">PNCP: {{ $h->numero_controle_pncp }}</div>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #475569;">
                                    {{ $h->tipo_alerta === 'oportunidade_perfil' ? 'Oportunidade' : 'Resumo Diário' }}
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #64748b;">
                                    {{ $h->destinatario_simulado }}
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <span style="background: #dbeafe; color: #1e40af; padding: 0.2rem 0.45rem; border-radius: 0.25rem; font-size: 0.72rem; font-weight: 700;">
                                        {{ str_replace('_', ' ', $h->status) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    @if ($h->feedback_alerta)
                                        <span style="background: #ecfdf5; color: #047857; padding: 0.2rem 0.45rem; border-radius: 0.25rem; font-size: 0.72rem; font-weight: 700;">
                                            {{ ucfirst(str_replace('_', ' ', $h->feedback_alerta)) }}
                                        </span>
                                    @else
                                        <span style="color: #9ca3af; font-size: 0.75rem;">Pendente</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #64748b;">
                                    {{ $h->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right;">
                                    <button type="button" wire:click="selecionarAlerta({{ $h->id }}); $set('abaAtiva', 'previa_email');" style="background: #065f46; color: #ffffff; border: none; padding: 0.3rem 0.65rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                        Ver Prévia
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2rem; text-align: center; color: #6b7280;">
                                    Nenhum alerta registrado no histórico.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
