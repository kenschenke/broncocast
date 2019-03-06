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
        // Old device token
//        $this->notifications->SendApplePushNotifications(['a07527aa7d18d006c7dd7015048472323c074511e628746d5e09ce26657fdf6f'], 'Shop opens at 10', 0);

        // Dev device token
//        $this->notifications->SendApplePushNotifications(['8e2f1b15d6393266bb7ab6d325ef32f6ec409027482306caf05d0909955dd030'], 'Shop opens at 10', 1707);

        // Prod device token
        $this->notifications->SendApplePushNotifications([
            'e092c6543f8fce4daeb8fb58c6235a801689fb7c3fdb361f0ad4e205a9d6b972',  // really old one
        ], 'Shop opens at 10', 1707);
    }
}
