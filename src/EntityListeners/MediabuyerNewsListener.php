<?php

namespace App\EntityListeners;

use App\Entity\MediabuyerNews;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class MediabuyerNewsListener
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
        $mediaBuyerNews = $args->getObject();

        if(!$mediaBuyerNews instanceof MediabuyerNews){
            return;
        }

        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));

        if (array_key_exists('dropSources', $args->getEntityChangeSet())) {
            $dropSources = $this->getChangeDropItemsList($args->getEntityChangeSet()['dropSources']);
            $this->clearCacheByTags($cache, $dropSources, 'source-');
        }
    }
}
