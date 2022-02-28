<?php

namespace App\Twig\Dashboard\MediaBuyer;

use App\Entity\Sources;
use App\Entity\Teaser;
use App\Entity\User;
use App\Twig\AppExtension;
use Twig\TwigFunction;

class SourcesExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('teasers_count', [$this, 'teasersCount'])
        ];
    }

    /**
     * @param Sources $source
     * @param User $user
     * @return array
     */
    public function teasersCount(Sources $source, User $user)
    {
        $allTeasersCount = $this->entityManager->getRepository(Teaser::class)->getCountTeasers($user);
        $banSources = $this->entityManager->getRepository(Teaser::class)->getCountTeasersByDropSource($source);

        return [
            'active_sources' => $allTeasersCount - $banSources,
            'ban_sources' => $banSources
        ];
    }


}