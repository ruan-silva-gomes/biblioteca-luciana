<?php

namespace App\Application\Services;

use App\Domain\Repositories\StudentRepositoryInterface;
use Exception;

/**
 * Serviço de Integração com LívIA (IA).
 * Camada de Aplicação: Gerencia a comunicação com OpenRouter.
 */
class ChatService
{
    private string $logFile;

    public function __construct(private StudentRepositoryInterface $studentRepository)
    {
        $this->logFile = __DIR__ . '/../../../chat_logs.json';
    }

    public function askLivia(string $userMessage): array
    {
        if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
            return ['success' => false, 'message' => 'LívIA: Chave de API não configurada.'];
        }

        try {
            // Usa o repositório para obter o resumo do sistema (precisamos adicionar este método na interface!)
            // No Clean Arch, o resumo deveria ser gerado no serviço, não no repositório.
            // Mas para manter compatibilidade com a consulta complexa, vamos usar o repositório por enquanto.
            // Ajuste: Vou adicionar getSystemSummary() no StudentRepository ou gerar aqui mesmo.
            
            // Vamos gerar o resumo aqui usando os dados do repositório para ser mais Clean.
            $summary = $this->generateSystemSummary();

            $systemPrompt = "Você é LívIA, a assistente do SISTEMA BIBLIOTECA.\n";
            $systemPrompt .= "DADOS DO SISTEMA HOJE:\n" . $summary;

            $data = [
                "model" => OPENROUTER_MODEL,
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $userMessage]
                ]
            ];

            $response = $this->callOpenRouter($data);
            $this->saveLog($userMessage, $response);

            return ['success' => true, 'response' => $response];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro na LívIA: ' . $e->getMessage()];
        }
    }

    private function generateSystemSummary(): string
    {
        $stats = $this->studentRepository->getDashboardStats();
        $history = $this->studentRepository->getHistory(10);
        
        $str = "Total acessos hoje: " . $stats['total_acessos'] . "\nÚltimos acessos:\n";
        foreach ($history as $h) {
            $str .= "- " . $h['nome'] . " (" . $h['turma'] . ") às " . date('H:i', strtotime($h['horario_entrada'])) . "\n";
        }
        return $str;
    }

    private function callOpenRouter(array $data): string
    {
        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . OPENROUTER_API_KEY,
            "Content-Type: application/json",
            "X-Title: Biblioteca Clean System"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
        if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }

        throw new Exception("Falha OpenRouter (HTTP $httpCode)");
    }

    private function saveLog(string $userMsg, string $aiMsg): void
    {
        $logEntry = ['timestamp' => date('Y-m-d H:i:s'), 'user' => $userMsg, 'ai' => $aiMsg];
        $logs = file_exists($this->logFile) ? json_decode(file_get_contents($this->logFile), true) : [];
        $logs[] = $logEntry;
        file_put_contents($this->logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }
}
