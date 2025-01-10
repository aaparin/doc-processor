<?php

declare(strict_types=1);

namespace App\Service\Document;

class TemplateDataProcessor
{
    private const TABLE_SEPARATOR = '#';

    public function processSimpleVariables(array $data, array $variables): array
    {
        $processed = [];
        foreach ($data as $key => $value) {
            if ($key === 'tables') {
                continue;
            }

            if (in_array($key, $variables)) {
                $processed[$key] = (string)$value;
            }
        }

        return $processed;
    }

    public function getTablePlaceholder(string $tableKey, string $column, int $index): string
    {
        return sprintf(
            '%s%s%s%s%d',
            $tableKey,
            self::TABLE_SEPARATOR,
            $column,
            self::TABLE_SEPARATOR,
            $index
        );
    }

    public function validateTableData(array $tableData): bool
    {
        return isset($tableData['rows'])
            && is_array($tableData['rows'])
            && !empty($tableData['rows']);
    }
}