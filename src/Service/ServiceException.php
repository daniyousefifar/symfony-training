<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceException extends HttpException
{
    public function __construct(private ServiceExceptionData $exceptionData)
    {
        $statusCode = $this->exceptionData->getStatusCode();
        $message = $this->exceptionData->getType();

        parent::__construct($statusCode, $message);
    }

    public function getExceptionData(): ServiceExceptionData
    {
        return $this->exceptionData;
    }
}