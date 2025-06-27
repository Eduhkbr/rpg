<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Usuario;

/**
 * Interface UsuarioRepositoryInterface
 *
 * Esta interface define o "contrato" para a persistência de dados da entidade Usuario.
 * Qualquer classe que queira atuar como um repositório de usuários (seja para MySQL,
 * um arquivo de texto, ou qualquer outra forma de armazenamento) DEVE implementar
 * estes métodos.
 *
 * O domínio da aplicação dependerá desta interface, não de uma implementação concreta.
 * Isso é um pilar da Arquitetura Hexagonal e do Princípio da Inversão de Dependência (SOLID).
 */
interface UsuarioRepositoryInterface
{
    /**
     * Busca um usuário pelo seu endereço de e-mail.
     *
     * @param string $email O e-mail a ser pesquisado.
     * @return Usuario|null Retorna um objeto Usuario se encontrado, ou null caso contrário.
     */
    public function buscarPorEmail(string $email): ?Usuario;

    /**
     * Busca um usuário pelo seu ID único.
     *
     * @param int $id O ID do usuário.
     * @return Usuario|null Retorna um objeto Usuario se encontrado, ou null caso contrário.
     */
    public function buscarPorId(int $id): ?Usuario;

    /**
     * Salva um objeto Usuario na fonte de dados.
     * Este metodo lida tanto com a criação (INSERT) de um novo usuário
     * quanto com a atualização (UPDATE) de um usuário existente.
     *
     * @param Usuario $usuario O objeto Usuario a ser salvo.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvar(Usuario $usuario): bool;

    /**
     * Deleta um usuário da fonte de dados pelo seu ID.
     *
     * @param int $id O ID do usuário a ser deletado.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function deletar(int $id): bool;
}