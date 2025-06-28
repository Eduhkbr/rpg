<?php

namespace App\Application\Controllers;

use App\Domain\Services\CriarSalaService;
use App\Domain\Repositories\SistemaRPGRepositoryInterface;
use Exception;

/**
 * Classe SalaController
 *
 * Responsável por receber as requisições HTTP relacionadas às salas de jogo.
 */
class SalaController
{
    private CriarSalaService $criarSalaService;
    private SistemaRPGRepositoryInterface $sistemaRPGRepository;

    public function __construct(
        CriarSalaService $criarSalaService,
        SistemaRPGRepositoryInterface $sistemaRPGRepository
    ) {
        $this->criarSalaService = $criarSalaService;
        $this->sistemaRPGRepository = $sistemaRPGRepository;
    }

    /**
     * Exibe o formulário para a criação de uma nova sala.
     * Lida com a requisição GET para /salas/criar.
     */
    public function exibirFormularioCriacao(): void
    {
        // Controlo de Acesso: Apenas utilizadores logados podem criar salas.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        // Busca todos os sistemas de RPG disponíveis para preencher o formulário.
        $sistemas = $this->sistemaRPGRepository->buscarTodos();

        $this->renderView('salas/criar', ['sistemas' => $sistemas]);
    }

    /**
     * Processa os dados do formulário de criação de sala.
     * Lida com a requisição POST para /salas/criar.
     */
    public function processarCriacao(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $idMestre = $_SESSION['user_id'];
        $idSistema = filter_input(INPUT_POST, 'id_sistema', FILTER_VALIDATE_INT);
        $nomeSala = filter_input(INPUT_POST, 'nome_sala', FILTER_SANITIZE_STRING);

        try {
            if (!$idSistema || !$nomeSala) {
                throw new Exception("Todos os campos são obrigatórios.");
            }

            // Delega a lógica de negócio para o serviço de domínio.
            $novaSala = $this->criarSalaService->executar($idMestre, $idSistema, $nomeSala);

            // Sucesso: Redireciona para o painel de controlo (por agora).
            // No futuro, poderíamos redirecionar para a própria sala: /sala/{$novaSala->id}
            header('Location: /dashboard');
            exit();

        } catch (Exception $e) {
            // Em caso de erro, exibe o formulário novamente com a mensagem de erro.
            $sistemas = $this->sistemaRPGRepository->buscarTodos();
            $this->renderView('salas/criar', [
                'sistemas' => $sistemas,
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * Método auxiliar para renderizar uma View.
     */
    private function renderView(string $viewName, array $data = []): void
    {
        if (!empty($data)) {
            extract($data);
        }
        require __DIR__ . '/../Views/' . $viewName . '.phtml';
    }
}