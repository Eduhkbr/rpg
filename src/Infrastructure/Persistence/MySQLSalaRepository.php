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
}