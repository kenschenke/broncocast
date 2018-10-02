<?php

namespace App\Command;

use App\Util\Purge;
use App\Util\SendBroadcast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Periodic extends Command
{
    private $sendBroadcast;
    private $purge;

    public function __construct(SendBroadcast $sendBroadcast, Purge $purge, ?string $name = null)
    {
        $this->sendBroadcast = $sendBroadcast;
        $this->purge = $purge;

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
//        $this->purge->PurgeBroadcasts();
//        $this->purge->PurgeOrphanAttachments();
//        $this->sendBroadcast->SendBroadcasts();
    }
}
