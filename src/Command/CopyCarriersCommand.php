<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyCarriersCommand extends Command
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
            ->setName('bcast:copy:carriers')
            ->setDescription('Copy CAR table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy CAR table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO carriers(id, name, gateway, created, updated) ' .
            'SELECT CarId, CarName, Gateway, Created, Updated FROM CAR ORDER BY CarId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(CarId) FROM CAR');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max carid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE carriers AUTO_INCREMENT = $max_id");
    }
}
