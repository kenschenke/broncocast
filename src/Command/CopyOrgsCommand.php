<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyOrgsCommand extends Command
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
            ->setName('bcast:copy:orgs')
            ->setDescription('Copy ORG table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy ORGS table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO orgs(id, org_name, default_tz, tag, max_brc_age, created, updated) ' .
            'SELECT OrgId, OrgName, DefaultTZ, Tag, MaxBRCAge, Created, Updated FROM ORG ORDER BY OrgId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(OrgId) FROM ORG');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max orgid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE orgs AUTO_INCREMENT = $max_id");
    }
}
