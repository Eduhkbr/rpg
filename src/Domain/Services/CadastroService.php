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
 * Este é o coração do caso de uso "Cadastrar Usuário". Ele valida as regras de negócio,
 * cria a entidade e coordena a persistência e o envio de notificações.
 */
class CadastroService
{
    /**
     * @var UsuarioRepositoryInterface O repositório para persistência de dados do usuário.
     */
    private UsuarioRepositoryInterface $usuarioRepository;

    /**
     * @var EmailServiceInterface O serviço para envio de e-mails.
     */
    private EmailServiceInterface $emailService;

    /**
     * O construtor recebe as dependências necessárias através de injeção.
     * Ele depende das ABSTRAÇÕES (interfaces), não das implementações concretas,
     * seguindo o Princípio da Inversão de Dependência (SOLID).
     *
     * @param UsuarioRepositoryInterface $usuarioRepository
     * @param EmailServiceInterface $emailService
     */
    public function __construct(
        UsuarioRepositoryInterface $usuarioRepository,
        EmailServiceInterface $emailService
    ) {
        $this->usuarioRepository = $usuarioRepository;
        $this->emailService = $emailService;
    }

    /**
     * Executa o processo completo de cadastro de um novo usuário.
     *
     * @param string $nomeUsuario O nome do novo usuário.
     * @param string $email O e-mail do novo usuário.
     * @param string $senhaPlana A senha em texto plano (não hasheada).
     * @return Usuario Retorna o objeto Usuario recém-criado.
     * @throws EmailJaExisteException Se o e-mail fornecido já estiver em uso.
     * @throws Exception Se ocorrer um erro inesperado durante o processo.
     */
    public function executar(string $nomeUsuario, string $email, string $senhaPlana): Usuario
    {
        // 1. Regra de Negócio: Verificar se o e-mail já existe para evitar duplicatas.
        $usuarioExistente = $this->usuarioRepository->buscarPorEmail($email);
        if ($usuarioExistente !== null) {
            throw new EmailJaExisteException($email);
        }

        // 2. Lógica de Segurança: Criar o hash da senha usando um algoritmo forte.
        $senhaHash = password_hash($senhaPlana, PASSWORD_ARGON2ID);
        if ($senhaHash === false) {
            throw new Exception("Falha ao gerar o hash da senha.");
        }

        // 3. Lógica de Negócio: Gerar um código de verificação numérico para o e-mail.
        $codigoVerificacao = (string) random_int(100000, 999999);

        // 4. Criação da Entidade: Instanciar um novo objeto Usuario com os dados validados.
        $novoUsuario = new Usuario(
            $nomeUsuario,
            $email,
            $senhaHash,
            null,  // ID é nulo, pois será gerado pelo banco de dados.
            false, // E-mail ainda não foi verificado.
            $codigoVerificacao
        );

        // 5. Persistência: Usar o repositório para salvar o novo usuário no banco de dados.
        $sucesso = $this->usuarioRepository->salvar($novoUsuario);
        if (!$sucesso) {
            throw new Exception("Não foi possível salvar o usuário no banco de dados.");
        }

        // 6. Notificação: Enviar o e-mail de verificação para o novo usuário.
        $this->enviarEmailDeVerificacao($novoUsuario);

        // 7. Retorno: Devolve o objeto Usuario completo para a camada que chamou o serviço.
        return $novoUsuario;
    }

    /**
     * Método privado para construir e enviar o e-mail de verificação.
     *
     * @param Usuario $usuario O objeto do usuário recém-criado.
     */
    private function enviarEmailDeVerificacao(Usuario $usuario): void
    {
        $assunto = 'Seu Código de Verificação - Central RPG';
        $corpo = "<h1>Bem-vindo à Central RPG!</h1>";
        $corpo .= "<p>Olá {$usuario->getNomeUsuario()},</p>";
        $corpo .= "<p>Obrigado por se cadastrar. Use o código abaixo para verificar seu e-mail:</p>";
        $corpo .= "<p style='font-size: 24px; font-weight: bold; letter-spacing: 5px;'>{$usuario->getCodigoVerificacao()}</p>";
        $corpo .= "<p>Atenciosamente,<br>Equipe Central RPG</p>";

        $this->emailService->enviar(
            $usuario->getEmail(),
            $usuario->getNomeUsuario(),
            $assunto,
            $corpo
        );
    }
}