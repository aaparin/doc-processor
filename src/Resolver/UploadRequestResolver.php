<?php

namespace App\Resolver;

use App\DTO\UploadRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UploadRequest::class) {
            return [];
        }

        $uploadRequest = new UploadRequest();

        $uploadRequest->file = $request->files->get('file');
        $uploadRequest->json = $request->request->get('json');

        $errors = $this->validator->validate($uploadRequest);
        if (count($errors) > 0) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException(
                (string) $errors
            );
        }

        yield $uploadRequest;
    }
}

