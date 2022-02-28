<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;

trait DateRangeTrait
{
    /**
     * @param Entity\News|null $news
     * @param bool $showTypeSelector
     * @return FormInterface
     */
    public function updateCreatedAtByDateRange(string $from, string $to, $objects)
    {
        $dateRangeArray = $this->createDateRangeArray($from, $to, count($objects));
        foreach ($objects as $i => $object) {
            if ($dateRangeArray[$i]) {
                $object->setCreatedAt($dateRangeArray[$i]);
                $this->entityManager->persist($object);
                $this->entityManager->flush();
            }
        }
    }

    public function createDateRangeArray($from, $to, $count)
    {
        $unixtimeFrom = strtotime($from);
        $unixtimeTo = strtotime($to);
        $difference = $unixtimeTo - $unixtimeFrom;
        if ($difference == 0 || $count <= 0) {
            return $from;
        }

        $timePerRow = $difference / $count;
        $timesArr = [];
        
        for ($i=0; $i<$count-1; $i++) {
            if ($i > 0 ) {
                $unixtimeFrom = $unixtimeFrom + $timePerRow;
            }
            $timesArr[$i] = \DateTime::createFromFormat( 'U', $unixtimeFrom);
        }

        $timesArr[] = \DateTime::createFromFormat( 'U', $unixtimeTo);
        
        return $timesArr;
    }
}