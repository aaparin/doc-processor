<?php

namespace App\Service\Document;

use App\Entity\IncomeRequest;
use Doctrine\ORM\EntityManagerInterface;

class DocumentStatusManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function markAsNew(IncomeRequest $request): void
    {
        $request->setStatus(IncomeRequest::STATUS_NEW);
        $request->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function markAsProcessing(IncomeRequest $request): void
    {
        $request->setStatus(IncomeRequest::STATUS_PROCESSING);
        $this->entityManager->flush();
    }

    public function markAsConverting(IncomeRequest $request): void
    {
        $request->setStatus(IncomeRequest::STATUS_CONVERTING_TO_PDF);
        $this->entityManager->flush();
    }

    public function markAsCompleted(IncomeRequest $request, string $outputFile): void
    {
        $request->setStatus(IncomeRequest::STATUS_COMPLETED);
        $request->setOutputFile($outputFile);
        $request->setCompletedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function markAsError(IncomeRequest $request, string $errorMessage): void
    {
        $request->setStatus(IncomeRequest::STATUS_ERROR);
        $request->setErrorMessage($errorMessage);
        $this->entityManager->flush();
    }
}