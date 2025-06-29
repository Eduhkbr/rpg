<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Sala;
use App\Domain\Repositories\SalaRepositoryInterface;
use PDO;
use PDOException;

/**
 * Classe MySQLSalaRepository
 * Implementação concreta da SalaRepositoryInterface para MySQL.
 */
class MySQLSalaRepository implements SalaRepositoryInterface
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * {@inheritdoc}
     */
    public function salvar(Sala $sala): ?Sala
    {
        // Se a sala já tem um ID, é uma atualização.
        if ($sala->id !== null) {
            $sql = "UPDATE salas SET nome_sala = :nome_sala WHERE id = :id;";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome_sala', $sala->getNomeSala());
            $stmt->bindValue(':id', $sala->id, PDO::PARAM_INT);
            $stmt->execute();
            return $sala;
        }

        // Caso contrário, é uma criação.
        $this->conexao->beginTransaction();
        try {
            $sqlSala = "INSERT INTO salas (id_mestre, id_sistema, nome_sala, codigo_convite) 
                        VALUES (:id_mestre, :id_sistema, :nome_sala, :codigo_convite);";
            $stmtSala = $this->conexao->prepare($sqlSala);
            $stmtSala->bindValue(':id_mestre', $sala->idMestre, PDO::PARAM_INT);
            $stmtSala->bindValue(':id_sistema', $sala->idSistema, PDO::PARAM_INT);
            $stmtSala->bindValue(':nome_sala', $sala->getNomeSala());
            $stmtSala->bindValue(':codigo_convite', $sala->codigoConvite);
            $stmtSala->execute();
            $idNovaSala = (int)$this->conexao->lastInsertId();

            $sqlParticipante = "INSERT INTO participantes (id_sala, id_usuario) VALUES (:id_sala, :id_usuario);";
            $stmtParticipante = $this->conexao->prepare($sqlParticipante);
            $stmtParticipante->bindValue(':id_sala', $idNovaSala, PDO::PARAM_INT);
            $stmtParticipante->bindValue(':id_usuario', $sala->idMestre, PDO::PARAM_INT);
            $stmtParticipante->execute();

            $this->conexao->commit();
            return $this->mapearDadosParaSala(['id' => $idNovaSala] + (array)$sala);
        } catch (PDOException $e) {
            $this->conexao->rollBack();
            error_log("Erro no Repositório de Sala (salvar): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorUsuarioId(int $idUsuario): array
    {
        // Esta query foi reescrita para ser mais poderosa:
        // 1. Junta `salas` (s) com `participantes` (p) para filtrar pelo utilizador.
        // 2. Junta o resultado com `sistemas_rpg` (sr) para obter o nome do sistema.
        // 3. Usa uma subquery para contar o total de participantes para cada sala.
        $sql = "SELECT 
                    s.*, 
                    sr.nome_sistema,
                    (SELECT COUNT(*) FROM participantes p2 WHERE p2.id_sala = s.id) AS quantidade_jogadores
                FROM salas s
                JOIN participantes p ON s.id = p.id_sala
                JOIN sistemas_rpg sr ON s.id_sistema = sr.id
                WHERE p.id_usuario = :id_usuario
                ORDER BY s.data_criacao DESC;";

        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $salasDados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $salasInfo = [];
            foreach ($salasDados as $dados) {
                // Para cada linha do resultado, criamos uma estrutura que contém
                // o objeto Sala e os dados extras que a View precisa.
                $salasInfo[] = [
                    'sala' => $this->mapearDadosParaSala($dados),
                    'nomeSistema' => $dados['nome_sistema'],
                    'quantidadeJogadores' => $dados['quantidade_jogadores']
                ];
            }
            return $salasInfo;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (buscarPorUsuarioId): " . $e->getMessage());
            return [];
        }
    }


    /**
     * {@inheritdoc}
     */
    public function buscarPorId(int $idSala): ?Sala
    {
        $sql = "SELECT * FROM salas WHERE id = :id LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $idSala, PDO::PARAM_INT);
            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            return $dados ? $this->mapearDadosParaSala($dados) : null;
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (buscarPorId): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorCodigoConvite(string $codigo): ?Sala
    {
        $sql = "SELECT * FROM salas WHERE codigo_convite = :codigo LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            return $dados ? $this->mapearDadosParaSala($dados) : null;
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (buscarPorCodigoConvite): " . $e->getMessage());
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function contarParticipantes(int $idSala): int
    {
        $sql = "SELECT COUNT(*) FROM participantes WHERE id_sala = :id_sala;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (contarParticipantes): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function adicionarParticipante(int $idSala, int $idUsuario): bool
    {
        $sql = "INSERT INTO participantes (id_sala, id_usuario) VALUES (:id_sala, :id_usuario);";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (adicionarParticipante): " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removerParticipante(int $idSala, int $idUsuario): bool
    {
        $sql = "DELETE FROM participantes WHERE id_sala = :id_sala AND id_usuario = :id_usuario;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (removerParticipante): " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletar(int $idSala): bool
    {
        $sql = "DELETE FROM salas WHERE id = :id;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $idSala, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (deletar): " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function associarPersonagem(int $idSala, int $idUsuario, int $idPersonagem): bool
    {
        // Atualiza a coluna `id_personagem` para um participante específico numa sala.
        $sql = "UPDATE participantes SET id_personagem = :id_personagem 
                WHERE id_sala = :id_sala AND id_usuario = :id_usuario;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_personagem', $idPersonagem, PDO::PARAM_INT);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (associarPersonagem): " . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function buscarParticipante(int $idSala, int $idUsuario): ?array
    {
        $sql = "SELECT * FROM participantes WHERE id_sala = :id_sala AND id_usuario = :id_usuario LIMIT 1;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            // fetch() retorna os dados ou `false` se não encontrar nada.
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            // Retorna o array de dados se encontrado, ou null se não.
            return $dados ?: null;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (buscarParticipante): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Método auxiliar para mapear um array de dados do banco para um objeto Sala.
     * Evita a repetição de código.
     *
     * @param array $dados O array associativo vindo do fetch do PDO.
     * @return Sala
     */
    private function mapearDadosParaSala(array $dados): Sala
    {
        $idMestre = isset($dados['id_mestre']) && $dados['id_mestre'] !== null ? (int)$dados['id_mestre'] : 0;
        $idSistema = isset($dados['id_sistema']) && $dados['id_sistema'] !== null ? (int)$dados['id_sistema'] : 0;
        $nomeSala = isset($dados['nome_sala']) ? $dados['nome_sala'] : '';
        $codigoConvite = isset($dados['codigo_convite']) ? $dados['codigo_convite'] : '';
        $id = isset($dados['id']) ? (int)$dados['id'] : null;
        $ativa = isset($dados['ativa']) ? (bool)$dados['ativa'] : true;
        $dataCriacao = isset($dados['data_criacao']) ? $dados['data_criacao'] : null;
        return new Sala($idMestre, $idSistema, $nomeSala, $codigoConvite, $id, $ativa, $dataCriacao);
    }

}