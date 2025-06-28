<?php

namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe ExpulsarJogadorService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para um mestre expulsar um jogador de uma sala.
 */
class ExpulsarJogadorService
{
    private SalaRepositoryInterface $salaRepository;

    public function __construct(SalaRepositoryInterface $salaRepository)
    {
        $this->salaRepository = $salaRepository;
    }

    /**
     * Executa o processo de expulsão.
     *
     * @param int $idSala O ID da sala.
     * @param int $idMestre O ID do utilizador que está a tentar expulsar (deve ser o mestre).
     * @param int $idJogadorAlvo O ID do jogador a ser expulso.
     * @return void
     * @throws AcessoNegadoException Se o solicitante não for o mestre.
     * @throws Exception Se o mestre tentar expulsar a si mesmo ou se houver outra falha.
     */
    public function executar(int $idSala, int $idMestre, int $idJogadorAlvo): void
    {
        // Regra de Negócio: O mestre não pode expulsar a si mesmo.
        if ($idMestre === $idJogadorAlvo) {
            throw new Exception("O mestre não pode expulsar a si mesmo.");
        }

        // Regra de Segurança: Apenas o mestre da sala pode expulsar jogadores.
        $sala = $this->salaRepository->buscarPorId($idSala);
        if ($sala === null || $sala->idMestre !== $idMestre) {
            throw new AcessoNegadoException("Apenas o mestre da sala pode expulsar jogadores.");
        }

        $sucesso = $this->salaRepository->removerParticipante($idSala, $idJogadorAlvo);

        if (!$sucesso) {
            throw new Exception("Não foi possível expulsar o jogador. Ele pode já não ser um participante.");
        }
    }
}