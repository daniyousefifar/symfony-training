<?php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationExceptionData extends ServiceExceptionData
{
    private ConstraintViolationList $violations;

    public function __construct(int $statusCode, string $type, ConstraintViolationList $violations)
    {
        parent::__construct($statusCode, $type);

        $this->violations = $violations;
    }

    public function getViolationsArray(): array
    {
        $errors = [];

        foreach ($this->violations as $violation) {
            $errors[] = [
                "code" => "validation_failed.{$violation->getPropertyPath()}",
                "message" => $violation->getMessage(),
            ];
        }

        return $errors;
    }

    public function toArray(): array
    {
        return [
            'status' => 'error',
            'message' => 'An error occurred while processing your request.',
            'errors' => $this->getViolationsArray(),
        ];
    }
}