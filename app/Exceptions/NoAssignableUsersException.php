<?php

namespace App\Exceptions;

use Exception;

class NoAssignableUsersException extends Exception
{
    public function __construct(?string $message = null)
    {
        $message = $message ?? 'No assignable users found.';
        parent::__construct($message);
    }
}
