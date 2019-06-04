<?php

namespace Recognize\DwhApplication\Schema;


/**
 * Class Schema
 * @package Recognize\DwhApplication\Schema
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class EntityMapping
{
    /** @var string */
    private $class;

    /** @var array|FieldMapping[] */
    private $fields = [];

    /**
     * EntityMapping constructor.
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * @param FieldMapping $fieldMapping
     * @return EntityMapping
     */
    public function addField(FieldMapping $fieldMapping): self {
        $this->fields[] = $fieldMapping;

        return $this;
    }

    /**
     * @return array|FieldMapping[]
     */
    public function getFields()
    {
        return $this->fields;
    }

}
