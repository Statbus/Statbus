<?php

namespace App\Controller;

use App\Repository\BanRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class BanController extends AbstractController
{

    public function __construct(
        private BanRepository $banRepository
    ) {}

    #[Route('/bans/{page}', name: 'bans')]
    public function index(int $page = 1): Response
    {
        $tgdb = false;
        if ($this->isGranted('ROLE_BAN')) {
            //User has TGDB access, show all bans
            $tgdb = true;
            $pagination = $this->banRepository->getBans($page);
        } else {
            //User does not have TGDB access, limiting them to bans applied to
            //their ckey
            $pagination = $this->banRepository->getBansForPlayer(
                $page,
                $this->getUser()
            );
        }
        return $this->render('ban/index.html.twig', [
            'tgdb' => $tgdb,
            'pagination' => $pagination
        ]);
    }

    #[Route('/ban/{id}', name: 'ban.view')]
    public function ban(int $id): Response
    {
        $tgdb = false;
        $ban = $this->banRepository->getBan($id);
        if (!$this->isGranted('ROLE_BAN') && $ban->getTarget()->getCkey() != $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException("You do not have permission to view this ban");
        } elseif ($this->isGranted('ROLE_BAN')) {
            $tgdb = true;
        }
        return $this->render('ban/ban.html.twig', [
            'ban' => $ban,
            'tgdb' => true
        ]);
    }
}
