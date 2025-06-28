<?php

namespace App\Domain\Services;

use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe EditarPersonagemService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a edição de uma ficha de personagem existente.
 */
class EditarPersonagemService
{
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(PersonagemRepositoryInterface $personagemRepository)
    {
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Executa o processo de edição de personagem.
     *
     * @param int $idPersonagem O ID do personagem a ser editado.
     * @param int $idUsuario O ID do utilizador que está a solicitar a edição.
     * @param array $novosDadosFicha Um array associativo com os novos dados da ficha.
     * @return void
     * @throws AcessoNegadoException Se o utilizador não for o dono do personagem.
     * @throws Exception Se o personagem não for encontrado ou se houver uma falha ao salvar.
     */
    public function executar(int $idPersonagem, int $idUsuario, array $novosDadosFicha): void
    {
        // 1. Busca o personagem na base de dados.
        $personagem = $this->personagemRepository->buscarPorId($idPersonagem);

        // 2. Verifica se o personagem existe.
        if ($personagem === null) {
            throw new Exception("Personagem não encontrado.");
        }

        // 3. Regra de Negócio de Segurança: Verifica se o utilizador é o dono.
        if ($personagem->getIdUsuario() !== $idUsuario) {
            throw new AcessoNegadoException("Você não tem permissão para editar este personagem.");
        }

        // 4. Validação dos Dados: Garante que o nome não está vazio.
        $novoNome = trim($novosDadosFicha['nome_personagem'] ?? '');
        if (empty($novoNome)) {
            throw new Exception("O nome do personagem não pode ficar vazio.");
        }

        // 5. Lógica da Entidade: Atualiza o estado do objeto Personagem.
        $personagem->atualizarNome($novoNome);
        $personagem->atualizarFicha($novosDadosFicha);

        // 6. Persistência: Usa o método salvar(), que fará um UPDATE porque o objeto já tem um ID.
        $sucesso = $this->personagemRepository->salvar($personagem);

        if (!$sucesso) {
            throw new Exception("Não foi possível atualizar o personagem.");
        }
    }
}