<?php

namespace Recognize\DwhApplication\Model;


/**
 * Class BaseOptions
 * @package Recognize\DwhApplication\Model
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class BaseOptions
{
    /** @var string */
    private $tenant;

    /**
     * @return string
     */
    public function getTenant(): string
    {
        return $this->tenant;
    }

    /**
     * @param string $tenant
     */
    public function setTenant(string $tenant): void
    {
        $this->tenant = $tenant;
    }
}
