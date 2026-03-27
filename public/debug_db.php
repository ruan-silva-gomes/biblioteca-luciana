<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

use App\Config\Database;

try {
    $db = (new Database())->getConnection();
    echo "<h1>Diagnóstico de Banco de Dados</h1>";

    $res = $db->query("DESCRIBE students");
    echo "<h2>Colunas em 'students':</h2><ul>";
    while ($row = $res->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";

    $res = $db->query("SHOW CREATE TABLE students");
    $row = $res->fetch_assoc();
    echo "<h2>Create Table 'students':</h2><pre>" . $row['Create Table'] . "</pre>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
