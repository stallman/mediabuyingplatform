<?php

namespace App\EntityListeners;

use App\Entity\MediabuyerNewsRotation;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class MediabuyerNewsRotationListener
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
        $mediaBuyerNewsRotation = $args->getObject();

        if(!$mediaBuyerNewsRotation instanceof MediabuyerNewsRotation){
            return;
        }

        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));

        $this->clearCacheByTags($cache, [$mediaBuyerNewsRotation->getMediabuyer()->getId()], 'media_buyer-');
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $mediaBuyerNewsRotation = $args->getObject();

        if(!$mediaBuyerNewsRotation instanceof MediabuyerNewsRotation){
            return;
        }

        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));

        $this->clearCacheByTags($cache, [$mediaBuyerNewsRotation->getMediabuyer()->getId()], 'media_buyer-');
    }
}
