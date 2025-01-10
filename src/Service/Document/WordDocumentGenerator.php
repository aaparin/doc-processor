<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Exception\DocumentGenerationException;
use PhpOffice\PhpWord\TemplateProcessor;

class WordDocumentGenerator implements DocumentGeneratorInterface
{
    private const SUPPORTED_MIME_TYPES = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    public function __construct(
        private DocumentFileManager $fileManager,
        private readonly DocumentProcessor $documentProcessor,
        private readonly TemplateDataProcessor $templateDataProcessor,
        ?string $templatesDir = null,
        ?string $outputDir = null
    ) {

    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, self::SUPPORTED_MIME_TYPES);
    }

    public function generate(string $jsonData, string $templateName): string
    {
        if (!$this->fileManager->templateExists($templateName)) {
            throw new DocumentGenerationException(sprintf('Template file "%s" not found', $templateName));
        }

        try {
            $data = $this->decodeJsonData($jsonData);
            $template = new TemplateProcessor($this->fileManager->getTemplatesDir() . $templateName);

            // Process simple variables
            $variables = $template->getVariables();
            $processedVars = $this->templateDataProcessor->processSimpleVariables($data, $variables);

            foreach ($processedVars as $key => $value) {
                $template->setValue($key, $value);
            }

            // Process tables
            if (isset($data['tables'])) {
                $this->processTables($template, $data['tables']);
            }

            $outputFileName = $this->fileManager->generateOutputFilePath();
            $template->saveAs($outputFileName);

            return $outputFileName;

        } catch (\Exception $e) {
            throw new DocumentGenerationException('Error generating document: ' . $e->getMessage(), 0, $e);
        }
    }

    private function processTables(TemplateProcessor $template, array $tables): void
    {
        foreach ($tables as $tableKey => $tableData) {
            $processedTable = $this->documentProcessor->processTable($tableData, $tableKey);

            if (empty($processedTable)) {
                continue;
            }

            $template->cloneBlock($tableKey, $processedTable['rowCount'], true, true);
            foreach ($processedTable['replacements'] as $placeholder => $value) {
                $template->setValue($placeholder, $value);
            }
        }
    }

    private function decodeJsonData(string $jsonData): array
    {
        try {
            return json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new DocumentGenerationException('Invalid JSON data: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getTemplatesDir(): string
    {
        return $this->fileManager->getTemplatesDir();
    }
}