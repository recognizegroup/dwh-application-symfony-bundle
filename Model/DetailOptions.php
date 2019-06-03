<?php

namespace Recognize\DwhApplication\Model;

/**
 * Class RequestOptions
 * @package Recognize\DwhApplication\Model
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class DetailOptions extends BaseOptions
{
    /** @var string */
    private $identifier;

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
