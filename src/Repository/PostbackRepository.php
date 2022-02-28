<?php

namespace App\Repository;

use App\Contract\CleanableRepositoryInterface;
use App\Entity\Postback;
use App\Entity\TeasersClick;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Postback|null find($id, $lockMode = null, $lockVersion = null)
 * @method Postback|null findOneBy(array $criteria, array $orderBy = null)
 * @method Postback[]    findAll()
 * @method Postback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostbackRepository extends ServiceEntityRepository implements CleanableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Postback::class);
    }

    public function getLastPostBackByClick(TeasersClick $click)
    {
        $query = $this->createQueryBuilder('pb')
            ->where('pb.click = :click')
            ->setParameter('click', $click)
            ->orderBy('pb.createdAt', 'DESC')
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function queryOlderThan(User $buyer, int $days, bool $count = false) : QueryBuilder {
        $tcQuery = $this
            ->getEntityManager()
            ->getRepository(TeasersClick::class)
            ->queryOlderThan($buyer, $days, $count)
            ->select('tc.id')
            ->getDQL();


        $builder = $this->createQueryBuilder('p');
        $builder
            ->where($builder->expr()->in('p.click', $tcQuery))
            ->setParameters(compact('buyer', 'days'));

        if($count){
            $builder->select('COUNT(p.id)');
        }else{
            $builder->delete();
        }
        return $builder;
    }
}
