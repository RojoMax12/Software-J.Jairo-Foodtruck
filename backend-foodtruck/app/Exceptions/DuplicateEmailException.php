<?php

namespace App\Exceptions;

class DuplicateEmailException extends BusinessException
{
    protected int $statusCode = 409;
}