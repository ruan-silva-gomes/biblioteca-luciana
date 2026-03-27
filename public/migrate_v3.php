<?php
// Script de Migração Final (V2) - Corrigido com porta 3306 e tag PHP
$db = new mysqli('127.0.0.1', 'root', '', 'library_vision', 3306);
if ($db->connect_error) {
    die(json_encode(['success' => false, 'message' => "Conexão falhou (Porta 3306): " . $db->connect_error]));
}

// 1. Drop index se existir (Silencia erro se não existir)
$db->query("ALTER TABLE students DROP INDEX idx_registration");

// 2. Drop colunas (Silencia erro se já foram removidas)
$db->query("ALTER TABLE students DROP COLUMN registration_number");
$db->query("ALTER TABLE students DROP COLUMN email");

$response = [];
if ($db->error) {
    $response = ['success' => false, 'message' => "Aviso/Erro: " . $db->error];
} else {
    $response = ['success' => true, 'message' => "Banco de dados atualizado com sucesso (ou já estava atualizado)."];
}
$db->close();
header('Content-Type: application/json');
echo json_encode($response);
unlink(__FILE__); // Deletar após sucesso
