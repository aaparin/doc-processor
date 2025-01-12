<?php

declare(strict_types=1);

namespace App\DTO;

class DocumentProcessingResult
{
    public function __construct(
        private readonly bool $success,
        private readonly ?string $pdfContent = null,
        private readonly ?string $error = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getPdfContent(): ?string
    {
        return $this->pdfContent;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}