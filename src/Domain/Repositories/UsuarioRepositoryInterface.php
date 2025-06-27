<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Usuario;

/**
 * Interface UsuarioRepositoryInterface
 *
 * Esta interface define o "contrato" para a persistência de dados da entidade Usuario.
 * Qualquer classe que queira atuar como um repositório de utilizadores DEVE implementar
 * estes métodos.
 */
interface UsuarioRepositoryInterface
{
    /**
     * Busca um utilizador pelo seu endereço de e-mail.
     *
     * @param string $email O e-mail a ser pesquisado.
     * @return Usuario|null Retorna um objeto Usuario se encontrado, ou null caso contrário.
     */
    public function buscarPorEmail(string $email): ?Usuario;

    /**
     * Busca um utilizador pelo seu ID único.
     *
     * @param int $id O ID do utilizador.
     * @return Usuario|null Retorna um objeto Usuario se encontrado, ou null caso contrário.
     */
    public function buscarPorId(int $id): ?Usuario;

    /**
     * NOVO MÉTODO: Busca um utilizador pelo seu código de verificação de e-mail.
     *
     * @param string $codigo O código de verificação.
     * @return Usuario|null Retorna um objeto Usuario se o código for válido, ou null caso contrário.
     */
    public function buscarPorCodigoVerificacao(string $codigo): ?Usuario;

    /**
     * Salva um objeto Usuario na fonte de dados.
     * Lida tanto com a criação (INSERT) quanto com a atualização (UPDATE).
     *
     * @param Usuario $usuario O objeto Usuario a ser salvo.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function salvar(Usuario $usuario): bool;

    /**
     * Deleta um utilizador da fonte de dados pelo seu ID.
     *
     * @param int $id O ID do utilizador a ser deletado.
     * @return bool Retorna true em caso de sucesso, false em caso de falha.
     */
    public function deletar(int $id): bool;
}