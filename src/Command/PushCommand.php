<?php

namespace App\Command;

use App\Util\PushNotifications;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushCommand extends Command
{
    private $notifications;

    public function __construct(PushNotifications $notifications, ?string $name = null)
    {
        $this->notifications = $notifications;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('bcast:pushtest')
            ->setDescription('Test push notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->notifications->SendApplePushNotifications(['a07527aa7d18d006c7dd7015048472323c074511e628746d5e09ce26657fdf6f'], 'Shop opens at 10', 0);
//        $this->notifications->SendApplePushNotifications(['a07527aa7d18d006c7dd7015048472323c074511e628746d5e09ce26657fdf60'], 'Shop opens at 10', 0);
    }
}
