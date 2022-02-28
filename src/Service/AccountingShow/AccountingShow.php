<?php


namespace App\Service\AccountingShow;


use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\DateHelper;
use Psr\Log\LoggerInterface;

abstract class AccountingShow
{
    private int $mediaBuyer;
    private string $countryCode;
    private ?int $source;
    private ?int $news = null;
    private string $trafficType;
    private string $pageType;
    private int $algorithm;
    private int $design;
    private DateTimeInterface $createdAt;
    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }


    abstract public function saveStatistic(ArrayCollection $items);

    abstract protected function getInsertList(ArrayCollection $items);

    public function getMediaBuyer(): int
    {
        return $this->mediaBuyer;
    }

    public function setMediaBuyer(int $mediaBuyer): self
    {
        $this->mediaBuyer = $mediaBuyer;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode) :self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getSource()
    {
        return $this->source ? $this->source : 'NULL';
    }

    public function setSource(?int $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getNews()
    {
        return $this->news ? $this->news : 'NULL';
    }

    public function setNews(?int $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getTrafficType(): string
    {
        return $this->trafficType;
    }

    public function setTrafficType(string $trafficType): self
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    public function getPageType(): string
    {
        return $this->pageType;
    }

    public function setPageType(string $pageType): self
    {
        $this->pageType = $pageType;

        return $this;
    }

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    public function setAlgorithm(int $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getDesign(): int
    {
        return $this->design;
    }

    public function setDesign(int $design): self
    {
        $this->design = $design;

        return $this;
    }

    public function getCreatedAt(): string
    {
        $this->createdAt = new \DateTime();

        return DateHelper::formatDatabaseFieldFormat($this->createdAt);
    }

    /**
     * @param ArrayCollection $entityCollection
     * @param string $tableName
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateEntity(ArrayCollection $entityCollection, string $tableName)
    {
        $idList = [];
        foreach($entityCollection as $entity) {
            $idList[] = $entity['id'];
        }
        $idList = implode(',', $idList);

        $sql = "UPDATE {$tableName}
                SET updated_at='{$this->getCreatedAt()}'
                WHERE id IN ({$idList});";

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
    }
}