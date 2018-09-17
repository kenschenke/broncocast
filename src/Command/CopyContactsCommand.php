<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyContactsCommand extends Command
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
            ->setName('bcast:copy:contacts')
            ->setDescription('Copy USRC table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy USRC table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO contacts(id, user_id, contact, carrier_id, created, updated) ' .
            'SELECT UsrCId, UsrId, Contact, CarId, Created, Updated FROM USRC ORDER BY UsrCId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(UsrCId) FROM USRC');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max usrcid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE contacts AUTO_INCREMENT = $max_id");
    }
}
