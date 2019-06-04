<?php

namespace Recognize\DwhApplication\Service;



use Recognize\DwhApplication\Loader\EntityLoaderInterface;
use Recognize\DwhApplication\Schema\EntityMapping;
use Recognize\DwhApplication\Schema\FieldMapping;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class ValidationService
 * @package Recognize\DwhApplication\Service
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class ValidationService
{
    /** @var array|EntityLoaderInterface[] */
    private $loaders;

    /** @var PropertyAccessor */
    private $propertyAccessor;

    /**
     * ValidateCommand constructor.
     * @param array|EntityLoaderInterface[] $loaders
     */
    public function __construct($loaders)
    {
        $this->loaders = $loaders;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @return array
     */
    public function validate(): array {
        $errors = [];

        /** @var EntityLoaderInterface $loader */
        foreach ($this->loaders as $loader) {
            $this->validateMapping($loader->getEntityMapping(), $errors);
        }

        return $errors;
    }

    /**
     * @param EntityMapping $mapping
     * @param array $errors
     */
    private function validateMapping(EntityMapping $mapping, array &$errors) {
        $entityClass = $mapping->getClass();
        $instance = new $entityClass;

        /** @var FieldMapping $field */
        foreach ($mapping->getFields() as $field) {
            $this->validateField($instance, $field, $errors);
        }
    }

    /**
     * @param $instance
     * @param FieldMapping $field
     * @param array $errors
     */
    private function validateField($instance, FieldMapping $field, array &$errors) {
        if (!$this->propertyAccessor->isReadable($instance, $field->getName())) {
            $errors[] = sprintf('Unable to read field %s for entity %s', $field->getName(), get_class($instance));
            return;
        }

        $type = $field->getType();
        if (in_array($type, [FieldMapping::TYPE_ARRAY, FieldMapping::TYPE_ENTITY])) {
            $entryMapping = $field->getEntryMapping();

            if ($entryMapping instanceof EntityMapping) {
                $this->validateMapping($entryMapping, $errors);
            } else if ($entryMapping instanceof FieldMapping) {
                $parent = $entryMapping->getParent();
                $instance = new $parent;

                $this->validateField($instance, $entryMapping, $errors);
            }
        }
    }
}
