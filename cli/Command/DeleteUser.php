<?php

namespace PromCMS\Cli\Command;

use PromCMS\Core\Services\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'users:delete',
    description: 'Deletes an user.',
    hidden: false,
)]
class DeleteUser extends AbstractCommand
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email of user');
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

        /**
         * @var UserService
         */
        $userService = $this->getPromApp($input->getOption('cwd'))->getSlimApp()->getContainer()->get(UserService::class);
        $userService->deleteBy([
            ["email", $email]
        ]);

        $output->writeln("User with email '$email' has been deleted!");

        return $this::SUCCESS;
    }
}
