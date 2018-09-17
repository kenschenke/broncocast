<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyAllCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('bcast:copy:all')
            ->setDescription('Copy all tables');
    }

    private function copyTable($table, InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->find("bcast:copy:$table")->run($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->copyTable('carriers', $input, $output);
        $this->copyTable('orgs', $input, $output);
        $this->copyTable('groups', $input, $output);
        $this->copyTable('users', $input, $output);
        $this->copyTable('grpmembers', $input, $output);
        $this->copyTable('contacts', $input, $output);
        $this->copyTable('orgmembers', $input, $output);
        $this->copyTable('broadcasts', $input, $output);
        $this->copyTable('attachments', $input, $output);
        $this->copyTable('recipients', $input, $output);
    }
}
