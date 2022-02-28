<?php


namespace App\Command;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AddUserCommand extends Command
{
    public const ROLES_MAP = [
        'mediabuyer' => 'ROLE_MEDIABUYER',
        'journalist' => 'ROLE_JOURNALIST',
        'admin' => 'ROLE_ADMIN',
    ];

    /** @var UserPasswordEncoderInterface  */
    public $passwordEncoder;
    /** @var EntityManagerInterface  */
    public $entityManager;
    /** @var ValidatorInterface  */
    public $validator;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    )
    {
        parent::__construct();
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }


    protected function configure()
    {
        $this
            ->setName('app:user:add')
            ->setDescription('Add new user')
            ->setHelp('This command allows you to create a user')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $emailQuestion = new Question('Enter user email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        if (empty($email)) {
            $output->writeln('<comment>Email can not be empty!</comment>');
            $this->execute($input, $output);
        }

        $passwordQuestion = new Question('Enter user password (empty to auto-generate): ');
        $password = $helper->ask($input, $output, $passwordQuestion);
        $password = !empty($password) ? $password : uniqid('');

        $roleQuestion = new ChoiceQuestion(
            'Please select user role (defaults to mediabuyer): ',
            ['mediabuyer', 'journalist', 'admin'],
            0
        );
        $role = $helper->ask($input, $output, $roleQuestion);

        $confirmationFormat = 'Do you want to create user with email %s and role %s? [Y/n]';
        $question = new ConfirmationQuestion( sprintf($confirmationFormat, $email, $role), true, '/^(y|j)/i');

        if ($helper->ask($input, $output, $question)) {
            $user = new User();
            $user
                ->setRoles([self::ROLES_MAP[$role]])
                ->setEmail($email)
                ->setPassword($this->passwordEncoder->encodePassword($user, $password))
                ->setStatus(User::ENABLE_STATUS)
                ;

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $messageFormat = 'User with email %s and role %s was created!';

            $message = sprintf($messageFormat, $email, $role);

            $output->writeln('<info>'.$message.'</info>');
        }


        return 0;
    }
}