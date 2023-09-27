<?php

namespace App\Controller\TGDB\Note;

use App\Controller\Controller;
use App\Domain\Note\Repository\NoteRepository;
use Psr\Http\Message\ResponseInterface;
use DI\Attribute\Inject;

class TGDBEditedNotesListingController extends Controller
{
    #[Inject]
    private NoteRepository $noteRepository;

    public function action(): ResponseInterface
    {
        $page = ($this->getArg('page')) ?: 1;
        $notes = $this->noteRepository->getEditedNotes(page: $page);
        return $this->render('tgdb/notes/edits.html.twig', [
            'notes' => $notes,
            'link' => 'tgdb.note',
            'pagination' => [
                'pages' => $this->noteRepository->getPages(),
                'currentPage' => $page,
                'url' => $this->getUriForRoute($this->getRoute()->getRoute()->getName())
            ],
        ]);
    }

}
