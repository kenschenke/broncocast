<?php

namespace App\Tests\Model\AdminGroupsModel;

use App\Model\AdminGroupsModel;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetGroupsTest extends TestCase {
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
        $response = $model->GetGroups($orgId);
        $this->assertEquals([
            'Success' => false,
            'Error' => 'Administrative privileges required',
        ], $response);
    }

    public function testSuccessful() {
        $orgId = 5;

        $em = $this->setUpEntityManager();
        $adminChecker = $this->setUpAdminChecker();
        $requestStack = $this->setUpRequestStack();

        $adminChecker
            ->expects($this->once())
            ->method('IsAdminUser')
            ->with($this->equalTo($orgId))
            ->will($this->returnValue(true));

        $groups = [];
        $groupRecs = [];
        for ($i = 10; $i <= 15; $i++) {
            $groupName = "Group Name $i";
            $group = $this->getMockBuilder('App\Entity\UserGrps')
                ->disableOriginalConstructor()
                ->getMock();
            $group->method('getId')->will($this->returnValue($i));
            $group->method('getGrpName')->will($this->returnValue($groupName));

            $groupRecs[] = $group;
            $groups[] = [
                'GroupId' => $i,
                'GroupName' => $groupName,
            ];
        }

        $groupsRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $groupsRepo
            ->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['orgId' => $orgId]))
            ->will($this->returnValue(new ArrayCollection($groupRecs)));

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('App:UserGrps'))
            ->will($this->returnValue($groupsRepo));

        $model = new AdminGroupsModel($em, $adminChecker, $requestStack);
        $response = $model->GetGroups($orgId);
        $this->assertEquals([
            'Success' => true,
            'Groups' => $groups
        ], $response);
    }
}