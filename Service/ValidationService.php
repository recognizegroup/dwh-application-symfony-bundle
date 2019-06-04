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
            if (!$this->propertyAccessor->isReadable($instance, $field->getName())) {
                $errors[] = sprintf('Unable to read field %s for entity %s', $field->getName(), $entityClass);
                continue;
            }

            $type = $field->getType();
            if (in_array($type, [FieldMapping::TYPE_ARRAY, FieldMapping::TYPE_ENTITY])) {
                $entryMapping = $field->getEntryMapping();

                $this->validateMapping($entryMapping, $errors);
            }
        }
    }
}
