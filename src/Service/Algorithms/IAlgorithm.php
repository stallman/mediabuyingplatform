<?php


namespace App\Service\Algorithms;


use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\Teaser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

interface IAlgorithm
{
    public function setAlgorithmId(int $algorithmId): IAlgorithm;

    public function setBuyerId(int $buyerId): IAlgorithm;

    public function setSourceId(?int $sourceId): IAlgorithm;

    public function setGeoCode(string $geoCode): IAlgorithm;

    public function setTrafficType(string $trafficType): IAlgorithm;

    public function setEntityManager(EntityManagerInterface $entityManager): IAlgorithm;

    /**
     * Получить новости для страницы общего топа новостей
     *
     * @param int $page
     * @return Collection|News[]
     */
    public function getNewsForTop(int $page = 1): Collection;

    /**
     * Получить новости для страницы топа категории новостей
     *
     * @param NewsCategory $category
     * @param int $page
     * @return Collection|News[]
     */
    public function getNewsForCategory(NewsCategory $category, int $page = 1): Collection;

    /**
     * Получить тизеры для страницы общего топа тизеров
     *
     * @param int $page
     * @return Collection|Teaser[]
     */
    public function getTeaserForTop(int $page = 1): Collection;

    /**
     *  Получить тизеры для страницы короткой или полной новости
     *
     * @param News $news
     * @param int $page
     * @return Collection|Teaser[]
     */
    public function getTeaserForNews(News $news, int $page = 1): Collection;
}