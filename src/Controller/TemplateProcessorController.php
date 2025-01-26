<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TemplateProcessorController extends AbstractController
{
    #[Route('/', name: 'front')]
    public function index(): Response
    {
        return $this->render('template_processor/index.html.twig');
    }
}