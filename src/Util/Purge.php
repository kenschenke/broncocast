<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Purge
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function PurgeBroadcasts()
    {
        $Orgs = $this->em->getRepository('App:Orgs')->findAll();
        foreach ($Orgs as $Org) {
            $MaxAge = $Org->getMaxBrcAge();
            if (!$MaxAge) {
                continue;
            }

            $this->PurgeBroadcastsForOrg($Org->getId(), $MaxAge);
        }
    }

    protected function PurgeAttachmentsForOrg($OrgId, $MaxAge)
    {
        $stmt = $this->em->getConnection()->executeQuery(
            'SELECT local_name FROM attachments WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId ' .
            'AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY))',
            ['OrgId' => $OrgId, 'Age' => $MaxAge]
        );

        $dir = getenv('BRONCOCAST_ATTACHMENTS_DIR');
        while (($row = $stmt->fetch(\PDO::FETCH_NUM))) {
            $filename = "$dir/{$row[0]}";
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        $this->em->getConnection()->executeQuery(
            'DELETE FROM attachments WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId ' .
            'AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY))',
            ['OrgId' => $OrgId, 'Age' => $MaxAge]
        );
    }

    protected function PurgeBroadcastsForOrg($OrgId, $MaxAge)
    {
        $this->em->getConnection()->executeQuery(
            'DELETE FROM recipients WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId ' .
            'AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY))',
            ['OrgId' => $OrgId, 'Age' => $MaxAge]
        );

        $this->PurgeAttachmentsForOrg($OrgId, $MaxAge);

        $this->em->getConnection()->executeQuery(
            'DELETE FROM broadcasts WHERE ' .
            'org_id = :OrgId AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY)',
            ['OrgId' => $OrgId, 'Age' => $MaxAge]
        );
    }

    public function PurgeOrphanAttachments()
    {
        $attachdir = getenv('BRONCOCAST_ATTACHMENTS_DIR');
        $attachrepo = $this->em->getRepository('App:Attachments');
        $dir = opendir($attachdir);
        if ($dir !== false) {
            while (($entry = readdir($dir)) !== false) {
                $fullpath = "$attachdir/$entry";
                if (is_dir($fullpath)) {
                    continue;
                }

                $attachment = $attachrepo->findOneBy(['localName' => $entry]);
                if (is_null($attachment)) {
                    unlink($fullpath);
                }
            }

            closedir($dir);
        }
    }
}
