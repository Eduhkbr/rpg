<?php

namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe DeletarSalaService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a exclusão de uma sala de jogo.
 */
class DeletarSalaService
{
    private SalaRepositoryInterface $salaRepository;

    public function __construct(SalaRepositoryInterface $salaRepository)
    {
        $this->salaRepository = $salaRepository;
    }

    /**
     * Executa o processo de exclusão de sala.
     *
     * @param int $idSala O ID da sala a ser excluída.
     * @param int $idMestre O ID do utilizador que está a solicitar a exclusão.
     * @return void
     * @throws AcessoNegadoException Se o utilizador não for o mestre da sala.
     * @throws Exception Se a sala não for encontrada ou se houver uma falha ao excluir.
     */
    public function executar(int $idSala, int $idMestre): void
    {
        // 1. Busca a sala na base de dados.
        $sala = $this->salaRepository->buscarPorId($idSala);

        // 2. Verifica se a sala existe.
        if ($sala === null) {
            throw new Exception("Sala não encontrada.");
        }

        // 3. Regra de Negócio de Segurança: Apenas o mestre pode excluir.
        if ($sala->idMestre !== $idMestre) {
            throw new AcessoNegadoException("Você não tem permissão para excluir esta sala.");
        }

        // 4. Persistência: Chama o repositório para excluir.
        $sucesso = $this->salaRepository->deletar($idSala);

        if (!$sucesso) {
            throw new Exception("Não foi possível excluir a sala.");
        }
    }
}