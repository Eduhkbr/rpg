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
     * @return array Uma lista de arrays, cada um contendo o objeto 'sala',
     * o 'nomeSistema' e a 'quantidadeJogadores'.
     */
    public function buscarPorUsuarioId(int $idUsuario): array;

    /**
     * Busca uma sala pelo seu ID único.
     *
     * @param int $idSala O ID da sala.
     * @return Sala|null Retorna um objeto Sala se encontrado, ou null caso contrário.
     */
    public function buscarPorId(int $idSala): ?Sala;

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

    /**
     * Remove um utilizador da lista de participantes de uma sala.
     *
     * @param int $idSala O ID da sala da qual o utilizador irá sair.
     * @param int $idUsuario O ID do utilizador a ser removido.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function removerParticipante(int $idSala, int $idUsuario): bool;

    /**
     * Busca informações detalhadas de todos os participantes de uma sala.
     *
     * @param int $idSala O ID da sala.
     * @return array Uma lista de arrays com informações dos participantes (nome do utilizador, nome do personagem, etc.).
     */
    public function buscarParticipantesInfo(int $idSala): array;

    /**
     * Associa um personagem a um participante numa sala específica.
     *
     * @param int $idSala O ID da sala.
     * @param int $idUsuario O ID do utilizador (participante).
     * @param int $idPersonagem O ID do personagem a ser associado.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function associarPersonagem(int $idSala, int $idUsuario, int $idPersonagem): bool;

    /**
     * Busca os dados de um participante específico numa sala.
     *
     * @param int $idSala O ID da sala.
     * @param int $idUsuario O ID do utilizador.
     * @return array|null Retorna os dados do participante (incluindo id_personagem) ou null.
     */
    public function buscarParticipante(int $idSala, int $idUsuario): ?array;

    /**
     * Deleta uma sala da fonte de dados pelo seu ID.
     *
     * @param int $idSala O ID da sala a ser deletada.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function deletar(int $idSala): bool;
}