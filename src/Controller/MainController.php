<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return new RedirectResponse("/file-view/main");
    }

    #[Route('/phpinfo', name: 'php_info')]
    public function phpInfo(ParameterBagInterface $params)
    {
        if ($params->get("kernel.environment") === "dev") {
            phpinfo();
        } else {
            return new Response("Prod mode is enabled. Please change it to view PHP info.",  Response::HTTP_BAD_REQUEST);
        }
    }
}
