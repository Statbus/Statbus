<?php

namespace App\Domain\Note\Repository;

use App\Domain\Note\Data\Note;
use App\Repository\Repository;

class NoteRepository extends Repository
{
    public ?string $entityClass = Note::class;

    private array $columns = [
        'n.id',
        'n.type',
        'n.targetckey',
        'n.adminckey',
        'n.text',
        'n.timestamp',
        'n.server_port',
        'n.server_ip',
        'n.round_id',
        'n.secret',
        'n.expire_timestamp',
        'n.severity',
        'n.playtime',
        'n.lasteditor',
        'n.edits',
        'n.deleted',
        'n.deleted_ckey',
        't.rank as t_rank',
        'a.rank as a_rank',
        'e.rank as e_rank',
        'n.server'
    ];

    private array $joins = [
        'LEFT JOIN admin t ON n.targetckey = t.ckey',
        'LEFT JOIN admin a ON n.adminckey = a.ckey',
        'LEFT JOIN admin e ON n.lasteditor = e.ckey'
    ];

    private array $where = [
        "n.deleted = 0",
        "n.type != 'memo'",
    ];

    public function getNotesForCkey(string $ckey, int $page = 1, int $per_page = 60, bool $secret = false): self
    {
        if(!$secret) {
            $this->where[] = "n.secret = 0";
        }
        $where = implode("\n AND ", [...$this->where, "n.targetckey = ?"]);
        $query = sprintf("SELECT count(n.id) FROM messages n WHERE %s", $where);
        $this->setPages((int) ceil($this->cell($query, $ckey) / $per_page));

        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $where = implode("\n AND ", [...$this->where, "n.targetckey = ?"]);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC LIMIT ?, ?", $cols, $joins, $where);
        $data = $this->run(
            $query,
            $ckey,
            ($page * $per_page) - $per_page,
            $per_page
        );
        $this->setResults($data, false);
        return $this;
    }

    public function getNoteById(int $id, bool $secret = false): self
    {
        if(!$secret) {
            $this->where[] = "n.secret = 0";
        }
        $cols = implode(",", $this->columns);
        $joins = implode("\n", $this->joins);
        $where = implode("\n AND ", [...$this->where, "n.id = ?"]);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC", $cols, $joins, $where);
        $data = $this->actualRow($query, [$id]);
        $this->setResult($data);
        return $this;
    }

    public function getNotesByAuthor(string $ckey, int $page = 1, int $per_page = 60, bool $secret = false)
    {
        if(!$secret) {
            $this->where[] = "n.secret = 0";
        }
        $where = implode("\n AND ", [...$this->where, "n.adminckey = ?"]);
        $query = sprintf("SELECT count(n.id) FROM messages n WHERE %s", $where);
        $this->setPages((int) ceil($this->cell($query, $ckey) / $per_page));

        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $where = implode("\n AND ", [...$this->where, "n.adminckey = ?"]);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC LIMIT ?, ?", $cols, $joins, $where);
        $data = $this->run(
            $query,
            $ckey,
            ($page * $per_page) - $per_page,
            $per_page
        );
        $this->setResults($data, false);
        return $this;
    }

    public function getNotes(int $page = 1, int $per_page = 60, bool $secret = false)
    {
        if(!$secret) {
            $this->where[] = "n.secret = 0";
        }
        $where = implode("\n AND ", [...$this->where]);
        $query = sprintf("SELECT count(n.id) FROM messages n WHERE %s", $where);
        $this->setPages((int) ceil($this->cell($query) / $per_page));

        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC LIMIT ?, ?", $cols, $joins, $where);
        $data = $this->run(
            $query,
            ($page * $per_page) - $per_page,
            $per_page
        );
        $this->setResults($data, false);
        return $this;
    }

    public function getCurrentMemos()
    {
        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $where = implode("\n AND ", ['n.deleted = 0', "n.type = 'memo'"]);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC", $cols, $joins, $where);
        $data = $this->run(
            $query
        );
        $this->setResults($data);
        return $this->getResults();
    }

    public function getCurrentWatchlists(int $page = 1, int $per_page = 60): self
    {
        $where = implode("\n AND ", ['n.deleted = 0',"n.type='watchlist entry'",'(n.expire_timestamp > NOW() OR n.expire_timestamp IS NULL)']);
        $query = sprintf("SELECT count(n.id) FROM messages n WHERE %s", $where);
        $this->setPages((int) ceil($this->cell($query) / $per_page));

        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC LIMIT ?, ?", $cols, $joins, $where);
        $data = $this->run(
            $query,
            ($page * $per_page) - $per_page,
            $per_page
        );
        $this->setResults($data, false);
        return $this;
    }

    public function getEditedNotes(int $page = 1, int $per_page = 60): array
    {
        $where = implode("\n AND ", [...$this->where, "n.lasteditor IS NOT NULL"]);
        $query = sprintf("SELECT count(n.id) FROM messages n WHERE %s", $where);
        $this->setPages((int) ceil($this->cell($query) / $per_page));
        $cols = implode(",\n", $this->columns);
        $joins = implode("\n", $this->joins);
        $query = sprintf("SELECT %s FROM messages n %s \nWHERE %s
        ORDER BY n.timestamp DESC LIMIT ?, ?", $cols, $joins, $where);
        $data = $this->run(
            $query,
            ($page * $per_page) - $per_page,
            $per_page
        );
        $this->setResults($data, false);
        return $this->getResults();
    }

}
