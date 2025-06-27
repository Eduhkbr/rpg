<?php

namespace App\Domain\Services;

use App\Domain\Entities\Usuario;
use App\Domain\Repositories\UsuarioRepositoryInterface;
use App\Domain\Exceptions\EmailJaExisteException;
use Exception;

/**
 * Classe CadastroService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para cadastrar um novo usuário.
 * Este é o coração do caso de uso "Cadastrar Usuário".
 */
class CadastroService
{
    private UsuarioRepositoryInterface $usuarioRepository;

    /**
     * O construtor recebe a implementação do repositório como dependência.
     * Ele depende da ABSTRAÇÃO (interface), não da implementação concreta (MySQL).
     *
     * @param UsuarioRepositoryInterface $usuarioRepository
     */
    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Executa o processo de cadastro.
     *
     * @param string $nomeUsuario O nome do novo usuário.
     * @param string $email O e-mail do novo usuário.
     * @param string $senhaPlana A senha em texto plano (não hasheada).
     * @return Usuario Retorna o objeto Usuario recém-criado.
     * @throws EmailJaExisteException Se o e-mail fornecido já estiver em uso.
     * @throws Exception Se ocorrer um erro inesperado durante o salvamento.
     */
    public function executar(string $nomeUsuario, string $email, string $senhaPlana): Usuario
    {
        // 1. Regra de Negócio: Verificar se o e-mail já existe.
        $usuarioExistente = $this->usuarioRepository->buscarPorEmail($email);
        if ($usuarioExistente !== null) {
            // Lança a exceção específica, agora passando o e-mail para o construtor.
            throw new EmailJaExisteException($email);
        }

        // 2. Lógica de Segurança: Criar o hash da senha.
        // NUNCA armazene senhas em texto plano.
        $senhaHash = password_hash($senhaPlana, PASSWORD_ARGON2ID);
        if ($senhaHash === false) {
            throw new Exception("Falha ao gerar o hash da senha.");
        }

        // 3. Lógica de Negócio: Gerar um código de verificação de e-mail.
        $codigoVerificacao = (string) random_int(100000, 999999);

        // 4. Criação da Entidade: Instanciar um novo objeto Usuario.
        $novoUsuario = new Usuario(
            $nomeUsuario,
            $email,
            $senhaHash,
            null, // ID é nulo porque é um novo usuário.
            false, // E-mail ainda não foi verificado.
            $codigoVerificacao
        );

        // 5. Persistência: Usar o repositório para salvar o novo usuário.
        $sucesso = $this->usuarioRepository->salvar($novoUsuario);
        if (!$sucesso) {
            throw new Exception("Não foi possível salvar o usuário no banco de dados.");
        }

        // 6. Retorno: Devolve o objeto Usuario completo para a camada que chamou.
        return $novoUsuario;
    }
}