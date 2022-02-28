<?php


namespace App\Service\Algorithms;


use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\Teaser;
use App\Entity\UserSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class HiddenBlocksAlgorithm extends AlgorithmAbstract
{
    const LIMIT_FIRST_PAGE = [
        'desktop' => 12,
        'tablet' => 9,
        'mobile' => 9
    ];

    const LIMIT_OTHER_PAGE = [
        'desktop' => 12,
        'tablet' => 9,
        'mobile' => 9
    ];

    private array $hiddenBlocks = [
        'short' => [
            'desktop' => [10, 11],
            'tablet' => [10, 11],
            'mobile' => null
        ],
        'full' => [
            'desktop' => [4, 5, 6, 7],
            'tablet' => [4, 5, 6, 7],
            'mobile' => null
        ]
    ];

    private string $pageType = 'full';

    private int $limit;
    private int $offset;

    /**
     * HiddenBlocksAlgorithm constructor.
     */
    public function __construct()
    {
        $this->setAlgorithmId(4);
    }

    /**
     * @param string $pageType
     * @return HiddenBlocksAlgorithm
     */
    public function setPageType(string $pageType): self
    {
        $this->pageType = $pageType;

        return $this;
    }

    public function getNewsForTop(int $page = 1): Collection
    {
        $this->setImpressionsNews($this->entityManager->getRepository(UserSettings::class)->getUserSetting($this->getBuyerId(), 'ecrm_news_view_count'));
        $this->getLimitByPage($page);
        $this->offset = $this->limit * ($page - 1);

        $cacheKey = $this->getAlgorithmId() . 'news_for_top'  . $this->pageType . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page . $this->limit . $this->offset;
        $cacheNews = $this->cache->getItem($cacheKey)->tag([
            'entity-news',
            'page_type-' . $this->pageType,
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getId(),
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
                AND statistic_news.innerShow >= :innerShow
                GROUP BY news.id
                ORDER BY statistic_news.inner_eCPM DESC, news.id DESC
                ";
            $news = $this->entityManager
                ->createQuery($dql)
                ->setMaxResults($this->limit)
                ->setFirstResult($this->offset)
                ->setParameters([
                    'entityFqn' => get_class(new News()),
                    'isRotation' => true,
                    'isActive' => true,
                    'isDeleted' => false,
                    'trafficType' => $this->getTrafficType(),
                    'isoCode' => $this->getGeoCode()->getIsoCode(),
                    'mediaBuyer' => $this->getBuyerId(),
                    'dropSources' => serialize($this->getSourceId()),
                    'type' => 'own',
                    'innerShow' => $this->getImpressionsNews(),
                ])
                ->getResult();

            $news = new ArrayCollection($news);
            $news = $this->hideBlocks($news);
            $cacheNews->set($news);

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheNews);
            }
        }

        return $cacheNews->get();
    }

    public function getNewsForCategory(NewsCategory $category, int $page = 1): Collection
    {
        $this->setImpressionsNews($this->entityManager->getRepository(UserSettings::class)->getUserSetting($this->getBuyerId(), 'ecrm_news_view_count'));
        $this->getLimitByPage($page);
        $this->offset = $this->limit * ($page - 1);

        $cacheKey = $this->getAlgorithmId() . 'news_for_category'  . $this->pageType . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $category->getSlug() . $page . $this->limit . $this->offset;
        $cacheNews = $this->cache->getItem($cacheKey)->tag([
            'entity-news',
            'category-' . $category->getId(),
            'page_type-' . $this->pageType,
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
                AND mediabuyer_news.dropSources NOT LIKE :dropSources
                AND statistic_news.innerShow >= :innerShow
                ORDER BY statistic_news.inner_eCPM DESC, news.id DESC
                ";
            $news = $this->entityManager
                ->createQuery($dql)
                ->setMaxResults($this->limit)
                ->setFirstResult($this->offset)
                ->setParameters([
                    'entityFqn' => get_class(new News()),
                    'isRotation' => true,
                    'isActive' => true,
                    'isDeleted' => false,
                    'trafficType' => $this->getTrafficType(),
                    'isoCode' => $this->getGeoCode()->getIsoCode(),
                    'category' => $category,
                    'mediaBuyer' => $this->getBuyerId(),
                    'dropSources' => serialize($this->getSourceId()),
                    'type' => 'own',
                    'innerShow' => $this->getImpressionsNews(),
                ])
                ->getResult();
            $news = new ArrayCollection($news);
            $news = $this->hideBlocks($news);

            $cacheNews->set($news);

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheNews);
            }
        }

        return $cacheNews->get();
    }

    public function getTeaserForTop(int $page = 1): Collection
    {
        $this->getLimitByPage($page);
        $this->offset = $this->limit * ($page - 1);
        $this->setImpressionsTeaser($this->entityManager->getRepository(UserSettings::class)->getUserSetting($this->getBuyerId(), 'ecrm_teasers_view_count'));
        $this->setCountry($this->getGeoCode());
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());

        $cacheKey = $this->getAlgorithmId() . 'teaser_for_top'  . $this->pageType . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page . $this->limit . $this->offset;
        $cacheTeasers = $this->cache->getItem($cacheKey)->tag([
            'entity-teasers',
            'page_type-' . $this->pageType,
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheTeasers->isHit()){
            $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, statistic_teasers.e_cpm   
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->getCountry()->getIsoCode()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
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
                teasers_sub_group_settings.geo_code = {$this->getCountry()->getId()},
                teasers_sub_group_settings.geo_code IS NULL)
                WHERE t.is_active = 1
                AND t.is_deleted = 0
                AND t.user_id = {$this->getBuyerId()}
                AND t.drop_sources NOT LIKE '{$dropSource}'
                AND teasers_sub_group_settings.link IS NOT NULL
                AND (statistic_teasers.teaser_show >= {$this->getImpressionsTeaser()} OR t.is_top = 1) 
                ORDER BY t.is_top DESC, statistic_teasers.e_cpm DESC, t.id DESC
                LIMIT {$this->limit} OFFSET {$this->offset}
                ";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $teasers = new ArrayCollection($stmt->fetchAll());

            $cacheTeasers->set($teasers);

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheTeasers);
            }
        }

        return $cacheTeasers->get();
    }

    public function getTeaserForNews(News $news, int $page = 1): Collection
    {
        $this->getLimitByPage($page);
        $this->offset = $this->limit * ($page - 1);
        $this->setCountry($this->getGeoCode());
        $this->setImpressionsTeaser($this->entityManager->getRepository(UserSettings::class)->getUserSetting($this->getBuyerId(), 'ecrm_teasers_view_count'));
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());
        $dropNews = serialize($news->getId());
        $categories = $this->entityManager->getRepository(News::class)->getNewsCategories($news);

        $cacheKey = $this->getAlgorithmId() . 'teaser_for_news'  . $this->pageType . $this->getTrafficType() . $this->getGeoCode()->getIsoCode() . $this->getBuyerId() . $page . $this->limit . $this->offset;

        $cacheTeasers = $this->cache->getItem($cacheKey)->tag([
            'entity-teasers',
            'news-' . $news->getId(),
            'page_type-' . $this->pageType,
            'traffic_type-' . $this->getTrafficType(),
            'geo_code-' . $this->getGeoCode()->getIsoCode(),
            'media_buyer-' . $this->getBuyerId(),
            'source-' . $this->getSourceId()
        ]);

        if(!$cacheTeasers->isHit()){
            $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, statistic_teasers.e_cpm   
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->country->getIsoCode()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
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
                teasers_sub_group_settings.geo_code = {$this->country->getId()},
                teasers_sub_group_settings.geo_code IS NULL)
                WHERE t.is_active = 1
                AND t.is_deleted = 0
                AND news_categories.slug IN ('" . implode("','", $categories) . "')
                AND t.user_id = {$this->getBuyerId()}
                AND t.drop_sources NOT LIKE '{$dropSource}'
                AND t.drop_news NOT LIKE '{$dropNews}' 
                AND teasers_sub_group_settings.link IS NOT NULL
                AND (statistic_teasers.teaser_show >= {$this->getImpressionsTeaser()} OR t.is_top = 1) 
                GROUP BY t.id, image.id, teasers_sub_group_settings.id, top_teasers.id
                ORDER BY t.is_top DESC, statistic_teasers.e_cpm DESC, t.id DESC
                LIMIT {$this->limit} OFFSET {$this->offset}
                ";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $teasers = new ArrayCollection($stmt->fetchAll());
            $teasers = $this->hideBlocks($teasers);

            $cacheTeasers->set($teasers);

            if($_ENV['CACHE_ENABLE']) {
                $this->cache->save($cacheTeasers);
            }
        }

        return $cacheTeasers->get();
    }

    /**
     * Удаляет из выборки определенные элементы в зависимости от типа страницы и типа трафика
     *
     * @param ArrayCollection|News[]|Teaser[] $items
     * @return Collection|News[]|Teaser[]
     */
    private function hideBlocks(ArrayCollection $items)
    {
        $hiddenBlocks = $this->getHiddenBlocks();
        if($hiddenBlocks){
            $numerableItems = new ArrayCollection();
            $i = $this->offset;
            foreach ($items as $item) {
                $numerableItems->set($i, $item);
                $i++;
            }

            $items = $numerableItems;

            foreach($hiddenBlocks as $hiddenBlock) {
                $blockNumber = $hiddenBlock - 1;
                if($items[$blockNumber]) $items->remove($blockNumber);
            }
        }

        return $items;
    }

    /**
     * Возвращает лимит выборки в зависимости от страницы
     *
     * @param int $page
     * @return void
     */
    private function getLimitByPage(int $page)
    {
        if($page == 1){
            $this->getLimitByTrafficType(self::LIMIT_FIRST_PAGE);

        } else {
            $this->getLimitByTrafficType(self::LIMIT_OTHER_PAGE);
        }
    }

    /**
     * Возвращает лимит выборки в зависимости от типа трафика
     *
     * @param array $limit
     * @return HiddenBlocksAlgorithm
     */
    private function getLimitByTrafficType(array $limit)
    {
        switch($this->getTrafficType()) {
            case 'tablet':
                $this->limit = $limit['tablet'];
                break;
            case 'mobile':
                $this->limit = $limit['mobile'];
                break;
            default:
                $this->limit = $limit['desktop'];
        }
        return $this;
    }

    /**
     * Возвращает список индексов для скрытия из массива с элементами в зависимости от лимита & начала выборки
     *
     * @return array
     */
    private function getHiddenBlocks()
    {
        $elementList = range($this->offset, $this->offset + $this->limit);
        $hiddenBlocks = isset($this->hiddenBlocks[$this->pageType][$this->getTrafficType()]) ? $this->hiddenBlocks[$this->pageType][$this->getTrafficType()] : [];

        return array_uintersect($elementList, $hiddenBlocks, "strcasecmp"); //возвращает совпадения в массиве по значению
    }
}