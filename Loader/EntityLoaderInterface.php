<?php

namespace Recognize\DwhApplication\Loader;

use Doctrine\ORM\QueryBuilder;
use Recognize\DwhApplication\Model\DetailOptions;
use Recognize\DwhApplication\Model\Filter;
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
     * @return EntityMapping
     */
    function getEntityMapping(): EntityMapping;
}
