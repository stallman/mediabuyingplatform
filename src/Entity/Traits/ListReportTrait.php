<?php

namespace App\Entity\Traits;

use App\Entity\Sources;
use App\Entity\User;
use App\Repository\VisitsRepository;

trait ListReportTrait
{
    public function getReport(User $buyer, array $campaignList, Sources $source, string $group, string $glue) {
        $campaigns = join(',', array_map('intval', $campaignList));
        $group_name = VisitsRepository::toSqlCN($group, false, true);
        $table = $this->getEntityManager()->getClassMetadata($this->_entityName)->getTableName();

        $sql = "
            SELECT GROUP_CONCAT(DISTINCT l.group_id SEPARATOR :glue) FROM {$table} l, visits v
            WHERE v.mediabuyer_id = :buyer
                AND v.source_id = :source
                AND v.utm_campaign IN ({$campaigns})
                AND v.{$group_name} = l.group_id
            ";

        return $this
            ->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, [
                'glue' => $glue,
                'buyer' => intval($buyer->getId()),
                'source' => intval($source->getId()),
            ])
            ->fetchOne();
    }
}
