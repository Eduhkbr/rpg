<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Class EmailNaoVerificadoException
 *
 * Exceção de domínio lançada quando um utilizador tenta fazer login
 * mas a sua conta de e-mail ainda não foi verificada.
 */
class EmailNaoVerificadoException extends Exception
{
}