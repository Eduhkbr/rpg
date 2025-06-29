<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\LogEntry;
use App\Domain\Repositories\LogRepositoryInterface;
use PDO;
use PDOException;

/**
 * Classe MySQLLogRepository
 *
 * Implementação concreta da LogRepositoryInterface para um banco de dados MySQL.
 */
class MySQLLogRepository implements LogRepositoryInterface
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * {@inheritdoc}
     */
    public function salvar(LogEntry $logEntry): bool
    {
        $sql = "INSERT INTO logs_jogo (id_sala, autor_nome, tipo_log, mensagem) 
                VALUES (:id_sala, :autor_nome, :tipo_log, :mensagem);";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $logEntry->idSala, PDO::PARAM_INT);
            $stmt->bindValue(':autor_nome', $logEntry->autorNome);
            $stmt->bindValue(':tipo_log', $logEntry->tipoLog);
            $stmt->bindValue(':mensagem', $logEntry->mensagem);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro no Repositório de Log (salvar): " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorSalaId(int $idSala): array
    {
        $sql = "SELECT * FROM logs_jogo WHERE id_sala = :id_sala ORDER BY timestamp ASC;";
        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_sala', $idSala, PDO::PARAM_INT);
            $stmt->execute();

            $logsDados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $logs = [];
            foreach ($logsDados as $dados) {
                $logs[] = $this->mapearDadosParaLogEntry($dados);
            }
            return $logs;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de Log (buscarPorSalaId): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Método auxiliar para mapear um array de dados do banco para um objeto LogEntry.
     */
    private function mapearDadosParaLogEntry(array $dados): LogEntry
    {
        return new LogEntry(
            $dados['id_sala'],
            $dados['autor_nome'],
            $dados['tipo_log'],
            $dados['mensagem'],
            $dados['id'],
            $dados['timestamp']
        );
    }
}