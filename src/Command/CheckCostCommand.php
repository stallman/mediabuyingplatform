<?php
namespace App\Command;

use App\Entity\Costs;
use App\Traits\Dashboard\CostsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCostCommand extends Command
{
    use CostsTrait;

    public EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:check:cost');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $costs = $this->entityManager->getRepository(Costs::class)->getCostsByIsFinal(false);
        foreach ($costs as $cost) {
            $this->changeIsFinal($cost);
            $this->entityManager->flush();
        }

        return 0;
    }
}