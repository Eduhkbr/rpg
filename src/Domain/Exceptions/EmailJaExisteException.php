<?php

namespace App\Domain\Exceptions;

use Exception;
use Throwable;

/**
 * Class EmailJaExisteException
 *
 * Exceção de domínio lançada quando uma tentativa de cadastro utiliza
 * um endereço de e-mail que já está registrado no sistema.
 *
 * Esta exceção carrega o e-mail conflitante como contexto adicional,
 * permitindo um tratamento de erro mais rico e informativo.
 */
class EmailJaExisteException extends Exception
{
    /**
     * @var string O e-mail que causou o conflito.
     */
    protected string $emailConflitante;

    /**
     * Construtor da exceção.
     *
     * @param string $email O e-mail que já existe.
     * @param string $message A mensagem de erro (opcional). Se vazia, uma padrão será gerada.
     * @param int $code O código de erro (opcional).
     * @param Throwable|null $previous A exceção anterior para encadeamento de exceções.
     */
    public function __construct(string $email, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $this->emailConflitante = $email;

        // Se nenhuma mensagem for fornecida, cria uma padrão e informativa.
        if (empty($message)) {
            $message = "O e-mail '{$email}' já está cadastrado no sistema.";
        }

        // Chama o construtor da classe pai (Exception).
        parent::__construct($message, $code, $previous);
    }

    /**
     * Método getter para obter o e-mail que causou a falha na regra de negócio.
     *
     * @return string
     */
    public function getEmailConflitante(): string
    {
        return $this->emailConflitante;
    }
}
