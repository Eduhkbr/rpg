<?php

namespace App\Application\Controllers;

use App\Domain\Services\CadastroService;
use App\Domain\Exceptions\EmailJaExisteException;
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

    /**
     * O construtor recebe as dependências necessárias
     *
     * @param CadastroService $cadastroService
     */
    public function __construct(CadastroService $cadastroService)
    {
        $this->cadastroService = $cadastroService;
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
    public function exibirCadastroSucesso(): void
    {
        $this->renderView('usuarios/sucesso');
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