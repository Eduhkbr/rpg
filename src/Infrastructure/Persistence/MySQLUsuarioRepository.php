<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Usuario;
use App\Domain\Repositories\UsuarioRepositoryInterface;
use PDO;
use PDOException;

/**
 * Classe MySQLUsuarioRepository
 *
 * Implementação concreta da UsuarioRepositoryInterface para um banco de dados MySQL.
 * Esta classe é responsável por todo o código SQL relacionado à entidade Usuario.
 */
class MySQLUsuarioRepository implements UsuarioRepositoryInterface
{
    /**
     * @var PDO A conexão com o banco de dados.
     */
    private PDO $conexao;

    /**
     * O construtor recebe a conexão com o banco de dados como uma dependência.
     * Isso é conhecido como Injeção de Dependência.
     *
     * @param PDO $conexao Uma instância de PDO já configurada.
     */
    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            $dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dadosUsuario) {
                return null; // Usuário não encontrado.
            }

            // Cria e retorna um novo objeto Usuario com os dados do banco.
            return new Usuario(
                $dadosUsuario['nome_usuario'],
                $dadosUsuario['email'],
                $dadosUsuario['senha_hash'],
                $dadosUsuario['id'],
                (bool)$dadosUsuario['email_verificado'],
                $dadosUsuario['codigo_verificacao'],
                $dadosUsuario['data_cadastro']
            );
        } catch (PDOException $e) {
            // Em uma aplicação real, você deveria logar este erro.
            error_log("Erro no Repositório de Usuário (buscarPorEmail): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorId(int $id): ?Usuario
    {
        // Lógica similar a buscarPorEmail, mas usando o ID.
        $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dadosUsuario) {
                return null;
            }

            return new Usuario(
                $dadosUsuario['nome_usuario'],
                $dadosUsuario['email'],
                $dadosUsuario['senha_hash'],
                $dadosUsuario['id'],
                (bool)$dadosUsuario['email_verificado'],
                $dadosUsuario['codigo_verificacao'],
                $dadosUsuario['data_cadastro']
            );
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (buscarPorId): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function salvar(Usuario $usuario): bool
    {
        // Se o usuário já tem um ID, é uma atualização (UPDATE).
        if ($usuario->getId() !== null) {
            return $this->atualizar($usuario);
        }
        // Caso contrário, é uma criação (INSERT).
        return $this->criar($usuario);
    }

    /**
     * {@inheritdoc}
     */
    public function deletar(int $id): bool
    {
        $sql = "DELETE FROM usuarios WHERE id = :id;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (deletar): " . $e->getMessage());
            return false;
        }
    }

    // --- Métodos Privados de Ajuda ---

    private function criar(Usuario $usuario): bool
    {
        $sql = "INSERT INTO usuarios (nome_usuario, email, senha_hash, codigo_verificacao, email_verificado) 
                VALUES (:nome_usuario, :email, :senha_hash, :codigo_verificacao, :email_verificado);";

        try {
            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(':nome_usuario', $usuario->getNomeUsuario());
            $stmt->bindValue(':email', $usuario->getEmail());
            $stmt->bindValue(':senha_hash', $usuario->getSenhaHash());
            $stmt->bindValue(':codigo_verificacao', $usuario->getCodigoVerificacao());
            $stmt->bindValue(':email_verificado', $usuario->isEmailVerificado(), PDO::PARAM_BOOL);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (criar): " . $e->getMessage());
            return false;
        }
    }

    private function atualizar(Usuario $usuario): bool
    {
        $sql = "UPDATE usuarios SET 
                    nome_usuario = :nome_usuario, 
                    email = :email, 
                    senha_hash = :senha_hash, 
                    codigo_verificacao = :codigo_verificacao, 
                    email_verificado = :email_verificado 
                WHERE id = :id;";

        try {
            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(':nome_usuario', $usuario->getNomeUsuario());
            $stmt->bindValue(':email', $usuario->getEmail());
            $stmt->bindValue(':senha_hash', $usuario->getSenhaHash());
            $stmt->bindValue(':codigo_verificacao', $usuario->getCodigoVerificacao());
            $stmt->bindValue(':email_verificado', $usuario->isEmailVerificado(), PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (atualizar): " . $e->getMessage());
            return false;
        }
    }
}