<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class MigrationAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoder;
    private $em;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException('Invalid username or password');
        }

        // Check to see if the password needs to be migrated
        $password = $user->getPassword();
        if (empty($password)) {
            // Encode the user-supplied password with SHA-1
            $sha1 = sha1($token->getCredentials() . $user->getSalt());
            // Make sure it matches the user record
            if ($sha1 !== $user->getLegacyPassword()) {
                throw new AuthenticationException('Invalid username or password');
            }
            // Encode the password using bcrypt
            $user->setSalt('');
            $password = $this->encoder->encodePassword($user, $token->getCredentials());
            // Store that in the user record
            $user->setPassword($password);
            $user->setLegacyPassword('');
            $this->em->persist($user);
            $this->em->flush();
        }

        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());
        if ($passwordValid) {
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        throw new AuthenticationException('Invalid username or password');
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}
