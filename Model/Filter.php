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

    /** @var string|null */
    private $queryParameter;

    /** @var string|null */
    private $field;

    /** @var string */
    private $type;

    /** @var boolean */
    private $required = false;

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
     * @return string | null
     */
    public function getQueryParameter(): ?string
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
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     * @return Filter
     */
    public function setField(?string $field): self
    {
        $this->field = $field;

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

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return Filter
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }
}
