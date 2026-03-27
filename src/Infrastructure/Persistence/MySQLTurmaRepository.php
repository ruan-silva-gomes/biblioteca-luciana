<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\TurmaRepositoryInterface;
use App\Domain\Entities\Turma;
use mysqli;
use Exception;

/**
 * Implementação SQL da persistência de Turmas.
 * Camada de Infraestrutura: Conhece o Banco de Dados.
 */
class MySQLTurmaRepository implements TurmaRepositoryInterface
{
    public function __construct(private mysqli $db) {}

    public function getAll(): array
    {
        $result = $this->db->query("SELECT id, nome, created_at FROM turmas ORDER BY nome ASC");
        $turmas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $turmas[] = new Turma($row['id'], $row['nome'], $row['created_at']);
            }
        }
        return $turmas;
    }

    public function create(string $nome): bool|string
    {
        $stmt = $this->db->prepare("INSERT INTO turmas (nome) VALUES (?)");
        if (!$stmt) return $this->db->error;

        $stmt->bind_param("s", $nome);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $error = $stmt->error;
        $stmt->close();
        return $error;
    }

    public function delete(int $id): bool
    {
        // Pega o nome da turma para liberar os alunos (lógica migrada de Model/Turma)
        $stmtName = $this->db->prepare("SELECT nome FROM turmas WHERE id = ?");
        if (!$stmtName) return false;
        $stmtName->bind_param("i", $id);
        $stmtName->execute();
        $result = $stmtName->get_result();

        if ($result->num_rows === 0) {
            $stmtName->close();
            return false;
        }

        $turma = $result->fetch_assoc();
        $nome = $turma['nome'];
        $stmtName->close();

        $this->db->begin_transaction();
        try {
            // Remove logs de acesso dos alunos da turma
            $stmtLogs = $this->db->prepare("DELETE FROM acessos_log WHERE usuario_id IN (SELECT id FROM usuarios WHERE turma = ?)");
            if ($stmtLogs) {
                $stmtLogs->bind_param("s", $nome);
                $stmtLogs->execute();
                $stmtLogs->close();
            }

            // Remove os alunos da turma
            $stmtStudents = $this->db->prepare("DELETE FROM usuarios WHERE turma = ?");
            if ($stmtStudents) {
                $stmtStudents->bind_param("s", $nome);
                $stmtStudents->execute();
                $stmtStudents->close();
            }

            // Exclui a turma
            $stmtDelete = $this->db->prepare("DELETE FROM turmas WHERE id = ?");
            if ($stmtDelete) {
                $stmtDelete->bind_param("i", $id);
                $stmtDelete->execute();
                $stmtDelete->close();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getByName(string $nome): ?Turma
    {
        $stmt = $this->db->prepare("SELECT id, nome, created_at FROM turmas WHERE nome = ?");
        if (!$stmt) return null;

        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? new Turma($data['id'], $data['nome'], $data['created_at']) : null;
    }
}
