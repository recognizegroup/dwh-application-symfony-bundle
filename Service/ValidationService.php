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

    /** @var DocumentationService */
    private $documentationService;

    /**
     * ValidateCommand constructor.
     * @param array|EntityLoaderInterface[] $loaders
     * @param DocumentationService $documentationService
     */
    public function __construct($loaders, DocumentationService $documentationService)
    {
        $this->loaders = $loaders;
        $this->propertyAccessor = new PropertyAccessor();
        $this->documentationService = $documentationService;
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

        $this->validateDocumentation($this->loaders, $errors);

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
        $hasCustomClosure = isset($field->getOptions()['value']);

        if (!$hasCustomClosure && !$this->propertyAccessor->isReadable($instance, $field->getName())) {
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

    /**
     * @param \Iterator|EntityLoaderInterface[] $loaders
     * @param array $errors
     */
    private function validateDocumentation($loaders, array &$errors)
    {
        try {
            $this->documentationService->generate(iterator_to_array($loaders));
        } catch (\Throwable $error) {
            $errors[] = sprintf('Could not generate documentation: %s', $error->getMessage());
        }
    }
}
