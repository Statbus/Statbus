<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HomeController extends AbstractController
{
    #[Route('', name: 'app.home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/logout', name: 'app.logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app.home');
    }

    #[Route('/privacy', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => "Privacy Policy",
            'content' => file_get_contents(dirname(__DIR__) . '/../privacy.md')
        ]);
    }

    #[Route('/changelog', name: 'changelog')]
    public function changelog(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => "Changelog",
            'content' => file_get_contents(dirname(__DIR__) . '/../changelog.md')
        ]);
    }

    #[Route('/content-warning', name: 'content-warning')]
    public function contentWarning(): Response
    {
        return $this->render('markdown.html.twig', [
            'title' => "Content Warning",
            'content' => file_get_contents(dirname(__DIR__) . '/../content-warning.md')
        ]);
    }
}
