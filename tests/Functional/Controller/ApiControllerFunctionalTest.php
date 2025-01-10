<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiControllerFunctionalTest extends WebTestCase
{
    private string $tempDir;
    private string $templateDir;
    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем временные директории
        $this->tempDir = sys_get_temp_dir() . '/functional_test_' . uniqid() . '/';
        $this->templateDir = dirname(__DIR__, 3) . '/templates/';
        $this->outputDir = dirname(__DIR__, 3) . '/generated_docs/';

        // Создаем все необходимые директории
        foreach ([$this->tempDir, $this->templateDir, $this->outputDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        // Создаем базовый docx файл для тестов
        copy(__DIR__ . '/../../Resources/template.docx', $this->templateDir . 'test_template.docx');
    }

    protected function tearDown(): void
    {
        // Очищаем временные файлы
        $this->removeDirectory($this->tempDir);
        $this->removeDirectory($this->templateDir);
        $this->removeDirectory($this->outputDir);
        parent::tearDown();
    }

    public function testUploadEndpoint(): void
    {
        $client = static::createClient();

        // Создаем тестовый docx файл
        $tempFile = $this->tempDir . 'test_template.docx';
        copy(__DIR__ . '/../../Resources/template.docx', $tempFile);

        // Создаем тестовый JSON
        $jsonData = [
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'john@example.com'
        ];

        // Создаем файл и устанавливаем правильный MIME-тип
        $uploadedFile = new UploadedFile(
            $tempFile,
            'template.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $client->request(
            method: 'POST',
            uri: '/api/upload',
            parameters: [],
            files: ['template' => $uploadedFile],
            server: ['CONTENT_TYPE' => 'multipart/form-data'],
            content: json_encode(['json' => json_encode($jsonData)])
        );

        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertEquals(200, $response->getStatusCode(), $content);

        $responseData = json_decode($content, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testUploadWithInvalidJson(): void
    {
        $client = static::createClient();

        // Создаем тестовый файл
        $tempFile = $this->tempDir . 'test_template.docx';
        copy(__DIR__ . '/../../Resources/template.docx', $tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'template.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $client->request(
            method: 'POST',
            uri: '/api/upload',
            parameters: [],
            files: ['template' => $uploadedFile],
            server: ['CONTENT_TYPE' => 'multipart/form-data'],
            content: json_encode(['json' => 'invalid-json{'])
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testUploadWithoutFile(): void
    {
        $client = static::createClient();

        $client->request(
            method: 'POST',
            uri: '/api/upload',
            parameters: [],
            files: [],
            server: ['CONTENT_TYPE' => 'multipart/form-data'],
            content: json_encode(['json' => json_encode(['name' => 'John'])])
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
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
                    @unlink($path);
                }
            }
        }
        @rmdir($dir);
    }
}