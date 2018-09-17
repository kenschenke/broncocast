<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyUsersCommand extends Command
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
            ->setName('bcast:copy:users')
            ->setDescription('Copy USR table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy USR table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO users(id, password, legacy_password, salt, is_active, ' .
            'fullname, single_msg, created, updated) ' .
            'SELECT UsrId, \'\', Password, Salt, true, UsrName, SingleMsg, Created, Updated FROM USR ORDER BY UsrId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(UsrId) FROM USR');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max usrid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE users AUTO_INCREMENT = $max_id");
    }
}
