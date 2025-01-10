<?php

declare(strict_types=1);

namespace App\Service\Document;

interface DocumentGeneratorInterface
{
    public function supports(string $mimeType): bool;
    public function generate(string $jsonData, string $templateName): string;
}