<?php

namespace App\Tests\Util;

use App\Util\Purge;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class PurgeBroadcastsHelper extends Purge
{
    public $OrgIds = [];
    public $MaxAges = [];

    protected function PurgeBroadcastsForOrg($OrgId, $MaxAge)
    {
        $this->OrgIds[] = $OrgId;
        $this->MaxAges[] = $MaxAge;
    }
}

class PurgeAttachmentsHelper extends Purge
{
    public function callPurgeAttachmentsForOrg($OrgId, $MaxAge)
    {
        $this->PurgeAttachmentsForOrg($OrgId, $MaxAge);
    }
}

class PurgeTest extends TestCase
{
    private $root;
    private $attachDir;

    protected function setUp()
    {
        $this->root = vfsStream::setup('home');
        $this->attachDir = $this->root->url() . '/attachments';
        mkdir($this->attachDir);
        chmod($this->attachDir, 0777);

        putenv("BRONCOCAST_ATTACHMENTS_DIR={$this->attachDir}");
    }

    public function testPurgeBroadcasts()
    {
        $MockData = [
            ['OrgId' => 1, 'MaxAge' => 30],
            ['OrgId' => 2, 'MaxAge' => 0],
            ['OrgId' => 3, 'MaxAge' => 60],
        ];
        $ExpectedOrgIds = [];
        $ExpectedMaxAges = [];

        $Orgs = [];
        foreach ($MockData as $data) {
            $Org = $this->getMockBuilder('App\Entity\Orgs')->getMock();
            $Org->method('getId')->will($this->returnValue($data['OrgId']));
            $Org->method('getMaxBrcAge')->will($this->returnValue($data['MaxAge']));
            $Orgs[] = $Org;

            if ($data['MaxAge']) {
                $ExpectedOrgIds[] = $data['OrgId'];
                $ExpectedMaxAges[] = $data['MaxAge'];
            }
        }

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($Orgs));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($repo));

        $purge = new PurgeBroadcastsHelper($em);
        $purge->PurgeBroadcasts();

        $this->assertSame($ExpectedOrgIds, $purge->OrgIds);
        $this->assertSame($ExpectedMaxAges, $purge->MaxAges);
    }

    public function testPurgeAttachmentsForOrg()
    {
        $OrgId = 5;
        $MaxAge = 30;

        // Set up the select result rows and attachment files
        for ($i = 1; $i <= 5; $i++) {
            $fh = fopen("{$this->attachDir}/attach${i}", 'w');
            fwrite($fh, "Attachment {$i}\n");
            fclose($fh);
        }

        $stmt = $this->getMockBuilder('Doctrine\DBAL\Driver\Statement')
            ->disableOriginalConstructor()
            ->getMock();
        $stmt->expects($this->at(0))
            ->method('fetch')
            ->with($this->equalTo(\PDO::FETCH_NUM))
            ->will($this->returnValue(["attach1"]));
        $stmt->expects($this->at(1))
            ->method('fetch')
            ->with($this->equalTo(\PDO::FETCH_NUM))
            ->will($this->returnValue(["attach2"]));

        $selectQuery = 'SELECT local_name FROM attachments WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId ' .
            'AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY))';
        $deleteQuery = 'DELETE FROM attachments WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId ' .
            'AND created < DATE_SUB(CURDATE(),INTERVAL :Age DAY))';
        $queryMap = [
            [$selectQuery, ['OrgId' => $OrgId, 'Age' => $MaxAge], [], null, $stmt],
            [$deleteQuery, [], [], null, null]
        ];
        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $conn->method('executeQuery')->will($this->returnValueMap($queryMap));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method('getConnection')->will($this->returnValue($conn));

        $purge = new PurgeAttachmentsHelper($em);
        $purge->callPurgeAttachmentsForOrg($OrgId, $MaxAge);

        $this->assertFalse(file_exists("{$this->attachDir}/attach1"));
        $this->assertFalse(file_exists("{$this->attachDir}/attach2"));
        $this->assertTrue(file_exists("{$this->attachDir}/attach3"));
        $this->assertTrue(file_exists("{$this->attachDir}/attach4"));
        $this->assertTrue(file_exists("{$this->attachDir}/attach5"));
    }
}
