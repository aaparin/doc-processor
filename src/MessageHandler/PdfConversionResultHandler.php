<?php

namespace App\MessageHandler;

use App\Message\PdfConversionResult;
use App\Repository\IncomeRequestRepository;
use App\Service\Document\DocumentStatusManager;

#[AsMessageHandler]
class PdfConversionResultHandler
{
    public function __construct(
        private readonly IncomeRequestRepository $repository,
        private readonly DocumentStatusManager $statusManager
    ) {}

    public function __invoke(PdfConversionResult $message): void
    {
        $request = $this->repository->find($message->getRequestId());

        if (!$request) {
            throw new \RuntimeException('Document request not found');
        }

        if ($message->isSuccess()) {
            $this->statusManager->markAsCompleted($request, $message->getPdfPath());
        } else {
            $this->statusManager->markAsError($request, $message->getError());
        }
    }
}