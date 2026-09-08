<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file-view', name: 'app_view_')]
final class FileViewController extends AbstractController
{
    #[Route('/main', name: 'main')]
    public function main(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/resize', name: 'resize')]
    public function resize(): Response
    {
        return $this->render('file/resize.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/convert', name: 'convert')]
    public function convert(): Response
    {
        return $this->render('file/convert.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/compress', name: 'compress')]
    public function compress(): Response
    {
        return $this->render('file/compress.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/rotate', name: 'rotate')]
    public function rotate(): Response
    {
        return $this->render('file/rotate.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/sepia', name: 'sepia')]
    public function sepia(): Response
    {
        return $this->render('file/sepia.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }
}
