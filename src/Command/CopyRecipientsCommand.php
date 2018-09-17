<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyRecipientsCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('bcast:copy:recipients')
            ->setDescription('Copy BRCR table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy BRCR table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO recipients(broadcast_id, user_id, created, updated) ' .
            'SELECT DISTINCT BrcId, UsrId, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM BRCR ' .
            'JOIN USRC ON BRCR.UsrCId = USRC.UsrCId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));
    }
}
