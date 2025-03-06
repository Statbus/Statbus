<?php

namespace App\Service\Poll;

use App\Entity\Poll;

class TallyMultiPollService
{
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
            }
        }
        arsort($result);
        $poll->setResults($result);
        $poll->setVoteCount(count(array_unique($voters)));
        dump($poll);
        return $poll;
    }
}
