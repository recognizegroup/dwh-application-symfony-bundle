<?php

namespace Recognize\DwhApplication\Model;

/**
 * Class Filter
 * @package Recognize\DwhApplication\Model
 */
class Filter
{
    public const OPERATOR_GREATER_THAN = 'gt';
    public const OPERATOR_GREATER_OR_EQUAL_THAN = 'geq';
    public const OPERATOR_LESS_THAN = 'lt';
    public const OPERATOR_LESS_OR_EQUAL_THAN = 'leq';
    public const OPERATOR_EQUAL = 'eq';

    public const OPERATORS_ALL = [
        self::OPERATOR_GREATER_THAN,
        self::OPERATOR_GREATER_OR_EQUAL_THAN,
        self::OPERATOR_LESS_THAN,
        self::OPERATOR_LESS_OR_EQUAL_THAN,
        self::OPERATOR_EQUAL,
    ];

    /** @var string[] */
    private $operators = [];

    /** @var string */
    private $queryParameter;

    /** @var string */
    private $field;

    /** @var string */
    private $type;

    /** @var mixed|null */
    private $value;

    /**
     * @return string[]
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * @param string[]|string $operators
     * @return Filter
     */
    public function setOperators($operators): self
    {
        $this->operators = is_array($operators) ? $operators : [$operators];

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryParameter(): string
    {
        return $this->queryParameter ?? $this->field;
    }

    /**
     * @param string $queryParameter
     * @return Filter
     */
    public function setQueryParameter(string $queryParameter): self
    {
        $this->queryParameter = $queryParameter;

        return $this;
    }

    /**`
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return Filter
     */
    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
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
     * @return Filter
     */
    public function setValue($value): self
    {
        $this->value = (string) $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Filter
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
