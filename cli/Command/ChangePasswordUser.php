<?php

namespace PromCMS\Cli\Command;

use PromCMS\Cli\Application;
use PromCMS\Core\Password;
use PromCMS\Core\Services\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'users:change-password',
    description: 'Changes password of an user.',
    hidden: false,
)]
class ChangePasswordUser extends AbstractCommand
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
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password of user');
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getOption('email');
        $password = $input->getOption('password');

        /**
         * @var UserService
         */
        $userService = Application::getPromApp($input->getOption('cwd'))->getSlimApp()->getContainer()->get(UserService::class);
        $user = $userService->findOneBy([
            ["email", $email]
        ]);

        $user->setPassword(Password::hash($password));
        $user->save();

        $output->writeln("User with email '$email' found and password has been changed!");

        return $this::SUCCESS;
    }
}
