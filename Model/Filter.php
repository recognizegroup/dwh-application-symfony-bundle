<?php

namespace Recognize\DwhApplication\Model;

/**
 * Class Filter
 * @package Recognize\DwhApplication\Model
 */
class Filter
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string|null
     */
    private $value;

    /**
     * Filter constructor.
     * @param string $operator
     * @param string $field
     */
    public function __construct(string $operator, string $field)
    {
        $this->operator = $operator;
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField(string $field): void
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = (string) $value;
    }
}
