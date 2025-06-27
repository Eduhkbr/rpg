<?php


namespace App\Domain\Exceptions;

use Exception;

/**
 * Class CodigoInvalidoException
 *
 * Exceção de domínio lançada quando uma tentativa de verificação
 * utiliza um código que não é válido ou já foi utilizado.
 */
class CodigoInvalidoException extends Exception
{
    // Podemos deixar esta classe vazia por agora.
    // O seu propósito é permitir um tratamento de erro específico no Controller.
}
