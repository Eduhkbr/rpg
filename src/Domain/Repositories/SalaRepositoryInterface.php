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

    /**
     * Busca todas as salas em que um determinado utilizador participa.
     *
     * @param int $idUsuario O ID do utilizador.
     * @return array Uma lista de objetos Sala.
     */
    public function buscarPorUsuarioId(int $idUsuario): array;

    /**
     * Busca uma sala pelo seu código de convite.
     * @param string código do convite
     */
    public function buscarPorCodigoConvite(string $codigo): ?Sala;

    /**
     * Conta quantos participantes uma sala possui.
     * @param int $idSala O ID da sala.
     */
    public function contarParticipantes(int $idSala): int;

    /**
     * Adiciona um novo utilizador à lista de participantes de uma sala.
     * @param int $idSala O ID da sala.
     * @param int $idUsuario O ID do utilizador.
     */
    public function adicionarParticipante(int $idSala, int $idUsuario): bool;
}