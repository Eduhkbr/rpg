<?php

namespace App\Domain\Services;

use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe DeletarPersonagemService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a exclusão de uma ficha de personagem.
 */
class DeletarPersonagemService
{
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(PersonagemRepositoryInterface $personagemRepository)
    {
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Executa o processo de exclusão de personagem.
     *
     * @param int $idPersonagem O ID do personagem a ser excluído.
     * @param int $idUsuario O ID do utilizador que está a solicitar a exclusão.
     * @return void
     * @throws AcessoNegadoException Se o utilizador não for o dono do personagem.
     * @throws Exception Se o personagem não for encontrado ou se houver uma falha ao excluir.
     */
    public function executar(int $idPersonagem, int $idUsuario): void
    {
        // 1. Busca o personagem na base de dados.
        $personagem = $this->personagemRepository->buscarPorId($idPersonagem);

        // 2. Verifica se o personagem existe.
        if ($personagem === null) {
            throw new Exception("Personagem não encontrado.");
        }

        // 3. Regra de Negócio de Segurança: Verifica se o utilizador é o dono.
        if ($personagem->getIdUsuario() !== $idUsuario) {
            throw new AcessoNegadoException("Você não tem permissão para excluir este personagem.");
        }

        // 4. Persistência: Se todas as verificações passaram, chama o repositório para excluir.
        $sucesso = $this->personagemRepository->deletar($idPersonagem);

        if (!$sucesso) {
            throw new Exception("Não foi possível excluir o personagem.");
        }
    }
}