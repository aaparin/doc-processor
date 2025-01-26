<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\DTO\DocumentProcessingResult;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class DocumentConversionService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $conversionEndpoint
    ) {}

    public function convertToPdf(string $docxPath): DocumentProcessingResult
    {
        try {
            // Создаем FormDataPart для отправки файла
            $formFields = [
                'file' => DataPart::fromPath($docxPath),
            ];

            $formData = new FormDataPart($formFields);

            $response = $this->httpClient->request('POST', $this->conversionEndpoint, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            if ($response->getStatusCode() !== 200) {

                return new DocumentProcessingResult(
                    false,
                    null,
                    sprintf('Conversion service returned status code: %d', $response->getStatusCode())
                );
            }

            return new DocumentProcessingResult(true, $response->getContent());

        } catch (\Exception $e) {
            return new DocumentProcessingResult(
                false,
                null,
                sprintf('Error during conversion: %s', $e->getMessage())
            );
        }
    }
}