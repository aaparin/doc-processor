<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UploadRequest
{
    #[Assert\NotNull(message: 'File is required.')]
    #[Assert\File(
        mimeTypes: [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        mimeTypesMessage: 'The file must be a valid .docx document.'
    )]
    public $file;

    #[Assert\NotNull(message: 'JSON data is required.')]
    #[Assert\Json(message: 'Invalid JSON syntax.')]
    public $json;
}
