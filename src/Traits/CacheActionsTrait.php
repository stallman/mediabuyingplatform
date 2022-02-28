<?php

namespace App\Traits;

use App\Entity\EntityInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

trait CacheActionsTrait
{
    /**
     * @param array $removedEntityRelations
     * @param array $insertedEntityRelations
     * @param string $getter
     * @return array
     */
    private function getChangeRelationsList(array $removedEntityRelations, array $insertedEntityRelations, $getter = 'getId')
    {
        $entityIdList = [];

        /** @var EntityInterface $removedElement */
        foreach($removedEntityRelations as $removedElement) {
            $entityIdList[] = $removedElement->$getter();
        }

        /** @var EntityInterface $insertedCategory */
        foreach($insertedEntityRelations as $insertedElement) {
            $entityIdList[] = $insertedElement->$getter();
        }

        return $entityIdList;
    }

    /**
     * @param array $dropItemsList
     * @return array
     */
    private function getChangeDropItemsList(array $dropItemsList)
    {
        return array_unique(array_merge($dropItemsList[0], $dropItemsList[1]));

    }

    /**
     * @param TagAwareAdapter $cache
     * @param array $tagValuesList
     * @param string $tagName
     * @return void
     */
    private function clearCacheByTags(TagAwareAdapter $cache, array $tagValuesList, string $tagName)
    {
        $tagList = [];

        foreach($tagValuesList as $tagValue) {
            $tagList[] = $tagName . $tagValue;
        }

        try {
            $cache->invalidateTags($tagList);
        } catch(InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}