<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Student;

/**
 * Interface StudentRepositoryInterface.
 * Define ações fundamentais para o gerenciamento de alunos e logs de acesso.
 */
interface StudentRepositoryInterface
{
    public function getAll(): array;
    public function getById(int $id): ?Student;
    public function create(array $data): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function updateClass(int $id, string $className): bool;
    public function logAccess(int $studentId): bool;
    public function getDashboardStats(string $period = 'today'): array;
    public function getHistory(int $limit = 50): array;
}
