<?php


namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe AssociarPersonagemService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para um jogador associar um dos seus
 * personagens a uma sala em que participa.
 */
class AssociarPersonagemService
{
    private SalaRepositoryInterface $salaRepository;
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(
        SalaRepositoryInterface       $salaRepository,
        PersonagemRepositoryInterface $personagemRepository
    )
    {
        $this->salaRepository = $salaRepository;
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Executa o processo de associação de personagem.
     *
     * @param int $idSala O ID da sala.
     * @param int $idUsuario O ID do utilizador que está a fazer a associação.
     * @param int $idPersonagem O ID do personagem a ser associado.
     * @return void
     * @throws AcessoNegadoException Se o personagem não pertencer ao utilizador.
     * @throws Exception Se ocorrerem outras falhas de validação.
     */
    public function executar(int $idSala, int $idUsuario, int $idPersonagem): void
    {
        // 1. Busca a sala para garantir que ela existe e para verificar o sistema de jogo.
        $sala = $this->salaRepository->buscarPorId($idSala);
        if ($sala === null) {
            throw new Exception("A sala em que está a tentar entrar não foi encontrada.");
        }

        // 2. Busca o personagem para garantir que ele existe e para verificar o dono.
        $personagem = $this->personagemRepository->buscarPorId($idPersonagem);
        if ($personagem === null) {
            throw new Exception("O personagem selecionado não foi encontrado.");
        }

        // 3. Regra de Negócio de Segurança: O personagem deve pertencer ao utilizador.
        if ($personagem->getIdUsuario() !== $idUsuario) {
            throw new AcessoNegadoException("Você só pode selecionar personagens que lhe pertencem.");
        }

        // 4. Regra de Negócio de Compatibilidade: O sistema do personagem deve ser o mesmo da sala.
        if ($personagem->getIdSistema() !== $sala->idSistema) {
            throw new Exception("Este personagem não é compatível com o sistema de jogo desta sala.");
        }

        // 5. Persistência: Se todas as regras passaram, associa o personagem ao participante.
        $sucesso = $this->salaRepository->associarPersonagem($idSala, $idUsuario, $idPersonagem);

        if (!$sucesso) {
            throw new Exception("Ocorreu um erro ao associar o seu personagem à sala.");
        }
    }
}