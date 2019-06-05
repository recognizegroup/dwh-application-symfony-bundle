<?php

namespace Recognize\DwhApplication\Model;


/**
 * Class RequestOptions
 * @package Recognize\DwhApplication\Model
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class ListOptions extends BaseOptions
{
    /** @var int|null */
    private $page;

    /** @var int|null */
    private $limit;

    /** @var RequestFilter[] */
    private $filters;

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
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
