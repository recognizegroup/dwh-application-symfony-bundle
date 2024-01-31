<?php

namespace Recognize\DwhApplication\Security;


use Recognize\DwhApplication\Model\DwhUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class DwhUserProvider
 * @package Recognize\DwhApplication\Security
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class DwhUserProvider implements UserProviderInterface
{
    /** @var string */
    private string $encryptedToken;

    /**
     * DwhUserProvider constructor.
     * @param string $encryptedToken
     */
    public function __construct(string $encryptedToken)
    {
        $this->encryptedToken = $encryptedToken;
    }

    /**
     * @param string $username
     * @return UserInterface
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        $user = new DwhUser();
        $user->setUsername($username);
        $user->setPassword($this->encryptedToken);

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return UserInterface|void
     * @throws \Exception
     */
    public function refreshUser(UserInterface $user)
    {
        throw new \LogicException('Method should not be called');
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass(string $class): bool
    {
        return DwhUser::class === $class;
    }

    /**
     * @param string $identifier
     * @return UserInterface
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }
}
