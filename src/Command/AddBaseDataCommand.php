<?php


namespace App\Command;


use App\Entity\Algorithm;
use App\Entity\ConversionStatus;
use App\Entity\Country;
use App\Entity\CropVariant;
use App\Entity\CurrencyList;
use App\Entity\Design;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Faker;


class AddBaseDataCommand extends Command
{
    private $container;

    const DEFAULT_BUYER = [
        'role' => 'ROLE_MEDIABUYER',
        'email' => 'buyer0@demo.com',
        'pass' => '112233',
    ];
    const ALGORITHMS = [
        'random' => 'Рандом',
        'section_random' => 'Секции + рандом',
        'screens' => 'Экраны',
        'hidden_blocks' => 'Скрытые блоки'
    ];
    const DESIGNES_COUNT = 5;
    const COUNTRY_LIST = [
        'RU' => 'Россия',
        'UA' => 'Украина',
        'US' => 'США',
    ];
    const CURRENCY_LIST = [
        [
            'name' => 'Доллар США',
            'iso_code' => 'usd',
            'symbol' => '$'
        ],
        [
            'name' => 'Российский рубль',
            'iso_code' => 'rub',
            'symbol' => '₽'
        ],
        [
            'name' => 'Украинская гривна',
            'iso_code' => 'uah',
            'symbol' => '₴'
        ],
        [
            'name' => 'Евро',
            'iso_code' => 'eur',
            'symbol' => '€'
        ],
    ];
    const DESIGN_PARAMS = [
        1 => [
            'news' => ['width' => 216, 'height' => 162],
            'teasers' => ['width' => 216, 'height' => 162],
        ],
//        2 => [
//            'teasers' => ['width' => 385, 'height' => 289],
//            'news' => ['width' => 385, 'height' => 289],
//        ],
        2 => [
            'teasers' => ['width' => 240, 'height' => 180],
            'news' => ['width' => 240, 'height' => 180],
        ],
        3 => [
            'teasers' => ['width' => 275, 'height' => 183],
            'news' => ['width' => 275, 'height' => 183],
        ],
        4 => [
            'teasers' => ['width' => 528, 'height' => 320],
            'news' => ['width' => 528, 'height' => 320],
        ],
        5 => [
            'teasers' => ['width' => 492, 'height' => 328],
            'news' => ['width' => 492, 'height' => 328],
        ],
    ];

    public UserPasswordEncoderInterface $passwordEncoder;
    public EntityManagerInterface $entityManager;
    public ValidatorInterface $validator;
    public Generator $faker;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ContainerInterface $container
    )
    {
        parent::__construct();
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->faker = Faker\Factory::create();
        $this->container = $container;
    }


    protected function configure()
    {
        $this
            ->setName('app:base-data:create')
            ->setDescription('Create base data')
            ->setHelp('Эта команда заполнит базу основными данными необходимыми для работы проекта');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>' . 'Заполняем таблицу с валютами..' . '</info>');
        $this->createCurrencies();

        $output->writeln('<info>' . 'Создаем дефолтного баера..' . '</info>');
        $this->createDefaultMediaBuyer();

        $output->writeln('<info>' . 'Заполняем таблицу с алгоритмами..' . '</info>');
        $this->createAlgorithms();

        $output->writeln('<info>' . 'Заполняем таблицу с дизайнами..' . '</info>');
        $this->createDesigns();

        $output->writeln('<info>' . 'Заполняем таблицу со странами..' . '</info>');
        $this->createCountries();

        $output->writeln('<info>' . 'Заполняем таблицу с вариантами кропа..' . '</info>');
        $this->createCropVariants();

        $output->writeln('<info>' . 'Заполняем таблицу статусов конверсий..' . '</info>');
        $this->createConversionStatuses();

        return 0;
    }

    private function createDefaultMediaBuyer()
    {

        $user = new User();
        $user->setRoles([self::DEFAULT_BUYER['role']])
            ->setEmail(self::DEFAULT_BUYER['email'])
            ->setStatus(true)
            ->setPassword($this->passwordEncoder->encodePassword($user, self::DEFAULT_BUYER['pass']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function createAlgorithms()
    {
        foreach(self::ALGORITHMS as $slug => $name) {
            $algorithm = new Algorithm();
            $algorithm->setName($name)
                    ->setSlug($slug);

            if($name == 'Рандом'){
                $algorithm->setIsDefault(1);
            } else {
                $algorithm->setIsDefault(0);
            }

            $algorithm->setIsActive(true);

            $this->entityManager->persist($algorithm);
            $this->entityManager->flush();
        }
    }

    private function createDesigns()
    {
        for($i = 0; $i < self::DESIGNES_COUNT; $i++) {
            $design = new Design();
            $design->setName("Дизайн " . ($i + 1))
                ->setSlug("theme_" . ($i + 1));
            $active = true;
            if($i == 2){
                $active = false;
            }
            $design->setIsActive($active);

            $this->entityManager->persist($design);
            $this->entityManager->flush();
        }
    }

    private function createCountries()
    {
        foreach(self::COUNTRY_LIST as $isoCode => $country) {
            $countryEntity = new Country();
            $countryEntity->setName($country);
            $countryEntity->setIsoCode($isoCode);

            $this->entityManager->persist($countryEntity);
        }
        $this->entityManager->flush();

        return $this;
    }

    private function createCurrencies()
    {
        foreach(self::CURRENCY_LIST as $currency) {
            $currencyEntity = new CurrencyList();
            $currencyEntity->setName($currency['name'])
                ->setIsoCode($currency['iso_code'])
                ->setSymbol($currency['symbol']);

            $this->entityManager->persist($currencyEntity);
        }
        $this->entityManager->flush();

        return $this;
    }

    private function createConversionStatuses()
    {
        foreach(InstallCommand::CONVERSION_STATUSES as $status) {
            $conversionStatus = new ConversionStatus();
            $conversionStatus
                ->setCode($status['code'])
                ->setLabelRu($status['label_ru'])
                ->setLabelEn($status['label_en']);

            $this->entityManager->persist($conversionStatus);
        }

        $this->entityManager->flush();
    }

    private function createCropVariants()
    {
        $countCropVariant = $this->container->getParameter('theme_count');

        for($i = 1; $i <= $countCropVariant; $i++) {
            $cropVariant = new CropVariant();

            $cropVariant->setDesignNumber("Дизайн $i");
            $cropVariant->setWidthTeaserBlock($this->getTeasersBlockAttr($i, 'width'));
            $cropVariant->setHeightTeaserBlock($this->getTeasersBlockAttr($i, 'height'));
            $cropVariant->setWidthNewsBlock($this->getNewsBlockAttr($i, 'width'));
            $cropVariant->setHeightNewsBlock($this->getNewsBlockAttr($i, 'height'));

            $this->entityManager->persist($cropVariant);
        }
        $this->entityManager->flush();
    }

    public function getTeasersBlockAttr($designNum, $param)
    {
        return self::DESIGN_PARAMS[$designNum]['teasers'][$param];
    }

    public function getNewsBlockAttr($designNum, $param)
    {
        return self::DESIGN_PARAMS[$designNum]['news'][$param];
    }
}