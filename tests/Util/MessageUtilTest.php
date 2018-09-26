<?php

namespace App\Tests\Util;

use App\Util\MessageUtil;
use PHPUnit\Framework\TestCase;

class MessageUtilTest extends TestCase
{
    public function testIsEmailContact()
    {
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil = new MessageUtil($em);

        $this->assertFalse($MessageUtil->IsEmail('email@example.c'));
        $this->assertTrue($MessageUtil->IsEmail('email@example.com'));
        $this->assertFalse($MessageUtil->IsEmail('8165551212'));
    }

    public function testIsPhoneContact()
    {
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $MessageUtil = new MessageUtil($em);

        $this->assertFalse($MessageUtil->IsPhone('+1555-1212'));
        $this->assertTrue($MessageUtil->IsPhone('8165551212'));
        $this->assertFalse($MessageUtil->IsPhone('email@example.com'));
    }
}
