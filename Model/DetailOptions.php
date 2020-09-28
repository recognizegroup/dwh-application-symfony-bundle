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

    /** @var RequestFilter[] */
    private $filters;

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

    /**
     * @return RequestFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param RequestFilter[] $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }
}
