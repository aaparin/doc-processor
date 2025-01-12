<?php

declare(strict_types=1);

namespace App\Service\HealthCheck;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class HealthCheckService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Filesystem $filesystem,
        private readonly ParameterBagInterface $params,
        private readonly string $templatesDir,
        private readonly string $version = '1.0.0'
    ) {}

    public function check(): array
    {
        $storageStatus = $this->checkStorage();
        $systemStatus = $this->checkSystem();

        $isHealthy = $storageStatus;

        return [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'storage' => $storageStatus,
            'version' => $this->version,
            'system' => $systemStatus
        ];
    }

    private function checkStorage(): bool
    {
        return $this->filesystem->exists($this->templatesDir)
            && is_writable($this->templatesDir);
    }

    private function checkSystem(): array
    {
        return [
            'memory' => $this->formatBytes(memory_get_usage(true)),
            'disk_space' => $this->formatBytes((int)disk_free_space($this->templatesDir))
        ];
    }

    private function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float)$bytes; // Приводим к float для корректных вычислений
        $level = 0;

        while ($bytes >= 1024 && $level < count($units) - 1) {
            $bytes /= 1024;
            $level++;
        }

        return round($bytes, 2) . ' ' . $units[$level];
    }
}