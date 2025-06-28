<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Personagem;

/**
 * Interface PersonagemRepositoryInterface
 *
 * Define o contrato para a persistência de dados da entidade Personagem.
 * Qualquer classe que queira atuar como um repositório de personagens
 * DEVE implementar estes métodos.
 */
interface PersonagemRepositoryInterface
{
    /**
     * Salva um objeto Personagem na fonte de dados.
     * Lida tanto com a criação (INSERT) quanto com a atualização (UPDATE).
     *
     * @param Personagem $personagem O objeto Personagem a ser salvo.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvar(Personagem $personagem): bool;

    /**
     * Busca todos os personagens pertencentes a um determinado utilizador.
     *
     * @param int $idUsuario O ID do utilizador.
     * @return array Uma lista de objetos Personagem.
     */
    public function buscarPorUsuarioId(int $idUsuario): array;

    /**
     * Busca um personagem pelo seu ID único.
     *
     * @param int $id O ID do personagem.
     * @return Personagem|null Retorna um objeto Personagem se encontrado, ou null caso contrário.
     */
    public function buscarPorId(int $id): ?Personagem;
}