<?php

namespace App\EntityListeners;

use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class TeasersSubGroupSettingsListener
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
        $teasersSubGroupSetting = $args->getObject();

        if (!$teasersSubGroupSetting instanceof TeasersSubGroupSettingsListener) {
            return;
        }

        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));
        $this->clearCacheByTags($cache, ['teasers'], 'entity-');
    }
}
