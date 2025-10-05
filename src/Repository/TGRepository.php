<?php

namespace App\Repository;

use App\Service\FeatureFlagService;
use App\Service\HTMLSanitizerService;
use App\Service\RankService;
use App\Service\ServerInformationService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\SqlFormatter\HtmlHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;

class TGRepository
{
    public const ENTITY = null;

    public const TABLE = '';
    public const ALIAS = '';

    public const COLUMNS = [];

    public const ORDERBY = '';

    public const PER_PAGE = 60;

    public ?string $query = null;
    public array $params = [];

    protected SqlFormatter $formatter;

    protected ?Pagerfanta $pager = null;

    public function __construct(
        protected Connection $connection,
        protected PaginatorInterface $paginatorInterface,
        protected RankService $rankService,
        protected ServerInformationService $serverInformationService,
        protected HTMLSanitizerService $HTMLSanitizerService,
        protected FeatureFlagService $feature
    ) {
        $this->formatter = new SqlFormatter(new HtmlHighlighter());
    }

    public function qb(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }

    public function getBaseQuery(): QueryBuilder
    {
        $qb = $this->qb();
        $qb->select(...static::COLUMNS)->from(static::TABLE, static::ALIAS);
        if (static::ORDERBY) {
            $qb->orderBy(static::ORDERBY, 'DESC');
        }
        return $qb;
    }

    public function findOneBy(string $key, int|string $value): mixed
    {
        $qb = $this->getBaseQuery();
        $result = $qb
            ->where(static::ALIAS .
                '.' .
                $key .
                '=' .
                $qb->createNamedParameter($value))
            ->executeQuery()
            ->fetchAssociative();
        if (!$result) {
            return null;
        }
        return $this->parseRow($result);
    }

    public function parseRow(array $result): object
    {
        $entity = static::ENTITY;

        // Use factory method if available
        if (method_exists($entity, 'new')) {
            return $entity::new($result);
        }

        $ref = new \ReflectionClass($entity);
        $ctor = $ref->getConstructor();

        if ($ctor) {
            $params = $ctor->getParameters();

            foreach ($params as $param) {
                $name = $param->getName();

                if (!array_key_exists($name, $result)) {
                    continue;
                }

                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();

                    // auto-convert DateTimeInterface params if string provided
                    if (
                        $typeName === \DateTimeInterface::class &&
                            is_string($result[$name])
                    ) {
                        try {
                            $result[$name] = new \DateTimeImmutable(
                                $result[$name]
                            );
                        } catch (\Exception) {
                            // If conversion fails, leave original string or set null
                            $result[$name] = null;
                        }
                    }
                }
            }

            // Only keep keys that match constructor params
            $paramNames = array_map(fn($p) => $p->getName(), $params);
            $filtered = array_intersect_key($result, array_flip($paramNames));

            return new $entity(...$filtered);
        }

        return new $entity();
    }

    public function getQuery(): ?array
    {
        if (!$this->query) {
            return null;
        }
        return [
            'sql' => $this->formatter->format($this->query),
            'params' => $this->params
        ];
    }

    public function getPager(): Pagerfanta
    {
        return $this->pager;
    }

    public function pingDBServer(): int
    {
        try {
            $qb = $this->qb();
            $qb->select('1')->from('round', 'r');
            $qb->executeQuery();
            return Response::HTTP_OK;
        } catch (Exception $e) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }
}
