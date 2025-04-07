<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/files-interface', name: 'app_file')]
final class FileController extends AbstractController
{
    #[Route('/main', name: 'app_file_main')]
    public function main(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/resize', name: 'app_file_resize')]
    public function resize(): Response
    {
        return $this->render('file/resize.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }
}
