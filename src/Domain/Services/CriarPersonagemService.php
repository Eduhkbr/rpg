<?php

namespace App\Domain\Services;

use App\Domain\Entities\Personagem;
use App\Domain\Repositories\PersonagemRepositoryInterface;
use Exception;

/**
 * Classe CriarPersonagemService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para a criação de uma nova ficha de personagem.
 */
class CriarPersonagemService
{
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(PersonagemRepositoryInterface $personagemRepository)
    {
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Executa o processo de criação de personagem.
     *
     * @param int $idUsuario O ID do utilizador que está a criar o personagem.
     * @param int $idSistema O ID do sistema de RPG escolhido.
     * @param array $dadosFicha Um array associativo com todos os dados da ficha, incluindo o nome.
     * @return Personagem Retorna o objeto Personagem recém-criado.
     * @throws Exception Se ocorrer uma falha na validação ou ao salvar.
     */
    public function executar(int $idUsuario, int $idSistema, array $dadosFicha): Personagem
    {
        // 1. Validação dos Dados Essenciais
        $nomePersonagem = trim($dadosFicha['nome_personagem'] ?? '');
        if (empty($nomePersonagem)) {
            throw new Exception("O nome do personagem é obrigatório.");
        }

        // (Opcional: aqui poderíamos adicionar mais validações, como verificar se o idSistema existe)

        // 2. Preparação dos Dados: Converte o array da ficha para uma string JSON.
        $fichaJson = json_encode($dadosFicha);
        if ($fichaJson === false) {
            throw new Exception("Falha ao processar os dados da ficha do personagem.");
        }

        // 3. Criação da Entidade
        $novoPersonagem = new Personagem(
            $idUsuario,
            $idSistema,
            $nomePersonagem,
            $fichaJson
        );

        // 4. Persistência: Usa o repositório para salvar o novo personagem.
        $sucesso = $this->personagemRepository->salvar($novoPersonagem);
        if (!$sucesso) {
            throw new Exception("Não foi possível salvar o personagem na base de dados.");
        }

        // 5. Retorno: Devolve o objeto Personagem completo.
        // Embora não estejamos a retornar o ID aqui, o registo já está na base de dados.
        return $novoPersonagem;
    }
}