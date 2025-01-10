<?php
namespace App\Message;

class ProcessDocument
{
    public function __construct(
        private int $filePath,
        private string $requestId
    ) {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }
}