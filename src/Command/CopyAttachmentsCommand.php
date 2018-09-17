<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyAttachmentsCommand extends Command
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
            ->setName('bcast:copy:attachments')
            ->setDescription('Copy BRCA table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Copy BRCA table');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery(
            'INSERT INTO attachments(id, broadcast_id, local_name, ' .
            'friendly_name, mime_type, created, updated) ' .
            'SELECT BrcAId, BrcId, LocalName, FriendlyName, MimeType, Created, Updated FROM BRCA ORDER BY BrcAId'
        );
        $rowCount = $stmt->rowCount();
        $output->writeln(" - $rowCount row" . ($rowCount===1?"":"s"));

        $stmt = $conn->executeQuery('SELECT max(BrcAId) FROM BRCA');
        if (!($id_row = $stmt->fetch(\PDO::FETCH_NUM)))
            throw new \Exception("Unable to fetch max brcaid");
        $max_id = $id_row[0] + 1;
        $conn->query("ALTER TABLE attachments AUTO_INCREMENT = $max_id");
    }
}
