<?php

namespace App\Repository;

use App\Security\User;

class CharacterImageRepository extends StatbusRepository
{
    public function insertNewEntry(
        User $user,
        string $characterName,
        string $image
    ) {
        $qb = $this->qb();
        $qb
            ->insert('character_image')
            ->values([
                'ckey' => $qb->createNamedParameter($user->getCkey()),
                'character_name' => $qb->createNamedParameter($characterName),
                'image' => $qb->createNamedParameter($image)
            ])
            ->executeStatement();
    }

    public function updateEntry(
        User $user,
        string $characterName,
        string $image
    ): void {
        $qb = $this->qb();
        $qb
            ->update('character_image')
            ->set('image', $qb->createNamedParameter($image))
            ->where('ckey = ' . $qb->createNamedParameter($user->getCkey()))
            ->andWhere('character_name = ' .
                $qb->createNamedParameter($characterName));
        $qb->executeStatement();
    }

    public function fetchImagesForUser(User $user): array
    {
        return $this->fetchImagesForCkey($user->getCkey());
    }

    public function fetchImagesForCkey(string $ckey): array
    {
        $qb = $this->qb();
        $qb
            ->select('character_name', 'image')
            ->from('character_image')
            ->where('ckey = ' . $qb->createNamedParameter($ckey));
        $results = $qb->executeQuery()->fetchAllKeyValue();
        return $results;
    }
}
