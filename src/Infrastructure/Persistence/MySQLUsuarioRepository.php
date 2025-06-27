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
 */
class MySQLUsuarioRepository implements UsuarioRepositoryInterface
{
    private PDO $conexao;

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
                return null;
            }

            return $this->mapearDadosParaUsuario($dadosUsuario);
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (buscarPorEmail): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dadosUsuario) {
                return null;
            }

            return $this->mapearDadosParaUsuario($dadosUsuario);
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (buscarPorId): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     * NOVO MÉTODO IMPLEMENTADO
     */
    public function buscarPorCodigoVerificacao(string $codigo): ?Usuario
    {
        $sql = "SELECT * FROM usuarios WHERE codigo_verificacao = :codigo AND email_verificado = FALSE LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $dadosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dadosUsuario) {
                return null; // Nenhum utilizador encontrado com este código.
            }

            // Reutiliza o método de mapeamento para criar o objeto Usuario.
            return $this->mapearDadosParaUsuario($dadosUsuario);
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Usuário (buscarPorCodigoVerificacao): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function salvar(Usuario $usuario): bool
    {
        if ($usuario->getId() !== null) {
            return $this->atualizar($usuario);
        }
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

    /**
     * Método auxiliar para mapear um array de dados do banco para um objeto Usuario.
     * Evita a repetição de código nos métodos de busca.
     *
     * @param array $dados O array associativo vindo do fetch do PDO.
     * @return Usuario
     */
    private function mapearDadosParaUsuario(array $dados): Usuario
    {
        return new Usuario(
            $dados['nome_usuario'],
            $dados['email'],
            $dados['senha_hash'],
            $dados['id'],
            (bool)$dados['email_verificado'],
            $dados['codigo_verificacao'],
            $dados['data_cadastro']
        );
    }
}