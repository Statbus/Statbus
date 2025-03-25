<?php

namespace App\Controller;

use App\Repository\LibraryRepository;
use App\Service\Library\BookDeletionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/library', name: 'library')]
class LibraryController extends AbstractController
{

    public function __construct(
        private LibraryRepository $libraryRepository,
        private BookDeletionService $bookDeletionService
    ) {}

    #[Route('/{page}', name: '')]
    public function index(int $page = 1): Response
    {
        $books = $this->libraryRepository->getLibrary($page);
        return $this->render('library/index.html.twig', [
            'pagination' => $books
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
                $book->getId() => $this->generateUrl('library.book', ['id' => $book->getId()])
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
