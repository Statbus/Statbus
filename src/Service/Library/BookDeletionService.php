<?php

namespace App\Service\Library;

use App\Entity\Book;
use App\Enum\ExternalAction\Type;
use App\Repository\ExternalActivityRepository;
use App\Repository\LibraryRepository;
use App\Security\User;

class BookDeletionService
{
    public function __construct(
        private LibraryRepository $libraryRepository,
        private ExternalActivityRepository $externalActivityRepository
    ) {}

    public function deleteBook(Book $book, User $user): void
    {
        $this->libraryRepository->deleteBook($book);
        $this->externalActivityRepository->logExternalAction(
            user: $user,
            type: Type::F541,
            text: 'Deleted book #' . $book->getId()
        );
    }
}
