<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Error response schema',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Error message description'),
        new OA\Property(
            property: 'errors',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'field', type: 'string'),
                    new OA\Property(property: 'message', type: 'string')
                ],
                type: 'object'
            ),
            nullable: true
        )
    ],
    type: 'object'
)]
class ApiSchemas
{
}