<?php

namespace App\Tests\Controller;

use App\Controller\Api\V1\DocumentController;
use App\DTO\UploadRequest;
use App\Entity\IncomeRequest;
use App\Service\Document\WordDocumentGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiControllerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private WordDocumentGenerator $documentGenerator;
    private DocumentController $controller;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/doc_generator_test_' . uniqid() . '/';
        mkdir($this->tempDir . 'templates', 0777, true);
        mkdir($this->tempDir . 'generated_docs', 0777, true);

        $this->documentGenerator = new WordDocumentGenerator(
            $this->tempDir . 'templates/',
            $this->tempDir . 'generated_docs/'
        );

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->controller = new DocumentController(
            $this->entityManager,
            $this->documentGenerator
        );

        // Создаем базовый docx файл для тестов
        copy(__DIR__ . '/../Resources/template.docx', $this->tempDir . 'templates/template.docx');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testUploadSuccess(): void
    {
        // Создаем копию тестового шаблона для загрузки
        $tempFile = $this->tempDir . 'upload_template.docx';
        copy(__DIR__ . '/../Resources/template.docx', $tempFile);

        // Create a real UploadedFile
        $file = new UploadedFile(
            $tempFile,
            'template.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $uploadRequest = new UploadRequest();
        $uploadRequest->file = $file;
        $uploadRequest->json = '{"name": "John Doe"}';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (IncomeRequest $entity) {
                return $entity->getTemplateName() === 'template.docx'
                    && $entity->getJsonData() === '{"name": "John Doe"}';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->upload($uploadRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('id', $content);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }
}