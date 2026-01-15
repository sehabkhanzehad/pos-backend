<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct($message = "Insufficient stock for the product.", $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
