<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Personagem;
use App\Domain\Repositories\PersonagemRepositoryInterface;
use PDO;
use PDOException;

/**
 * Classe MySQLPersonagemRepository
 *
 * Implementação concreta da PersonagemRepositoryInterface para um banco de dados MySQL.
 */
class MySQLPersonagemRepository implements PersonagemRepositoryInterface
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * {@inheritdoc}
     */
    public function salvar(Personagem $personagem): bool
    {
        if ($personagem->getId() !== null) {
            return $this->atualizar($personagem);
        }
        return $this->criar($personagem);
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorUsuarioId(int $idUsuario): array
    {
        $sql = "SELECT p.*, s.nome_sistema FROM personagens p
                JOIN sistemas_rpg s ON p.id_sistema = s.id
                WHERE p.id_usuario = :id_usuario 
                ORDER BY p.nome_personagem ASC;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $personagensDados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $personagens = [];
            foreach ($personagensDados as $dados) {
                // Criamos uma estrutura que contém o objeto e o nome do sistema
                $personagens[] = [
                    'personagem' => $this->mapearDadosParaPersonagem($dados),
                    'nomeSistema' => $dados['nome_sistema']
                ];
            }
            return $personagens;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de Personagem (buscarPorUsuarioId): " . $e->getMessage());
            return [];
        }
    }

    // --- Métodos Privados de Ajuda ---

    private function criar(Personagem $personagem): bool
    {
        $sql = "INSERT INTO personagens (id_usuario, id_sistema, nome_personagem, ficha_json) 
                VALUES (:id_usuario, :id_sistema, :nome_personagem, :ficha_json);";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_usuario', $personagem->getIdUsuario(), PDO::PARAM_INT);
            $stmt->bindValue(':id_sistema', $personagem->getIdSistema(), PDO::PARAM_INT);
            $stmt->bindValue(':nome_personagem', $personagem->getNomePersonagem());
            $stmt->bindValue(':ficha_json', $personagem->getFichaJson());
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Personagem (criar): " . $e->getMessage());
            return false;
        }
    }

    private function atualizar(Personagem $personagem): bool
    {
        $sql = "UPDATE personagens SET 
                    nome_personagem = :nome_personagem, 
                    ficha_json = :ficha_json 
                WHERE id = :id AND id_usuario = :id_usuario;"; // Segurança extra
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome_personagem', $personagem->getNomePersonagem());
            $stmt->bindValue(':ficha_json', $personagem->getFichaJson());
            $stmt->bindValue(':id', $personagem->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $personagem->getIdUsuario(), PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Personagem (atualizar): " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorId(int $id): ?Personagem
    {
        $sql = "SELECT * FROM personagens WHERE id = :id LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            return $dados ? $this->mapearDadosParaPersonagem($dados) : null;
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Personagem (buscarPorId): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletar(int $idPersonagem): bool
    {
        $sql = "DELETE FROM personagens WHERE id = :id;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $idPersonagem, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Personagem (deletar): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método auxiliar para mapear um array de dados do banco para um objeto Personagem.
     */
    private function mapearDadosParaPersonagem(array $dados): Personagem
    {
        return new Personagem(
            $dados['id_usuario'],
            $dados['id_sistema'],
            $dados['nome_personagem'],
            $dados['ficha_json'],
            $dados['id']
        );
    }
}