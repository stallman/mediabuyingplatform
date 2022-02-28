<?php


namespace App\Twig\Front;


use App\Entity\News;
use App\Entity\Teaser;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModalAdvExtension extends AbstractExtension
{
    public EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_modal_adv', [$this, 'getModalAdv']),
        ];
    }

    public function getModalAdv(int $limit = 6)
    {
        $rows = [];
        $news = [];
        $teasers = [];
        $limit = intval($limit / 2);

        try {
            $conn = $this->em->getConnection();
            $sql = <<<SQL
                SELECT n.id, n.title, i.file_path, i.file_name FROM news n
                LEFT JOIN image i on i.entity_fqn = :class AND n.id = i.entity_id
                LEFT JOIN statistic_news sn on n.id = sn.news_id
                WHERE sn.mediabuyer_id IS NULL
                ORDER BY sn.inner_e_cpm DESC
                LIMIT 0, 30
            SQL;

            $stmt = $conn->executeQuery($sql, ['class' => get_class(new News())], [ParameterType::STRING]);
            $news = $stmt->fetchAllAssociative();
        } catch (\Throwable $e) {}

        try {
            $conn = $this->em->getConnection();
            $sql = <<<SQL
                SELECT t.id, t.text AS title, i.file_path, i.file_name FROM teasers t
                LEFT JOIN image i on i.entity_fqn = :class AND t.id = i.entity_id
                LEFT JOIN statistic_teasers st on t.id = st.teaser_id
                ORDER BY st.e_cpm DESC
                LIMIT 0, 30
            SQL;

            $stmt = $conn->executeQuery($sql, ['class' => get_class(new Teaser())], [ParameterType::STRING]);
            $teasers = $stmt->fetchAllAssociative();
        } catch (\Throwable $e) {}

        $news = array_intersect_key($news, array_flip(array_rand($news, $limit)));
        $teasers = array_intersect_key($teasers, array_flip(array_rand($teasers, $limit)));

        while (!empty($news) || !empty($teasers)) {
            if (!empty($teasers)) {
                $rows[] = array_merge(array_shift($teasers), ['type' => 'teasers']);
            }
            if (!empty($news)) {
                $rows[] = array_merge(array_shift($news), ['type' => 'news']);
            }
        }

        return $rows;
    }
}