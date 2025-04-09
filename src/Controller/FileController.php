<?php

namespace App\Controller;

use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file', name: 'app_file')]
final class FileController extends AbstractController
{
    #[Route('/resize/{size}', requirements: ["size" => "\d+"], name: 'app_file_resize', methods: ['POST'])]
    public function resizeAction(Request $request, FileService $fileService, int $size): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        if ($file === null) {
            return new Response("File not found", Response::HTTP_BAD_REQUEST);
        } else if ($file->getClientMimeType() !== 'image/jpeg' && $file->getClientMimeType() !== 'image/png') {
            return new Response("File must be a JPEG or PNG", Response::HTTP_BAD_REQUEST);
        } else   if ($size < 10 || $size > 300) {
            return new Response("Size must be between 1 and 100", Response::HTTP_BAD_REQUEST);
        } else if ($file->getSize() > 5000000) {
            return new Response("File size must be less than 5MB", Response::HTTP_BAD_REQUEST);
        }

        try {
            $r = $fileService->resize($file, $size);
            return new BinaryFileResponse(
                $r,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(),
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
                ]
            );
        } catch (\Exception $e) {
            return new Response("Error resizing file: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
