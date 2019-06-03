<?php

namespace Recognize\DwhApplication\Schema;


/**
 * Class Schema
 * @package Recognize\DwhApplication\Schema
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class EntityMapping
{
    /** @var array|FieldMapping[] */
    private $fields = [];

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
