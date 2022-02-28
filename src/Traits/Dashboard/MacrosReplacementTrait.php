<?php


namespace App\Traits\Dashboard;

use Doctrine\Common\Collections\ArrayCollection;

trait MacrosReplacementTrait
{
    private function replaceMacrosToCity(string $city) {
        $teasers = [];
        foreach($this->teasers as $key => $teaser) {
            $teasers[$key] = $teaser;
            $teasers[$key]['text'] = str_replace('[CITY]', $city, $teaser['text']);
        }

        return new ArrayCollection($teasers);
    }
}
