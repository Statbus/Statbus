<?php

namespace App\Domain\User\Repository;

use App\Domain\User\Data\User;
use App\Repository\Repository;

class UserRepository extends Repository
{
    public function getUserByCkey(string $ckey): User
    {
        $user = $this->connection
        ->execute("SELECT
        p.ckey,
        SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1) as rank, 
        (SELECT r.flags FROM admin_ranks r WHERE rank = SUBSTRING_INDEX(SUBSTRING_INDEX(a.rank, '+', 1), ',', -1)) as flags,
        a.feedback,
        p.lastseen_round_id
        FROM `player` p
        LEFT JOIN `admin` a ON a.ckey = p.ckey
        WHERE p.ckey = :ckey", ['ckey' => $ckey])
        ->fetch('assoc');
        return User::fromArray($user);
    }

    public function getUserByLastIp(string $ip): ?User
    {
        $res = $this->connection
        ->execute("SELECT
        ckey, datetime FROM connection_log WHERE ip = :ip
        ORDER BY id DESC LIMIT 1", ['ip' => ip2long($ip)])->fetch('assoc');

        // Connection is rendered stale if older than 7 days
        if(!is_array($res) || date_diff(new \DateTime($res['datetime']), new \DateTime())->d > 7) {
            return null;
        }

        return $this->getUserByCkey($res['ckey']);
    }
}
