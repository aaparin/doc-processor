<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\UploadRequest;
use OpenApi\Attributes as OA;
use App\Response\ApiResponse;
use App\Service\Document\DocumentConversionService;
use App\Service\Document\WordDocumentGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;

#[Route('/api/v1')]
class DocumentController
{
    public function __construct(
        private readonly WordDocumentGenerator $documentGenerator,
        private readonly DocumentConversionService $conversionService,
        private readonly Filesystem $filesystem
    ) {}

    #[Route('/documents/upload', name: 'v1_document_upload', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/documents/upload',
        description: 'Upload and process document template with JSON data',
        summary: 'Upload and process document',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file', 'json'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary',
                            description: 'Document template file (.docx)'
                        ),
                        new OA\Property(
                            property: 'json',
                            type: 'string',
                            description: 'JSON data for template processing'
                        )
                    ]
                )
            )
        ),
        tags: ['Documents'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Document processed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'generated_file', type: 'file',format: 'binary'),
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Document generation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function upload(UploadRequest $uploadRequest): Response
    {
        $tempFile = null;

        try {
            // Сохраняем загруженный файл во временную директорию
            $originalFilename = $uploadRequest->file->getClientOriginalName();
            $tempFile = $this->documentGenerator->getTemplatesDir() . $originalFilename;

            // Копируем файл в директорию шаблонов
            $this->filesystem->copy(
                $uploadRequest->file->getRealPath(),
                $tempFile,
                true
            );

            // Генерируем DOCX из шаблона
            $docxPath = $this->documentGenerator->generate(
                $uploadRequest->json,
                $originalFilename
            );

            // Отправляем на конвертацию
            $result = $this->conversionService->convertToPdf($docxPath);

            if (!$result->isSuccess()) {
                return new JsonResponse(
                    ApiResponse::error($result->getError())->toArray(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Возвращаем PDF
            return new Response(
                $result->getPdfContent(),
                Response::HTTP_OK,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                ApiResponse::error('Error processing document: ' . $e->getMessage())->toArray(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } finally {
            // Очищаем временные файлы
            if ($tempFile && file_exists($tempFile)) {
                $this->filesystem->remove($tempFile);
            }
            if (isset($docxPath) && file_exists($docxPath)) {
                $this->filesystem->remove($docxPath);
            }
        }
    }
}