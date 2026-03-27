<?php

namespace App\Application\Services;

use mysqli;
use Exception;

/**
 * Serviço de Exportação de Dados.
 * Camada de Aplicação: Contém a lógica de formatação de relatórios.
 */
class ExportService
{
    public function __construct(private mysqli $db) {}

    public function generateAccessCsv(?string $period = 'all', ?string $targetDate = null): void
    {
        $whereClause = "";
        $refDate = $targetDate && !empty($targetDate) ? $targetDate : date('Y-m-d');

        if ($period === 'today') {
            $whereClause = "WHERE DATE(al.horario_entrada) = '{$refDate}'";
        } elseif ($period === 'week') {
            $whereClause = "WHERE YEARWEEK(al.horario_entrada, 1) = YEARWEEK('{$refDate}', 1)";
        } elseif ($period === 'month') {
            $whereClause = "WHERE MONTH(al.horario_entrada) = MONTH('{$refDate}') AND YEAR(al.horario_entrada) = YEAR('{$refDate}')";
        }

        $sql = "SELECT al.id as log_id, u.nome, u.turma, al.horario_entrada 
                FROM acessos_log al
                JOIN usuarios u ON al.usuario_id = u.id
                {$whereClause}
                ORDER BY al.horario_entrada DESC";

        $result = $this->db->query($sql);
        if (!$result) throw new Exception("Erro consulta exportação: " . $this->db->error);

        $this->outputCsv('relatorio_acessos_' . date('Y-m-d_H-i') . '.csv', [
            ['ID do Log', 'Nome do Usuário', 'Turma', 'Data/Hora'],
            function() use ($result) {
                $rows = [];
                while ($data = $result->fetch_assoc()) {
                    $rows[] = [
                        $data['log_id'],
                        $data['nome'],
                        $data['turma'] ?? 'Sem Turma',
                        date('d/m/Y H:i:s', strtotime($data['horario_entrada']))
                    ];
                }
                return $rows;
            }
        ]);
    }

    public function generateStudentsCsv(): void
    {
        $sql = "SELECT id, nome, turma, rosto_cadastrado_at, ultima_entrada_at FROM usuarios ORDER BY nome ASC";
        $result = $this->db->query($sql);
        if (!$result) throw new Exception("Erro consulta exportação alunos.");

        $this->outputCsv('relatorio_alunos_' . date('Y-m-d_H-i') . '.csv', [
            ['ID', 'Nome', 'Turma', 'Cadastrado em', 'Último Acesso'],
            function() use ($result) {
                $rows = [];
                while ($data = $result->fetch_assoc()) {
                    $rows[] = [
                        $data['id'],
                        $data['nome'],
                        $data['turma'] ?? 'Sem Turma',
                        $data['rosto_cadastrado_at'] ? date('d/m/Y', strtotime($data['rosto_cadastrado_at'])) : 'N/A',
                        $data['ultima_entrada_at'] ? date('d/m/Y H:i:s', strtotime($data['ultima_entrada_at'])) : 'Nunca'
                    ];
                }
                return $rows;
            }
        ]);
    }

    private function outputCsv(string $filename, array $content): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        fputcsv($output, $content[0], ';');
        $rows = $content[1]();
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }
}
