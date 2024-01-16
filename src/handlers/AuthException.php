<?php

namespace App\Handlers;

use App\Handlers\AppExceptionsHandler;
use Exception;

class AuthException extends Exception
{
    public function status()
    {
        return 401;
    }
}
