<?php

namespace App\EntityListeners;

use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\StatisticNews;
use App\Entity\User;
use App\Traits\CacheActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;


class NewsListener
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
        $news = $args->getObject();

        if(!$news instanceof News){
            return;
        }
        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));
        $this->clearCacheByTags($cache, ['news'], 'entity-');

        $this->createStatisticNews($news);
        if($news->getUser()->getRole() == 'ROLE_MEDIABUYER') $this->createStatisticNews($news, $news->getUser());
        $this->createMediabuyerNews($news);

    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $news = $args->getObject();

        if(!$news instanceof News){
            return;
        }
        $cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));

        if($news->getCategories()->isDirty()){
            $categoriesList = $this->getChangeRelationsList($news->getCategories()->getDeleteDiff(), $news->getCategories()->getInsertDiff());
            $this->clearCacheByTags($cache, $categoriesList, 'category-');
        }
        if($news->getCountries()->isDirty()){
            $countriesList = $this->getChangeRelationsList($news->getCountries()->getDeleteDiff(), $news->getCountries()->getInsertDiff(), 'getIsoCode');
            $this->clearCacheByTags($cache, $countriesList, 'geo_code-');
        }
        if(array_key_exists('is_deleted', $args->getEntityChangeSet()) ||
            array_key_exists('isActive', $args->getEntityChangeSet()) ||
            array_key_exists('title', $args->getEntityChangeSet())){
            $this->clearCacheByTags($cache, ['news'], 'entity-');
        }
    }

    private function createStatisticNews(News $news, User $mediaBuyer = null)
    {
        $statisticNews = new StatisticNews();

        $statisticNews->setNews($news);
        if($mediaBuyer) $statisticNews->setMediabuyer($mediaBuyer);

        $this->entityManager->persist($statisticNews);
        $this->entityManager->flush();
    }

    private function createMediabuyerNews(News $news)
    {
        $mediaBuyers = $this->getMediabuyerUsers($news->getUser());
        /** @var User $mediaBuyer */
        foreach ($mediaBuyers as $mediaBuyer) {
            $mediaBuyerNews = new MediabuyerNews();
            $mediaBuyerNews->setMediabuyer($mediaBuyer)
                ->setNews($news);

            $this->entityManager->persist($mediaBuyerNews);
            $this->entityManager->flush();
        }
    }

    private function getMediabuyerUsers(User $author)
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roles LIKE :role AND u.id != :author";
        return $this->entityManager
            ->createQuery($dql)
            ->setParameters([
                'role' => '%ROLE_MEDIABUYER%',
                'author' => $author->getId(),
            ])
            ->getResult();
    }
}
