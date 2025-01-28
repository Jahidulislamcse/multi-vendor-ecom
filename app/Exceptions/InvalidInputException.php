<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidInputException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_UNAUTHORIZED);
    }
}
