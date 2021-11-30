<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use PHPUnit\Framework\TestCase;

class RemoveGroupTest extends TestCase {
    protected function setUpAdminChecker()
    {
        $adminChecker = $this->getMockBuilder('App\Util\AdminChecker')
            ->disableOriginalConstructor()
            ->getMock();

        return $adminChecker;
    }

    protected function setUpEntityManager()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }

    protected function setUpRequestStack()
    {
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        return $requestStack;
    }

    public function testMissingGroupRecord() {
        $groupId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue(null));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroup($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Group record not found',
        ], $response);
    }

    public function testNonAdminUser() {
        $orgId = 5;
        $groupId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group->method('getOrgId')->will($this->returnValue($orgId));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroup($groupId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testSuccessful() {
        $orgId = 5;
        $groupId = 6;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $group = $this->getMockBuilder('App\Entity\UserGrps')
            ->disableOriginalConstructor()
            ->getMock();
        $group->method('getOrgId')->will($this->returnValue($orgId));

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($groupId))
            ->will($this->returnValue($group));

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo("DELETE FROM grp_members WHERE grp_id = ?"), $this->equalTo([$groupId]));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));
        $em
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));
        $em
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($group));
        $em->expects($this->once())->method('flush');

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->RemoveGroup($groupId);
        $this->assertEquals(['Success' => true], $response);
    }
}
