<?php
namespace Recognize\DwhApplication\Model;

use Recognize\DwhApplication\Security\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class DwhUser
 * @package ${NAMESPACE}
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class DwhUser implements UserInterface
{
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return [Role::ROLE_DWH_BRIDGE];
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function eraseCredentials()
    {
        return;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
