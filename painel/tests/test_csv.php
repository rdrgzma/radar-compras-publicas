<?php

require_once __DIR__ . '/../app/Services/CsvExportService.php';

use App\Services\CsvExportService;

echo "=== Testando CsvExportService ===\n";

// Teste 1: fórmula com '='
$res1 = CsvExportService::sanitizarCampo('=SUM(A1:A10)');
assert($res1 === "'=SUM(A1:A10)", "Falha no teste 1: obteve {$res1}");
echo "[PASS] Teste 1: '=' prefixado com apóstrofo\n";

// Teste 2: fórmula com '+'
$res2 = CsvExportService::sanitizarCampo('+12345');
assert($res2 === "'+12345", "Falha no teste 2");
echo "[PASS] Teste 2: '+' prefixado com apóstrofo\n";

// Teste 3: comando com '-'
$res3 = CsvExportService::sanitizarCampo('-cmd|/C calc!A0');
assert($res3 === "'-cmd|/C calc!A0", "Falha no teste 3");
echo "[PASS] Teste 3: '-' prefixado com apóstrofo\n";

// Teste 4: fórmula com '@'
$res4 = CsvExportService::sanitizarCampo('@SUM(1,2)');
assert($res4 === "'@SUM(1,2)", "Falha no teste 4");
echo "[PASS] Teste 4: '@' prefixado com apóstrofo\n";

// Teste 5: tabulação antes de fórmula
$res5 = CsvExportService::sanitizarCampo("\t=1+1");
assert($res5 === "'\t=1+1", "Falha no teste 5");
echo "[PASS] Teste 5: '\\t' prefixado com apóstrofo\n";

// Teste 6: texto limpo
$res6 = CsvExportService::sanitizarCampo('Aquisição de EPI');
assert($res6 === 'Aquisição de EPI', "Falha no teste 6");
echo "[PASS] Teste 6: texto limpo inalterado\n";

// Teste 7: geração de CSV completo com BOM
$csv = CsvExportService::gerarCsv(['Objeto', 'Preço'], [['=1+1', '100,50'], ['Luvas', '25,00']], ';');
assert(str_starts_with($csv, "\xEF\xBB\xBF"), "Falha no teste 7: BOM ausente");
assert(str_contains($csv, "'=1+1"), "Falha no teste 7: fórmula sem escape no CSV");
echo "[PASS] Teste 7: CSV gerado com BOM UTF-8 e proteção contra injeção\n";

echo "\nTODOS OS 7 TESTES DE SANITIZAÇÃO DE CSV FORAM APROVADOS COM SUCESSO!\n";
