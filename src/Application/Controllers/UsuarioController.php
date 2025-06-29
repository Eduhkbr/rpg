<?php

namespace App\Application\Controllers;

use App\Domain\Repositories\PersonagemRepositoryInterface;
use App\Domain\Repositories\SalaRepositoryInterface;
use App\Domain\Services\CadastroService;
use App\Domain\Services\VerificacaoEmailService;
use App\Domain\Services\LoginService;
use App\Domain\Exceptions\EmailJaExisteException;
use App\Domain\Exceptions\CodigoInvalidoException;
use App\Domain\Exceptions\CredenciaisInvalidasException;
use App\Domain\Exceptions\EmailNaoVerificadoException;
use Exception;

/**
 * Classe UsuarioController
 *
 * Responsável por receber as requisições HTTP relacionadas a usuários,
 * interagir com os serviços de domínio apropriados e retornar uma resposta
 */
class UsuarioController
{
    private CadastroService $cadastroService;
    private VerificacaoEmailService $verificacaoEmailService;
    private LoginService $loginService;
    private SalaRepositoryInterface $salaRepository;
    private PersonagemRepositoryInterface $personagemRepository;

    /**
     * O construtor recebe as dependências necessárias
     *
     * @param CadastroService $cadastroService
     * @param VerificacaoEmailService $verificacaoEmailService
     * @param LoginService $loginService
     * @param SalaRepositoryInterface $salaRepository
     * @param PersonagemRepositoryInterface $personagemRepository
     */
    public function __construct(
        CadastroService $cadastroService,
        VerificacaoEmailService $verificacaoEmailService,
        LoginService $loginService,
        SalaRepositoryInterface $salaRepository,
        PersonagemRepositoryInterface $personagemRepository
    ) {
        $this->cadastroService = $cadastroService;
        $this->verificacaoEmailService = $verificacaoEmailService;
        $this->loginService = $loginService;
        $this->salaRepository = $salaRepository;
        $this->personagemRepository = $personagemRepository;
    }

    /**
     * Exibe o formulário de cadastro de usuário.
     * Este método lida com a requisição GET para a página de cadastro.
     */
    public function exibirFormularioCadastro(): void
    {
        $this->renderView('usuarios/cadastro');
    }

    /**
     * Processa os dados enviados pelo formulário de cadastro.
     * Este método lida com a requisição POST do formulário.
     */
    public function processarCadastro(): void
    {
        // 1. Coleta e sanitização básica dos dados de entrada.
        $nomeUsuario = filter_input(INPUT_POST, 'nome_usuario');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? ''; // Senha não tem um filtro padrão, pegamos diretamente.

        // 2. Validação simples.
        if (!$nomeUsuario || !$email || empty($senha)) {
            $this->renderView('usuarios/cadastro', [
                'erro' => 'Todos os campos são obrigatórios e o e-mail deve ser válido.'
            ]);
            return;
        }

        try {
            // 3. Delega a lógica de negócio para o Serviço de Domínio.
            // O Controller não sabe (e não precisa saber) como o cadastro funciona.
            $this->cadastroService->executar($nomeUsuario, $email, $senha);

            // 4. Sucesso: Redireciona para uma página de sucesso.
            // Isso segue o padrão Post-Redirect-Get (PRG) para evitar reenvio do formulário.
            header('Location: /cadastro/sucesso');
            exit();

        } catch (EmailJaExisteException $e) {
            // 5. Falha Específica: Trata o erro de e-mail duplicado.
            $this->renderView('usuarios/cadastro', [
                'erro' => $e->getMessage(),
                'nomeUsuario' => $nomeUsuario, // Reenvia os dados para preencher o formulário novamente.
                'email' => $email
            ]);
        } catch (Exception $e) {
            // 6. Falha Genérica: Trata qualquer outro erro inesperado.
            // Em um ambiente de produção, poderíamos logar $e->getMessage() para depuração.
            $this->renderView('usuarios/cadastro', [
                'erro' => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.'
            ]);
        }
    }

    /**
     * Exibe a página de sucesso após o cadastro.
     */
    public function exibirCadastroSucesso(): void { $this->renderView('usuarios/sucesso'); }

    /**
     * Exibe o formulário para o utilizador inserir o código de verificação.
     * Lida com a requisição GET para /verificar.
     */
    public function exibirFormularioVerificacao(): void
    {
        // Pega o e-mail da URL, se existir.
        $email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);

        // Pega a mensagem flash, se existir.
        $flash_message = $_SESSION['flash_message'] ?? null;
        if ($flash_message) {
            unset($_SESSION['flash_message']);
        }

        // Passa ambos para a View.
        $this->renderView('usuarios/verificar', [
            'email' => $email,
            'flash_message' => $flash_message
        ]);
    }

    /**
     * Processa o código de verificação submetido pelo utilizador.
     * Lida com a requisição POST para /verificar.
     */
    public function processarVerificacao(): void
    {
        // 1. Coleta o código do formulário.
        $codigo = $_POST['codigo'] ?? '';

        try {
            // 2. Delega a lógica para o serviço de domínio.
            $this->verificacaoEmailService->executar($codigo);

            // 3. Sucesso: Prepara uma mensagem de sucesso e redireciona para o login.
            // Usamos a sessão para passar a mensagem entre as requisições (Flash Message).
            $_SESSION['flash_message'] = [
                'type' => 'sucesso',
                'message' => 'E-mail verificado com sucesso! Por favor, faça o login.'
            ];
            header('Location: /login');
            exit();

        } catch (CodigoInvalidoException $e) {
            // 4. Falha Específica: Código inválido. Renderiza o formulário novamente com o erro.
            $this->renderView('usuarios/verificar', ['erro' => $e->getMessage()]);
        } catch (Exception $e) {
            // 5. Falha Genérica: Outro erro inesperado.
            $this->renderView('usuarios/verificar', ['erro' => 'Ocorreu um erro inesperado.']);
        }
    }

    /**
     * Exibe o formulário de login.
     * Lida com a requisição GET para /login.
     */
    public function exibirFormularioLogin(): void
    {
        // Verifica se existe uma "flash message" na sessão (ex: vinda da verificação).
        $flash_message = $_SESSION['flash_message'] ?? null;

        // Se existir, remove-a da sessão para que não seja mostrada novamente.
        if ($flash_message) {
            unset($_SESSION['flash_message']);
        }

        // Renderiza a view, passando a mensagem (se houver) para ela.
        $this->renderView('usuarios/login', [
            'flash_message' => $flash_message
        ]);
    }

    /**
     * Processa os dados do formulário de login.
     * Lida com a requisição POST para /login.
     */
    public function processarLogin(): void
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        try {
            // 1. Delega a lógica para o Serviço de Domínio.
            $usuario = $this->loginService->executar($email, $senha);

            // 2. Sucesso: Regenera o ID da sessão e armazena o ID do utilizador.
            // Isto é uma boa prática de segurança para prevenir "session fixation".
            session_regenerate_id(true);
            $_SESSION['user_id'] = $usuario->getId();

            // 3. Redireciona para o painel de controlo.
            header('Location: /dashboard');
            exit();

        } catch (EmailNaoVerificadoException $e) {
            // NOVO COMPORTAMENTO: Se o e-mail não for verificado,
            // redireciona para a página de verificação, passando o e-mail na URL.
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'A sua conta ainda não foi verificada. Por favor, insira o código que enviámos para o seu e-mail.'];
            header('Location: /verificar?email=' . urlencode($email));
            exit();

        } catch (CredenciaisInvalidasException $e) {
            // 4. Falha Específica: Trata os erros de login conhecidos.
            $this->renderView('usuarios/login', [
                'erro' => $e->getMessage(),
                'email' => $email
            ]);
        } catch (Exception $e) {
            // 5. Falha Genérica: Trata qualquer outro erro inesperado.
            $this->renderView('usuarios/login', [
                'erro' => 'Ocorreu um erro inesperado. Por favor, tente novamente.'
            ]);
        }
    }

    /**
     * Exibe o painel principal do utilizador (página protegida).
     * Lida com a requisição GET para /dashboard.
     */
    public function exibirDashboard(): void
    {
        // Controlo de Acesso: Verifica se o utilizador está logado.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        // Busca as salas em que o utilizador participa.
        $idUsuario = $_SESSION['user_id'];
        $salas = $this->salaRepository->buscarPorUsuarioId($idUsuario);
        $personagens = $this->personagemRepository->buscarPorUsuarioId($idUsuario);

        // Renderiza a view do painel, passando a lista de salas para ela.
        $this->renderView('usuarios/dashboard', [
            'salas' => $salas,
            'personagens' => $personagens
        ]);
    }

    /**
     * Encerra a sessão do utilizador (logout).
     * Lida com a requisição GET para /logout.
     */
    public function logout(): void
    {
        // Limpa todas as variáveis da sessão.
        $_SESSION = [];

        // Destrói a sessão.
        session_destroy();

        // Redireciona para a página de login.
        header('Location: /login');
        exit();
    }

    /**
     * Método auxiliar para renderizar uma View.
     *
     * @param string $viewName O nome do arquivo da view (sem a extensão).
     * @param array $data Um array de dados a serem extraídos como variáveis na view.
     */
    private function renderView(string $viewName, array $data = []): void
    {
        // A função extract() transforma as chaves do array em variáveis.
        // Ex: ['erro' => 'mensagem'] se torna a variável $erro.
        if (!empty($data)) {
            extract($data);
        }

        // Inclui o arquivo da view. O caminho é relativo à localização deste controller.
        require __DIR__ . '/../Views/' . $viewName . '.phtml';
    }
}