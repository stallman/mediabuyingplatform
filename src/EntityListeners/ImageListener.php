<?php

namespace App\EntityListeners;

use App\Entity\Image;
use App\Entity\News;
use App\Entity\Teaser;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class ImageListener
{
    use CacheActionsTrait;

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $image = $args->getObject();

        if(!$image instanceof Image){
            return;
        }
        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));

        $entityClass = $image->getEntityFQN();

        if(new $entityClass() instanceof Teaser){
            $this->clearCacheByTags($cache, ['teasers'], 'entity-');
        }
        if(new $entityClass() instanceof News){
            $this->clearCacheByTags($cache, ['news'], 'entity-');
        }
    }
}
