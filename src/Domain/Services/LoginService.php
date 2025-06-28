<?php

namespace App\Domain\Services;

use App\Domain\Entities\Usuario;
use App\Domain\Repositories\UsuarioRepositoryInterface;
use App\Domain\Exceptions\CredenciaisInvalidasException;
use App\Domain\Exceptions\EmailNaoVerificadoException;

/**
 * Classe LoginService (Caso de Uso)
 *
 * Orquestra a lógica de negócio para autenticar um utilizador.
 */
class LoginService
{
    private UsuarioRepositoryInterface $usuarioRepository;

    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Executa o processo de autenticação.
     *
     * @param string $email O e-mail fornecido pelo utilizador.
     * @param string $senhaPlana A senha em texto plano fornecida pelo utilizador.
     * @return Usuario Retorna o objeto Usuario autenticado.
     * @throws CredenciaisInvalidasException Se o e-mail não for encontrado ou a senha estiver incorreta.
     * @throws EmailNaoVerificadoException Se o e-mail do utilizador ainda não foi verificado.
     */
    public function executar(string $email, string $senhaPlana): Usuario
    {
        // 1. Tenta encontrar o utilizador pelo e-mail.
        $usuario = $this->usuarioRepository->buscarPorEmail($email);

        // 2. Se o utilizador não existir, ou se a senha não corresponder, lança uma exceção.
        // Usamos a mesma exceção para ambos os casos para não dar pistas a atacantes (evita enumeração de utilizadores).
        if ($usuario === null || !password_verify($senhaPlana, $usuario->getSenhaHash())) {
            throw new CredenciaisInvalidasException("E-mail ou senha inválidos.");
        }

        // 3. Regra de Negócio: Verifica se a conta já foi ativada.
        if (!$usuario->isEmailVerificado()) {
            throw new EmailNaoVerificadoException("A sua conta de e-mail ainda não foi verificada. Por favor, verifique o seu e-mail e utilize o código de verificação.");
        }

        // 4. Sucesso: Retorna o objeto do utilizador autenticado.
        return $usuario;
    }
}