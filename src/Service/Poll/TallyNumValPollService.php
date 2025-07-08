<?php

namespace App\Service\Poll;

use App\Entity\Poll;

class TallyNumValPollService
{
    public static function tally(Poll $poll): Poll
    {
        $candidates = [];
        $skippedVotes = [];
        foreach ($poll->getOptions() as $o) {
            $candidates[$o->getId()]['votes'] = array_flip(range(
                $o->getMin(),
                $o->getMax()
            ));
            $candidates[$o->getId()]['votes'] = array_map(function ($e) {
                return 0;
            }, $candidates[$o->getId()]['votes']);
            $candidates[$o->getId()]['option'] = $o;
            $candidates[$o->getId()]['voters'] = [];
        }
        foreach ($poll->getVotes() as $v) {
            if (
                !in_array(
                    $v->getPlayer()->getCkey(),
                    $candidates[$v->getOption()]['voters']
                )
            ) {
                $candidates[$v->getOption()]['votes'][(int) $v->getText()]++;
                $candidates[$v->getOption()]['voters'][] =
                    $v->getPlayer()->getCkey();
            } else {
                $skippedVotes[] = $v->getPlayer()->getCkey();
            }
        }

        foreach ($candidates as &$v) {
            arsort($v['votes']);
        }
        $poll->setResults($candidates);
        return $poll;
    }
}
