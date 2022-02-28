<?php


namespace App\Service\Algorithms;


use App\Entity\Country;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\Teaser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RandomAlgorithm extends AlgorithmAbstract
{
    /**
     * RandomAlgorithm constructor.
     */
    public function __construct()
    {
        $this->setAlgorithmId(1);
    }

    public function getNewsForTop(int $page = 1): Collection
    {
        $cacheKey = $this->getAlgorithmId() . 'news_for_top' . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page;
        $cacheNews = $this->cache->getItem($cacheKey)->tag([
            'entity-news',
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheNews->isHit()){
            $dql = "SELECT news.id, news.title, news.createdAt, image.filePath, image.fileName, statistic_news.inner_eCPM
                FROM App\Entity\News news
                LEFT JOIN news.countries country
                LEFT JOIN news.categories category
                LEFT JOIN news.topNews top_news WITH news.id = top_news.news  AND top_news.mediabuyer = :mediaBuyer AND top_news.trafficType = :trafficType AND top_news.geoCode = :isoCode
                LEFT JOIN news.statistic statistic_news WITH news.id = statistic_news.news  AND statistic_news.mediabuyer = :mediaBuyer
                LEFT JOIN App\Entity\Image image WITH news.id = image.entityId AND image.entityFQN = :entityFqn
                LEFT JOIN news.mediabuyerNews mediabuyer_news WITH news.id = mediabuyer_news.news AND mediabuyer_news.mediabuyer = :mediaBuyer
                LEFT JOIN news.mediabuyerNewsRotation mbnr WITH mbnr.mediabuyer = :mediaBuyer AND news.id = mbnr.news AND mbnr.isRotation = :isRotation
                WHERE news.isActive = :isActive
                AND news.is_deleted = :isDeleted
                AND (mbnr.isRotation = :isRotation
                OR (news.user = :mediaBuyer AND news.type = :type))
                AND country.iso_code = :isoCode
                AND category.isEnabled = :isActive
                AND mediabuyer_news.dropSources NOT LIKE :dropSources
                GROUP BY news.id
                ORDER BY RAND()
                ";
            $news = $this->entityManager
                ->createQuery($dql)
                ->setMaxResults(10)
                ->setParameters([
                    'entityFqn' => get_class(new News()),
                    'isRotation' => true,
                    'isActive' => true,
                    'isDeleted' => false,
                    'trafficType' => $this->getTrafficType(),
                    'isoCode' => $this->getGeoCode()->getIsoCode(),
                    'mediaBuyer' => $this->getBuyerId(),
                    'type' => 'own',
                    'dropSources' => serialize($this->getSourceId())
                ])
                ->getResult();

            $cacheNews->set(new ArrayCollection($news));

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheNews);
            }
        }

        return $cacheNews->get();
    }

    public function getNewsForCategory(NewsCategory $category, int $page = 1): Collection
    {
        $cacheKey = $this->getAlgorithmId() . 'news_for_category' . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page . $category->getSlug();
        $cacheNews = $this->cache->getItem($cacheKey)->tag([
            'entity-news',
            'category-' . $category->getId(),
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheNews->isHit()){
            $dql = "SELECT news.id, news.title, news.createdAt, image.filePath, image.fileName, statistic_news.inner_eCPM, category.title as category_title
                FROM App\Entity\News news
                LEFT JOIN news.countries country
                LEFT JOIN news.categories category
                LEFT JOIN news.topNews top_news WITH news.id = top_news.news  AND top_news.mediabuyer = :mediaBuyer AND top_news.trafficType = :trafficType AND top_news.geoCode = :isoCode
                LEFT JOIN news.statistic statistic_news WITH news.id = statistic_news.news  AND statistic_news.mediabuyer = :mediaBuyer
                LEFT JOIN App\Entity\Image image WITH news.id = image.entityId AND image.entityFQN = :entityFqn
                LEFT JOIN news.mediabuyerNews mediabuyer_news WITH news.id = mediabuyer_news.news AND mediabuyer_news.mediabuyer = :mediaBuyer
                LEFT JOIN news.mediabuyerNewsRotation mbnr WITH mbnr.mediabuyer = :mediaBuyer AND news.id = mbnr.news AND mbnr.isRotation = :isRotation
                WHERE news.isActive = :isActive
                AND news.is_deleted = :isDeleted
                AND (mbnr.isRotation = :isRotation
                OR (news.user = :mediaBuyer AND news.type = :type))
                AND country.iso_code = :isoCode
                AND category = :category
                AND category.isEnabled = :isActive
                AND mediabuyer_news.dropSources NOT LIKE :dropSources
                ORDER BY RAND()
                ";
            $news = $this->entityManager
                ->createQuery($dql)
                ->setMaxResults(10)
                ->setParameters([
                    'entityFqn' => get_class(new News()),
                    'isRotation' => true,
                    'isActive' => true,
                    'isDeleted' => false,
                    'trafficType' => $this->getTrafficType(),
                    'isoCode' => $this->getGeoCode()->getIsoCode(),
                    'category' => $category,
                    'mediaBuyer' => $this->getBuyerId(),
                    'type' => 'own',
                    'dropSources' => serialize($this->getSourceId())
                ])
                ->getResult();

            $cacheNews->set(new ArrayCollection($news));

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheNews);
            }

        }

        return $cacheNews->get();
    }

    public function getTeaserForTop(int $page = 1): Collection
    {
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());

        $cacheKey = $this->getAlgorithmId() . 'teaser_for_top' . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page;
        $cacheTeasers = $this->cache->getItem($cacheKey)->tag([
            'entity-teasers',
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheTeasers->isHit()){
            $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, statistic_teasers.e_cpm
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->getGeoCode()->getId()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
                LEFT JOIN statistic_teasers ON t.id = statistic_teasers.teaser_id
                LEFT JOIN image ON t.id = image.entity_id AND image.entity_fqn = '{$teaserClass}'
                LEFT JOIN teasers_sub_groups ON t.teasers_sub_group_id = teasers_sub_groups.id 
                LEFT JOIN teasers_sub_group_settings ON teasers_sub_groups.id = teasers_sub_group_settings.teasers_sub_group_id AND 
                IF(
                (SELECT COUNT(teasers_sub_group_settings.id) as count
                FROM  teasers
                LEFT JOIN teasers_sub_groups ON teasers.teasers_sub_group_id = teasers_sub_groups.id 
                LEFT JOIN teasers_sub_group_settings ON teasers_sub_groups.id = teasers_sub_group_settings.teasers_sub_group_id AND teasers_sub_group_settings.geo_code IS NOT NULL 
                WHERE teasers.id = t.id) != 0,
                teasers_sub_group_settings.geo_code = {$this->getGeoCode()->getId()},
                teasers_sub_group_settings.geo_code IS NULL)
                WHERE t.is_active = 1
                AND t.is_deleted = 0
                AND t.user_id = {$this->getBuyerId()}
                AND t.drop_sources NOT LIKE '{$dropSource}'
                AND teasers_sub_group_settings.link IS NOT NULL 
                ORDER BY RAND () 
                LIMIT 10
                ";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();

            $cacheTeasers->set(new ArrayCollection($stmt->fetchAll()));

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheTeasers);
            }
        }

        return $cacheTeasers->get();
    }

    public function getTeaserForNews(News $news, int $page = 1): Collection
    {
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());
        $dropNews = serialize($news->getId());
        $categories = $this->entityManager->getRepository(News::class)->getNewsCategories($news);

        $cacheKey = $this->getAlgorithmId() . 'teaser_for_news' . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page;
        $cacheTeasers = $this->cache->getItem($cacheKey)->tag([
            'entity-teasers',
            'news-' . $news->getId(),
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheTeasers->isHit()){
            $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, statistic_teasers.e_cpm 
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->getGeoCode()->getId()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
                LEFT JOIN statistic_teasers ON t.id = statistic_teasers.teaser_id
                LEFT JOIN image ON t.id = image.entity_id AND image.entity_fqn = '{$teaserClass}'
                LEFT JOIN teasers_sub_groups ON t.teasers_sub_group_id = teasers_sub_groups.id 
                LEFT JOIN teasersSubGroup_newsCategories_relations ON teasers_sub_groups.id = teasersSubGroup_newsCategories_relations.teasers_sub_group_id
                LEFT JOIN news_categories ON news_categories.id = teasersSubGroup_newsCategories_relations.news_category_id
                LEFT JOIN teasers_sub_group_settings ON teasers_sub_groups.id = teasers_sub_group_settings.teasers_sub_group_id AND 
                IF(
                (SELECT COUNT(teasers_sub_group_settings.id) as count
                FROM  teasers
                LEFT JOIN teasers_sub_groups ON teasers.teasers_sub_group_id = teasers_sub_groups.id 
                LEFT JOIN teasers_sub_group_settings ON teasers_sub_groups.id = teasers_sub_group_settings.teasers_sub_group_id AND teasers_sub_group_settings.geo_code IS NOT NULL 
                WHERE teasers.id = t.id) != 0,
                teasers_sub_group_settings.geo_code = {$this->getGeoCode()->getId()},
                teasers_sub_group_settings.geo_code IS NULL)
                WHERE t.is_active = 1
                AND t.is_deleted = 0
                AND news_categories.slug IN ('" . implode("','", $categories) . "')
                AND t.user_id = {$this->getBuyerId()}
                AND t.drop_sources NOT LIKE '{$dropSource}'
                AND t.drop_news NOT LIKE '{$dropNews}' 
                AND teasers_sub_group_settings.link IS NOT NULL 
                GROUP BY t.id, image.id, teasers_sub_group_settings.id
                ORDER BY RAND () 
                LIMIT 10
                ";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();

            $cacheTeasers->set(new ArrayCollection($stmt->fetchAll()));

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheTeasers);
            }
        }

        return $cacheTeasers->get();
    }
}