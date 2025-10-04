<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Form\AllowListType;
use App\Form\FeedbackType;
use App\Service\AllowListService;
use App\Service\TGDB\FeedbackLinkService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BAN')]
#[Route('/tgdb')]
class TGDBController extends AbstractController
{
    public function __construct(
        private AllowListService $allowListService,
        private FeedbackLinkService $feedbackLinkService
    ) {}

    #[Route('/allow', name: 'tgdb.allow', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_BAN')]
    #[IsGranted('ROLE_PERMISSIONS')]
    #[FeatureEnabled('tgdb.allowList')]
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
    #[IsGranted('ROLE_BAN')]
    #[IsGranted('ROLE_PERMISSIONS')]
    #[FeatureEnabled('tgdb.allowList')]
    public function revoke(int $entry): Response
    {
        $this->allowListService->revokeEntry($entry, $this->getUser());
        return $this->redirectToRoute('tgdb.allow');
    }

    #[Route('/feedback', name: 'tgdb.feedback', methods: ['GET', 'POST'])]
    #[FeatureEnabled('tgdb.feedback')]
    public function feedback(Request $request): Response
    {
        if ($this->getUser()->hasRole('ROLE_TEMPORARY')) {
            throw new Exception(
                'You do not have permission to access this feature',
                403
            );
        }
        if ('' === $this->feedbackLinkService->getValidUri()) {
            throw new Exception(
                "The 'FEEDBACK_URI' environment variable is not set; this feature is disabled"
            );
        }
        $form = $this->createForm(
            FeedbackType::class,
            ['uri' => $this->getUser()->getFeedbackUri()]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->feedbackLinkService->setFeedbackLink(
                $form->getData()['uri'],
                $this->getUser()
            );
            return $this->redirectToRoute('tgdb.feedback');
        }
        return $this->render('tgdb/feedback.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
