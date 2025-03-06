<?php

namespace App\Repository;

use App\Entity\Option;
use App\Entity\Player;
use App\Entity\Poll;
use App\Entity\Rank;
use App\Entity\Search;
use App\Entity\Vote;
use App\Enum\Poll\Type;
use App\Service\Poll\TallyIRVPollService;
use App\Service\Poll\TallyMultiPollService;
use App\Service\Poll\TallyNumValPollService;
use App\Service\Poll\TallyOptionPollService;
use DateTimeImmutable;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;

class PollRepository extends TGRepository
{

    public const TABLE = 'poll_question';
    public const ALIAS = 'p';

    public const ENTITY = Poll::class;

    public const ORDERBY = 'p.endtime';

    public const COLUMNS = [
        'p.id',
        'p.polltype as `type`',
        'p.created_datetime as created',
        'p.starttime as `start`',
        'p.endtime as `end`',
        'p.question',
        'p.subtitle',
        'p.adminonly',
        'p.createdby_ckey as creator',
        'p.dontshow'
    ];

    public function getBaseQuery(): QueryBuilder
    {
        $qb = parent::getBaseQuery();
        $qb->orderBy('p.endtime', 'DESC');
        $qb->andWhere('p.deleted = 0');
        return $qb;
    }

    // public function getPolls(): array {

    // }

    public function getFinishedPolls(int $page): array
    {
        $query = $this->getBaseQuery();
        $query->andWhere("p.endtime < NOW()");
        $countQueryBuilderModifier = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->select('COUNT(DISTINCT p.id) AS total_results')
                ->setMaxResults(1);
        };
        $adapter = new QueryAdapter($query, $countQueryBuilderModifier);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $page, static::PER_PAGE);
        $this->pager = $pager;
        $data = [];
        foreach ($pager->getCurrentPageResults() as $item) {
            $data[] = $this->parseRow($item);
        }
        return $data;
    }

    public function getPoll(int $id, Search $search): Poll
    {
        $qb = $this->getBaseQuery();
        $qb->where('p.id = ' . $qb->createNamedParameter($id));
        $poll = $this->parseRow($qb->executeQuery()->fetchAssociative());
        $poll->setOptions($this->getPollOptions($poll));
        if (Type::IRV === $poll->getType()) {
            $poll->setVotes($this->getIRVVotes($poll, $search));
            $poll = TallyIRVPollService::tally($poll);
        } elseif (Type::TEXT === $poll->getType()) {
            $poll->setVotes($this->getTextReplies($poll));
            $poll->setVoteCount(count($poll->getVotes()));
        } elseif (Type::OPTION === $poll->getType()) {
            $poll->setVotes($this->getOptionVotes($poll));
            $poll->setVoteCount(count($poll->getVotes()));
            $poll = TallyOptionPollService::tally($poll);
        } elseif (Type::MULTICHOICE === $poll->getType()) {
            $poll->setVotes($this->getMultiVotes($poll));
            $poll->setVoteCount(count($poll->getVotes()));
            $poll = TallyMultiPollService::tally($poll);
        } elseif (Type::NUMVAL === $poll->getType()) {
            $poll->setVotes($this->getNumValVotes($poll));
            $poll->setVoteCount(count($poll->getVotes()));
            $poll = TallyNumValPollService::tally($poll);
        }
        return $poll;
    }

    public function getPollOptions(Poll $poll): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(
            'o.id',
            'o.text',
            'o.minval as `min`',
            'o.maxval as `max`'
        )->from('poll_option', 'o')
            ->where('o.pollid = ' . $poll->getId())
            ->andWhere('o.deleted != 1');
        $results = $qb->executeQuery()->fetchAllAssociative();
        foreach ($results as &$r) {
            $r = new Option(
                id: $r['id'],
                text: $r['text'],
                poll: $poll->getId(),
                min: $r['min'],
                max: $r['max']
            );
        }
        return $results;
    }

    public function getIRVVotes(Poll $poll, Search $search): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'v.optionid',
                'v.ckey',
                'o.text'
            )
            ->from('poll_vote', 'v')
            ->leftJoin('v', 'poll_option', 'o', 'v.optionid = o.id')
            ->leftJoin('v', 'player', 'p', 'v.ckey = p.ckey')
            ->where('v.pollid = ' . $qb->createNamedParameter($poll->getId()))
            ->andWhere('v.deleted != 1')
            ->andWhere('o.deleted != 1');

        // if ($search->getFirstSeen()) {
        //     $qb->andWhere('p.firstseen < ' . $qb->createNamedParameter($search->getFirstSeen()));
        // }

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$r) {
            $r = new Vote(
                option: $r['optionid'],
                player: Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank()),
                text: $r['text'],
                datetime: null
            );
        }
        return $results;
    }

    public function getOptionVotes(Poll $poll): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'v.optionid',
                'v.ckey',
                'o.text',
                'v.datetime'
            )
            ->from('poll_vote', 'v')
            ->leftJoin('v', 'poll_option', 'o', 'v.optionid = o.id')
            ->leftJoin('v', 'player', 'p', 'v.ckey = p.ckey')
            ->where('v.pollid = ' . $qb->createNamedParameter($poll->getId()))
            ->andWhere('v.deleted != 1')
            ->andWhere('o.deleted != 1');

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$r) {
            $r = new Vote(
                option: $r['optionid'],
                player: Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank()),
                text: $r['text'],
                datetime: new DateTimeImmutable($r['datetime'])
            );
        }
        return $results;
    }

    public function getNumValVotes(Poll $poll): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'v.optionid',
                'v.ckey',
                'v.rating as `text`',
                'v.datetime'
            )
            ->from('poll_vote', 'v')
            ->leftJoin('v', 'player', 'p', 'v.ckey = p.ckey')
            ->where('v.pollid = ' . $qb->createNamedParameter($poll->getId()))
            ->andWhere('v.deleted != 1');

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$r) {
            $r = new Vote(
                option: $r['optionid'],
                player: Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank()),
                text: (string) $r['text'],
                datetime: new DateTimeImmutable($r['datetime'])
            );
        }
        return $results;
    }

    public function getMultiVotes(Poll $poll): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'v.optionid',
                'v.ckey',
                'o.text',
                'v.datetime'
            )
            ->from('poll_vote', 'v')
            ->leftJoin('v', 'poll_option', 'o', 'v.optionid = o.id')
            ->leftJoin('v', 'player', 'p', 'v.ckey = p.ckey')
            ->where('v.pollid = ' . $qb->createNamedParameter($poll->getId()))
            ->andWhere('v.deleted != 1')
            ->andWhere('o.deleted != 1');

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$r) {
            $r = new Vote(
                option: $r['optionid'],
                player: Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank()),
                text: $r['text'],
                datetime: new DateTimeImmutable($r['datetime'])
            );
        }
        return $results;
    }

    public function getTextReplies(Poll $poll): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(
                'v.id',
                'v.ckey',
                'v.replytext',
                'v.datetime'
            )
            ->from('poll_textreply', 'v')
            ->leftJoin('v', 'player', 'p', 'v.ckey = p.ckey')
            ->where('v.pollid = ' . $qb->createNamedParameter($poll->getId()))
            ->andWhere('v.deleted != 1');
        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$r) {
            $r['replytext'] = $this->HTMLSanitizerService->sanitizeString($r['replytext']);
            $r = new Vote(
                option: $r['id'],
                player: Player::newDummyPlayer($r['ckey'], Rank::getPlayerRank()),
                text: $r['replytext'],
                datetime: new DateTimeImmutable($r['datetime'])
            );
        }
        return $results;
    }
}
