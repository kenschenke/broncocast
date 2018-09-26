<?php

namespace App\Command;

use App\Util\SendBroadcast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Periodic extends Command
{
    private $sendBroadcast;

    public function __construct(SendBroadcast $sendBroadcast, ?string $name = null)
    {
        $this->sendBroadcast = $sendBroadcast;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('bcast:periodic')
            ->setDescription('Periodic task to send broadcasts and other maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->sendBroadcast->SendBroadcasts();
    }
}
