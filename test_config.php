<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/autoload.php';

use App\Config\Database;

header('Content-Type: text/plain; charset=utf-8');

echo "--- TESTE DE CONFIGURAÇÃO PORTÁTIL ---\n\n";

echo "1. Verificando BASE_URL automática:\n";
echo "   URL Detectada: " . BASE_URL . "\n";
echo "   (Dica: Se estiver em branco ou 'http://localhost', está correto para XAMPP padrão)\n\n";

echo "2. Verificando Variáveis do .env:\n";
echo "   DB_HOST: " . env('DB_HOST', 'NÃO DEFINIDO') . "\n";
echo "   DB_NAME: " . env('DB_NAME', 'NÃO DEFINIDO') . "\n";
echo "   GEMINI_API_KEY: " . (GEMINI_API_KEY ? 'CONFIGURADA (OK)' : 'NÃO CONFIGURADA') . "\n\n";

echo "3. Testando Conexão com Banco de Dados (mysqli):\n";
try {
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        echo "   CONEXÃO COM O BANCO REALIZADA COM SUCESSO!\n";
        echo "   Versão do Servidor: " . $conn->server_info . "\n";
    }
} catch (Exception $e) {
    echo "   ERRO NA CONEXÃO: " . $e->getMessage() . "\n";
}

echo "\n--- FIM DO TESTE ---";
