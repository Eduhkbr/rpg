<?php

namespace App\Application\Controllers;

use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Services\AssociarPersonagemService;
use App\Domain\Services\CriarSalaService;
use App\Domain\Services\EntrarSalaService;
use App\Domain\Services\EditarSalaService;
use App\Domain\Services\DeletarSalaService;
use App\Domain\Repositories\SistemaRPGRepositoryInterface;
use App\Domain\Exceptions\SalaNaoEncontradaException;
use App\Domain\Exceptions\SalaCheiaException;
use App\Domain\Exceptions\UtilizadorJaParticipaException;
use App\Domain\Exceptions\LimiteDeSalasAtingidoException;
use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use App\Domain\Services\SairSalaService;
use Exception;

/**
 * Classe SalaController
 *
 * Responsável por receber as requisições HTTP relacionadas às salas de jogo.
 */
class SalaController
{
    private CriarSalaService $criarSalaService;
    private EntrarSalaService $entrarSalaService;
    private EditarSalaService $editarSalaService;
    private DeletarSalaService $deletarSalaService;
    private SairSalaService $sairSalaService;
    private SistemaRPGRepositoryInterface $sistemaRPGRepository;
    private SalaRepositoryInterface $salaRepository;
    private AssociarPersonagemService $associarPersonagemService;
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(
        CriarSalaService $criarSalaService,
        EntrarSalaService $entrarSalaService,
        EditarSalaService $editarSalaService,
        DeletarSalaService $deletarSalaService,
        SairSalaService $sairSalaService,
        AssociarPersonagemService $associarPersonagemService,
        SistemaRPGRepositoryInterface $sistemaRPGRepository,
        SalaRepositoryInterface $salaRepository,
        PersonagemRepositoryInterface $personagemRepository
    ) {
        $this->criarSalaService = $criarSalaService;
        $this->entrarSalaService = $entrarSalaService;
        $this->editarSalaService = $editarSalaService;
        $this->deletarSalaService = $deletarSalaService;
        $this->sairSalaService = $sairSalaService;
        $this->sistemaRPGRepository = $sistemaRPGRepository;
        $this->salaRepository = $salaRepository;
        $this->associarPersonagemService = $associarPersonagemService;
        $this->personagemRepository = $personagemRepository;
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
        $nomeSala = filter_input(INPUT_POST, 'nome_sala', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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
     * Processa o código de convite para entrar numa sala.
     * Lida com a requisição POST para /salas/entrar.
     */
    public function processarEntrada(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $idUsuario = $_SESSION['user_id'];
        $codigoConvite = strtoupper(trim($_POST['codigo_convite'] ?? ''));

        try {
            // Delega a lógica de negócio para o serviço de domínio.
            $this->entrarSalaService->executar($idUsuario, $codigoConvite);

            // Sucesso: Redireciona para o painel de controlo, que agora mostrará a nova sala.
            header('Location: /dashboard');
            exit();

        } catch (SalaNaoEncontradaException | SalaCheiaException | UtilizadorJaParticipaException | LimiteDeSalasAtingidoException $e) {
            // Falha Específica: Guarda a mensagem de erro na sessão para exibi-la no painel.
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header('Location: /dashboard');
            exit();
        } catch (Exception $e) {
            // Falha Genérica.
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => 'Ocorreu um erro inesperado ao tentar entrar na sala.'];
            header('Location: /dashboard');
            exit();
        }
    }

    /*
     * Exibe o formulário para a edição de uma sala.
     * Lida com a requisição GET para /salas/editar/{id}
     */
    public function exibirFormularioEdicao(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $sala = $this->salaRepository->buscarPorId($id);
            if ($sala === null || $sala->idMestre !== $_SESSION['user_id']) {
                throw new AcessoNegadoException("Acesso negado ou sala não encontrada.");
            }
            $this->renderView('salas/editar', ['sala' => $sala]);
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Processa os dados do formulário de edição de sala.
     * Lida com a requisição POST para /salas/editar/{id}.
     *
     * @param int $id O ID da sala a ser editada.
     */
    public function processarEdicao(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $novoNome = filter_input(INPUT_POST, 'nome_sala', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $this->editarSalaService->executar($id, $_SESSION['user_id'], $novoNome);
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Sala atualizada com sucesso!'];
            header('Location: /dashboard');
            exit();
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header("Location: /salas/editar/{$id}");
            exit();
        }
    }

    /**
     * Processa o pedido de exclusão de uma sala.
     * Lida com a requisição POST para /salas/deletar/{id}.
     *
     * @param int $id O ID da sala a ser excluída.
     */
    public function processarDelecao(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $this->deletarSalaService->executar($id, $_SESSION['user_id']);
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Sala excluída com sucesso.'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
        }
        header('Location: /dashboard');
        exit();
    }

    /**
     * Processa o pedido de saida de uma sala.
     */
    public function processarSaida(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $this->sairSalaService->executar($id, $_SESSION['user_id']);
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Você saiu da sala.'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
        }
        header('Location: /dashboard');
        exit();
    }

    /**
     * Ponto de entrada para uma sala de jogo.
     * Verifica se o utilizador precisa de selecionar um personagem ou se já pode entrar no jogo.
     * Lida com a requisição GET para /sala/{id}.
     */
    public function exibirEntradaSala(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $idUsuario = $_SESSION['user_id'];
            $sala = $this->salaRepository->buscarPorId($id);
            if ($sala === null) { throw new Exception("Sala não encontrada."); }

            // Se o utilizador for o mestre, ele não precisa de selecionar personagem.
            if ($sala->idMestre === $idUsuario) {
                // Futuramente, redirecionará para a sala de jogo. Por agora, uma mensagem.
                $this->renderView('salas/jogo', ['sala' => $sala]);
                return;
            }

            $participante = $this->salaRepository->buscarParticipante($id, $idUsuario);
            if ($participante === null) { throw new AcessoNegadoException("Você não participa nesta sala."); }

            // Se o jogador já associou um personagem, entra direto no jogo.
            if (!empty($participante['id_personagem'])) {
                $this->renderView('salas/jogo', ['sala' => $sala]);
                return;
            }

            // Se não, busca os personagens compatíveis para a seleção.
            $personagensDoUsuario = $this->personagemRepository->buscarPorUsuarioId($idUsuario);
            $personagensCompativeis = array_filter($personagensDoUsuario, function($info) use ($sala) {
                return $info['personagem']->getIdSistema() === $sala->idSistema;
            });

            $this->renderView('salas/selecionar-personagem', [
                'sala' => $sala,
                'personagens' => $personagensCompativeis
            ]);

        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Processa a seleção de personagem para uma sala.
     * Lida com a requisição POST para /sala/{id}/selecionar.
     */
    public function processarSelecaoPersonagem(int $id): void
    {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit(); }

        try {
            $idUsuario = $_SESSION['user_id'];
            $idPersonagem = filter_input(INPUT_POST, 'id_personagem', FILTER_VALIDATE_INT);

            if (!$idPersonagem) { throw new Exception("Nenhum personagem foi selecionado."); }

            $this->associarPersonagemService->executar($id, $idUsuario, $idPersonagem);

            // Sucesso: Redireciona para a própria sala, onde agora entrará direto.
            header("Location: /sala/{$id}");
            exit();

        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header("Location: /sala/{$id}");
            exit();
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