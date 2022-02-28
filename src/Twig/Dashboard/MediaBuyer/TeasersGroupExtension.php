<?php

namespace App\Twig\Dashboard\MediaBuyer;

use App\Entity\Teaser;
use App\Entity\TeasersSubGroup;
use App\Twig\AppExtension;
use Twig\TwigFunction;

class TeasersGroupExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('teasers_count_by_subgroup', [$this, 'teasersCountBySubGroup'])
        ];
    }

    /**
     * @param TeasersSubGroup $subGroup
     * @return int
     */
    public function teasersCountBySubGroup(TeasersSubGroup $subGroup)
    {
        return $this->entityManager->getRepository(Teaser::class)->getCountTeasersBySubGroup($subGroup);
    }


}