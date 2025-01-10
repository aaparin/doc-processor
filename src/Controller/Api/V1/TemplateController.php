<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\DTO\TemplateAnalyzeRequest;
use App\Exception\TemplateAnalysisException;
use App\Response\ApiResponse;
use App\Service\TemplateAnalyzer;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class TemplateController
{
    #[Route('/templates/analyze', name: 'v1_analyze_template', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/templates/analyze',
        description: 'Analyzes uploaded template file and returns its structure',
        summary: 'Analyze template structure',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary',
                            description: 'Template file to analyze (.docx)'
                        )
                    ]
                )
            )
        ),
        tags: ['Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Template analysis successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'variables', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'tables', type: 'array', items: new OA\Items(type: 'string'))
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Template analysis failed',
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function analyzeTemplate(
        TemplateAnalyzeRequest $request,
        TemplateAnalyzer $analyzer,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            $tempPath = $request->file->getRealPath();
            $analysis = $analyzer->analyze($tempPath);

            $logger->debug('Template analysis result', ['analysis' => $analysis]);

            return new JsonResponse(
                ApiResponse::success('Template analyzed successfully', $analysis)->toArray()
            );

        } catch (TemplateAnalysisException $e) {
            $logger->error('Template analysis failed', ['error' => $e->getMessage()]);
            return new JsonResponse(
                ApiResponse::error('Error analyzing template: ' . $e->getMessage())->toArray(),
                422
            );
        } catch (\Exception $e) {
            $logger->error('Internal server error during template analysis', ['error' => $e->getMessage()]);
            return new JsonResponse(
                ApiResponse::error('Internal server error while analyzing template')->toArray(),
                500
            );
        }
    }
}