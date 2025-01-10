<?php

declare(strict_types=1);

namespace App\Service\Document;

class DocumentProcessor
{
    public function __construct(
        private readonly TemplateDataProcessor $templateDataProcessor
    ) {}

    public function processTable(array $tableData, string $tableKey): array
    {
        if (!$this->templateDataProcessor->validateTableData($tableData)) {
            return [];
        }

        $result = [];
        $rows = $tableData['rows'];

        foreach ($rows as $index => $rowData) {
            foreach ($rowData as $column => $value) {
                $placeholder = $this->templateDataProcessor->getTablePlaceholder($tableKey, $column, $index + 1);
                $result[$placeholder] = (string)$value;
            }
        }

        return [
            'replacements' => $result,
            'rowCount' => count($rows)
        ];
    }
}
