<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class MotorBusquedaException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $tipo = 'inesperado',
        public readonly ?int $statusHttp = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
