<?php
namespace Recognize\DwhApplication\Util;


use Doctrine\ORM\Mapping as ORM;

/**
 * Trait TenantUUID
 * @package Recognize\DwhApplication\Util
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
trait TenantUUID
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $tenantUuid;

    /**
     * @return string|null
     */
    public function getTenantUuid(): ?string
    {
        return $this->tenantUuid;
    }

    /**
     * @param string|null $tenantUuid
     */
    public function setTenantUuid(?string $tenantUuid): void
    {
        $this->tenantUuid = $tenantUuid;
    }
}
