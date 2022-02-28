<?php

namespace App\Contract;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

/**
 * Entity can be cleaned up by deleting old records from DB
 */
interface CleanableRepositoryInterface {
    /**
     * Delete old records from DB
     *
     * @param User $mediabuyer stats for that user
     * @param int $days number of days that data should be stored
     * @param bool $count get the query for counting or for deleting
     * @return QueryBuilder ready to execute query builder
     */
    public function queryOlderThan(User $mediabuyer, int $days, bool $count = false) : QueryBuilder;
}
