<?php

namespace App\Application\Services;

use App\Domain\Repositories\StudentRepositoryInterface;
use App\Domain\Repositories\TurmaRepositoryInterface;
use Exception;

/**
 * Serviço de Integração com LívIA (IA).
 * Camada de Aplicação: Gerencia a comunicação com Gemini API.
 */
class ChatService
{
    private string $logFile;

    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private TurmaRepositoryInterface $turmaRepository
    ) {
        $this->logFile = __DIR__ . '/../../../chat_logs.json';
    }

    public function askLivia(string $userMessage): array
    {
        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            return ['success' => false, 'message' => 'LívIA: Chave de API do Google não configurada.'];
        }

        try {
            // Gera o resumo completo do sistema para máxima inteligência
            $summary = $this->generateSystemSummary();

            $systemPrompt = "Você é LívIA, a inteligência central e analista do SISTEMA BIBLIOTECA.\n";
            $systemPrompt .= "Você tem acesso total aos dados de alunos, turmas e logs de frequência.\n";
            $systemPrompt .= "OBJETIVO: Responder perguntas administrativa, gerar insights de frequência e ajudar na gestão.\n";
            $systemPrompt .= "ESTADO ATUAL DO SISTEMA:\n" . $summary;

            // Estrutura específica da API Gemini
            $data = [
                "contents" => [
                    [
                        "role" => "user",
                        "parts" => [
                            ["text" => $systemPrompt . "\n\nPergunta do Administrador: " . $userMessage]
                        ]
                    ]
                ]
            ];

            $response = $this->callGemini($data);
            $this->saveLog($userMessage, $response);

            return ['success' => true, 'response' => $response];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro na LívIA: ' . $e->getMessage()];
        }
    }

    private function generateSystemSummary(): string
    {
        // 1. Estatísticas Gerais
        $totalStudents = $this->studentRepository->getTotalCount();
        $turmas = $this->turmaRepository->getAll();
        $totalTurmas = count($turmas);
        $distribution = $this->studentRepository->getClassDistribution();

        // 2. Fluxo de Acesso (Hoje, Semana, Mês)
        $statsToday = $this->studentRepository->getDashboardStats('today');
        $statsWeek = $this->studentRepository->getDashboardStats('week');
        $statsMonth = $this->studentRepository->getDashboardStats('month');

        // 3. Histórico Recente
        $history = $this->studentRepository->getHistory(20);
        
        $str = "=== RESUMO EXECUTIVO ===\n";
        $str .= "Alunos Totais: $totalStudents | Turmas Totais: $totalTurmas\n";
        
        $str .= "\nDISTRIBUIÇÃO DE ALUNOS:\n";
        foreach ($distribution as $d) {
            $turmaNome = $d['turma'] ?? 'Sem Turma';
            $str .= "- $turmaNome: " . $d['count'] . " alunos\n";
        }

        $str .= "\nFLUXO DE ACESSOS:\n";
        $str .= "- Hoje: " . $statsToday['total_acessos'] . "\n";
        $str .= "- Esta Semana: " . $statsWeek['total_acessos'] . "\n";
        $str .= "- Este Mês: " . $statsMonth['total_acessos'] . "\n";

        $str .= "\nÚLTIMOS 20 ACESSOS REGISTRADOS:\n";
        foreach ($history as $h) {
            $str .= "- " . $h['nome'] . " (Turma: " . $h['turma'] . ") em " . date('d/m/Y H:i', strtotime($h['horario_entrada'])) . "\n";
        }

        return $str;
    }

    private function callGemini(array $data): string
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        $errorMsg = $result['error']['message'] ?? "Falha na resposta da API (HTTP $httpCode)";
        throw new Exception($errorMsg);
    }

    private function saveLog(string $userMsg, string $aiMsg): void
    {
        $logEntry = ['timestamp' => date('Y-m-d H:i:s'), 'user' => $userMsg, 'ai' => $aiMsg];
        $logs = file_exists($this->logFile) ? json_decode(file_get_contents($this->logFile), true) : [];
        $logs[] = $logEntry;
        file_put_contents($this->logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }
}
