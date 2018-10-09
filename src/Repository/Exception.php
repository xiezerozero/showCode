<?php

namespace Tourscool\Repository;


class Exception extends \Exception
{

    public $statusCode;

    public function __construct($statusCode, $message = "", $code = 0, \Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }


}