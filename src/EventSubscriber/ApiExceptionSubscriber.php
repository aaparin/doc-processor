<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Handles all exceptions and converts them to standardized API responses
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $status = 500;
        $errors = null;

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        if ($exception instanceof ValidationFailedException) {
            $status = 400;
            $errors = $this->formatValidationErrors($exception);
        }

        $response = new JsonResponse(
            ApiResponse::error($exception->getMessage(), $errors)->toArray(),
            $status
        );

        $event->setResponse($response);
    }

    private function formatValidationErrors(ValidationFailedException $exception): array
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }
        return $errors;
    }
}