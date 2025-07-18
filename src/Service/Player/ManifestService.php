<?php

namespace App\Service\Player;

use App\Entity\Player;
use App\Entity\Round;
use App\Repository\ManifestRepository;

class ManifestService
{
    public function __construct(
        private ManifestRepository $manifestRepository
    ) {}

    public function getCharactersForCkey(Player|string $ckey): array
    {
        $chars = $this->manifestRepository->fetchPlayerCharacters($ckey);
        foreach ($chars as &$c) {
            $c['rounds'] += floor(rand(1, 10));
        }
        return $chars;
    }

    public function getManifestForRound(Round $round): array
    {
        return $this->manifestRepository->fetchRoundManifest($round);
    }
}
