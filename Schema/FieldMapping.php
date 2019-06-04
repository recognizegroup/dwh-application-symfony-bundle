<?php

namespace Recognize\DwhApplication\Schema;


use Recognize\DwhApplication\Util\NameHelper;

/**
 * Class Field
 * @package Recognize\DwhApplication\Schema
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class FieldMapping
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_STRING = 'string';
    public const TYPE_ENTITY = 'entity';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATE_TIME = 'date-time';
    public const TYPE_EMAIL = 'email';

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var array */
    private $options;

    /**
     * FieldMapping constructor.
     * @param string $name
     * @param string $type
     * @param array $options
     */
    public function __construct(string $name, string $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getSerializedName(): string {
        return NameHelper::camelToSnake($this->options['map_to'] ?? $this->name);
    }

    /**
     * @return string|null
     */
    public function getArrayType(): ?string {
        return $this->options['array_type'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string {
        return $this->options['parent'] ?? null;
    }

    /**
     * @return EntityMapping|FieldMapping|null
     */
    public function getEntryMapping() {
        return $this->options['entry_mapping'] ?? null;
    }
}
