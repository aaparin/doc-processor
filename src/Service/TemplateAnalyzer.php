<?php

declare(strict_types=1);

namespace App\Service;

use PhpOffice\PhpWord\TemplateProcessor;

class TemplateAnalyzer
{
    private const TABLE_SEPARATOR = '#';

    private TemplateProcessor $template;

    public function analyze(string $templatePath): array
    {
        $this->template = new TemplateProcessor($templatePath);
        $allVariables = $this->template->getVariables();

        $result = [
            'variables' => [],
            'tables' => []
        ];

        $tableRelatedVars = [];

        foreach ($allVariables as $variable) {
            // if it's a table-related variable
            if ($this->isTableRelated($variable)) {
                $tableRelatedVars[] = $variable;
                if (str_contains($variable, self::TABLE_SEPARATOR)) {
                    $this->processTableVariable($variable, $result['tables']);
                }
                continue;
            }

            $result['variables'][] = $variable;
        }

        return $result;
    }

    private function isTableRelated(string $variable): bool
    {
        // parameter is considered table-related if it's a closing tag, or a table column, or a table name
        return str_starts_with($variable, '/') ||
            str_contains($variable, self::TABLE_SEPARATOR) ||
            preg_match('/^[a-zA-Z0-9_]+$/', $variable) &&
            $this->isTableName($variable);
    }

    private function isTableName(string $variable): bool
    {
        //if the variable is a table name, it should have a closing tag
        return in_array('/' . $variable, $this->getTableClosingTags());
    }

    private function getTableClosingTags(): array
    {
        static $closingTags = null;
        if ($closingTags === null) {
            $closingTags = array_filter($this->template->getVariables(), fn($var) => str_starts_with($var, '/'));
        }
        return $closingTags;
    }

    private function processTableVariable(string $variable, array &$tables): void
    {
        $parts = explode(self::TABLE_SEPARATOR, $variable);

        if (count($parts) !== 2) {
            return;
        }

        [$tableName, $columnName] = $parts;

        if (!isset($tables[$tableName])) {
            $tables[$tableName] = [
                'columns' => []
            ];
        }

        if (!in_array($columnName, $tables[$tableName]['columns'])) {
            $tables[$tableName]['columns'][] = $columnName;
        }
    }
}