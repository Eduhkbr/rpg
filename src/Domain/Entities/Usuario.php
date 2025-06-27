<?php

// Define o namespace para ajudar na organização e no autoloading.
namespace App\Domain\Entities;

/**
 * Classe Usuario
 * * Representa a entidade Usuario no domínio da aplicação.
 * Contém as propriedades e os métodos de negócio relacionados a um usuário.
 */
class Usuario
{
    // Propriedades da classe, espelhando as colunas da tabela `usuarios`.
    // Usamos 'readonly' para o id, pois ele não deve ser alterado após a criação.
    public readonly ?int $id;
    private string $nomeUsuario;
    private string $email;
    private string $senhaHash;
    private ?string $codigoVerificacao;
    private bool $emailVerificado;
    private readonly string $dataCadastro;

    /**
     * Construtor da classe Usuario.
     * * @param string $nomeUsuario O nome de usuário.
     * @param string $email O endereço de e-mail.
     * @param string $senhaHash A senha já processada por um algoritmo de hash.
     * @param ?int $id O ID do usuário (opcional, usado ao carregar do banco).
     * @param bool $emailVerificado O status de verificação do e-mail.
     * @param ?string $codigoVerificacao O código para verificação do e-mail.
     * @param ?string $dataCadastro A data de cadastro (opcional).
     */
    public function __construct(
        string $nomeUsuario,
        string $email,
        string $senhaHash,
        ?int $id = null,
        bool $emailVerificado = false,
        ?string $codigoVerificacao = null,
        ?string $dataCadastro = null
    ) {
        $this->id = $id;
        $this->nomeUsuario = $nomeUsuario;
        $this->email = $email;
        $this->senhaHash = $senhaHash;
        $this->emailVerificado = $emailVerificado;
        $this->codigoVerificacao = $codigoVerificacao;
        // Se a data de cadastro não for fornecida, define a data e hora atuais.
        $this->dataCadastro = $dataCadastro ?? date('Y-m-d H:i:s');
    }

    // --- Getters ---
    // Métodos públicos para acessar as propriedades privadas da classe.

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomeUsuario(): string
    {
        return $this->nomeUsuario;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function isEmailVerificado(): bool
    {
        return $this->emailVerificado;
    }

    public function getCodigoVerificacao(): ?string
    {
        return $this->codigoVerificacao;
    }

    public function getDataCadastro(): string
    {
        return $this->dataCadastro;
    }

    // --- Métodos de Negócio ---
    // Métodos que contêm a lógica de negócio da entidade.

    /**
     * Marca o e-mail do usuário como verificado.
     */
    public function marcarEmailComoVerificado(): void
    {
        $this->emailVerificado = true;
        $this->codigoVerificacao = null; // O código não é mais necessário.
    }

    /**
     * Define um novo código de verificação para o usuário.
     * @param string $codigo O novo código gerado.
     */
    public function definirNovoCodigoVerificacao(string $codigo): void
    {
        $this->codigoVerificacao = $codigo;
    }

    /**
     * Altera a senha do usuário.
     * @param string $novaSenhaHash A nova senha já processada por hash.
     */
    public function alterarSenha(string $novaSenhaHash): void
    {
        $this->senhaHash = $novaSenhaHash;
    }
}