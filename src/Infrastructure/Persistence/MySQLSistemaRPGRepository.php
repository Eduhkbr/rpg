<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\SistemaRPG;
use App\Domain\Repositories\SistemaRPGRepositoryInterface;
use PDO;
use PDOException;

/**
 * Classe MySQLSistemaRPGRepository
 * Implementação concreta da SistemaRPGRepositoryInterface para MySQL.
 */
class MySQLSistemaRPGRepository implements SistemaRPGRepositoryInterface
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /**
     * {@inheritdoc}
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM sistemas_rpg ORDER BY nome_sistema ASC;";
        try {
            $stmt = $this->conexao->query($sql);
            $sistemasDados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sistemas = [];
            foreach ($sistemasDados as $dados) {
                $sistemas[] = new SistemaRPG(
                    $dados['nome_sistema'],
                    $dados['descricao'],
                    $dados['ficha_template_json'],
                    $dados['id']
                );
            }
            return $sistemas;

        } catch (PDOException $e) {
            error_log("Erro no Repositório de SistemaRPG (buscarTodos): " . $e->getMessage());
            return []; // Retorna um array vazio em caso de erro.
        }
    }
}