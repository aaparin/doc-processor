<?php

declare(strict_types=1);

namespace App\Response;

class ApiResponse
{
    private function __construct(
        private readonly string $status,
        private readonly string $message,
        private readonly mixed $data = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ]);
    }

    public static function success(string $message, mixed $data = null): self
    {
        return new self('success', $message, $data);
    }

    public static function error(string $message): self
    {
        return new self('error', $message);
    }
}