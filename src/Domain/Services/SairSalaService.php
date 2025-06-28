<?php

namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use Exception;

/**
 * Classe SairSalaService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para um jogador sair de uma sala.
 */
class SairSalaService
{
    private SalaRepositoryInterface $salaRepository;

    public function __construct(SalaRepositoryInterface $salaRepository)
    {
        $this->salaRepository = $salaRepository;
    }

    /**
     * Executa o processo de um jogador sair de uma sala.
     *
     * @param int $idSala O ID da sala.
     * @param int $idUsuario O ID do próprio utilizador que está a sair.
     * @return void
     * @throws Exception Se o mestre tentar sair ou se houver uma falha.
     */
    public function executar(int $idSala, int $idUsuario): void
    {
        // Regra de Negócio: O mestre não pode usar esta funcionalidade para sair.
        // Ele deve excluir a sala.
        $sala = $this->salaRepository->buscarPorId($idSala);
        if ($sala && $sala->idMestre === $idUsuario) {
            throw new Exception("O mestre não pode sair da sala. A sala deve ser excluída.");
        }

        $sucesso = $this->salaRepository->removerParticipante($idSala, $idUsuario);

        if (!$sucesso) {
            throw new Exception("Não foi possível sair da sala. Você pode já não ser um participante.");
        }
    }
}