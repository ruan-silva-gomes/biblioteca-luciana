<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/autoload.php';

use App\Config\Database;

$sql = "
SET FOREIGN_KEY_CHECKS=0;

-- Tabelas fundamentais do sistema
CREATE TABLE IF NOT EXISTS turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(255) NOT NULL,
  turma VARCHAR(50) NOT NULL,
  face_descriptor LONGTEXT NULL DEFAULT NULL,
  criado_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  rosto_cadastrado_at DATETIME NULL DEFAULT NULL,
  ultima_entrada_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS acessos_log (
  id INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id INT(11) NOT NULL,
  horario_entrada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  INDEX idx_usuario_acesso (usuario_id),
  CONSTRAINT fk_usuario_acesso
    FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelas auxiliares
CREATE TABLE IF NOT EXISTS attendance_logs (
  id INT(11) NOT NULL AUTO_INCREMENT,
  student_id INT(11) NOT NULL,
  data DATE NOT NULL,
  status ENUM('presente', 'falta') NULL DEFAULT 'falta',
  horario_registro TIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX student_id (student_id ASC),
  CONSTRAINT attendance_logs_ibfk_1
    FOREIGN KEY (student_id)
    REFERENCES usuarios (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
";

try {
  $db = (new Database())->getConnection();

  // Executa múltiplas queries
  if ($db->multi_query($sql)) {
    do {
      // Limpa os resultados das queries
      if ($result = $db->store_result()) {
        $result->free();
      }
    } while ($db->more_results() && $db->next_result());

    echo "Banco de dados atualizado com sucesso!\n";

    // Verifica as tabelas criadas
    $result = $db->query("SHOW TABLES");
    echo "Tabelas no banco de dados:\n";
    while ($row = $result->fetch_array()) {
      echo "- " . $row[0] . "\n";
    }
  } else {
    echo "Erro ao executar queries: " . $db->error . "\n";
  }
} catch (Exception $e) {
  echo "Erro: " . $e->getMessage() . "\n";
}
