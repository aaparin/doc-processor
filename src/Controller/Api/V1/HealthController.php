<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Response\ApiResponse;
use App\Service\HealthCheck\HealthCheckService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class HealthController
{
    public function __construct(
        private readonly HealthCheckService $healthCheckService
    ) {}

    #[Route('/health', name: 'v1_health_check', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/health',
        description: 'Check the health status of the service and its dependencies',
        summary: 'Service health check',
        tags: ['Monitoring'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Health check passed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Service is healthy'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'database', type: 'boolean'),
                                new OA\Property(property: 'storage', type: 'boolean'),
                                new OA\Property(property: 'version', type: 'string'),
                                new OA\Property(
                                    property: 'system',
                                    properties: [
                                        new OA\Property(property: 'memory', type: 'string'),
                                        new OA\Property(property: 'disk_space', type: 'string')
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Service unhealthy',
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function check(): JsonResponse
    {
        $health = $this->healthCheckService->check();

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return new JsonResponse(
            ApiResponse::success('Health check completed', $health)->toArray(),
            $statusCode
        );
    }
}