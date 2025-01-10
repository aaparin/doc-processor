<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\DocumentGenerationException;
use App\Service\Document\DocumentGeneratorInterface;

class DocumentGeneratorFactory
{
    /**
     * @var DocumentGeneratorInterface[]
     */
    private array $generators;

    public function __construct(iterable $generators)
    {
        $this->generators = iterator_to_array($generators);
    }

    public function getGenerator(string $mimeType): DocumentGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($mimeType)) {
                return $generator;
            }
        }

        throw new DocumentGenerationException(
            sprintf('No document generator found for mime type: %s', $mimeType)
        );
    }
}