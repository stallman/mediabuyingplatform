<?php

namespace App\Repository;

use App\Entity\Traits\ListReportTrait;
use App\Entity\WhiteList;
use App\Entity\User;
use App\Entity\Sources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WhiteList|null find($id, $lockMode = null, $lockVersion = null)
 * @method WhiteList|null findOneBy(array $criteria, array $orderBy = null)
 * @method WhiteList[]    findAll()
 * @method WhiteList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WhiteListRepository extends ServiceEntityRepository
{
    use ListReportTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WhiteList::class);
    }

    public function getByUser(User $buyer, array $newsList, Sources $source)
    {
        $query = $this->createQueryBuilder('wl')
            ->leftJoin('wl.visitor', 'visits')
            ->andWhere('wl.buyer = :buyer')
            ->andWhere('visits.news IN (:newsList)')
            ->andWhere('visits.source = :source')
            ->setParameters([
                'buyer' => $buyer,
                'newsList' => $newsList,
                'source' => $source,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getByGroupName(User $buyer)
    {
        $rows = [];

        try {
            $conn = $this->getEntityManager()->getConnection();

            $buyerId = intval($buyer->getId());
            $stmt = $conn->executeQuery("SELECT group_name,group_id FROM white_list WHERE buyer_id = $buyerId GROUP BY group_name,group_id");
            $results = $stmt->fetchAllAssociative();

            foreach ($results as $result) {
                $groupName = $result['group_name'];
                $groupId = $result['group_id'];

                if (null === $groupName || null === $groupId) {
                    continue;
                }

                if (!isset($rows[$groupName])) {
                    $rows[$groupName] = [];
                }

                if (!in_array($groupId, $rows[$groupName])) {
                    $rows[$groupName][] = $groupId;
                }
            }
        } catch (\Throwable $e) {}

        return $rows;
    }

    public function checkInList(User $buyer, array $groups, string $group, string $item): ?WhiteList
    {
        //sort($groups);
        //$groupsByString = implode(',', $groups);

        $query = $this->createQueryBuilder('bl')
            ->where('bl.buyer = :buyer')
            //->andWhere('bl.field = :field')
            ->andWhere('bl.groupId = :groupId')
            ->andWhere('bl.groupName = :groupName')
            ->setParameters([
                'buyer' => $buyer,
                //'field' => $groupsByString,
                'groupId' => $item,
                'groupName' => $group,
            ]);

        $whiteListItems = $query->getQuery()->getResult();
        return array_shift($whiteListItems);
    }

    public function inWhiteList(User $buyer, array $groups, array $item): array
    {
        /** @var WhiteList[] $inWhiteListItems */
        $inWhiteListItems = [];
        $sortedGroups = $groups;
        sort($sortedGroups);

        foreach ($groups as $i => $group) {
            $itemId = $item[$i];
            $whiteListItem = $this->checkInList($buyer, $sortedGroups, $group, $itemId);

            if (null !== $whiteListItem) {
                $inWhiteListItems[] = $whiteListItem;
            }
        }

        return $inWhiteListItems;
    }

    public function notInWhiteList(User $buyer, array $groups, array $item): array
    {
        /** @var array $inWhiteListItems */
        $notInWhiteListItems = [];
        $sortedGroups = $groups;
        sort($sortedGroups);

        foreach ($groups as $i => $group) {
            $itemId = $item[$i];
            $whiteListItem = $this->checkInList($buyer, $sortedGroups, $group, $itemId);

            if (null === $whiteListItem) {
                $notInWhiteListItems[] = [
                    'groupId' => $itemId,
                    'groupName' => $group,
                    //'field' => implode(',', $groups),
                ];
            }
        }

        return $notInWhiteListItems;
    }
}
