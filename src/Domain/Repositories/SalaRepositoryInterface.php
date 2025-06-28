<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Sala;

/**
 * Interface SalaRepositoryInterface
 * Define o contrato para a persistência de dados da entidade Sala.
 */
interface SalaRepositoryInterface
{
    /**
     * Salva uma nova sala e o seu mestre como primeiro participante.
     *
     * @param Sala $sala O objeto Sala a ser salvo.
     * @return Sala|null Retorna o objeto Sala com o ID preenchido em caso de sucesso, ou null em caso de falha.
     */
    public function salvar(Sala $sala): ?Sala;
}