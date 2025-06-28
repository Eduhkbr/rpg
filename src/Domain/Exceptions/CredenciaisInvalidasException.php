<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Class CredenciaisInvalidasException
 *
 * Exceção de domínio lançada quando uma tentativa de login falha
 * devido a e-mail ou senha incorretos.
 */
class CredenciaisInvalidasException extends Exception
{
}
