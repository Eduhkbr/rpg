<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\LogEntry;

/**
 * Interface LogRepositoryInterface
 *
 * Define o contrato para a persistência de dados da entidade LogEntry.
 */
interface LogRepositoryInterface
{
    /**
     * Salva uma nova entrada de log na fonte de dados.
     *
     * @param LogEntry $logEntry O objeto LogEntry a ser salvo.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvar(LogEntry $logEntry): bool;

    /**
     * Busca todas as entradas de log para uma sala específica.
     *
     * @param int $idSala O ID da sala.
     * @return array Uma lista de objetos LogEntry, geralmente ordenada pela mais recente.
     */
    public function buscarPorSalaId(int $idSala): array;
}