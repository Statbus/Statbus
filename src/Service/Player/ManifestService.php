<?php
namespace App\Service\Player;

use App\Entity\Player;
use App\Entity\Round;
use App\Repository\ManifestRepository;

class ManifestService
{
    public function __construct(
        private ManifestRepository $manifestRepository
    ) {

    }

    public function getCharactersForCkey(Player | string $ckey): array
    {
        return $this->manifestRepository->fetchPlayerCharacters($ckey);
    }

    public function getManifestForRound(Round $round): array
    {
        return $this->manifestRepository->fetchRoundManifest($round);
    }
}
