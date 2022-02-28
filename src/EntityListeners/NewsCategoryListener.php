<?php

namespace App\EntityListeners;

use App\Entity\NewsCategory;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class NewsCategoryListener
{
    use CacheActionsTrait;

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $category = $args->getObject();
        if(!$category instanceof NewsCategory){
            return;
        }

        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($category);
        if(in_array('isEnabled', $changeSet)){
            $cache = new TagAwareAdapter(new RedisAdapter(
                RedisAdapter::createConnection($_ENV['REDIS_URL'])
            ));
            $this->clearCacheByTags($cache, [$category->getId()], 'category-');
        }
    }
}
