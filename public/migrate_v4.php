<?php
// Script de Migração Final (V4) - Tentativa com Host:Port
$db = new mysqli('127.0.0.1', 'root', '', 'library_vision', 3306);
if ($db->connect_error) {
    die("Erro de conexão: " . $db->connect_error);
}

// 1. Drop index
$db->query("ALTER TABLE students DROP INDEX idx_registration");

// 2. Drop colunas
$db->query("ALTER TABLE students DROP COLUMN registration_number");
$db->query("ALTER TABLE students DROP COLUMN email");

if ($db->error) {
    echo "Erro: " . $db->error;
} else {
    echo "Sucesso: Colunas removidas.";
}
$db->close();
