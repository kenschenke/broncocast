<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyOrgMembersCommand extends Command
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
            ->setName('bcast:copy:orgmembers')
            ->setDescription('Copy ORGM table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy ORGM table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO org_members(id, org_id, user_id, is_admin, is_approved, ' .
            'is_blocked, alt_usr_name, created, updated) ' .
            'SELECT OrgMId, OrgId, UsrId, IsAdmin, Approved, Blocked, AltUsrName, Created, Updated FROM ORGM ORDER BY ORGMId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(OrgMId) FROM ORGM');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max orgmid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE org_members AUTO_INCREMENT = $max_id");
    }
}
