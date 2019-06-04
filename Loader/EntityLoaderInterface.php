<?php

namespace Recognize\DwhApplication\Loader;

use Doctrine\ORM\QueryBuilder;
use Recognize\DwhApplication\Model\DetailOptions;
use Recognize\DwhApplication\Model\ListOptions;
use Recognize\DwhApplication\Model\ProtocolResponse;
use Recognize\DwhApplication\Schema\EntityMapping;

/**
 * Interface EntityLoaderInterface
 * @package Recognize\DwhApplication\Mapping
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
interface EntityLoaderInterface
{
    /**
     * @param ListOptions $listOptions
     * @return ProtocolResponse
     */
    function fetchList(ListOptions $listOptions): ProtocolResponse;

    /**
     * @param DetailOptions $detailOptions
     * @return ProtocolResponse
     */
    function fetchDetail(DetailOptions $detailOptions): ProtocolResponse;

    /**
     * Restricts access to resources to a specific tenant
     *
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $tenantUuid
     */
    function applyTenant(QueryBuilder $queryBuilder, string $alias, string $tenantUuid);

    /**
     * Loads a specific entity
     *
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $identifier
     */
    function applyIdentifier(QueryBuilder $queryBuilder, string $alias, string $identifier);

    /**
     * @return string
     */
    function getEntityType(): string;

    /**
     * Translates database result to schema. How it works:
     * - Every entity is mapped FROM the database to a JSON response
     * - Fields of an entity can be added by using a field mapping
     * - If you want to change the name that ends up in the serialization, you can use map_to
     * - Sometimes, you might want to serialize a nested entity. You can use an entity type, in combination with a
     *   entry_mapping, which can be:
     *      - a new entity mapping
     *      - a field mapping, if you want to serialize an entire entity based as one field (ex. User -> email)
     *        This does require a reference to the parent class, using parent, for validation purposes
     * - You can also serialize collections using the array type and the entry_mapping
     * - If you want to serialize an array of primitive types, you can use array_type
     *
     * @return EntityMapping
     */
    function getEntityMapping(): EntityMapping;
}
