<?php

namespace App\Controller;

use App\Enum\ExtensionToConvert;
use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function App\replace_extension;

#[Route('/file', name: 'app_file_')]
final class FileController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/resize/{size}', requirements: ['size' => '\d+'], name: 'resize', methods: ['POST'])]
    public function resizeAction(Request $request, FileService $fileService, int $size): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        $errorResponse = $this->checkTypicalIssues($file);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        if ($size < 10 || $size > 300) {
            return new Response($this->translator->trans('error.resize_invalid_size'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultPath = $fileService->resize($file, $size);

            $response = new BinaryFileResponse(
                $resultPath,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(),
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
                ]
            );
            $response->deleteFileAfterSend(true);

            return $response;
        } catch (\Exception $e) {
            return new Response(
                $this->translator->trans('error.resize_failed', ['%message%' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/convert/{extension}', name: 'convert', methods: ['POST'])]
    public function convertAction(Request $request, FileService $fileService, string $extension): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        $errorResponse = $this->checkTypicalIssues($file);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $targetExtension = ExtensionToConvert::tryFrom($extension);
        if ($targetExtension === null) {
            return new Response($this->translator->trans('error.convert_invalid_extension'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultPath = $fileService->changeExtension($file, $targetExtension);

            $response = new BinaryFileResponse(
                $resultPath,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'image/' . $extension,
                    'Content-Disposition' => 'attachment; filename="' . replace_extension($file->getClientOriginalName(), $extension) . '"',
                ]
            );
            $response->deleteFileAfterSend(true);

            return $response;
        } catch (\Exception $e) {
            return new Response(
                $this->translator->trans('error.convert_failed', ['%message%' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/compress/{ratio}', requirements: ['ratio' => '\d+'], name: 'compress', methods: ['POST'])]
    public function compressAction(Request $request, FileService $fileService, int $ratio): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        $errorResponse = $this->checkTypicalIssues($file);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        if ($ratio < 1 || $ratio > 100) {
            return new Response($this->translator->trans('error.compress_invalid_ratio'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultPath = $fileService->compress($file, $ratio);

            $response = new BinaryFileResponse(
                $resultPath,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(),
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
                ]
            );
            $response->deleteFileAfterSend(true);

            return $response;
        } catch (\Exception $e) {
            return new Response(
                $this->translator->trans('error.compress_failed', ['%message%' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/rotate/{degrees}', requirements: ['degrees' => '\d+'], name: 'rotate', methods: ['POST'])]
    public function rotateAction(Request $request, FileService $fileService, int $degrees): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        $errorResponse = $this->checkTypicalIssues($file);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        if ($degrees < 0 || $degrees > 360) {
            return new Response($this->translator->trans('error.rotate_invalid_degrees'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultPath = $fileService->rotate($file, $degrees);

            $response = new BinaryFileResponse(
                $resultPath,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(),
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
                ]
            );
            $response->deleteFileAfterSend(true);

            return $response;
        } catch (\Exception $e) {
            return new Response(
                $this->translator->trans('error.rotate_failed', ['%message%' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/sepia/{intensity}', requirements: ['intensity' => '\d+'], name: 'sepia', methods: ['POST'])]
    public function sepiaAction(Request $request, FileService $fileService, int $intensity): Response
    {
        $file = array_values($request->files->all())[0] ?? null;
        $errorResponse = $this->checkTypicalIssues($file);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        if ($intensity < 1 || $intensity > 100) {
            return new Response($this->translator->trans('error.sepia_invalid_intensity'), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultPath = $fileService->applySepiaTone($file, $intensity);

            $response = new BinaryFileResponse(
                $resultPath,
                Response::HTTP_OK,
                [
                    'Content-Type' => $file->getClientMimeType(),
                    'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
                ]
            );
            $response->deleteFileAfterSend(true);

            return $response;
        } catch (\Exception $e) {
            return new Response(
                $this->translator->trans('error.sepia_failed', ['%message%' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function checkTypicalIssues(mixed $file): ?Response
    {
        if ($file === null) {
            return new Response($this->translator->trans('error.file_not_found'), Response::HTTP_BAD_REQUEST);
        }

        if ($file->getClientMimeType() !== 'image/jpeg'
            && $file->getClientMimeType() !== 'image/png'
            && $file->getClientMimeType() !== 'image/gif'
        ) {
            return new Response($this->translator->trans('error.invalid_file_type'), Response::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() > 5_000_000) {
            return new Response($this->translator->trans('error.file_too_large'), Response::HTTP_BAD_REQUEST);
        }

        return null;
    }
}
