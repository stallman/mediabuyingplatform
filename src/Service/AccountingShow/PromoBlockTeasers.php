<?php


namespace App\Service\AccountingShow;


use Doctrine\Common\Collections\ArrayCollection;

class PromoBlockTeasers extends AccountingShow
{
    public function saveStatistic(ArrayCollection $teasers)
    {
        if(!$teasers->isEmpty()){
            $insertList = $this->getInsertList($teasers);
            $insertList = implode(',', $insertList);
            $sql = "INSERT INTO statistic_promo_block_teasers 
                (teaser_id, mediabuyer_id, source_id, news_id, algorithm_id, design_id, country_code, traffic_type, page_type, created_at) VALUES {$insertList}";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();

            try{
                $this->updateEntity($teasers, 'teasers');
            } catch(\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function getInsertList(ArrayCollection $teasers)
    {
        $insertList = [];

        foreach($teasers as $teaser) {
            $insertList[] = "({$teaser['id']}, {$this->getMediaBuyer()}, {$this->getSource()}, {$this->getNews()}, {$this->getAlgorithm()}, {$this->getDesign()}, '{$this->getCountryCode()}', '{$this->getTrafficType()}', '{$this->getPageType()}', '{$this->getCreatedAt()}')";
        }

        return $insertList;
    }
}