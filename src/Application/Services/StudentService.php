<?php

namespace App\Application\Services;

use App\Domain\Repositories\StudentRepositoryInterface;

/**
 * Serviço de Aplicação para Alunos e Acessos.
 * Centraliza a lógica de negócio separando-a do controller HTTP.
 */
class StudentService
{
    public function __construct(private StudentRepositoryInterface $repository) {}

    public function listAll(): array
    {
        return $this->repository->getAll();
    }

    public function register(array $data): array
    {
        if (empty($data['nome']) || empty($data['turma'])) {
            return ['success' => false, 'message' => 'Dados obrigatórios ausentes.'];
        }

        $success = $this->repository->create($data);
        return [
            'success' => $success,
            'message' => $success ? 'Usuário cadastrado com sucesso!' : 'Erro ao cadastrar usuário.'
        ];
    }

    public function recordAccess(int $studentId): bool
    {
        return $this->repository->logAccess($studentId);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateClass(int $id, string $className): bool
    {
        return $this->repository->updateClass($id, $className);
    }

    public function getDashboardStats(string $period = 'today'): array
    {
        return $this->repository->getDashboardStats($period);
    }

    public function listHistory(): array
    {
        return $this->repository->getHistory(100);
    }
}
