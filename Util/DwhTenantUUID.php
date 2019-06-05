<?php
namespace Recognize\DwhApplication\Util;


use Doctrine\ORM\Mapping as ORM;

/**
 * Trait DwhTenantUUID
 * @package Ret Wesselink <b.wesselink@recognize.nl>
 */
trait DwhTenantUUID
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $dwhTenantUuid;

    /**
     * @return string|null
     */
    public function getDwhTenantUuid(): ?string
    {
        return $this->dwhTenantUuid;
    }

    /**
     * @param string|null $dwhTenantUuid
     */
    public function setDwhTenantUuid(?string $dwhTenantUuid): void
    {
        $this->dwhTenantUuid = $dwhTenantUuid;
    }
}
