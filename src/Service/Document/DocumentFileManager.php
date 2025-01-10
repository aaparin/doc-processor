<?php

declare(strict_types=1);

namespace App\Service\Document;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class DocumentFileManager
{
    private string $templatesDir;
    private string $outputDir;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private Filesystem $filesystem,
        ?string $templatesDir = null,
        ?string $outputDir = null
    ) {
        $this->templatesDir = $templatesDir ?? $this->parameterBag->get('kernel.project_dir') . '/templates/documents/';
        $this->outputDir = $outputDir ?? $this->parameterBag->get('kernel.project_dir') . '/public/generated_docs/';
        $this->ensureDirectoriesExist();
    }

    public function getTemplatesDir(): string
    {
        return $this->templatesDir;
    }

    public function getOutputDir(): string
    {
        return $this->outputDir;
    }

    public function generateOutputFilePath(): string
    {
        return $this->outputDir . 'output_' . uniqid() . '_' . time() . '.docx';
    }

    public function templateExists(string $templateName): bool
    {
        return $this->filesystem->exists($this->templatesDir . $templateName);
    }

    private function ensureDirectoriesExist(): void
    {
        $this->filesystem->mkdir($this->templatesDir, 0755);
        $this->filesystem->mkdir($this->outputDir, 0755);
    }
}