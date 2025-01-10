<?php

declare(strict_types=1);

namespace App\Resolver;

use App\DTO\TemplateAnalyzeRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TemplateAnalyzeRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== TemplateAnalyzeRequest::class) {
            return [];
        }

        $file = $request->files->get('file');

        if ($file === null) {
            throw new BadRequestHttpException('No file uploaded. Please provide a file in the request.');
        }

        $analyzeRequest = new TemplateAnalyzeRequest();
        $analyzeRequest->file = $file;

        $errors = $this->validator->validate($analyzeRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            throw new BadRequestHttpException(
                'Validation failed: ' . implode(', ', $errorMessages)
            );
        }

        yield $analyzeRequest;
    }
}