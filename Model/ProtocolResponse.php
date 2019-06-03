<?php

namespace Recognize\DwhApplication\Model;


/**
 * Class ProtocolResponse
 * @package Recognize\DwhApplication\Model
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class ProtocolResponse
{
    /** @var array */
    private $metadata = [];

    /** @var mixed */
    private $body;

    /**
     * ProtocolResponse constructor.
     * @param array $metadata
     * @param mixed $body
     */
    public function __construct(array $metadata, $body)
    {
        $this->metadata = $metadata;
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

}
