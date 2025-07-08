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
        foreach ($votes as &$voteList) {
            $voteList = array_unique($voteList);
        }
        foreach ($votes as $ckey => $vote) {
            try {
                $v = new Vote(implode(' > ', $vote), ['ckey' => $ckey]);
                $election->addVote($v);
            } catch (Exception $e) {
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
