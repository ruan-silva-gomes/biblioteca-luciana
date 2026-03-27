// Script de Migração Final (V2)
$db = new mysqli('127.0.0.1', 'root', '', 'library_vision', 3306);
if ($db->connect_error) {
die(json_encode(['success' => false, 'message' => "Conexão falhou (Porta 3306): " . $db->connect_error]));
}

// 1. Drop index se existir
$db->query("ALTER TABLE students DROP INDEX idx_registration");

// 2. Drop colunas
$db->query("ALTER TABLE students DROP COLUMN registration_number");
$db->query("ALTER TABLE students DROP COLUMN email");

$response = [];
if ($db->error) {
$response = ['success' => false, 'message' => "Erro: " . $db->error];
} else {
$response = ['success' => true, 'message' => "Banco de dados atualizado com sucesso!"];
}
$db->close();
header('Content-Type: application/json');
echo json_encode($response);
// O arquivo será deletado manualmente após a execução para evitar 404 precoce