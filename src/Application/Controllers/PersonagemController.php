<?php

namespace App\Application\Controllers;

use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Services\DeletarPersonagemService;
use App\Domain\Services\CriarPersonagemService;
use App\Domain\Repositories\SistemaRPGRepositoryInterface;
use App\Domain\Exceptions\AcessoNegadoException;
use App\Domain\Services\EditarPersonagemService;
use Exception;

/**
 * Classe PersonagemController
 *
 * Responsável por receber as requisições HTTP relacionadas às fichas de personagem.
 */
class PersonagemController
{
    private CriarPersonagemService $criarPersonagemService;
    private DeletarPersonagemService $deletarPersonagemService;
    private EditarPersonagemService $editarPersonagemService;
    private SistemaRPGRepositoryInterface $sistemaRPGRepository;
    private PersonagemRepositoryInterface $personagemRepository;

    public function __construct(
        CriarPersonagemService $criarPersonagemService,
        DeletarPersonagemService $deletarPersonagemService,
        EditarPersonagemService $editarPersonagemService,
        SistemaRPGRepositoryInterface $sistemaRPGRepository,
        PersonagemRepositoryInterface $personagemRepository
    ) {
        $this->criarPersonagemService = $criarPersonagemService;
        $this->deletarPersonagemService = $deletarPersonagemService;
        $this->editarPersonagemService = $editarPersonagemService;
        $this->sistemaRPGRepository = $sistemaRPGRepository;
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Exibe o formulário para a criação de uma nova ficha de personagem.
     * Lida com a requisição GET para /personagens/criar.
     */
    public function exibirFormularioCriacao(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $sistemas = $this->sistemaRPGRepository->buscarTodos();

        // Verifica se um sistema foi selecionado através de um parâmetro GET na URL.
        $idSistemaSelecionado = filter_input(INPUT_GET, 'id_sistema', FILTER_VALIDATE_INT);
        $sistemaSelecionado = null;
        $fichaTemplate = null;

        if ($idSistemaSelecionado) {
            // Procura o sistema selecionado na lista de sistemas disponíveis.
            foreach ($sistemas as $sistema) {
                if ($sistema->id === $idSistemaSelecionado) {
                    $sistemaSelecionado = $sistema;
                    // Descodifica o modelo JSON da ficha para um array PHP.
                    $fichaTemplate = json_decode($sistema->fichaTemplateJson, true);
                    break;
                }
            }
        }

        // Passa todos os dados necessários para a View.
        $this->renderView('personagens/criar', [
            'sistemas' => $sistemas,
            'sistemaSelecionado' => $sistemaSelecionado,
            'fichaTemplate' => $fichaTemplate
        ]);
    }

    /**
     * Processa os dados do formulário de criação de personagem.
     * Lida com a requisição POST para /personagens/criar.
     */
    public function processarCriacao(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            $idUsuario = $_SESSION['user_id'];
            $idSistema = filter_input(INPUT_POST, 'id_sistema', FILTER_VALIDATE_INT);

            // O 'personagem' é um array que contém todos os campos da ficha.
            $dadosFicha = $_POST['personagem'] ?? [];

            if (!$idUsuario || !$idSistema || empty($dadosFicha)) {
                throw new Exception("Dados inválidos para a criação do personagem.");
            }

            // Delega a lógica de negócio para o serviço de domínio.
            $this->criarPersonagemService->executar($idUsuario, $idSistema, $dadosFicha);

            // Sucesso: Redireciona para o painel de controlo.
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Personagem criado com sucesso!'];
            header('Location: /dashboard');
            exit();

        } catch (Exception $e) {
            // Em caso de erro, exibe o formulário novamente com a mensagem de erro.
            $sistemas = $this->sistemaRPGRepository->buscarTodos();
            $this->renderView('personagens/criar', [
                'sistemas' => $sistemas,
                'sistemaSelecionado' => null,
                'ficha' => null,
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * Exibe a ficha de um personagem específico.
     * Lida com a requisição GET para /personagens/ver/{id}.
     *
     * @param int $id O ID do personagem vindo da URL.
     */
    public function exibirFicha(int $id): void
    {
        // 1. Controlo de Acesso: Verifica se o utilizador está logado.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            // 2. Busca o personagem na base de dados.
            $personagem = $this->personagemRepository->buscarPorId($id);

            // 3. Verificação de Segurança e Existência:
            // Garante que o personagem existe E que pertence ao utilizador logado.
            if ($personagem === null || $personagem->getIdUsuario() !== $_SESSION['user_id']) {
                // Se não, redireciona para o painel com uma mensagem de erro.
                $_SESSION['flash_message'] = ['type' => 'erro', 'message' => 'Personagem não encontrado ou acesso não permitido.'];
                header('Location: /dashboard');
                exit();
            }

            // 4. Sucesso: Renderiza a view da ficha, passando os dados do personagem.
            $this->renderView('personagens/ver', [
                'personagem' => $personagem,
                'ficha' => $personagem->getFichaComoArray() // Passa a ficha já descodificada
            ]);

        } catch (Exception $e) {
            // Em caso de erro inesperado na base de dados.
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => 'Ocorreu um erro ao buscar os dados do personagem.'];
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Processa o pedido de exclusão de um personagem.
     * Lida com a requisição POST para /personagens/deletar/{id}.
     *
     * @param int $id O ID do personagem a ser excluído.
     */
    public function processarDelecao(int $id): void
    {
        // 1. Controlo de Acesso: Verifica se o utilizador está logado.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            // 2. Delega a lógica de negócio para o serviço de domínio.
            $this->deletarPersonagemService->executar($id, $_SESSION['user_id']);

            // 3. Sucesso: Prepara uma mensagem e redireciona para o painel de controlo.
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Personagem excluído com sucesso.'];
            header('Location: /dashboard');
            exit();

        } catch (AcessoNegadoException | Exception $e) {
            // 4. Falha: Se o utilizador não for o dono ou ocorrer outro erro.
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Exibe o formulário para editar um personagem existente.
     * Lida com a requisição GET para /personagens/editar/{id}.
     *
     * @param int $id O ID do personagem a ser editado.
     */
    public function exibirFormularioEdicao(int $id): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            $personagem = $this->personagemRepository->buscarPorId($id);

            // Verificação de Segurança e Existência
            if ($personagem === null || $personagem->getIdUsuario() !== $_SESSION['user_id']) {
                $_SESSION['flash_message'] = ['type' => 'erro', 'message' => 'Personagem não encontrado ou acesso não permitido.'];
                header('Location: /dashboard');
                exit();
            }

            // Renderiza a view de edição, passando os dados do personagem.
            $this->renderView('personagens/editar', [
                'personagem' => $personagem,
                'ficha' => $personagem->getFichaComoArray()
            ]);

        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => 'Ocorreu um erro ao carregar os dados para edição.'];
            header('Location: /dashboard');
            exit();
        }
    }

    /**
     * Processa os dados do formulário de edição de personagem.
     * Lida com a requisição POST para /personagens/editar/{id}.
     *
     * @param int $id O ID do personagem a ser editado.
     */
    public function processarEdicao(int $id): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        try {
            $dadosFicha = $_POST['personagem'] ?? [];

            // Delega a lógica de negócio para o serviço de domínio.
            $this->editarPersonagemService->executar($id, $_SESSION['user_id'], $dadosFicha);

            // Sucesso: Redireciona de volta para a ficha do personagem com uma mensagem.
            $_SESSION['flash_message'] = ['type' => 'sucesso', 'message' => 'Personagem atualizado com sucesso!'];
            header('Location: /personagens/ver/' . $id);
            exit();

        } catch (AcessoNegadoException | Exception $e) {
            // Em caso de erro, redireciona de volta para o formulário de edição com a mensagem.
            $_SESSION['flash_message'] = ['type' => 'erro', 'message' => $e->getMessage()];
            header('Location: /personagens/editar/' . $id);
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