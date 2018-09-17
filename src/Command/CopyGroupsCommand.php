<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyGroupsCommand extends Command
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
            ->setName('bcast:copy:groups')
            ->setDescription('Copy GRP table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy GRP table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO groups(id, org_id, grp_name, created, updated) ' .
            'SELECT GrpId, OrgId, GrpName, Created, Updated FROM GRP ORDER BY GrpId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(GrpId) FROM GRP');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max grpid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE groups AUTO_INCREMENT = $max_id");
    }
}
