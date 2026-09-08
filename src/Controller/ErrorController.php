<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        return $this->render('file/error.html.twig', [
            'status_code' => $statusCode,
        ], new Response('', $statusCode));
    }
}
