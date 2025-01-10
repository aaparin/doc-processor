<?php

namespace App\Tests\Service;

use App\Service\Document\WordDocumentGenerator;
use PHPUnit\Framework\TestCase;

class DocumentGeneratorTest extends TestCase
{
    private WordDocumentGenerator $generator;
    private string $templatesDir;
    private string $outputDir;

    protected function setUp(): void
    {
        // make temporary directories
        $this->templatesDir = sys_get_temp_dir() . '/test_templates_' . uniqid() . '/';
        $this->outputDir = sys_get_temp_dir() . '/test_output_' . uniqid() . '/';

        mkdir($this->templatesDir);
        mkdir($this->outputDir);

        $this->generator = new WordDocumentGenerator(
            $this->templatesDir,
            $this->outputDir
        );

        copy(
            __DIR__ . '/../Resources/template.docx',
            $this->templatesDir . 'test_template.docx'
        );
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->templatesDir);
        $this->removeDirectory($this->outputDir);
    }

    public function testGenerateDocumentWithInvalidJson(): void
    {
        $this->expectException(\JsonException::class);
        $this->generator->generate('{invalid_json}', 'test_template.docx');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . '/' . $file;
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