<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file-view', name: 'app_file')]
final class FileViewController extends AbstractController
{
    #[Route('/main', name: 'file_view_main')]
    public function main(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/resize', name: 'file_view_resize')]
    public function resize(): Response
    {
        return $this->render('file/resize.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/convert', name: 'file_view_convert')]
    public function convert(): Response
    {
        return $this->render('file/convert.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route('/compress', name: 'file_view_compress')]
    public function compress(): Response
    {
        return $this->render('file/compress.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }
};
