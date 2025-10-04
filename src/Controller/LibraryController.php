<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Repository\LibraryRepository;
use App\Service\Library\BookDeletionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[FeatureEnabled('library')]
#[IsGranted('ROLE_USER')]
#[Route('/library', name: 'library')]
class LibraryController extends AbstractController
{
    public function __construct(
        private LibraryRepository $libraryRepository,
        private BookDeletionService $bookDeletionService
    ) {}

    #[Route('/{page}', name: '')]
    public function index(Request $request, int $page = 1): Response
    {
        if ($request->query->get('clear', null)) {
            $request->getSession()->set('term', null);
            return $this->redirectToRoute('library');
        }
        $term = $request->getSession()->get('term', null);
        if ($request->isMethod('POST')) {
            $term = $request->request->get('term', null);
            $request->getSession()->set('term', $term);
        }
        $books = $this->libraryRepository->getLibrary($page, $term);
        return $this->render('library/index.html.twig', [
            'pagination' => $books,
            'term' => $term
        ]);
    }

    #[Route('/book/{id}', name: '.book')]
    public function book(int $id): Response
    {
        $book = $this->libraryRepository->getBook($id);
        return $this->render('library/book.html.twig', [
            'book' => $book,
            'breadcrumb' => [
                'Library' => $this->generateUrl('library'),
                $book->getId() => $this->generateUrl(
                    'library.book',
                    ['id' => $book->getId()]
                )
            ]
        ]);
    }

    #[IsGranted('ROLE_BAN')]
    #[Route('/book/{id}/delete', name: '.book.delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $book = $this->libraryRepository->getBook($id);
        $this->bookDeletionService->deleteBook($book, $this->getUser());
        return $this->redirectToRoute('library');
    }
}
