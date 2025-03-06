<?php

namespace App\Service\Poll;

use App\Entity\Poll;
use CondorcetPHP\Condorcet\Candidate;
use CondorcetPHP\Condorcet\Election;
use CondorcetPHP\Condorcet\Vote;
use Exception;

class TallyIRVPollService
{

    public const MODE = 'IRV';

    public static function tally(Poll $poll): Poll
    {
        $election = new Election();
        foreach ($poll->getOptions() as $option) {
            $election->addCandidate(new Candidate($option->getId()));
        }
        $votes = [];
        foreach ($poll->getVotes() as $v) {
            $votes[$v->getPlayer()->getCkey()][] = $v->getOption();
        }
        foreach ($votes as $ckey => $vote) {
            try {
                $vote = new Vote(implode(' > ', $vote), ['ckey' => $ckey]);
                $election->addVote($vote);
            } catch (Exception $e) {
                dump("Duplicate vote detected from $ckey");
            }
        }
        $result = $election->getResult(static::MODE);
        $winner = $election->getWinner(static::MODE);
        $poll->setResults($result);
        $poll->setWinner($winner);
        $poll->setVoteCount($election->countVotes());
        return $poll;
    }
}
