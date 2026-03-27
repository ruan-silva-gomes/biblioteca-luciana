<?php

namespace App\Application\Services;

use App\Domain\Repositories\TurmaRepositoryInterface;

/**
 * Serviço de Aplicação para Turmas.
 * Camada de Aplicação: Orquestra Use Cases.
 */
class TurmaService
{
    public function __construct(private TurmaRepositoryInterface $repository) {}

    public function listAll(): array
    {
        $turmas = $this->repository->getAll();
        // Converte entidades em arrays simples para o controller/JSON
        return array_map(fn($t) => $t->toArray(), $turmas);
    }

    public function create(string $nome): bool|string
    {
        if (empty($nome)) {
            return "O nome da turma não pode estar vazio.";
        }
        return $this->repository->create($nome);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
