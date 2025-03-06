<?php

namespace App\Service\Poll;

use App\Entity\Poll;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election;
use CondorcetPHP\Condorcet\Vote;
use Exception;
use \CondorcetPHP\Condorcet\Algo\StatsVerbosity;

class TallyNumValPollService
{


    public static function tally(Poll $poll): Poll
    {
        $voters = [];
        $option = $poll->getOptions()[0];
        $poll->setSubtitle($option->getText());
        $result = array_flip(range($option->getMin(), $option->getMax()));
        $result = array_map(function ($e) {
            return 0;
        }, $result);
        dump($poll->getOptions());
        foreach ($poll->getVotes() as $v) {
            if (!in_array($v->getPlayer()->getCkey(), $voters)) {
                $result[$v->getText()]++;
                $voters[] = $v->getPlayer()->getCkey();
            } else {
                dump("Duplicate vote detected from " . $v->getPlayer()->getCkey());
            }
        }
        arsort($result);
        $poll->setResults($result);
        $poll->setVoteCount(count($voters));
        return $poll;
    }
}
