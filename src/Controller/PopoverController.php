<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BAN')]
#[Route('/popover')]
class PopoverController extends AbstractController
{
    #[Route('/ip/{ip}', name: 'popover.ip')]
    public function ip(string $ip): Response
    {
        return $this->render('popover/ip.html.twig', [
            'ip' => $ip
        ]);
    }

    #[Route('/cid/{cid}', name: 'popover.cid')]
    public function cid(int $cid): Response
    {
        return $this->render('popover/cid.html.twig', [
            'cid' => $cid
        ]);
    }
}
