<?php

namespace Tests\Unit;

use App\Services\CsvExportService;
use PHPUnit\Framework\TestCase;

class CsvExportTest extends TestCase
{
    public function test_sanitiza_formulas_maliciosas_corretamente(): void
    {
        // 1. Início com '=' (fórmula Excel comum)
        $campo1 = '=SUM(A1:A10)';
        $this->assertEquals("'=SUM(A1:A10)", CsvExportService::sanitizarCampo($campo1));

        // 2. Início com '+'
        $campo2 = '+12345';
        $this->assertEquals("'+12345", CsvExportService::sanitizarCampo($campo2));

        // 3. Início com '-'
        $campo3 = '-cmd|/C calc!A0';
        $this->assertEquals("'-cmd|/C calc!A0", CsvExportService::sanitizarCampo($campo3));

        // 4. Início com '@'
        $campo4 = '@SUM(1,2)';
        $this->assertEquals("'@SUM(1,2)", CsvExportService::sanitizarCampo($campo4));

        // 5. Início com tabulação ou retorno de carro
        $campo5 = "\t=1+1";
        $this->assertEquals("'\t=1+1", CsvExportService::sanitizarCampo($campo5));

        // 6. Texto limpo normal (não deve ser alterado)
        $campoLimpo = 'Aquisição de equipamentos de proteção individual';
        $this->assertEquals('Aquisição de equipamentos de proteção individual', CsvExportService::sanitizarCampo($campoLimpo));

        // 7. Campo nulo ou vazio
        $this->assertEquals('', CsvExportService::sanitizarCampo(null));
        $this->assertEquals('', CsvExportService::sanitizarCampo(''));
    }

    public function test_geracao_csv_com_bom_utf8(): void
    {
        $cabecalhos = ['Objeto', 'Preço'];
        $linhas = [
            ['=1+1', '100,50'],
            ['Luvas de Proteção', '25,00'],
        ];

        $csv = CsvExportService::gerarCsv($cabecalhos, $linhas, ';');

        // Deve conter o BOM UTF-8 no início
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Deve conter o apóstrofo de escape para o campo perigoso
        $this->assertStringContainsString("'=1+1", $csv);

        // Deve preservar acentuação corretamente
        $this->assertStringContainsString('Proteção', $csv);
    }
}
