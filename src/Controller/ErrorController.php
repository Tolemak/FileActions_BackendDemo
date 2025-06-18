<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(): Response
    {
        // Symfony will pass the status code to the template automatically
        return $this->render('file/404.html.twig', [], new Response('', 404));
    }
}
