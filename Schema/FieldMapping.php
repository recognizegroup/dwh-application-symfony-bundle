<?php

namespace Recognize\DwhApplication\Schema;


use Recognize\DwhApplication\Model\DataTransformationInterface;
use Recognize\DwhApplication\Util\NameHelper;

/**
 * Class Field
 * @package Recognize\DwhApplication\Schema
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class FieldMapping
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';
    public const TYPE_STRING = 'string';
    public const TYPE_ENTITY = 'entity';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATE_TIME = 'date-time';
    public const TYPE_EMAIL = 'email';

    /** @var string */
    private $name;

    /** @var string|null */
    private $type;

    /** @var array */
    private $options;

    /** @var array|DataTransformationInterface[] */
    private $transformations = [];

    /**
     * FieldMapping constructor.
     * @param string $name
     * @param string|null $type
     * @param array $options
     */
    public function __construct(string $name, ?string $type = null, array $options = [])
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
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
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

    /**
     * @return array|DataTransformationInterface[]
     */
    public function getTransformations()
    {
        return $this->transformations;
    }

    /**
     * @param array|DataTransformationInterface[] $transformations
     */
    public function setTransformations($transformations): void
    {
        $this->transformations = $transformations;
    }

    /**
     * @param DataTransformationInterface $transformation
     * @return $this
     */
    public function addTransformation(DataTransformationInterface $transformation): self {
        $this->transformations[] = $transformation;

        return $this;
    }
}
