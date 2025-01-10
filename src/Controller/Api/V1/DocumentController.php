<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\UploadRequest;
use App\Entity\IncomeRequest;
use App\Exception\DocumentGenerationException;
use App\Message\ProcessDocument;
use App\Response\ApiResponse;
use App\Service\Document\DocumentStatusManager;
use App\Service\Document\WordDocumentGenerator;
use App\Service\DocumentGeneratorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/v1')]
class DocumentController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WordDocumentGenerator  $documentGenerator,
        private readonly Filesystem             $filesystem,
        private readonly DocumentGeneratorFactory $documentGeneratorFactory,
        private readonly DocumentStatusManager $statusManager

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
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'generated_file', type: 'string')
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
    public function upload(UploadRequest $uploadRequest, MessageBusInterface $messageBus): JsonResponse
    {
        try {
            // 1. Создаем запись в БД
            $entity = $this->createIncomeRequest($uploadRequest);
            $this->statusManager->markAsNew($entity);

            // 2. Сохраняем загруженный шаблон
            $this->saveTemplate($uploadRequest, $entity);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            // 3. Отправляем в очередь для обработки
            $message = new ProcessDocument($entity->getId(), $entity->getTemplateName());
            $messageBus->dispatch($message);

            return new JsonResponse(
                ApiResponse::success('Document sent for processing', [
                    'id' => $entity->getId()
                ])->toArray()
            );

        } catch (\Exception $e) {
            if (isset($entity)) {
                $this->statusManager->markAsError($entity, $e->getMessage());
                $this->cleanupOnError($entity);
            }
            throw new DocumentGenerationException('Error processing request: ' . $e->getMessage());
        }
    }

    private function createIncomeRequest(UploadRequest $uploadRequest): IncomeRequest
    {
        $entity = new IncomeRequest();
        $templateName = uniqid() . '_' . $uploadRequest->file->getClientOriginalName();

        $entity->setJsonData($uploadRequest->json);
        $entity->setTemplateName($templateName);
        $entity->setCreatedAt(new \DateTimeImmutable());

        return $entity;
    }

    private function saveTemplate(UploadRequest $uploadRequest, IncomeRequest $entity): void
    {
        $this->filesystem->copy(
            $uploadRequest->file->getRealPath(),
            $this->documentGenerator->getTemplatesDir() . $entity->getTemplateName(),
            true
        );
    }

    private function cleanupOnError(?IncomeRequest $entity): void
    {
        if ($entity && $entity->getTemplateName()) {
            $templatePath = $this->documentGenerator->getTemplatesDir() . $entity->getTemplateName();
            if ($this->filesystem->exists($templatePath)) {
                $this->filesystem->remove($templatePath);
            }
        }
    }
}