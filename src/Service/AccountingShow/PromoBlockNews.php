<?php


namespace App\Service\AccountingShow;


use Doctrine\Common\Collections\ArrayCollection;

class PromoBlockNews extends AccountingShow
{
  public function saveStatistic(ArrayCollection $news)
  {
      if($news->isEmpty()) return;

      $insertList = $this->getInsertList($news);
      $insertList = implode(',', $insertList);
      $sql = "INSERT INTO statistic_promo_block_news 
                (news_id, mediabuyer_id, source_id, algorithm_id, design_id, country_code, traffic_type, page_type, created_at) VALUES {$insertList}";
      $stmt = $this->entityManager->getConnection()->prepare($sql);
      $stmt->execute();

      try{
          $this->updateEntity($news, 'news');
      } catch(\Exception $e) {
          $this->logger->error($e->getMessage());
      }
  }

  protected function getInsertList(ArrayCollection $news)
  {
    $insertList = [];

    foreach($news as $newsItem){
        $insertList[] = "({$newsItem['id']}, {$this->getMediaBuyer()}, {$this->getSource()}, {$this->getAlgorithm()}, {$this->getDesign()}, '{$this->getCountryCode()}', '{$this->getTrafficType()}', '{$this->getPageType()}', '{$this->getCreatedAt()}')";
    }

    return $insertList;
  }
}