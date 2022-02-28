<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user_settings")
 * @ORM\Entity(repositoryClass="App\Repository\UserSettingsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class UserSettings
{

    public const SLUG_ECRM_TEASERS_VIEW_COUNT = 'ecrm_teasers_view_count';
    public const SLUG_ECRM_NEWS_VIEW_COUNT = 'ecrm_news_view_count';
    public const SLUG_DEFAULT_CURRENCY = 'default_currency';
    public const SLUG_STATS_STORAGE_DAYS = 'stats_storage_days';
    public const VALID_SLUGS = [
        self::SLUG_ECRM_TEASERS_VIEW_COUNT,
        self::SLUG_ECRM_NEWS_VIEW_COUNT,
        self::SLUG_DEFAULT_CURRENCY,
        self::SLUG_STATS_STORAGE_DAYS
    ];
    public const DEFAULT_STATS_STORAGE_DAYS = 730;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userSettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $value;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        if(!in_array($slug, static::VALID_SLUGS)){
            throw new \Error('Invalid slug value');
        }
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }
}
