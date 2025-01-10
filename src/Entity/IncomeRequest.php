<?php

namespace App\Entity;

use App\Repository\IncomeRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomeRequestRepository::class)]
class IncomeRequest
{
    final public const STATUS_NEW = 'new';
    final public const STATUS_PROCESSING = 'processing';
    final public const STATUS_CONVERTING_TO_PDF = 'converting_to_pdf';
    final public const STATUS_COMPLETED = 'completed';
    final public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $json_data = null;

    #[ORM\Column(length: 255)]
    private ?string $template_name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'new'])]
    private string $status = 'new';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error_message = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $output_file = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completed_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getJsonData(): ?string
    {
        return $this->json_data;
    }

    public function setJsonData(string $json_data): static
    {
        $this->json_data = $json_data;
        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->template_name;
    }

    public function setTemplateName(string $template_name): static
    {
        $this->template_name = $template_name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_NEW, self::STATUS_PROCESSING, self::STATUS_CONVERTING_TO_PDF, self::STATUS_COMPLETED, self::STATUS_ERROR])) {
            throw new \InvalidArgumentException('Invalid status provided');
        }

        $this->status = $status;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $error_message): static
    {
        $this->error_message = $error_message;
        return $this;
    }

    public function getOutputFile(): ?string
    {
        return $this->output_file;
    }

    public function setOutputFile(?string $output_file): static
    {
        $this->output_file = $output_file;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?\DateTimeImmutable $completed_at): static
    {
        $this->completed_at = $completed_at;
        return $this;
    }
}