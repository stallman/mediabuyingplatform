<?php

namespace App\Service\Algorithms;

use App\Entity\Country;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\Teaser;
use App\Service\CacheService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

abstract class AlgorithmAbstract implements IAlgorithm
{
    /**
     * Алгоритм, который будет использоваться для формирования списка новостей и тизеров
     *
     * @var int
     */
    protected int $algorithmId;

    /**
     * Байер, которому должны принадлежать отображаемые тизеры и находящиеся в его ротации новости
     *
     * @var int
     */
    protected int $buyerId;

    /**
     * Источник трафика, с которого пришел посетитель сайта
     *
     * @var int|null
     */
    protected ?int $sourceId;

    /**
     * Количество показов новости для перехода на собственный eCPM
     *
     * @var int
     */
    protected int $impressionsNews;

    /**
     * Количество показов тизера для перехода на собственный eCPM
     *
     * @var int
     */
    protected int $impressionsTeaser;

    /**
     * Гео посетителя сайта
     *
     * @var Country
     */
    protected Country $geoCode;

    /**
     * Страна посетителя сайта
     *
     * @var Country|null
     */
    protected ?Country $country;

    /**
     * Тип трафика (desktop, tablet, mobile)
     *
     * @var string
     */
    protected string $trafficType;

    /**
     * Места в списках новостей и тизеров для элементов, которые еще не набрали достаточное количество
     * своих показов для выборки на общих условиях
     *
     * @var array|int[]
     */
    protected array $lowItemsPlaces = [
        1, 4, 6, 8,
    ];

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $cache;

    /**
     * Получить Х случайных новостей, которые еще не набрали достаточное кол-во показов для выборки
     * на общих условиях
     *
     * @param int $limit
     * @return Collection|News[]
     */
    protected function getRandomLowNews(int $limit = 4): Collection
    {
        $dql = "SELECT news.id, news.title, news.createdAt, image.filePath, image.fileName, top_news.eCPM, top_news.impressions
                FROM App\Entity\News news
                LEFT JOIN news.countries country
                LEFT JOIN news.topNews top_news WITH news.id = top_news.news  AND top_news.mediabuyer = :mediaBuyer AND top_news.trafficType = :trafficType AND top_news.geoCode = :isoCode
                LEFT JOIN App\Entity\Image image WITH news.id = image.entityId AND image.entityFQN = :entityFqn
                LEFT JOIN news.mediabuyerNews mediabuyer_news WITH news.id = mediabuyer_news.news AND mediabuyer_news.mediabuyer = :mediaBuyer
                LEFT JOIN news.mediabuyerNewsRotation mbnr WITH mbnr.mediabuyer = :mediaBuyer AND news.id = mbnr.news AND mbnr.isRotation = :isRotation
                WHERE news.isActive = :isActive
                AND news.is_deleted = :isDeleted
                AND country.iso_code = :isoCode
                AND news.user = :mediaBuyer
                AND mediabuyer_news.dropSources NOT LIKE :dropSources
                AND top_news.impressions < :impressions
                ORDER BY RAND()
                ";
        $news = $this->entityManager
            ->createQuery($dql)
            ->setMaxResults($limit)
            ->setParameters([
                'entityFqn' => get_class(new News()),
                'isRotation' => true,
                'isActive' => true,
                'isDeleted' => false,
                'trafficType' => $this->getTrafficType(),
                'isoCode' => $this->getGeoCode(),
                'mediaBuyer' => $this->getBuyerId(),
                'dropSources' => serialize($this->getSourceId()),
                'impressions' => $this->getImpressionsNews(),
            ])
            ->getResult();

        return new ArrayCollection($news);
    }

    /**
     * Получить Х случайных новостей по категории, которые еще не набрали достаточное кол-во показов для выборки
     * на общих условиях
     *
     * @param NewsCategory $category
     * @param int $limit
     * @return Collection|News[]
     */
    protected function getRandomLowNewsForCategory(NewsCategory $category, int $limit = 4): Collection
    {
        $dql = "SELECT news.id, news.title, news.createdAt, image.filePath, image.fileName, category.title as category_title, top_news.eCPM, top_news.impressions
                FROM App\Entity\News news
                LEFT JOIN news.countries country
                LEFT JOIN news.categories category
                LEFT JOIN news.topNews top_news WITH news.id = top_news.news  AND top_news.mediabuyer = :mediaBuyer AND top_news.trafficType = :trafficType AND top_news.geoCode = :isoCode
                LEFT JOIN App\Entity\Image image WITH news.id = image.entityId AND image.entityFQN = :entityFqn
                LEFT JOIN news.mediabuyerNews mediabuyer_news WITH news.id = mediabuyer_news.news AND mediabuyer_news.mediabuyer = :mediaBuyer
                LEFT JOIN news.mediabuyerNewsRotation mbnr WITH mbnr.mediabuyer = :mediaBuyer AND news.id = mbnr.news AND mbnr.isRotation = :isRotation
                WHERE news.isActive = :isActive
                AND news.is_deleted = :isDeleted
                AND country.iso_code = :isoCode
                AND category = :category
                AND news.user = :mediaBuyer
                AND mediabuyer_news.dropSources NOT LIKE :dropSources
                AND top_news.impressions < :impressions
                ORDER BY RAND()
                ";
        $news = $this->entityManager
            ->createQuery($dql)
            ->setMaxResults($limit)
            ->setParameters([
                'entityFqn' => get_class(new News()),
                'isRotation' => true,
                'isActive' => true,
                'isDeleted' => false,
                'trafficType' => $this->getTrafficType(),
                'isoCode' => $this->getGeoCode(),
                'category' => $category,
                'mediaBuyer' => $this->getBuyerId(),
                'dropSources' => serialize($this->getSourceId()),
                'impressions' => $this->getImpressionsNews(),
            ])
            ->getResult();

        return new ArrayCollection($news);
    }

    /**
     * Получить Х случайных тизеров, которые еще не набрали достаточное кол-во показов для выборки
     * на общих условиях
     *
     * @param int $limit
     * @return Collection|Teaser[]
     */
    protected function getRandomLowTeasers(int $limit = 4): Collection
    {
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());

        $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, top_teasers.e_cpm, top_teasers.impressions 
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->getCountry()->getIsoCode()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
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
                AND top_teasers.impressions < {$this->getImpressionsTeaser()}
                AND t.is_top = 0
                ORDER BY top_teasers.e_cpm
                LIMIT {$limit}
                ";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        return new ArrayCollection($stmt->fetchAll());
    }

    /**
     * Получить Х случайных тизеров для новости, которые еще не набрали достаточное кол-во показов для выборки
     * на общих условиях
     *
     * @param News $news
     * @param array $categories
     * @param int $limit
     * @return Collection|Teaser[]
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getRandomLowTeasersForNews(News $news, array $categories, int $limit = 4): Collection
    {
        $teaserClass = addslashes(get_class(new Teaser()));
        $dropSource = serialize($this->getSourceId());
        $dropNews = serialize($news->getId());

        $sql = "SELECT t.id, t.text, image.file_path, image.file_name as fileName, teasers_sub_group_settings.link, top_teasers.e_cpm, top_teasers.impressions 
                FROM  teasers t
                LEFT JOIN top_teasers ON t.id = top_teasers.teaser_id AND top_teasers.traffic_type = '{$this->getTrafficType()}' AND top_teasers.geo_code = '{$this->country->getIsoCode()}' AND top_teasers.mediabuyer_id = {$this->getBuyerId()}
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
                AND news_categories.slug IN ('".implode("','", $categories)."')
                AND t.user_id = {$this->getBuyerId()}
                AND t.drop_sources NOT LIKE '{$dropSource}'
                AND t.drop_news NOT LIKE '{$dropNews}' 
                AND teasers_sub_group_settings.link IS NOT NULL
                AND top_teasers.impressions < {$this->getImpressionsTeaser()}  
                AND t.is_top = 0
                GROUP BY t.id, image.id, teasers_sub_group_settings.id, top_teasers.id
                ORDER BY top_teasers.e_cpm
                LIMIT {$limit}
                ";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        return new ArrayCollection($stmt->fetchAll());
    }

    /**
     * Объединить элементы выбранные по eCPM с элементами, которые еще не набрали достаточного кол-во показов
     *
     * lowItems должны стоять на местах указанных в lowItemsPlaces
     *
     * @param Collection|News[]|Teaser[] $highItems Элементы выбранные на общих условиях
     * @param Collection|Teaser[] $lowItems Элемент выбранные дополнительно по getRandomLowTeasers|getRandomLowTeasersForNews
     * @return Collection|News[]|Teaser[]
     */
    protected function combineHighAndLowItems(Collection $highItems, Collection $lowItems): Collection
    {
        if(!$highItems->isEmpty() && !$lowItems->isEmpty()){
            $i = 0;
            foreach($this->lowItemsPlaces as $key) {
                if($highItems[$key] || $highItems[$key+1] && $lowItems[$i]){
                    $highItems[$key] = $lowItems[$i];
                    $i++;
                }
            }
        }

        return $highItems;
    }

    /**
     * @return string
     */
    public function getTrafficType(): string
    {
        return $this->trafficType;
    }

    /**
     * @param string $trafficType
     * @return IAlgorithm
     */
    public function setTrafficType(string $trafficType): IAlgorithm
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    /**
     * @return int
     */
    public function getBuyerId(): int
    {
        return $this->buyerId;
    }

    /**
     * @param int $buyerId
     * @return IAlgorithm
     */
    public function setBuyerId(int $buyerId): IAlgorithm
    {
        $this->buyerId = $buyerId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSourceId(): ?int
    {
        return $this->sourceId;
    }

    /**
     * @param int|null $sourceId
     * @return IAlgorithm
     */
    public function setSourceId(?int $sourceId): IAlgorithm
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getImpressionsNews(): int
    {
        return $this->impressionsNews;
    }

    /**
     * @param int|null $impressionsNews
     * @return IAlgorithm
     */
    public function setImpressionsNews(?int $impressionsNews): IAlgorithm
    {
        $this->impressionsNews = !is_null($impressionsNews) ? $impressionsNews : 1000;

        return $this;
    }

    /**
     * @return int
     */
    public function getImpressionsTeaser(): int
    {
        return $this->impressionsTeaser;
    }

    /**
     * @param int|null $impressionsTeaser
     * @return IAlgorithm
     */
    public function setImpressionsTeaser(?int $impressionsTeaser): IAlgorithm
    {
        $this->impressionsTeaser = !is_null($impressionsTeaser) ? $impressionsTeaser : 1000;

        return $this;
    }

    /**
     * @return Country
     */
    public function getGeoCode(): Country
    {
        return $this->geoCode;
    }

    /**
     * @param string $geoCode
     * @return IAlgorithm
     */
    public function setGeoCode(string $geoCode): IAlgorithm
    {
        $country = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode($geoCode);
        $this->geoCode = $country ? $country : $this->entityManager->getRepository(Country::class)->getCountryByIsoCode('UA');

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): IAlgorithm
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param int $algorithmId
     * @return IAlgorithm
     */
    public function setAlgorithmId($algorithmId): IAlgorithm
    {
        $this->algorithmId = $algorithmId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAlgorithmId(): int
    {
        return $this->algorithmId;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return IAlgorithm
     */
    public function setEntityManager(EntityManagerInterface $entityManager): IAlgorithm
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @param AdapterInterface $cache
     * @return IAlgorithm
     */
    public function setCacheService(AdapterInterface $cache): IAlgorithm
    {
        $this->cache = new TagAwareAdapter($cache);

        return $this;
    }
}