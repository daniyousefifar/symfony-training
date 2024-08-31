<?php

namespace App\EventSubscriber;

use App\Event\AfterDtoCreatedEvent;
use App\Service\ServiceException;
use App\Service\ServiceExceptionData;
use App\Service\ValidationExceptionData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidatorInterface $validator
    )
    {
        // ...
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterDtoCreatedEvent::NAME => 'validateDto',
        ];
    }

    public function validateDto(AfterDtoCreatedEvent $event): void
    {
        $dto = $event->getDto();

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $validationExceptionData = new ValidationExceptionData(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Validation failed',
                $errors
            );

            throw new ServiceException($validationExceptionData);
        }
    }
}