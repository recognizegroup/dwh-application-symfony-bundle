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

    /** @var Filter[] */
    private $filers;

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
     * @return Filter[]
     */
    public function getFilers(): array
    {
        return $this->filers;
    }

    /**
     * @param Filter[] $filers
     */
    public function setFilers(array $filers): void
    {
        $this->filers = $filers;
    }
}
