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
        // Inicia uma transação para garantir a atomicidade da operação.
        $this->conexao->beginTransaction();

        try {
            // 1. Insere a nova sala na tabela `salas`.
            $sqlSala = "INSERT INTO salas (id_mestre, id_sistema, nome_sala, codigo_convite) 
                        VALUES (:id_mestre, :id_sistema, :nome_sala, :codigo_convite);";

            $stmtSala = $this->conexao->prepare($sqlSala);
            $stmtSala->bindValue(':id_mestre', $sala->idMestre, PDO::PARAM_INT);
            $stmtSala->bindValue(':id_sistema', $sala->idSistema, PDO::PARAM_INT);
            $stmtSala->bindValue(':nome_sala', $sala->nomeSala);
            $stmtSala->bindValue(':codigo_convite', $sala->codigoConvite);
            $stmtSala->execute();

            // 2. Obtém o ID da sala que acabámos de criar.
            $idNovaSala = (int)$this->conexao->lastInsertId();

            // 3. Insere o mestre como o primeiro participante na tabela `participantes`.
            $sqlParticipante = "INSERT INTO participantes (id_sala, id_usuario) VALUES (:id_sala, :id_usuario);";
            $stmtParticipante = $this->conexao->prepare($sqlParticipante);
            $stmtParticipante->bindValue(':id_sala', $idNovaSala, PDO::PARAM_INT);
            $stmtParticipante->bindValue(':id_usuario', $sala->idMestre, PDO::PARAM_INT);
            $stmtParticipante->execute();

            // 4. Se tudo correu bem, confirma a transação.
            $this->conexao->commit();

            // 5. Retorna um novo objeto Sala, agora com o ID que foi gerado pelo banco.
            return new Sala(
                $sala->idMestre,
                $sala->idSistema,
                $sala->nomeSala,
                $sala->codigoConvite,
                $idNovaSala,
                $sala->ativa,
                $sala->dataCriacao
            );

        } catch (PDOException $e) {
            // 6. Se algo deu errado, desfaz todas as operações da transação.
            $this->conexao->rollBack();
            error_log("Erro no Repositório de Sala (salvar): " . $e->getMessage());
            return null; // Retorna null para indicar a falha.
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorUsuarioId(int $idUsuario): array
    {
        // Esta query junta as tabelas `salas` e `participantes` para encontrar
        // todas as salas (s) associadas a um id de utilizador (p).
        $sql = "SELECT s.* FROM salas s
                JOIN participantes p ON s.id = p.id_sala
                WHERE p.id_usuario = :id_usuario
                ORDER BY s.data_criacao DESC;";

        try {
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $salasDados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $salas = [];
            foreach ($salasDados as $dados) {
                // Reutiliza o método de mapeamento para criar cada objeto Sala.
                $salas[] = $this->mapearDadosParaSala($dados);
            }
            return $salas;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de Sala (buscarPorUsuarioId): " . $e->getMessage());
            return []; // Retorna um array vazio em caso de erro.
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
        return new Sala(
            $dados['id_mestre'],
            $dados['id_sistema'],
            $dados['nome_sala'],
            $dados['codigo_convite'],
            $dados['id'],
            (bool)$dados['ativa'],
            $dados['data_criacao']
        );
    }
}