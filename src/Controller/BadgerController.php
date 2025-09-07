<?php

namespace App\Controller;

use App\Entity\Badger\BadgerRequest;
use App\Form\BadgerType;
use App\Service\BadgerService;
use App\Service\Icons\RenderDMI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        // $repo = (new Git())->open($this->renderDMI->getIconDir() . '/../');
        // $commit = $repo->getLastCommit();
        return $this->render('badger/index.html.twig', [
            'form' => $form->createView()
            // 'commit' => $commit
        ]);
    }

    #[Route('/generate', methods: ['POST'], name: '.generate')]
    public function generate(Request $request): Response
    {
        $form = $this->createForm(BadgerType::class, new BadgerRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BadgerRequest $badgerRequest */
            $badgerRequest = $form->getData();

            return new JsonResponse([
                'output' => $this->badger->generate($badgerRequest)
            ]);
        }

        return new JsonResponse($form->getErrors());
    }

    #[Route('/generate/assign', methods: ['POST'], name: '.generate.assign')]
    public function generateAndAssign(Request $request): Response
    {
        $form = $this->createForm(BadgerType::class, new BadgerRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $target = $form->get('assign')->getData();
            if (!$target) {
                throw new BadRequestException(
                    'You must specify a character to assign this image to'
                );
            }
            /** @var BadgerRequest $badgerRequest */
            $badgerRequest = $form->getData();
            $output = $this->badger->generate($badgerRequest);
            $this->badger->assignImage($this->getUser(), $target, $output->mob);
            return new JsonResponse([
                'output' => $output
            ]);
        }

        return new JsonResponse($form->getErrors());
    }
}
