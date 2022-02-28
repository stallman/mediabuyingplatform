<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @param int $length
     * @param int $start
     * @param array $order
     * @param array|null $categories
     * @param string $search
     * @return int|mixed|string
     */
    public function getUndeletedNewsList($length = 20, $start = 0, array  $order, array $categories = null, string $search = null)
    {
        $query = $this->createQueryBuilder('news');

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

        if($this->isStatisticOrdering($order)){
            $query = $this->orderByStatistic($query, $order);
        } else {
            $query = $this->orderByColumn($query, $order);
        }

        $query = $query->andWhere('news.is_deleted = :is_deleted')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameter('is_deleted', 0);

        return $query->getQuery()->getResult();
    }

    private function isStatisticOrdering($order) {    
        return stripos($order[0]['column'], 'stat') !== false;    
    }

    /**
     * @param array|null $categories
     * @param string|null $search
     * @param bool|null $isActive
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUndeletedNewsCount(array $categories = null, string $search = null, bool $isActive = null)
    {
        $query = $this->createQueryBuilder('news')
            ->select('count(DISTINCT(news.id)) as count');

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

        if(!is_null($isActive)){
            $query = $query->andWhere('news.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        $query = $query->andWhere('news.is_deleted = :is_deleted')
            ->setParameter('is_deleted', 0)
            ->getQuery();

        return $query->getOneOrNullResult()['count'];
    }

    public function getMediaBuyerNewsList(User $user)
    {
        $query = $this->createQueryBuilder('news')
            ->where('news.user = :user')
            ->andWhere('news.is_deleted = :is_deleted')
            ->orWhere('news.type = :type AND news.isActive = :active')
            ->setParameters([
                'user' => $user->getId(),
                'type' => 'common',
                'active' => 1,
                'is_deleted' => 0,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getMediaBuyerNewsPaginated(User $user, array $categories = [], string $search = '', array $order = [], $offset = 0, $limit = 20): array
    {
        $em = $this->getEntityManager();
        $sql = $this->getMediaBuyerNewsSql($user, $categories, $search);

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $newsResults = $stmt->fetchAll();

        $newsStats = [];
        $newsArray = [];

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles()) ? true : false ;

        foreach ($newsResults as $key => $news) {
            if (
                // Недоступные (Которые НЕ выводить) вроде получаются "частная + другой байер" в остальных случаях всё выводим.
                (intval($news['user_id']) !== intval($user->getId()) && $news['type'] === 'own') ||
                // если статус "активна" и новость общедоступна – то новость отображается у всех пользователей системы
                // если статус "неактивна" – то новость может видеть только админ и тот, кто её добавил, а использовать не может никто.
                (!$isAdmin && intval($news['user_id']) !== intval($user->getId()) && $news['type'] === 'common' && !boolval(intval($news['is_active'])))
            ) {
                unset($newsResults[$key]);
                continue;
            }

            $newsArray[$news['id']] = $news;
        }

        // news has statistic with same news user_id
        if ($isAdmin) {
            $buyerNewsStats = $this->getMediaBuyerNewsStatSql($user, $newsArray, true, true);
        } else {
            $buyerNewsStats = $this->getMediaBuyerNewsStatSql($user, $newsArray, true);
        }

        if (count($buyerNewsStats) < count($newsArray)) {
            $newsIds = array_column($newsResults, 'id');
            $buyerNewsIds = array_column($buyerNewsStats, 'news_id');
            $otherNewsIds = array_diff($newsIds, $buyerNewsIds);

            $otherNews = [];
            foreach ($otherNewsIds as $newsId) {
                $otherNews[$newsId] = $newsArray[$newsId];
            }

            // news has no statistic with same news user_id
            $newsStats = $this->getMediaBuyerNewsStatSql($user, $otherNews);
        }

        $newsStats = $buyerNewsStats + $newsStats;
        ksort($newsStats);

        foreach ($newsResults as $news) {
            if (!isset($newsStats[$news['id']])) {
                continue;
            }

            $newsArray[$news['id']] += $newsStats[$news['id']];
        }

        return $this->getSortedNews($user, $newsArray, $order, $offset, $limit);
    }

    private function getSortedNews(UserInterface $user, array $newsArray = [], array $orders = [], $offset = 0, $limit = 20): array
    {
        $newsCount = count($newsArray);
        $newsActiveCount = 0;
        $buyerNews = [];
        $otherNews = [];
        foreach ($newsArray as $id => $news) {
            if (boolval(intval($news['is_active'])) && boolval(intval($news['is_rotation']))) {
                $newsActiveCount++;
            }
            //else {
            //    $otherNews[$id] = $news;
            //}
            $buyerNews[$id] = $news;
        }

        if ($orders) {
            $order = array_shift($orders);
            $pos = intval(strpos($order['column'], '.'));
            $column = substr($order['column'], $pos ? $pos + 1 : $pos);
            $direction = $order['dir'];
            $cc = new CamelCaseToSnakeCaseNameConverter();
            $column = $cc->normalize($column);
            if ($column === 'inner_c_t_r') $column = 'inner_ctr';
            if ($column === 'inner_e_c_p_m') $column = 'inner_e_cpm';
            if ($column === 'e_p_c') $column = 'epc';
            if ($column === 'c_r') $column = 'cr';

            // sort by speciffic column
            $buyerNews = $this->sortBySpecifficColumn($buyerNews, $column, $direction);
            $otherNews = $this->sortBySpecifficColumn($otherNews, $column, $direction);

            $newsArray = $buyerNews + $otherNews;

            $offset = intval($offset);
            $limit = intval($limit);

            if ($limit > 0) {
                $newsArray = array_slice($newsArray, $offset, $limit);
            }
        }

        return [$newsArray, $newsCount, $newsActiveCount];
    }

    private function injectIdKeysForArray(array $array = []): array
    {
        $newArray = [];
        foreach ($array as $key => $item) {
            $key = isset($item['id']) ? $item['id'] : $key ;
            $newArray[$key] = $item;
        }
        return $newArray;
    }

    private function sortBySpecifficColumn(array $newsArray = [], string $column = 'id', string $direction = 'asc'): array
    {
        usort($newsArray, function ($a, $b) use ($column, $direction) {
            $a = $a[$column];
            $b = $b[$column];

            $a = floatval($a);
            $b = floatval($b);

            if ($a == $b) return 0;

            if ($direction == 'asc')
                return ($a < $b) ? -1 : 1;
            else
                return ($a > $b) ? -1 : 1;
        });

        $newsArray = $this->injectIdKeysForArray($newsArray);

        return $newsArray;
    }

    private function getMediaBuyerNewsStatSql(UserInterface $user, array $newsArray = [], bool $isMediabuyerStat = false, bool $isAdmin = false): array
    {
        $stats = [];

        $resetStats = [
            "inner_show" => "0",
            "inner_click" => "0",
            "inner_ctr" => "0.0000",
            "inner_e_cpm" => "0.0000",
            "click" => "0",
            "uniq_visits" => "0",
            "click_on_teaser" => "0",
            "probiv" => "0.0000",
            "conversion" => "0",
            "approve_conversion" => "0",
            "approve" => "0",
            "involvement" => "0.0000",
            "epc" => "0.0000",
            "cr" => "0.0000",
            "has_statistic" => 0,
        ];

        if ($newsArray) {
            $em = $this->getEntityManager();
            $newsIds = array_column($newsArray, 'id');
            $buyerCriteria = $isMediabuyerStat ? "mediabuyer_id = " . $user->getId() : "mediabuyer_id is null" ;

            if ($isMediabuyerStat && $isAdmin) {
                $buyerCriteria = "mediabuyer_id is not null";
            }

            // fix group by for admin
            $sql = 'select *, id as sid from statistic_news where news_id in (' . implode(',', $newsIds) . ') and ' . $buyerCriteria;

            if (!$isAdmin) {
                $sql .= ' group by news_id';
            }

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $statNewsArray = $stmt->fetchAll();
            $statKeys = array_keys($resetStats);

            if ($isMediabuyerStat && $isAdmin) {

                foreach ($statNewsArray as $item) {
                    $item['has_statistic'] = 0;

                    foreach ($item as $key => $statItem) {
                        if (in_array($key, $statKeys)) {
                            $item['has_statistic'] += floatval($statItem);
                        }
                    }

                    if (!isset($stats[$item['news_id']])) {
                        $stats[$item['news_id']] = $item;
                    } else {
                        foreach ($item as $key => $value) {
                            if (in_array($key, $statKeys) && isset($stats[$item['news_id']][$key])) {
                                $statItem = ($stats[$item['news_id']][$key] + 0) + ($value + 0);
                                $item[$key] = $statItem;
                                $item['has_statistic'] += floatval($statItem);
                            }
                        }
                        $stats[$item['news_id']] = $item;
                    }
                }
            } else {
                foreach ($statNewsArray as $item) {
                    $item['has_statistic'] = 0;

                    foreach ($item as $key => $statItem) {
                        if (in_array($key, $statKeys)) {
                            $item['has_statistic'] += floatval($statItem);
                        }
                    }

                    $stats[$item['news_id']] = $item;
                }
            }

            if (empty($statNewsArray)) {
                foreach ($newsArray as $item) {
                    $stats[$item['id']] = $resetStats;
                }
            }

            /**
             * У байера по общедоступным + неактивным не нужно отображать никакую статистику
             * (т.е. пока он не наберет её сам – отображаем нули).
             * Когда байер сделал общедоступную новость активной, набрал по ней стату,
             * а затем сделал снова неактивной. В таком случае следует отображать ему ту статистику,
             * которую он успел набрать прежде
             */
            if (!$isMediabuyerStat) {
                foreach ($newsArray as $news) {
                    if ($news['is_active'] === '0' && $news['type'] === 'common' && isset($stats[$news['id']])) {
                        $stats[$news['id']] = array_replace($stats[$news['id']], $resetStats);
                    }
                }
            }
        }

        return $stats;
    }

    private function getMediaBuyerNewsSql(User $user, array $categories = [], string $search = '', ?bool $isActive = null): string
    {
        $sql = 'select n.id, n.type, n.is_active, n.user_id, n.title, mnr.is_rotation';
        $sql .= ', GROUP_CONCAT(DISTINCT c.name SEPARATOR \',<br>\') AS countries';
        $sql .= ', GROUP_CONCAT(DISTINCT nc2.title ORDER BY nc2.id ASC SEPARATOR \',<br>\') AS categories';
        $sql .= ' from news n';
        $sql .= ' left join mediabuyer_news_rotation mnr on (mnr.mediabuyer_id = ' . $user->getId() . ' and mnr.news_id = n.id) ';
        $sql .= ' left join news_categories_relations ncr on (ncr.news_id = n.id) ';
        $sql .= ' left join news_categories nc on (nc.id = ncr.news_category_id) ';
        $sql .= ' inner join news_countries_relations ncnr on (ncnr.news_id = n.id)';
        $sql .= ' inner join country c on (c.id = ncnr.country_id)';
        $sql .= ' left join news_categories_relations ncr2 on (ncr2.news_id = n.id) ';
        $sql .= ' left join news_categories nc2 on (nc2.id = ncr2.news_category_id) ';
        $sql .= ' where n.is_deleted = 0';

        if (null !== $isActive) {
            $sql .= ' and n.is_active = ' . intval($isActive);
        }

        if ($categories) {
            $sql .= ' and nc.id in ('.implode(',', $categories).')';
        }

        if ($search) {
            $sql .= " and (n.id LIKE '%$search%' OR n.title LIKE '%$search%')";
        }

        $sql .= " group by n.id";

        return $sql;
    }

    public function getMediaBuyerNewsPaginateList(User $user, $length = 20, $start = 0, array  $order, array $categories = null, string $search = null)
    {
        $query = $this->createQueryBuilder('news')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->where('news.user = :user')
            ->orWhere('news.type = :type')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'type' => 'common',
                'is_deleted' => 0,
            ]);

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

        if($this->isStatisticOrdering($order)){
            $query = $this->orderByBuyerStatistic($query, $order);
        } else {
            $query = $this->orderByColumn($query, $order);
        }

        return $query->getQuery()->getResult();
    }

    public function getMediaBuyerNewsCount(User $user, array $categories = null, string $search = null, ?bool $isActive = null)
    {
        $query = $this->createQueryBuilder('news')
            ->select('count(DISTINCT(news.id)) as count')
            ->andWhere('news.user = :user')
            ->orWhere('news.type = :type')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'type' => 'common',
                'is_deleted' => 0,
            ]);

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

        if(!is_null($isActive)){
            $query = $query->andWhere('news.isActive = :isActive')
            ->setParameter('isActive', $isActive);
        }

        return $query->getQuery()->getSingleResult()['count'];
    }

    public function getJournalistNewsList(User $user)
    {
        $query = $this->createQueryBuilder('news')
            ->leftJoin(User::class, 'user', Expr\Join::WITH, 'news.user = user.id')
            ->where('user.roles NOT LIKE :role')
            ->andWhere('news.user = :user')
            ->andWhere('news.type = :type')
            ->orWhere('news.type = :type AND news.isActive = :active')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => 0,
                'role' => '%ROLE_MEDIABUYER%',
                'type' => 'common',
                'active' => 1,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getJournalistNewsPaginateList(User $user, array $order, $length = 20, $start = 0, array $categories = null, string $search = null)
    {
        $query = $this->createQueryBuilder('news')
            ->leftJoin(User::class, 'user', Expr\Join::WITH, 'news.user = user.id')
            ->where('user.roles NOT LIKE :role')
            ->andWhere('news.user = :user')
            ->andWhere('news.type = :type')
            ->orWhere('news.type = :type AND news.isActive = :active')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => 0,
                'role' => '%ROLE_MEDIABUYER%',
                'type' => 'common',
                'active' => 1,
            ]);

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

       $query = $this->orderByColumn($query, $order);

        return $query->getQuery()->getResult();
    }

    public function getJournalistNewsCount(User $user, array $categories = null, string $search = null, ?bool $isActive = null)
    {
        $query = $this->createQueryBuilder('news')
            ->select('count(DISTINCT(news.id)) as count')
            ->leftJoin(User::class, 'user', Expr\Join::WITH, 'news.user = user.id')
            ->where('user.roles NOT LIKE :role')
            ->andWhere('news.user = :user')
            ->andWhere('news.type = :type')
            ->orWhere('news.type = :type AND news.isActive = :active')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => 0,
                'role' => '%ROLE_MEDIABUYER%',
                'type' => 'common',
                'active' => 1,
            ]);

        if(!is_null($isActive)){
            $query = $query->andWhere('news.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        if($categories){
            $query = $this->getByCategories($query, $categories);
        }

        if($search){
            $query = $this->searchNews($query, $search);
        }

        return $query->getQuery()->getSingleResult()['count'];
    }

    public function getActiveNews()
    {
        $query = $this->createQueryBuilder('news')
            ->where('news.isActive = :is_active')
            ->andWhere('news.is_deleted = :is_deleted')
            ->setParameters([
                'is_active' => 1,
                'is_deleted' => 0,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getActiveNewsByCountry($countryCode, User $user, $source)
    {
        $query = $this->createQueryBuilder('news')
            ->select('news.id', 'news.title', 'news.createdAt', 'image.filePath', 'image.fileName')
            ->leftJoin('news.countries', 'country')
            ->leftJoin(Image::class, 'image', 'WITH', 'news.id = image.entityId AND image.entityFQN = :entityFQN')
            ->leftJoin(MediabuyerNews::class, 'mediaBuyerNews', 'WITH', 'mediaBuyerNews.news = news.id AND mediaBuyerNews.mediabuyer = news.user')
            ->where('news.isActive = :is_active')
            ->andWhere('news.is_deleted = :is_deleted')
            ->andWhere('news.user = :user')
            ->andWhere('mediaBuyerNews.dropSources NOT LIKE :source')
            ->andWhere('country.iso_code = :iso_code')
            ->setParameters([
                'is_active' => 1,
                'is_deleted' => 0,
                'user' => $user,
                'source' => "%$source%",
                'iso_code' => $countryCode,
                'entityFQN' => get_class(new News())
            ])
            ->getQuery();

        return $query->getResult();
    }

    private function getByCategories($query, $categories)
    {
        return $query->leftJoin('news.categories', 'category')
            ->andWhere('category.id IN(:categories)')
            ->setParameter('categories', $categories);
    }

    private function searchNews($query, $search)
    {
        return $query->andWhere('news.id LIKE :search OR news.title LIKE :search')
            ->setParameter('search', "%$search%");
    }

    private function orderByStatistic($query, $order)
    {
        $column = $order[0]['column'];
        $direction = $order[0]['dir'];

        return $query->leftJoin('news.statistic', 'stat')
            ->andWhere('stat.mediabuyer is NULL')
            ->orderBy($column, $direction)
            ->addGroupBy('news.id', 'stat.id');
    }

    private function orderByBuyerStatistic($query, $order)
    {
        $column = $order[0]['column'];
        $direction = $order[0]['dir'];
        $orderedNewStatisticSqlString = implode(',', array_reverse($this->getStatisticNewsOrderedByColumnIds($column)));

        return $query->orderBy('FIELD(news.id,' . $orderedNewStatisticSqlString . ')', $direction);
    }

    private function getStatisticNewsOrderedByColumnIds($column) {
        $column = $this->prepareColumnNameForSql($column);
        $sql = 'SELECT news_id FROM statistic_news sn 
            WHERE 
            ' . $column . ' > 0
            AND 
            sn.mediabuyer_id IS NULL
            GROUP BY news_id
            ORDER BY ' . $column . ' DESC;';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function prepareColumnNameForSql($column) {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
        $column = str_replace('stat.', '', $column);
        
        switch ($column) {
            case 'inner_eCPM':
                $column = 'inner_e_cpm';
                break;
            case 'innerCTR':
                $column = 'inner_ctr';
                break;
            case 'EPC':
                $column = 'epc';
                break;
            case 'CR':
                $column = 'cr';
                break;
            default:
                $column = $nameConverter->normalize($column);
        }
    
        return $column;
    }

    private function orderByColumn($query, $order)
    {
        return $query->orderBy("news.{$order[0]['column']}", $order[0]['dir'])
            ->addGroupBy('news.id');
    }

    public function getNewsCategories(News $news)
    {
        $query = $this->createQueryBuilder('news')
            ->select('category.slug')
            ->leftJoin('news.categories', 'category')
            ->where('news = :news')
            ->setParameter('news', $news)
            ->getQuery();
        $result = $query->getResult();

        return array_column($result, "slug");
    }

    public function getMediaBuyerNewsOwnECPMCount(User $user, int $innerShow)
    {
        $query = $this->createQueryBuilder('news')
            ->select('count(DISTINCT(news.id)) as count')
            ->leftJoin('news.topNews', 'topNews')
            ->leftJoin('news.statistic', 'statistic')
            ->andWhere('news.user = :user')
            ->orWhere('news.type = :type')
            ->andWhere('news.is_deleted = :is_deleted')
            ->andWhere('statistic.innerShow >= :innerShow')
            ->setParameters([
                'user' => $user->getId(),
                'type' => 'common',
                'is_deleted' => 0,
                'innerShow' => $innerShow,
            ]);

        return $query->getQuery()->getSingleResult()['count'];
    }
}
