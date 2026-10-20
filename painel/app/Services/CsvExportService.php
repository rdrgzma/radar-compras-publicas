<?php

namespace App\Services;

class CsvExportService
{
    /**
     * Sanitiza strings para mitigar vulnerabilidades de CSV Injection (Formula Injection).
     * Caracteres perigosos como '=', '+', '-', '@', tabulação ou retorno de carro são prefixados com apóstrofo.
     */
    public static function sanitizarCampo(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $texto = (string) $valor;
        $textoTrim = ltrim($texto);

        // Lista de caracteres que disparam execução de fórmulas em planilhas eletrônicas
        $caracteresPerigosos = ['=', '+', '-', '@', "\t", "\r"];

        foreach ($caracteresPerigosos as $char) {
            if (str_starts_with($textoTrim, $char)) {
                return "'" . $texto;
            }
        }

        return $texto;
    }

    /**
     * Gera o conteúdo CSV formatado e protegido contra injeção.
     *
     * @param array $cabecalhos Nomes das colunas
     * @param array $linhas Matriz de dados
     * @param string $delimitador Delimitador de colunas (padrão ';' para Excel pt-BR)
     * @return string Conteúdo binário com BOM UTF-8
     */
    public static function gerarCsv(array $cabecalhos, array $linhas, string $delimitador = ';'): string
    {
        $handle = fopen('php://temp', 'r+');

        // Adiciona BOM UTF-8 para reconhecimento automático de acentuação no Microsoft Excel
        fwrite($handle, "\xEF\xBB\xBF");

        // Escreve cabeçalhos sanitizados
        $cabecalhosSanitizados = array_map([self::class, 'sanitizarCampo'], $cabecalhos);
        fputcsv($handle, $cabecalhosSanitizados, $delimitador);

        // Escreve linhas de dados sanitizadas
        foreach ($linhas as $linha) {
            $linhaSanitizada = array_map(function ($coluna) {
                if (is_numeric($coluna)) {
                    return $coluna;
                }
                return self::sanitizarCampo((string) $coluna);
            }, $linha);

            fputcsv($handle, $linhaSanitizada, $delimitador);
        }

        rewind($handle);
        $conteudo = stream_get_contents($handle);
        fclose($handle);

        return $conteudo;
    }
}
