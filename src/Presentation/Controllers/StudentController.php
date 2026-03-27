<?php

namespace App\Presentation\Controllers;

use App\Application\Services\StudentService;

/**
 * Controller de Alunos e Acessos.
 * Camada de Apresentação: Processa requisições HTTP para Alunos.
 */
class StudentController
{
    public function __construct(private StudentService $service) {}

    public function listAll(): array
    {
        return $this->service->listAll();
    }

    public function register(array $data): array
    {
        return $this->service->register($data);
    }

    public function recordAccess(int $studentId): array
    {
        $success = $this->service->recordAccess($studentId);
        return [
            'success' => $success,
            'message' => $success ? 'Acesso registrado com sucesso (Clean Arch)!' : 'Erro ao registrar acesso.'
        ];
    }

    public function delete(int $id): array
    {
        $success = $this->service->delete($id);
        return [
            'success' => $success,
            'message' => $success ? 'Usuário excluído definitivamente!' : 'Erro ao excluir usuário.'
        ];
    }

    public function updateClass(int $id, string $className): array
    {
        $success = $this->service->updateClass($id, $className);
        return [
            'success' => $success,
            'message' => $success ? 'Turma do aluno atualizada com sucesso!' : 'Erro ao atualizar turma.'
        ];
    }

    public function getDashboardStats(string $period = 'today'): array
    {
        return $this->service->getDashboardStats($period);
    }

    public function listHistory(): array
    {
        return $this->service->listHistory();
    }
}
