<?php

namespace App\Controller;

use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file', name: 'app_file')]
final class FileController extends AbstractController
{
    #[Route('/resize/{size}', requirements: ["size" => "\d+"], name: 'app_file_resize', methods: ['POST'])]
    public function resizeAction(Request $request, FileService $fileService, int $size): Response
    {
        $r = $fileService->resize($request->files->get('file'), $size);
        return new Response("");
        // return $this->render('file/resize.html.twig', [
        //     'controller_name' => 'FileController',
        // ]);
    }
}
