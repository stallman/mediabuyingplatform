<?php

namespace App\EntityListeners;

use App\Entity\StatisticTeasers;
use App\Entity\Teaser;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class TeasersListener
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
        $teaser = $args->getObject();

        if (!$teaser instanceof Teaser) {
            return;
        }
        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));
        $this->clearCacheByTags($cache, ['teasers'], 'entity-');

        $this->createStatisticTeasers($teaser);

    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $teaser = $args->getObject();

        if (!$teaser instanceof Teaser) {
            return;
        }
        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));
        if (array_key_exists('dropSources', $args->getEntityChangeSet())) {
            $dropSources = $this->getChangeDropItemsList($args->getEntityChangeSet()['dropSources']);
            $this->clearCacheByTags($cache, $dropSources, 'source-');
        }
        if (array_key_exists('dropNews', $args->getEntityChangeSet())) {
            $dropSources = $this->getChangeDropItemsList($args->getEntityChangeSet()['dropNews']);
            $this->clearCacheByTags($cache, $dropSources, 'news-');
        }
        if(array_key_exists('is_deleted', $args->getEntityChangeSet()) ||
            array_key_exists('isActive', $args->getEntityChangeSet()) ||
            array_key_exists('text', $args->getEntityChangeSet())){
            $this->clearCacheByTags($cache, ['teasers'], 'entity-');
        }
    }

    private function createStatisticTeasers(Teaser $teaser)
    {
        $statisticTeaser = new StatisticTeasers();

        $statisticTeaser->setTeaser($teaser);

        $this->entityManager->persist($statisticTeaser);
        $this->entityManager->flush();
    }
}
