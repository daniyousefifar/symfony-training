<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {
            $errors = [];

            foreach ($exception->getViolations() as $violation) {
                $errors[] = [
                    'code' => "validation_failed.{$violation->getPropertyPath()}",
                    'message' => $violation->getMessage(),
                ];
            }

            $response = new JsonResponse([
                'status' => 'error',
                'message' => 'An error occurred while validating your request.',
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } elseif ($exception instanceof UnprocessableEntityHttpException) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode(), $exception->getHeaders());
        } elseif ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND, $exception->getHeaders());
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_METHOD_NOT_ALLOWED, $exception->getHeaders());
        } else {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}