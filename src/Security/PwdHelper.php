<?php

namespace App\Security;

use App\Entity\Users;
use App\Util\MessageUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PwdHelper
{
    private $messageUtil;
    private $encoder;
    private $em;

    public function __construct(MessageUtil $messageUtil, UserPasswordEncoderInterface $encoder,
                                EntityManagerInterface $em)
    {
        $this->messageUtil = $messageUtil;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * This is a private utility function that returns a DateTime
     * object 7 days from the current time.
     *
     * @return \DateTime
     */
    protected function GetResetExpire()
    {
        $resetExpire = new \DateTime();
        $resetExpire->add(new \DateInterval('P7D'));  // seven days

        return $resetExpire;
    }

    /**
     * This is a private utility function that generates a password reset
     * string and stores the string in the passed User record along with
     * an expiration.
     *
     * @param Users $user
     */
    protected function MakeResetStr(Users $user)
    {
        $resetSeed = $user->getFullname() . date('Y-m-d H:i:s');
        for ($i = 1; $i < 32; $i++) {
            $resetSeed .= chr(rand(65, 65 + 25));
        }

        $user->setResetStr(hash("sha256", $resetSeed));
        $user->setResetExpire($this->GetResetExpire());
    }

    public function MigratePassword(Users $user, $plainpwd)
    {
        // Encode the user-supplied password with SHA-1
        $sha1 = sha1($plainpwd . $user->getSalt());
        // Make sure it matches the user record
        if ($sha1 !== $user->getLegacyPassword()) {
            throw new AuthenticationException('Invalid username or password');
        }
        // Encode the password using bcrypt
        $user->setSalt('');
        $password = $this->encoder->encodePassword($user, $plainpwd);
        // Store that in the user record
        $user->setPassword($password);
        $user->setLegacyPassword('');
        $this->em->persist($user);
        $this->em->flush();
    }

    public function SaveUserPassword(Users $user, $plainpwd)
    {
        $encoded_password = $this->encoder->encodePassword($user, $plainpwd);
        $user->setPassword($encoded_password);
    }

    public function SendResetEmail(Users $user, $email)
    {
        $resetExpire = $user->getResetExpire();
        $resetStr = $user->getResetStr();

        if (!is_null($resetExpire) && !is_null($resetStr)) {
            $resetStr = $user->getResetStr();
            $user->setResetExpire($this->GetResetExpire());
        } else {
            $this->MakeResetStr($user);
            $resetStr = $user->getResetStr();
        }

        $expires = $user->getResetExpire()->format('m/d/Y H:i:s');

        $body = "This message is in response to a request to recover your BroncoCast user account.\n\n";
        $body .= "Please click on the following link to recover your account:\n\n";
        $body .= "https://www.broncocast.org/auth/recover/" . urlencode($resetStr) . "\n\n";
        $body .= "Note: the above link expires at $expires central time.\n\n";
        $body .= "Tip: If the above link does not work, try copying and pasting the link into your browser.\n";

        $this->messageUtil->SendEmail([$email], $body, null, null, null);
    }
}
