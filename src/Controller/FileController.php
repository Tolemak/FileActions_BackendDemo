<?php

namespace App\Controller;

use App\Service\ExtensionToConvert;
use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function App\replace_extension;

#[Route('/file', name: 'app_file')] // Base route for all file-related actions.
final class FileController extends AbstractController
{
    #[Route('/resize/{size}', requirements: ["size" => "\d+"], name: 'app_file_resize', methods: ['POST'])]
    // Hierarchical route: '/file/resize/{size}' builds on the base '/file' route.
    // Validates 'size' as a numeric parameter and restricts the method to POST.
    public function resizeAction(Request $request, FileService $fileService, int $size): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        if ($file === null) {
            return new Response("File not found", Response::HTTP_BAD_REQUEST);
        } else if ($file->getClientMimeType() !== 'image/jpeg' && $file->getClientMimeType() !== 'image/png') {
            return new Response("File must be a JPEG or PNG", Response::HTTP_BAD_REQUEST);
        } else if ($size < 10 || $size > 300) {
            return new Response("Size must be between 10 and 300", Response::HTTP_BAD_REQUEST);
        } else if ($file->getSize() > 5000000) {
            return new Response("File size must be less than 5MB", Response::HTTP_BAD_REQUEST);
        }

        try {
            $r = $fileService->resize($file, $size);
            return new BinaryFileResponse(
                $r,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(), // Maintains the original MIME type.
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"', // Sets the original file name for download.
                ]
            );
        } catch (\Exception $e) {
            return new Response("Error resizing file: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/convert/{extension}', name: 'app_file_convert', methods: ['POST'])]
    public function convertAction(Request $request, FileService $fileService, string $extension): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        if ($file === null) {
            return new Response("File not found", Response::HTTP_BAD_REQUEST);
        } else if ($file->getClientMimeType() !== 'image/jpeg' && $file->getClientMimeType() !== 'image/png' && $file->getClientMimeType() !== 'image/gif') {
            return new Response("File must be a JPEG or PNG or GIF", Response::HTTP_BAD_REQUEST);
        } else if ($extension !== 'jpeg' && $extension !== 'png' && $extension !== 'gif') {
            return new Response("Extension must be jpeg, png or gif", Response::HTTP_BAD_REQUEST);
        } else if ($file->getSize() > 5000000) {
            return new Response("File size must be less than 5MB", Response::HTTP_BAD_REQUEST);
        }

        try {
            $r = $fileService->changeExtension($file, ExtensionToConvert::from($extension));
            return new BinaryFileResponse(
                $r,
                Response::HTTP_OK,
                [
                    'Content-Type' => "image/" . $extension, // Maintains the original MIME type.
                    'Content-Disposition' => 'attachment; filename="' . replace_extension($file->getClientOriginalName(), $extension) . '"', // Sets the original file name for download.
                ]
            );
        } catch (\Exception $e) {
            return new Response("Error converting file: " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
