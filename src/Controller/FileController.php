<?php

namespace App\Controller;

use App\Enum\ExtensionToConvert;
use App\Exception\ImageTooLargeException;
use App\Exception\InvalidImageException;
use App\Service\FileServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function App\replace_extension;

#[Route('/file', name: 'app_file_')]
final class FileController extends AbstractController
{
    private const array ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

    private const int MAX_UPLOAD_BYTES = 5_000_000;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/resize/{size}', requirements: ['size' => '\d+'], name: 'resize', methods: ['POST'])]
    public function resizeAction(Request $request, FileServiceInterface $fileService, int $size): Response
    {
        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $error = $this->validateRange($size, 10, 300, 'error.resize_invalid_size');
        if ($error !== null) {
            return $error;
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->resize($file, $size),
            $file->getClientOriginalName(),
            (string) $file->getMimeType(),
            'error.resize_failed',
        );
    }

    #[Route('/convert/{extension}', name: 'convert', methods: ['POST'])]
    public function convertAction(Request $request, FileServiceInterface $fileService, string $extension): Response
    {
        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $targetExtension = ExtensionToConvert::tryFrom($extension);
        if ($targetExtension === null) {
            return new Response($this->translator->trans('error.convert_invalid_extension'), Response::HTTP_BAD_REQUEST);
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->changeExtension($file, $targetExtension),
            replace_extension($file->getClientOriginalName(), $targetExtension->value),
            'image/' . $targetExtension->value,
            'error.convert_failed',
        );
    }

    #[Route('/compress/{ratio}', requirements: ['ratio' => '\d+'], name: 'compress', methods: ['POST'])]
    public function compressAction(Request $request, FileServiceInterface $fileService, int $ratio): Response
    {
        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $error = $this->validateRange($ratio, 1, 100, 'error.compress_invalid_ratio');
        if ($error !== null) {
            return $error;
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->compress($file, $ratio),
            $file->getClientOriginalName(),
            (string) $file->getMimeType(),
            'error.compress_failed',
        );
    }

    #[Route('/rotate/{degrees}', requirements: ['degrees' => '\d+'], name: 'rotate', methods: ['POST'])]
    public function rotateAction(Request $request, FileServiceInterface $fileService, int $degrees): Response
    {
        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $error = $this->validateRange($degrees, 0, 360, 'error.rotate_invalid_degrees');
        if ($error !== null) {
            return $error;
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->rotate($file, $degrees),
            $file->getClientOriginalName(),
            (string) $file->getMimeType(),
            'error.rotate_failed',
        );
    }

    #[Route('/sepia/{intensity}', requirements: ['intensity' => '\d+'], name: 'sepia', methods: ['POST'])]
    public function sepiaAction(Request $request, FileServiceInterface $fileService, int $intensity): Response
    {
        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $error = $this->validateRange($intensity, 1, 100, 'error.sepia_invalid_intensity');
        if ($error !== null) {
            return $error;
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->applySepiaTone($file, $intensity),
            $file->getClientOriginalName(),
            (string) $file->getMimeType(),
            'error.sepia_failed',
        );
    }

    private function validateRange(int $value, int $min, int $max, string $errorKey): ?Response
    {
        if ($value < $min || $value > $max) {
            return new Response($this->translator->trans($errorKey), Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * The client-supplied Content-Type is not trusted: getMimeType() sniffs the
     * actual bytes, so a disguised SVG or PDF cannot reach Imagick.
     */
    private function resolveFile(Request $request): UploadedFile|Response
    {
        $file = array_values($request->files->all())[0] ?? null;

        if (!$file instanceof UploadedFile) {
            return new Response($this->translator->trans('error.file_not_found'), Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            return new Response($this->translator->trans('error.invalid_file_type'), Response::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            return new Response($this->translator->trans('error.file_too_large'), Response::HTTP_BAD_REQUEST);
        }

        return $file;
    }

    /**
     * @param callable(): string $operation
     */
    private function respondWithProcessedFile(
        callable $operation,
        string $filename,
        string $contentType,
        string $failureKey,
    ): Response {
        try {
            $resultPath = $operation();
        } catch (ImageTooLargeException) {
            return new Response($this->translator->trans('error.image_too_large'), Response::HTTP_BAD_REQUEST);
        } catch (InvalidImageException) {
            return new Response($this->translator->trans('error.invalid_file_type'), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $this->logger->error('Image processing failed.', ['exception' => $e]);

            return new Response($this->translator->trans($failureKey), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new BinaryFileResponse($resultPath, Response::HTTP_OK, ['Content-Type' => $contentType]);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename, 'download');
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
