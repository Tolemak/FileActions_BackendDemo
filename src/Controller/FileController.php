<?php

namespace App\Controller;

use App\Action\ActionRegistry;
use App\Action\ChangesOutputFormat;
use App\Enum\ExtensionToConvert;
use App\Exception\ImageTooLargeException;
use App\Exception\InvalidImageException;
use App\Http\DownloadFilename;
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

#[Route('/file', name: 'app_file_')]
final class FileController extends AbstractController
{
    private const int MAX_UPLOAD_BYTES = 5_000_000;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly ActionRegistry $registry,
    ) {
    }

    #[Route(
        '/{action}/{value}',
        name: 'process',
        requirements: ['action' => '[a-z0-9-]+', 'value' => '[^/]+'],
        defaults: ['value' => null],
        methods: ['POST'],
    )]
    public function process(Request $request, FileServiceInterface $fileService, string $action, ?string $value): Response
    {
        $fileAction = $this->registry->find($action) ?? throw $this->createNotFoundException();
        $option = $fileAction->option();

        if ($option === null ? $value !== null : ($value === null || !$option->isRoutable($value))) {
            throw $this->createNotFoundException();
        }

        $file = $this->resolveFile($request);
        if ($file instanceof Response) {
            return $file;
        }

        $normalized = null;
        if ($option !== null && $value !== null) {
            if (!$option->isValid($value)) {
                return new Response($this->translator->trans('error.' . $action . '_invalid'), Response::HTTP_BAD_REQUEST);
            }
            $normalized = $option->normalize($value);
        }

        return $this->respondWithProcessedFile(
            static fn (): string => $fileService->process(
                $file,
                static fn (\Imagick $image) => $fileAction->process($image, $normalized),
            ),
            $file,
            'error.' . $action . '_failed',
            $fileAction instanceof ChangesOutputFormat && is_string($normalized) ? $fileAction->outputFormat($normalized) : null,
        );
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

        if (ExtensionToConvert::fromMimeType((string) $file->getMimeType()) === null) {
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
        UploadedFile $source,
        string $failureKey,
        ?ExtensionToConvert $outputFormat = null,
    ): Response {
        $outputFormat ??= ExtensionToConvert::fromMimeType((string) $source->getMimeType())
            ?? throw new \LogicException('Unsupported source format.');
        $filename = DownloadFilename::fromClientName($source->getClientOriginalName(), $outputFormat->fileExtension());

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

        $response = new BinaryFileResponse($resultPath, Response::HTTP_OK, ['Content-Type' => $outputFormat->mimeType()]);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename->name, $filename->asciiFallback);
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
