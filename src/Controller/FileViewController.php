<?php

namespace App\Controller;

use App\Action\ActionRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/file-view', name: 'app_view_')]
final class FileViewController extends AbstractController
{
    public function __construct(private readonly ActionRegistry $registry)
    {
    }

    #[Route('/main', name: 'main')]
    public function main(): Response
    {
        return $this->render('file/index.html.twig');
    }

    #[Route('/{action}', name: 'action', requirements: ['action' => '[a-z0-9-]+'])]
    public function action(string $action): Response
    {
        $fileAction = $this->registry->find($action) ?? throw $this->createNotFoundException();

        return $this->render('file/action.html.twig', ['action' => $fileAction]);
    }
}
