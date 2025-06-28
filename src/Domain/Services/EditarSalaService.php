<?php

namespace App\Domain\Services;

use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use Exception;

/**
 * Classe EditarSalaService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a edição de uma sala existente.
 */
class EditarSalaService
{
    private SalaRepositoryInterface $salaRepository;

    public function __construct(SalaRepositoryInterface $salaRepository)
    {
        $this->salaRepository = $salaRepository;
    }

    /**
     * Executa o processo de edição de sala.
     *
     * @param int $idSala O ID da sala a ser editada.
     * @param int $idMestre O ID do utilizador que está a solicitar a edição.
     * @param string $novoNome O novo nome para a sala.
     * @return void
     * @throws AcessoNegadoException Se o utilizador não for o mestre da sala.
     * @throws Exception Se a sala não for encontrada ou se houver uma falha ao salvar.
     */
    public function executar(int $idSala, int $idMestre, string $novoNome): void
    {
        // 1. Busca a sala na base de dados.
        $sala = $this->salaRepository->buscarPorId($idSala);

        // 2. Verifica se a sala existe.
        if ($sala === null) {
            throw new Exception("Sala não encontrada.");
        }

        // 3. Regra de Negócio de Segurança: Apenas o mestre pode editar.
        if ($sala->idMestre !== $idMestre) {
            throw new AcessoNegadoException("Você não tem permissão para editar esta sala.");
        }

        // 4. Validação: O novo nome não pode estar vazio.
        if (empty(trim($novoNome))) {
            throw new Exception("O nome da sala não pode estar vazio.");
        }

        // 5. Lógica da Entidade: Atualiza o nome da sala.
        $sala->alterarNome($novoNome);

        // 6. Persistência: Usa o método salvar(), que fará um UPDATE.
        $sucesso = $this->salaRepository->salvar($sala);

        if (!$sucesso) {
            throw new Exception("Não foi possível atualizar a sala.");
        }
    }
}