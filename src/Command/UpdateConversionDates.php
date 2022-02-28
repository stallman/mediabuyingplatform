<?php
namespace App\Command;

use App\Entity\Conversions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker;

class UpdateConversionDates extends Command
{
    /** @var EntityManagerInterface  */
    public $entityManager;

    public $faker;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    protected function configure()
    {
        $this
            ->setName('app:conversions:redate')
            ->setDescription('Сгенерировать фейковые даты создания/обновления конверсий')
            ->setHelp('Эта команда генерирует фейковые даты создания/обновления конверсий')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startMessage = "Начинаем менять даты у конверсий...\n";
        $output->writeln('<info>'.$startMessage.'</info>');

        $conversions = $this->entityManager->getRepository(Conversions::class)->findAll();
        foreach ($conversions as $conversion) {
            $dateAt = $this->faker->dateTimeBetween($startDate = '-2 month', $endDate = 'now', $timezone = null);
            $conversion->setCreatedAt($dateAt);
            $conversion->setUpdatedAt($dateAt);

            $this->entityManager->flush();
        }

        $endMessage =  "Выполнение скприта завершено\n";
        $output->writeln('<info>'.$endMessage.'</info>');
        return 0;
    }
}