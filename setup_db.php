<?php
// Script para rodar migrations básicas de bd
$host = "127.0.0.1";
$port = 3306;
$db_name = "biblioteca_vision";
$username = "root";
$password = "";

try {
    $conn = new mysqli($host, $username, $password, $db_name, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql1 = "CREATE TABLE IF NOT EXISTS turmas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql1) === TRUE) {
        echo "Tabela turmas criada com sucesso.\n";
    } else {
        echo "Erro ao criar tabela turmas: " . $conn->error . "\n";
    }

    // Insert some default classes to start with, ignore if already exists
    $sql2 = "INSERT IGNORE INTO turmas (nome) VALUES ('TDS26'), ('TDS27'), ('MEC10'), ('ELETRO15')";
    if ($conn->query($sql2) === TRUE) {
        echo "Turmas iniciais injetadas com sucesso.\n";
    } else {
        echo "Erro ao inserir turmas iniciais: " . $conn->error . "\n";
    }

    $conn->close();
} catch (Exception $e) {
    echo "Exceção capturada: " . $e->getMessage() . "\n";
}
