<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\StudentRepositoryInterface;
use App\Domain\Entities\Student;
use mysqli;

/**
 * Implementação SQL da persistência de Alunos e Logs.
 * Camada de Infraestrutura: Centraliza as queries SQL originais migradas do Model.
 */
class MySQLStudentRepository implements StudentRepositoryInterface
{
    private string $table = "usuarios";

    public function __construct(private mysqli $db) {}

    public function getAll(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT u.id, u.nome, u.turma, u.criado_at, u.rosto_cadastrado_at, u.ultima_entrada_at, u.face_descriptor,
                       (SELECT COUNT(*) FROM acessos_log al WHERE al.usuario_id = u.id AND DATE(al.horario_entrada) = ?) as daily_access_count,
                       (SELECT MAX(horario_entrada) FROM acessos_log al WHERE al.usuario_id = u.id AND DATE(al.horario_entrada) = ?) as last_entry
                FROM {$this->table} u 
                ORDER BY last_entry DESC, u.nome ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $data;
    }

    public function getById(int $id): ?Student
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if (!$data) return null;

        return new Student(
            $data['id'],
            $data['nome'],
            $data['turma'],
            $data['face_descriptor'],
            $data['criado_at'],
            $data['rosto_cadastrado_at'],
            $data['ultima_entrada_at']
        );
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nome, turma, face_descriptor, rosto_cadastrado_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("sss", $data['nome'], $data['turma'], $data['face_descriptor']);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET nome = ? WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $data['nome'], $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function delete(int $id): bool
    {
        // 1. Limpa logs (lógica migrada)
        $stmtLogs = $this->db->prepare("DELETE FROM acessos_log WHERE usuario_id = ?");
        if ($stmtLogs) {
            $stmtLogs->bind_param("i", $id);
            $stmtLogs->execute();
            $stmtLogs->close();
        }

        // 2. Remove o registro
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function updateClass(int $id, string $className): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET turma = ? WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $className, $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function logAccess(int $studentId): bool
    {
        $stmtLog = $this->db->prepare("INSERT INTO acessos_log (usuario_id) VALUES (?)");
        if ($stmtLog) {
            $stmtLog->bind_param("i", $studentId);
            $stmtLog->execute();
            $stmtLog->close();
        }

        $stmtUpdate = $this->db->prepare("UPDATE {$this->table} SET ultima_entrada_at = NOW() WHERE id = ?");
        if ($stmtUpdate) {
            $stmtUpdate->bind_param("i", $studentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }

        return true;
    }

    public function getDashboardStats(string $period = 'today'): array
    {
        $whereClause = "DATE(horario_entrada) = CURDATE()";
        
        if ($period === 'week') {
            $whereClause = "YEARWEEK(horario_entrada, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($period === 'month') {
            $whereClause = "MONTH(horario_entrada) = MONTH(CURDATE()) AND YEAR(horario_entrada) = YEAR(CURDATE())";
        }

        // 1. Total de acessos no período
        $sqlTotal = "SELECT COUNT(*) as total FROM acessos_log WHERE {$whereClause}";
        $resTotal = $this->db->query($sqlTotal);
        $totalAcessos = ($resTotal && $row = $resTotal->fetch_assoc()) ? (int)$row['total'] : 0;

        // 2. Fluxo horário (distribuição por horas do dia dentro do período)
        $sqlFlow = "SELECT HOUR(horario_entrada) as hour, COUNT(*) as count 
                    FROM acessos_log 
                    WHERE {$whereClause}
                    GROUP BY HOUR(horario_entrada) 
                    ORDER BY hour ASC";
        
        $resFlow = $this->db->query($sqlFlow);
        $flowData = array_fill(8, 10, 0);

        if ($resFlow) {
            while ($row = $resFlow->fetch_assoc()) {
                $hour = (int)$row['hour'];
                if ($hour >= 8 && $hour <= 17) $flowData[$hour] = (int)$row['count'];
            }
        }

        return [
            'total_acessos' => $totalAcessos,
            'flow_data' => array_values($flowData),
            'period' => $period
        ];
    }

    public function getHistory(int $limit = 50): array
    {
        $sql = "SELECT al.id, u.nome, u.turma, al.horario_entrada 
                FROM acessos_log al
                JOIN usuarios u ON al.usuario_id = u.id
                ORDER BY al.horario_entrada DESC LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $data;
    }

    public function getTotalCount(): int
    {
        $res = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        return ($res && $row = $res->fetch_assoc()) ? (int)$row['total'] : 0;
    }

    public function getClassDistribution(): array
    {
        $sql = "SELECT turma, COUNT(*) as count FROM {$this->table} GROUP BY turma ORDER BY count DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
