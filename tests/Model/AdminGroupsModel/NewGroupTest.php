<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use PHPUnit\Framework\TestCase;

class NewGroupTest extends TestCase {
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

    public function testNonAdminUser() {
        $orgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(false));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->NewGroup($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testMissingOrgRecord() {
        $orgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(null));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->NewGroup($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Organization Record Not Found',
        ], $response);
    }

    public function testMissingNameParameter() {
        $orgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $org = $this->getMockBuilder('App\Entity\Orgs')
            ->disableOriginalConstructor()
            ->getMock();

        $orgsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgsRepo
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue($org));

        $params = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $params
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('Name'))
            ->will($this->returnValue(false));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->request = $params;

        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:Orgs'))
            ->will($this->returnValue($orgsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->NewGroup($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Name parameter missing',
        ], $response);
    }
}

