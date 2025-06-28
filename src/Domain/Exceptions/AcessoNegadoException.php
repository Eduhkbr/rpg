<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Class AcessoNegadoException
 *
 * Exceção de domínio lançada quando um utilizador tenta executar
 * uma ação num recurso que não lhe pertence.
 */
class AcessoNegadoException extends Exception {}
