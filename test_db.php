<?php
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "CONEXÃO OK: MySQL está online na porta 3306 binária.";
} catch (Exception $e) {
    echo "ERRO DE CONEXÃO: " . $e->getMessage();
}
