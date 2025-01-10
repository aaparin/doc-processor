<?php
namespace App\MessageHandler;

use App\Entity\IncomeRequest;
use App\Message\ProcessDocument;
use App\Repository\IncomeRequestRepository;
use App\Service\Document\DocumentStatusManager;
use App\Service\Document\DocumentGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessDocumentHandler
{
    public function __construct(
        private readonly IncomeRequestRepository $repository,
        private readonly DocumentStatusManager $statusManager,
        private readonly DocumentGeneratorInterface $documentGenerator
    ) {}

    public function __invoke(ProcessDocument $message): void
    {
        $request = $this->repository->find($message->getRequestId());

        if (!$request) {
            throw new \RuntimeException('Document request not found');
        }

        try {
            // 1. Генерируем DOCX
            $this->statusManager->markAsProcessing($request);
            $generatedFile = $this->documentGenerator->generate(
                $request->getJsonData(),
                $request->getTemplateName()
            );

            // 2. Отправляем в очередь для конвертации в PDF
            // Тут нужно отправить в другую очередь для второго микросервиса
            // и сохранить временный результат
            $this->statusManager->markAsConverting($request, $generatedFile);

            // Можно создать отдельный Message для конвертации
//            $this->messageBus->dispatch(new ConvertToPdf(
//                $request->getId(),
//                $generatedFile
//            ));

        } catch (\Exception $e) {
            $this->statusManager->markAsError($request, $e->getMessage());
            throw $e;
        }
    }
}