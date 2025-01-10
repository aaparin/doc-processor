<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TemplateAnalyzeRequest
{
    #[Assert\NotNull(message: 'File is required.')]
    #[Assert\File(
        mimeTypes: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        mimeTypesMessage: 'The file must be a valid .docx document.'
    )]
    public $file;
}