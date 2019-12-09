<?php

namespace Recognize\DwhApplication\Service;

use erasys\OpenApi\Spec\v3 as OASv3;
use Recognize\DwhApplication\Loader\EntityLoaderInterface;
use Recognize\DwhApplication\Model\Filter;
use Recognize\DwhApplication\Schema\EntityMapping;
use Recognize\DwhApplication\Schema\FieldMapping;
use Recognize\DwhApplication\Util\NameHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DocumentationService
 * @package Recognize\DwhApplication\Service
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class DocumentationService
{
    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $specificationVersion;

    /**
     * DocumentationService constructor.
     * @param RouterInterface $router
     * @param string $specificationVersion
     */
    public function __construct(RouterInterface $router, string $specificationVersion)
    {
        $this->router = $router;
        $this->specificationVersion = $specificationVersion;
    }

    /**
     * @param array $entityTypes
     * @return array
     */
    public function generate(array $entityTypes): array {
        $paths = [];
        $components = [];

        /**
         * @var string $type
         * @var EntityLoaderInterface $loader
         */
        foreach ($entityTypes as $type => $loader) {
            [$pluralName, $singularName] = NameHelper::splitPluralName(NameHelper::dashToCamel($type));

            $pluralSchemaPath = $this->createSchemaPath($pluralName);
            $singularSchemaPath = $this->createSchemaPath($singularName);

            $this->addSchema($singularName, $loader->getEntityMapping(), $components);
            $this->addArraySchema($pluralName, $singularSchemaPath, $components);

            $paths[sprintf('/%s', $type)] = $this->createListPathItem($type, $pluralSchemaPath, $loader->getFilters());
            $paths[sprintf('/%s/{id}', $type)] = $this->createDetailPathItem($type, $singularSchemaPath);
        }

        $document = new OASv3\Document(
            new OASv3\Info('Internal API', $this->specificationVersion, 'Used for internal bridging.'),
            $paths,
            '3.0.0',
            [
                'components' => new OASv3\Components([
                    'schemas' => $components,
                ]),
                'servers' => [
                    new OASv3\Server($this->router->generate('recognize_dwh_definition', [], UrlGeneratorInterface::ABSOLUTE_URL)),
                ],
            ]
        );

        return $document->toArray();
    }

    /**
     * @param string $type
     * @param string $schemaPath
     * @param Filter[] $filters
     * @return OASv3\PathItem
     */
    private function createListPathItem(string $type, string $schemaPath, array $filters = []): OASv3\PathItem {
        $parameters = [
            new OASv3\Parameter(
                'limit',
                'query',
                null,
                ['schema' => new OASv3\Schema(['type' => 'integer'])]
            ),
            new OASv3\Parameter(
                'page',
                'query',
                null,
                ['schema' => new OASv3\Schema(['type' => 'integer'])]
            ),
        ];

        /** @var Filter $filter */
        foreach ($filters as $filter) {
            $parameters = array_merge($parameters, $this->createParametersForFilter($filter));
        }

        $operation = new OASv3\Operation(
            [
                '200' => $this->createResponse(sprintf('List of %s', $type), $schemaPath),
            ],
            null,
            null,
            [
                'parameters' => $parameters,
            ]
        );

        return new OASv3\PathItem([
            'get' => $operation,
        ]);
    }

    /**
     * @param string $type
     * @param string $schemaPath
     * @return OASv3\PathItem
     */
    private function createDetailPathItem(string $type, string $schemaPath): OASv3\PathItem {
        $operation = new OASv3\Operation(
            [
                '200' => $this->createResponse(sprintf('Detail of %s', $type), $schemaPath),
            ],
            null,
            null,
            [
                'parameters' => [
                    new OASv3\Parameter(
                        'id',
                        'path',
                        null,
                        ['required' => true, 'schema' => new OASv3\Schema(['type' => 'integer'])]
                    ),
                ],
            ]
        );

        return new OASv3\PathItem([
            'get' => $operation,
        ]);
    }

    /**
     * @param string $name
     * @param string $schemaPath
     * @param array $components
     */
    private function addArraySchema(string $name, string $schemaPath, array &$components) {
        $components[$name] = $this->createArraySchema(new OASv3\Reference($schemaPath));
    }

    /**
     * @param mixed $itemType
     * @return OASv3\Schema
     */
    private function createArraySchema($itemType) {
        return new OASv3\Schema([
            'type' => 'array',
            'items' => $itemType,
        ]);
    }

    /**
     * @param string $description
     * @param string $schema
     * @return OASv3\Response
     */
    private function createResponse(string $description, string $schema): OASv3\Response {
        return new OASv3\Response(
            $description,
            [
                'application/json' => new OASv3\MediaType(
                    ['schema' => new OASv3\Reference($schema)]
                ),
            ]
        );
    }

    /**
     * @param string $name
     * @param EntityMapping $mapping
     * @param array $components
     */
    private function addSchema(string $name, EntityMapping $mapping, array &$components) {
        $properties = [];

        /** @var FieldMapping $field */
        foreach ($mapping->getFields() as $field) {
            $serializedName = $field->getSerializedName();
            $type = $field->getType();

            if (\in_array($type, [FieldMapping::TYPE_ARRAY, FieldMapping::TYPE_ENTITY], true)) {
                $schemaName = ucfirst($field->getName());

                $arrayType = $field->getArrayType();
                if ($arrayType !== null) {
                    $properties[$serializedName] = $this->createArraySchema($this->createField($arrayType));
                } else {
                    if ($type === FieldMapping::TYPE_ARRAY) {
                        [$pluralName, $schemaName] = NameHelper::splitPluralName($schemaName);
                    }

                    $entryMapping = $field->getEntryMapping();

                    $fieldOnly = true;
                    if ($entryMapping instanceof EntityMapping) {
                        $fieldOnly = false;
                        $this->addSchema($schemaName, $entryMapping, $components);
                    }

                    $schemaItem = $fieldOnly
                        ?
                        $this->createField($entryMapping->getArrayType() ?? $entryMapping->getType())
                        :
                        new OASv3\Reference($this->createSchemaPath($schemaName))
                    ;

                    if ($type === FieldMapping::TYPE_ARRAY) {
                        $properties[$serializedName] = $this->createArraySchema($schemaItem);
                    } else {
                        $properties[$serializedName] = $schemaItem;
                    }
                }
            } else {
                $properties[$serializedName] = $this->createField($field->getType());
            }
        }

        $components[$name] = new OASv3\Schema(['properties' => $properties]);
    }

    /**
     * @param string|null $type
     * @return OASv3\Schema
     */
    private function createField(?string $type): OASv3\Schema {
        if ($type === null) {
            return new OASv3\Schema(['description' => 'Mixed type.']);
        }

        $format = null;

        if ($type === FieldMapping::TYPE_DATE_TIME) {
            $type = FieldMapping::TYPE_STRING;
            $format = FieldMapping::TYPE_DATE_TIME;
        } else if ($type === FieldMapping::TYPE_EMAIL) {
            $type = FieldMapping::TYPE_STRING;
            $format = FieldMapping::TYPE_EMAIL;
        }

        if ($format === null) {
            return new OASv3\Schema(['type' => $type]);
        } else {
            return new OASv3\Schema(['type' => $type, 'format' => $format]);
        }
    }

    /**
     * @param Filter $filter
     * @return OASv3\Parameter[]
     */
    private function createParametersForFilter(Filter $filter): array {
        $result = [];

        foreach ($filter->getOperators() as $operator) {
            $name = sprintf('%s[%s]', $filter->getQueryParameter(), $operator);

            $operatorDescriptions = [
                Filter::OPERATOR_EQUAL => 'equal to',
                Filter::OPERATOR_GREATER_THAN => 'greater than',
                Filter::OPERATOR_GREATER_OR_EQUAL_THAN => 'greater than or equal to',
                Filter::OPERATOR_LESS_THAN => 'less than or equal to',
                Filter::OPERATOR_LESS_OR_EQUAL_THAN => 'less than or equal to',
            ];

            $operatorDescription = $operatorDescriptions[$operator] ?? null;

            if ($operatorDescription === null) {
                throw new \LogicException(sprintf('Unsupported operator %s', $operator));
            }

            $description = sprintf('Filter field %s %s where the value is of type %s', $filter->getQueryParameter(), $operatorDescription, $filter->getType());

            if ($filter->getType() === FieldMapping::TYPE_DATE_TIME) {
                $description .= ' (in format of ISO8601)';
            }

            $result[] = new OASv3\Parameter(
                $name,
                'query',
                $description,
                ['schema' => $this->createField($filter->getType())]
            );
        }

        return $result;
    }

    /**
     * @param string $name
     * @return string
     */
    private function createSchemaPath(string $name): string {
        return sprintf('#/components/schemas/%s', $name);;
    }
}
