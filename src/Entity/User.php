<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\DateHelper;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, EntityInterface
{
    const ENABLE_STATUS = 1;
    const FIRST_ROLES_KEY = 0;
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_JOURNALIST = 'ROLE_JOURNALIST';
    const ROLE_MEDIABUYER = 'ROLE_MEDIABUYER';
    const ROLES = [
        'ROLE_ADMIN' => 'Admin'
    ];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     */
    private $email;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    private $roles;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nickname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $telegram;

    public function __toString()
    {
        return $this->email ? $this->email : get_class($this);
    }

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=false)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\News", mappedBy="user")
     */
    private $news;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sources", mappedBy="user")
     */
    private $sources;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Teaser", mappedBy="user")
     */
    private $teasers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="buyer")
     */
    private $teasersClick;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserSettings", mappedBy="user")
     */
    private $userSettings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MediabuyerNews", mappedBy="mediabuyer")
     */
    private $mediabuyerNews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MediabuyerNewsRotation", mappedBy="mediabuyer")
     */
    private $mediabuyerNewsRotation;

    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity=DomainParking::class, mappedBy="user", orphanRemoval=true)
     */
    private $domains;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="mediabuyer", orphanRemoval=true)
     */
    private $conversions;

    /**
     * @ORM\OneToMany(targetEntity=Visits::class, mappedBy="mediabuyer")
     */
    private $visits;

    /**
     * @ORM\OneToMany(targetEntity=BlackList::class, mappedBy="buyer")
     */
    private $blackList;

    /**
     * @ORM\OneToMany(targetEntity=WhiteList::class, mappedBy="buyer")
     */
    private $whiteList;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockNews::class, mappedBy="mediabuyer")
     */
    private $statisticPromoBlockNews;

    /**
     * @ORM\OneToMany(targetEntity=AlgorithmsAggregatedStatistics::class, mappedBy="mediabuyer")
     */
    private $algorithmsAggregatedStatistics;

    /**
     * @ORM\OneToMany(targetEntity=DesignsAggregatedStatistics::class, mappedBy="mediabuyer")
     */
    private $designsAggregatedStatistics;

    /**
     * @ORM\OneToMany(targetEntity=MediabuyerAlgorithms::class, mappedBy="mediabuyer")
     */
    private $mediabuyerAlgorithms;

    /**
     * @ORM\OneToMany(targetEntity=MediabuyerDesigns::class, mappedBy="mediabuyer")
     */
    private $mediabuyerDesigns;

    /**
     * @ORM\OneToMany(targetEntity=Costs::class, mappedBy="mediabuyer")
     */
    private $costs;

    /**
     * @ORM\OneToMany(targetEntity=NewsClick::class, mappedBy="buyer")
     */
    private $newsClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClickShortToFull::class, mappedBy="buyer")
     */
    private $newsClickShortToFull;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity=Campaign::class, mappedBy="mediabuyer")
     */
    private $campaigns;

    public function __construct()
    {
        $this->news = new ArrayCollection();
        $this->sources = new ArrayCollection();
        $this->teasers = new ArrayCollection();
        $this->teasersClick = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->conversions = new ArrayCollection();
        $this->visits = new ArrayCollection();
        $this->statisticPromoBlockNews = new ArrayCollection();
        $this->algorithmsAggregatedStatistics = new ArrayCollection();
        $this->deisgnsAggregatedStatistics = new ArrayCollection();
        $this->mediabuyerAlgorithms = new ArrayCollection();
        $this->costs = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): bool
    {
        return (bool)$this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNickname(): string
    {
        return (string)$this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getTelegram(): string
    {
        return (string)$this->telegram;
    }

    public function setTelegram(?string $telegram): self
    {
        $this->telegram = $telegram;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): ?array
    {
        $roles = json_decode($this->roles);

        return $roles ? array_unique($roles) : null;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = json_encode($roles);

        return $this;
    }

    public function getRole(): ?string
    {
        $role = $this->roles ? json_decode($this->roles)[self::FIRST_ROLES_KEY] : null;

        return $role;
    }

    public function setRole(string $role): self
    {
        $this->roles = json_encode([$role]);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getCreatedAt(): string
    {
        return DateHelper::formatDefaultDate($this->createdAt);
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection|Sources[]
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    /**
     * @return Collection|Teaser[]
     */
    public function getTeasers(): Collection
    {
        return $this->teasers;
    }

    /**
     * @return Collection|TeasersClick[]
     */
    public function getTeasersClick(): Collection
    {
        return $this->teasersClick;
    }

    public function getUserSettings(): Collection
    {
        return $this->userSettings;
    }

    /**
     * @param string $slug
     * @return UserSettings|null
     */
    public function getUserSettingsBySlug(string $slug)
    {
        $result = null;

        /** @var UserSettings $userSetting */
        foreach ($this->getUserSettings() as $userSetting) {
            if ($userSetting->getSlug() == $slug) {
                $result = $userSetting;
            }
        }

        return $result;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setUser($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            // set the owning side to null (unless already changed)
            if ($news->getUser() === $this) {
                $news->setUser(null);
            }
        }

        return $this;
    }

    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return Collection|DomainParking[]
     */
    public function getDomain(): Collection
    {
        return $this->domains;
    }

    public function addDomain(DomainParking $domains): self
    {
        if (!$this->domains->contains($domains)) {
            $this->domains[] = $domains;
            $domains->setUser($this);
        }

        return $this;
    }

    public function removeDomains(DomainParking $domains): self
    {
        if ($this->domains->contains($domains)) {
            $this->domains->removeElement($domains);
            // set the owning side to null (unless already changed)
            if ($domains->getUser() === $this) {
                $domains->setUser(null);
            }
        }

        return $this;
    }

    public function getMediabuyerNews()
    {
        return $this->mediabuyerNews;

    }

    public function getMediabuyerNewsRotation()
    {
        return $this->mediabuyerNewsRotation;

    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'email',
        ]));
    }

    /**
     * @return Collection|Conversions[]
     */
    public function getConversions(): Collection
    {
        return $this->conversions;
    }

    public function addConversion(Conversions $conversion): self
    {
        if (!$this->conversions->contains($conversion)) {
            $this->conversions[] = $conversion;
            $conversion->setMediabuyer($this);
        }

        return $this;
    }

    public function removeConversion(Conversions $conversion): self
    {
        if ($this->conversions->contains($conversion)) {
            $this->conversions->removeElement($conversion);
            // set the owning side to null (unless already changed)
            if ($conversion->getMediabuyer() === $this) {
                $conversion->setMediabuyer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Visits[]
     */
    public function getVisits(): Collection
    {
        return $this->visits;
    }

    public function addVisit(Visits $visit): self
    {
        if (!$this->visits->contains($visit)) {
            $this->visits[] = $visit;
            $visit->setMediabuyer($this);
        }

        return $this;
    }

    public function removeVisit(Visits $visit): self
    {
        if ($this->visits->contains($visit)) {
            $this->visits->removeElement($visit);
            // set the owning side to null (unless already changed)
            if ($visit->getMediabuyer() === $this) {
                $visit->setMediabuyer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StatisticPromoBlockNews[]
     */
    public function getStatisticPromoBlockNews(): Collection
    {
        return $this->statisticPromoBlockNews;
    }

    public function addStatisticPromoBlockNews(StatisticPromoBlockNews $statisticPromoBlockNews): self
    {
        if (!$this->statisticPromoBlockNews->contains($statisticPromoBlockNews)) {
            $this->statisticPromoBlockNews[] = $statisticPromoBlockNews;
            $statisticPromoBlockNews->setMediabuyer($this);
        }

        return $this;
    }

    public function removeStatisticPromoBlockNews(StatisticPromoBlockNews $statisticPromoBlockNews): self
    {
        if ($this->statisticPromoBlockNews->contains($statisticPromoBlockNews)) {
            $this->statisticPromoBlockNews->removeElement($statisticPromoBlockNews);
            // set the owning side to null (unless already changed)
            if ($statisticPromoBlockNews->getMediabuyer() === $this) {
                $statisticPromoBlockNews->setMediabuyer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AlgorithmsAggregatedStatistics[]
     */
    public function getAlgorithmsAggregatedStatistics(): Collection
    {
        return $this->algorithmsAggregatedStatistics;
    }

    public function getMediabuyerAlgorithms(): Collection
    {
        return $this->mediabuyerAlgorithms;
    }

    /**
     * @return Collection|Costs[]
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCost(Costs $cost): self
    {
        if (!$this->costs->contains($cost)) {
            $this->costs[] = $cost;
            $cost->setMediabuyer($this);
        }

        return $this;
    }

    public function removeCost(Costs $cost): self
    {
        if ($this->costs->contains($cost)) {
            $this->costs->removeElement($cost);
            // set the owning side to null (unless already changed)
            if ($cost->getMediabuyer() === $this) {
                $cost->setMediabuyer(null);
            }
        }

        return $this;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): self
    {
        $this->settings = json_encode($settings);

        return $this;
    }

    public function getReportFields(): ?array
    {
        return isset($this->getSettings()['report_fields']) ? $this->getSettings()['report_fields'] : null;
    }

    public function setReportFields(?array $settings): self
    {
        $this->settings = $this->getSettings();
        $this->settings['report_fields'] = $settings;
        json_encode($this->settings);

        return $this;
    }

    /**
     * @return Collection|Campaign[]
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    public function addCampaign(Campaign $campaign): self
    {
        if (!$this->campaigns->contains($campaign)) {
            $this->campaigns[] = $campaign;
            $campaign->setMediabuyer($this);
        }

        return $this;
    }

    public function removeCampaign(Campaign $campaign): self
    {
        if ($this->campaigns->removeElement($campaign)) {
            // set the owning side to null (unless already changed)
            if ($campaign->getMediabuyer() === $this) {
                $campaign->setMediabuyer(null);
            }
        }

        return $this;
    }
}
