<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyBroadcastsCommand extends Command
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
            ->setName('bcast:copy:broadcasts')
            ->setDescription('Copy BRC table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy BRC table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO broadcasts(id, org_id, usr_name, scheduled, delivered, ' .
            'short_msg, long_msg, created, updated) ' .
            'SELECT BrcId, OrgId, UsrName, Scheduled, IFNULL(Scheduled,Created), ' .
            'ShortMsg, LongMsg, Created, Updated FROM BRC ORDER BY BrcId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(BrcId) FROM BRC');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max brcid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE broadcasts AUTO_INCREMENT = $max_id");
    }
}
