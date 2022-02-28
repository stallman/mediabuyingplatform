<?php

namespace App\Repository;

use App\Entity\BlackList;
use App\Entity\Traits\ListReportTrait;
use App\Entity\User;
use App\Entity\Sources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BlackList|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlackList|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlackList[]    findAll()
 * @method BlackList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlackListRepository extends ServiceEntityRepository
{
    use ListReportTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlackList::class);
    }

    public function getByUser(User $buyer, array $campaignList, Sources $source)
    {
        $query = $this->createQueryBuilder('bl')
            ->leftJoin('bl.visitor', 'visits')
            ->andWhere('bl.buyer = :buyer')
            ->andWhere('visits.utmCampaign IN (:campaignList)')
            ->andWhere('visits.source = :source')
            ->setParameters([
                'buyer' => $buyer,
                'campaignList' => $campaignList,
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
            $stmt = $conn->executeQuery("SELECT group_name,group_id FROM black_list WHERE buyer_id = $buyerId GROUP BY group_name,group_id");
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

    public function checkInList(User $buyer, array $groups, string $group, string $item): ?BlackList
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

        $blackListItems = $query->getQuery()->getResult();
        return array_shift($blackListItems);
    }

    public function inBlackList(User $buyer, array $groups, array $item): array
    {
        /** @var BlackList[] $inBlackListItems */
        $inBlackListItems = [];
        $sortedGroups = $groups;
        sort($sortedGroups);

        foreach ($groups as $i => $group) {
            $itemId = $item[$i];
            $blackListItem = $this->checkInList($buyer, $sortedGroups, $group, $itemId);

            if (null !== $blackListItem) {
                $inBlackListItems[] = $blackListItem;
            }
        }

        return $inBlackListItems;
    }

    public function notInBlackList(User $buyer, array $groups, array $item): array
    {
        /** @var array $inBlackListItems */
        $notInBlackListItems = [];
        $sortedGroups = $groups;
        sort($sortedGroups);

        foreach ($groups as $i => $group) {
            $itemId = $item[$i];
            $blackListItem = $this->checkInList($buyer, $sortedGroups, $group, $itemId);

            if (null === $blackListItem) {
                $notInBlackListItems[] = [
                    'groupId' => $itemId,
                    'groupName' => $group,
                    //'field' => implode(',', $groups),
                ];
            }
        }

        return $notInBlackListItems;
    }
}
