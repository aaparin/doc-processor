<?php

declare(strict_types=1);

namespace App\Message;

class PdfConversionResult
{
    public function __construct(
        private readonly int $requestId,
        private readonly bool $success,
        private readonly ?string $pdfPath = null,
        private readonly ?string $error = null
    ) {}

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}