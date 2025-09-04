<?php

namespace App\Controller;

use App\Entity\Badger\BadgerRequest;
use App\Entity\Map\Render;
use App\Factory\SpeciesFactory;
use App\Form\BadgerType;
use App\Service\BadgerService;
use App\Service\Icons\RenderDMI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/badger', name: 'badger')]
final class BadgerController extends AbstractController
{
    public function __construct(
        private RenderDMI $renderDMI,
        private BadgerService $badger
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        $form = $this->createForm(BadgerType::class, null, [
            'action' => $this->generateUrl('badger.generate'),
            'method' => 'POST'
        ]);

        return $this->render('badger/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/generate', methods: ['POST'], name: '.generate')]
    public function generate(Request $request): Response
    {
        $form = $this->createForm(BadgerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BadgerRequest $badgerRequest */
            $badgerRequest = $form->getData();

            return new JsonResponse([
                'output' => $this->badger->generate($badgerRequest),
                'request' => $badgerRequest
            ]);
        }

        return new JsonResponse($form->getErrors());
    }
}
