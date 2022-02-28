<?php

namespace App\DataFixtures;

use App\Entity\CurrencyList;
use App\Entity\UserSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const FAKE_PASS = '112233';
    const ROLES_ALIASES = ['ROLE_ADMIN' => 'admin', 'ROLE_JOURNALIST' => 'news', 'ROLE_MEDIABUYER' => 'buyer'];
    const CHANCE_OF_NOT_NULL = 0.8;

    /** @var UserPasswordEncoderInterface */
    public $passwordEncoder;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    public $user;


    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }


    public function load(ObjectManager $manager)
    {
        $this->generateManyUsers('ROLE_ADMIN', 2);
        $this->generateManyUsers('ROLE_JOURNALIST', 3);
        $this->generateManyUsers('ROLE_MEDIABUYER', 3);
    }

    public function generateManyUsers($role, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->createFakeUser(
                $role,
                $this->generateFakeEmail($role, $i),
                $this->getStatus($count, $i)
            );
        }
    }

    public function generateFakeEmail($role, $i)
    {
        return $this->getAliasByRole($role) . $i . '@demo.com';
    }

    private function getAliasByRole($role)
    {
        return self::ROLES_ALIASES[$role];
    }

    public function getStatus($count, $i)
    {
        return ($i + 1 == $count) ? 0 : 1;
    }

    private function createFakeUser($role, $email, $status)
    {
        $this->user = new User();
        $this->user->setRoles([$role])
            ->setEmail($email)
            ->setStatus($status)
            ->setNickname($this->generateFakeUserName())
            ->setTelegram($this->generateFakeTelegram())
            ->setPassword(
                $this->passwordEncoder->encodePassword($this->user, self::FAKE_PASS)
            );
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
        $this->generateUserSettings();
    }

    private function generateFakeTelegram() {
        $telegram = $this->faker->optional($weight = self::CHANCE_OF_NOT_NULL)->userName;
        if (!is_null($telegram)) {
            return '@' . str_replace('.', '', $telegram); //недопустимый символ в телеграме, который генерируется фейкером
        }
        return $telegram;
    }

    private function generateFakeUserName() {
        return $this->faker->optional($weight = self::CHANCE_OF_NOT_NULL)->userName;
    }

    private function generateUserSettings() {
        $this->generateMediabuyerUserSettings();
    }

    private function generateMediabuyerUserSettings() {
        $this->generateEcrmViewCount('ecrm_teasers_view_count');
        $this->generateEcrmViewCount('ecrm_news_view_count');
        $this->generateDefaultUserCurrency('default_currency');
    }

    private function generateEcrmViewCount($slug) {
        $userSettingsRepo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $this->user->getId()]
        );

        if(empty($userSettingsRepo)) {
            $userSettings = new UserSettings();
            $userSettings->setUser($this->user);
            $userSettings->setSlug($slug);
            if($this->user->getEmail() == 'buyer0@demo.com') {
                $userSettings->setValue(0);
            } else {
                $userSettings->setValue($this->faker->numberBetween(0, 9999));
            }
            $this->entityManager->persist($userSettings);
        } else {
            if($this->user->getEmail() == 'buyer0@demo.com') {
                $userSettingsRepo[0]->setValue(0);
            } else {
                $userSettingsRepo[0]->setValue($this->faker->numberBetween(0, 9999));
            }
        }
        $this->entityManager->flush();
    }

    private function generateDefaultUserCurrency($slug) {
        $userSettingsRepo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $this->user->getId()]
        );
        $currency = $this->entityManager->getRepository(CurrencyList::class)->getByIsoCode('rub');

        if(empty($userSettingsRepo)) {
            $userSettings = new UserSettings();
            $userSettings->setUser($this->user);
            $userSettings->setSlug($slug);
            $userSettings->setValue($currency->getId());
            $this->entityManager->persist($userSettings);
        } else {
            $userSettingsRepo[0]->setValue($currency->getId());
        }
        $this->entityManager->flush();
    }

    public function getDependencies()
    {
        return array(
            CurrencyFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'UserFixtures'];
    }
}