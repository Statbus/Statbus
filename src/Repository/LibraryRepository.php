<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Player;
use App\Enum\Library\Category;
use App\Enum\Ticket\Action;
use DateTimeImmutable;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;

class LibraryRepository extends TGRepository
{

    public const TABLE = 'library';
    public const ALIAS = 'l';

    public const ENTITY = Book::class;

    public const ORDERBY = 'l.datetime';

    public const COLUMNS = [
        'l.id',
        'l.author',
        'l.title',
        'l.content',
        'l.category',
        'l.ckey as player',
        'l.datetime as date',
        'l.round_id_created as round',
        'p.rank'
    ];

    public function getBaseQuery(): QueryBuilder
    {
        $qb = parent::getBaseQuery();
        $qb->leftJoin(static::ALIAS, 'admin', 'p', 'p.ckey = l.ckey');
        $qb->orderBy('l.datetime', 'DESC');
        $qb->andWhere('l.deleted IS NULL');
        return $qb;
    }

    public function parseRow(array $r): Book
    {
        $rank = $this->rankService->getRankByName($r['rank']);
        $player = Player::newDummyPlayer($r['player'], $rank);
        return new Book(
            id: $r['id'],
            author: $r['author'],
            title: $r['title'],
            content: $this->HTMLSanitizerService->sanitizeString($r['content']),
            category: Category::tryFrom($r['category']) ?? Category::FICTION,
            player: $player,
            date: new DateTimeImmutable($r['date']),
            round: $r['round']
        );
    }

    public function getLibrary(int $page = 1): PaginationInterface
    {
        $query = $this->getBaseQuery();
        $pagination = $this->paginatorInterface->paginate($query, $page, 30);
        $tmp = $pagination->getItems();
        foreach ($tmp as &$r) {
            $r = $this->parseRow($r);
        }
        $pagination->setItems($tmp);
        return $pagination;
    }

    public function getBook(int $id): Book
    {
        $qb = $this->getBaseQuery();
        $qb->where('l.id = ' . $qb->createNamedParameter($id));
        $result = $qb->executeQuery()->fetchAssociative();
        return $this->parseRow($result);
    }

    public function deleteBook(Book $book): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update(static::TABLE, static::ALIAS)
            ->set('l.deleted', $qb->createNamedParameter(true))
            ->where('l.id = ' . $qb->createNamedParameter($book->getId()))
            ->executeStatement();
    }
}
