<?php

namespace PromCMS\Cli\Command;

use PromCMS\Cli\Application;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Services\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'users:create',
    description: 'Creates a new user.',
    hidden: false,
)]
class CreateUser extends AbstractCommand
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email of user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password of user')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Full name of user');
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    // TODO: add validation here
    // protected function initialize(InputInterface $input, OutputInterface $output)
    // {
    //     $email = $input->getOption('email');
    //     $password = $input->getOption('password');
    //     $name = $input->getOption('name');
    // }

    /**
     * {@inheritDoc}
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newUser = new User();
        $newUser->setEmail($input->getOption('email'));
        $newUser->setPassword($input->getOption('password'));
        $newUser->setName($input->getOption('name'));
        $newUser->setState(UserState::ACTIVE);
        $newUser->setRole('admin');

        /**
         * @var UserService
         */
        $userService = Application::getPromApp($input->getOption('cwd'))->getSlimApp()->getContainer()->get(UserService::class);
        $createdUser = $userService->create($newUser);
        $createdUserEmail = $createdUser->getEmail();

        $output->writeln("User with email '$createdUserEmail' has been created!");

        return $this::SUCCESS;
    }
}
