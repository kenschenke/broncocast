<?php

namespace App\Tests\Security;

use App\Security\PwdHelper;
use PHPUnit\Framework\TestCase;

class PwdHelperTest extends TestCase
{
    protected function setUpEntityManager()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }

    protected function setUpEncoder()
    {
        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $encoder;
    }

    public function setUpMessageUtil()
    {
        $messageUtil = $this->getMockBuilder('App\Util\MessageUtil')
            ->disableOriginalConstructor()
            ->getMock();

        return $messageUtil;
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid username or password
     */
    public function testMigratePasswordInvalidPassword()
    {
        $salt = 'Salt';
        $plainPwd = 'Plain Password';
        $legacyPassword = 'Legacy Password';

        $messageUtil = $this->setUpMessageUtil();
        $encoder = $this->setUpEncoder();
        $em = $this->setUpEntityManager();

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getSalt')
            ->will($this->returnValue($salt));
        $user->expects($this->once())
            ->method('getLegacyPassword')
            ->will($this->returnValue($legacyPassword));

        $pwdHelper = new PwdHelper($messageUtil, $encoder, $em);
        $pwdHelper->MigratePassword($user, $plainPwd);
    }

    public function testMigratePasswordSuccessful()
    {
        $salt = 'Salt';
        $plainPwd = 'Plain Password';
        $legacyPassword = sha1($plainPwd . $salt);
        $encodedPassword = 'Encoded Password';

        $messageUtil = $this->setUpMessageUtil();
        $encoder = $this->setUpEncoder();
        $em = $this->setUpEntityManager();

        $user = $this->getMockBuilder('App\Entity\Users')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getSalt')
            ->will($this->returnValue($salt));
        $user->expects($this->once())
            ->method('getLegacyPassword')
            ->will($this->returnValue($legacyPassword));
        $user->expects($this->once())
            ->method('setSalt')
            ->with($this->equalTo(''));
        $user->expects($this->once())
            ->method('setPassword')
            ->with($this->equalTo($encodedPassword));
        $user->expects($this->once())
            ->method('setLegacyPassword')
            ->with($this->equalTo(''));

        $encoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->equalTo($user), $this->equalTo($plainPwd))
            ->will($this->returnValue($encodedPassword));

        $em->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($user));
        $em->expects($this->once())
            ->method('flush');

        $pwdHelper = new PwdHelper($messageUtil, $encoder, $em);
        $pwdHelper->MigratePassword($user, $plainPwd);
    }
}
