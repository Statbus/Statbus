<?php

namespace App\Controller;

use App\Form\AllowListType;
use App\Service\AllowListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tgdb')]
class TGDBController extends AbstractController
{

    public function __construct(
        private AllowListService $allowListService
    ) {}

    #[Route('/allow', name: 'tgdb.allow', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PERMISSIONS')]
    public function allow(Request $request): Response
    {
        $form = $this->createForm(AllowListType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->allowListService->addCkeyToAllowList(
                ckey: $form->get('ckey')->getData(),
                admin: $this->getUser(),
                expiration: $form->get('expiration')->getData(),
                reason: $form->get('reason')->getData()
            );
            return $this->redirectToRoute('tgdb.allow');
        }
        return $this->render('tgdb/allowList.html.twig', [
            'form' => $form->createView(),
            'list' => $this->allowListService->getActiveList()
        ]);
    }
    #[Route('/allow/revoke/{entry}', name: 'tgdb.revoke', methods: ['POST'])]
    #[IsGranted('ROLE_PERMISSIONS')]
    public function revoke(int $entry): Response
    {
        $this->allowListService->revokeEntry($entry, $this->getUser());
        return $this->redirectToRoute('tgdb.allow');
    }
}
