<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyGrpMembersCommand extends Command
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
            ->setName('bcast:copy:grpmembers')
            ->setDescription('Copy GRPM table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy GRPM table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO grp_members(id, grp_id, user_id, created, updated) ' .
            'SELECT GrpMId, GrpId, UsrId, Created, Updated FROM GRPM ORDER BY GrpMId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(GrpMId) FROM GRPM');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max grpmid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE grp_members AUTO_INCREMENT = $max_id");

        $conn->executeQuery(
            'DELETE FROM grp_members WHERE user_id NOT IN ' .
            '(SELECT DISTINCT user_id FROM org_members)'
        );
    }
}
