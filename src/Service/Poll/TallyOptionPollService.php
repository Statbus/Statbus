<?php

namespace App\Service\Poll;

use App\Entity\Poll;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election;
use CondorcetPHP\Condorcet\Vote;
use Exception;
use \CondorcetPHP\Condorcet\Algo\StatsVerbosity;

class TallyOptionPollService
{

    public const MODE = 'Schulze';

    public static function tally(Poll $poll): Poll
    {
        $result = [];
        $voters = [];
        foreach ($poll->getOptions() as $o) {
            $result[$o->getText()] = 0;
        }
        foreach ($poll->getVotes() as $v) {
            if (!in_array($v->getPlayer()->getCkey(), $voters)) {
                $result[$v->getText()]++;
                $voters[] = $v->getPlayer()->getCkey();
            } else {
            }
        }
        arsort($result);
        $poll->setResults($result);
        $poll->setVoteCount(count($voters));
        return $poll;
    }
}
