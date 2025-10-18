<?php

namespace App\Repository;

use App\Enum\ExternalAction\Type;
use App\Security\User;
use IPTools\IP;

class ExternalActivityRepository extends TGRepository
{
    public const TABLE = 'external_activity';
    public const ALIAS = 'e';

    // public const ENTITY = Book::class;

    public const ORDERBY = 'e.datetime';

    public const COLUMNS = [
        'e.id',
        'e.datetime',
        'e.ckey',
        'e.ip',
        'e.action',
        'e.text',
        'p.rank'
    ];

    public function logExternalAction(
        User $user,
        Type $type,
        string $text,
        string $ip
    ): void {
        $ip = IP::parse($ip)->toLong();
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert(static::TABLE)
            ->values([
                'ckey' => $qb->createNamedParameter($user->getCkey()),
                'ip' => $qb->createNamedParameter($ip),
                'action' => $qb->createNamedParameter($type->value),
                'text' => $qb->createNamedParameter($text)
            ])
            ->executeStatement();
    }
}
