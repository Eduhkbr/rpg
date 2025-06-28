<?php

namespace App\Domain\Repositories;

/**
 * Interface SistemaRPGRepositoryInterface
 * Define o contrato para a persistência de dados da entidade SistemaRPG.
 */
interface SistemaRPGRepositoryInterface
{
    /**
     * Busca todos os sistemas de RPG disponíveis.
     *
     * @return array Uma lista de objetos SistemaRPG.
     */
    public function buscarTodos(): array;
}