<?php

namespace App\Config;

use mysqli;
use Exception;

/**
 * MOTOR DE CONEXÃO (Database Layer)
 * 
 * Esta classe é responsável por abrir o túnel de comunicação entre o PHP 
 * e o servidor MySQL (XAMPP). Utiliza a biblioteca nativa mysqli.
 */
class Database
{
    // --- Configurações de Acesso ao Servidor ---
    private string $host = "127.0.0.1";           // Endereço local (localhost)
    private string $port = "3306";                // Porta customizada do MariaDB/MySQL no XAMPP
    private string $db_name = "biblioteca_vision"; // Nome do banco de dados criado
    private string $username = "root";            // Usuário padrão do XAMPP
    private string $password = "";                // Senha padrão (vazia) do XAMPP

    // Link ativo da conexão
    public ?mysqli $conn = null;

    /**
     * getConnection()
     * Abre e configura a conexão com o banco.
     * 
     * @return mysqli Objeto de conexão pronto para uso.
     * @throws Exception Caso o MySQL esteja desligado ou as credenciais estejam erradas.
     */
    public function getConnection(): mysqli
    {
        try {
            // Tenta instanciar o objeto mysqli
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                (int) $this->port
            );

            // Se houver qualquer falha no aperto de mão (handshake), lança erro
            if ($this->conn->connect_error) {
                throw new Exception(
                    "FALHA DE CONEXÃO: " . $this->conn->connect_error .
                    ". Verifique se o MySQL (Porta 3306) está iniciado no painel do XAMPP."
                );
            }

            // Define o suporte para acentuação brasileira (UTF-8 Unicode)
            $this->conn->set_charset("utf8mb4");

            return $this->conn;
        } catch (Exception $e) {
            // Repassa o erro para ser tratado no api.php
            throw $e;
        }
    }
}
