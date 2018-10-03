<?php

namespace App\Command;

use App\Util\MessageUtil;
use App\Util\PeriodicLock;
use App\Util\Purge;
use App\Util\SendBroadcast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Periodic extends Command
{
    private $sendBroadcast;
    private $purge;
    private $messageUtil;

    public function __construct(SendBroadcast $sendBroadcast, Purge $purge, MessageUtil $messageUtil,
                                ?string $name = null)
    {
        $this->sendBroadcast = $sendBroadcast;
        $this->purge = $purge;
        $this->messageUtil = $messageUtil;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('bcast:periodic')
            ->setDescription('Periodic task to send broadcasts and other maintenance')
            ->addOption(
                'purge',
                'p',
                InputOption::VALUE_NONE,
                'Purge old records then exit'
            )
            ->addOption(
                'send',
                's',
                InputOption::VALUE_NONE,
                'Send broadcasts then exit'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = new PeriodicLock();

        $purgeOnly = $input->getOption('purge');
        $sendOnly = $input->getOption('send');

        try {
            if ($lock->IsLocked() || $lock->Lock() === false) {
                $lock->MarkFailure();
                if ($lock->TooManyFailures() && !$lock->HaveFailuresBeenNotified()) {
                    $this->messageUtil->SendEmail(
                        [getenv('ADMIN_EMAIL')],
                        'Broncocast Periodic has failed to lock too many consecutive times in a row.',
                        null, null, null
                    );
                    $lock->MarkFailuresAsNotified();
                }
                return;
            }

//            if ($purgeOnly) {
//                $this->purgeRecords();
//            } elseif ($sendOnly) {
//                $this->sendBroadcast->SendBroadcasts();
//            } else {
//                $this->purgeRecords();
//                $this->sendBroadcast->SendBroadcasts();
//            }
        } catch (\Exception $e) {
            $this->messageUtil->SendEmail(
                [getenv('ADMIN_EMAIL')],
                "Broncocast Periodic has failed with the following exception:\n\n" .
                $e->getMessage(),
                null, null, null
            );
        }

        $lock->Unlock();
        $lock->ClearFailures();
    }

    private function purgeRecords()
    {
        $this->purge->PurgeBroadcasts();
        $this->purge->PurgeOrphanAttachments();
        $this->purge->PurgeSmsLogs();
        $this->sendBroadcast->SendBroadcasts();
    }
}
