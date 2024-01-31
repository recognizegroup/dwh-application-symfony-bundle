<?php

namespace Recognize\DwhApplication\Loader;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use Recognize\DwhApplication\Model\BaseOptions;
use Recognize\DwhApplication\Model\DetailOptions;
use Recognize\DwhApplication\Model\Filter;
use Recognize\DwhApplication\Model\ListOptions;
use Recognize\DwhApplication\Model\ProtocolResponse;
use Recognize\DwhApplication\Model\RequestFilter;
use Recognize\DwhApplication\Schema\EntityMapping;
use Recognize\DwhApplication\Schema\FieldMapping;
use Recognize\DwhApplication\Service\DataPipelineService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class AbstractEntityLoader
 * @package Recognize\DwhApplication\Loader
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
abstract class AbstractEntityLoader implements EntityLoaderInterface
{
    protected const OPERATOR_MAPPING = [
        'gt' => '>',
        'lt' => '<',
        'leq' => '<=',
        'geq' => '>=',
        'eq' => '='
    ];

    private const ENTITY_ALIAS = 'entity';

    /** @var ObjectManager */
    protected $entityManager;

    /** @var PropertyAccessor */
    private $propertyAccessor;

    /** @var string */
    private $protocolVersion;

    /** @var DataPipelineService */
    private $dataPipelineService;

    /**
     * AbstractEntityLoader constructor.
     * @param ManagerRegistry $managerRegistry
     * @param ParameterBagInterface $parameterBag
     * @param DataPipelineService $dataPipelineService
     */
    public function __construct(ManagerRegistry $managerRegistry, ParameterBagInterface $parameterBag, DataPipelineService $dataPipelineService)
    {
        $this->entityManager = $managerRegistry->getManager();
        $this->propertyAccessor = new PropertyAccessor();
        $this->dataPipelineService = $dataPipelineService;
        $this->protocolVersion = $parameterBag->get('recognize.dwh_application.protocol_version');
    }

    /**
     * @param ListOptions $listOptions
     * @return ProtocolResponse
     * @throws NonUniqueResultException
     */
    public function fetchList(ListOptions $listOptions): ProtocolResponse
    {
        $queryBuilder = $this->createQueryBuilder($listOptions);

        $this->applyFilters($queryBuilder, $listOptions->getFilters());

        $countQueryBuilder = clone $queryBuilder;
        $countQueryBuilder->select(sprintf('COUNT(%s)', self::ENTITY_ALIAS));

        $total = (int) $countQueryBuilder->getQuery()
            ->getSingleScalarResult();

        $queryBuilder->setMaxResults($listOptions->getLimit());
        $queryBuilder->setFirstResult(($listOptions->getPage() - 1) * $listOptions->getLimit());

        $query = $queryBuilder->getQuery();
        $results = $query->getResult();

        $usedFilterTuples = $this->getAllowedFilters($listOptions->getFilters());

        $requestFilters = array_map(function ($tuple) { return $tuple[0]; }, $usedFilterTuples);

        $mapped = $this->mapList($results, $requestFilters);

        return new ProtocolResponse(['protocol' => $this->protocolVersion, 'page' => $listOptions->getPage(), 'limit' => $listOptions->getLimit(), 'total' => $total], $mapped);
    }

    /**
     * @param DetailOptions $detailOptions
     * @return ProtocolResponse
     * @throws NonUniqueResultException
     */
    public function fetchDetail(DetailOptions $detailOptions): ProtocolResponse
    {
        $queryBuilder = $this->createQueryBuilder($detailOptions);
        $queryBuilder->setMaxResults(1);

        $this->applyIdentifier($queryBuilder, self::ENTITY_ALIAS, $detailOptions->getIdentifier());

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        if ($result === null) {
            throw new NotFoundHttpException(sprintf('No entity found with identifier %s', $detailOptions->getIdentifier()));
        }

        $mapping = $this->getEntityMapping();

        $usedFilterTuples = $this->getAllowedFilters($detailOptions->getFilters());
        $requestFilters = array_map(function ($tuple) { return $tuple[0]; }, $usedFilterTuples);

        $mapped = $this->mapEntity($result, $mapping, $requestFilters);

        return new ProtocolResponse(['protocol' => $this->protocolVersion], $mapped);
    }

    /**
     * @param QueryBuilder   $queryBuilder
     * @param array|RequestFilter[] $filters
     */
    public function applyFilters(QueryBuilder $queryBuilder, array $filters)
    {
        $allowedFilters = $this->getAllowedFilters($filters);

        foreach ($allowedFilters as $tuple) {
            [$requestFilter, $definedFilter] = $tuple;

            $parameterName = sprintf('%s_%s', $definedFilter->getField(), $requestFilter->getOperator());
            $this->applyFilter($queryBuilder, $definedFilter, $requestFilter, $parameterName);
        }
    }

    /**
     * Returns an array of tuples that contain the request filter, and the defined filter
     *
     * @param array $filters
     * @return array
     */
    private function getAllowedFilters(array $filters): array {
        $availableFilters = $this->getFilters();
        $result = [];

        /**
         * @var int $index
         * @var RequestFilter $requestFilter
         */
        foreach ($filters as $index => $requestFilter) {
            $definedFilter = array_values(array_filter($availableFilters, function (Filter $filter) use ($requestFilter) {
                    return strtolower($filter->getQueryParameter()) === strtolower($requestFilter->getField());
                }))[0] ?? null;

            if ($definedFilter instanceof Filter) {
                $result[] = [$requestFilter, $definedFilter];
            }
        }

        return $result;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Filter $baseFilter
     * @param RequestFilter $filter
     * @param string $parameterName
     */
    private function applyFilter(QueryBuilder $queryBuilder, Filter $baseFilter, RequestFilter $filter, string $parameterName)
    {
        // filters without a field are ignored
        if ($baseFilter->getField() === null) {
            return;
        }

        $mappedOperator = self::OPERATOR_MAPPING[$filter->getOperator()] ?? null;

        if (!$mappedOperator) {
            throw new LogicException('Should not be possible to use this operator.');
        }

        $value = $filter->getValue();

        if ($baseFilter->getType() === FieldMapping::TYPE_DATE_TIME) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $exception) {
                throw new BadRequestHttpException('Could not create date time field.');
            }
        }

        $queryBuilder
            ->andWhere(sprintf('%s.%s %s :%s', self::ENTITY_ALIAS, $baseFilter->getField(), $mappedOperator, $parameterName))
            ->setParameter($parameterName, $value);
    }

    /**
     * @param array $results
     * @param array $usedFilters
     * @return array
     */
    private function mapList(array $results, array $usedFilters = []): array
    {
        $mapping = $this->getEntityMapping();

        return array_map(function ($entity) use ($mapping, $usedFilters) {
            return $this->mapEntity($entity, $mapping, $usedFilters);
        }, $results);
    }

    /**
     * @param $entity
     * @param EntityMapping $mapping
     * @param array $usedFilters
     * @return array
     */
    private function mapEntity($entity, EntityMapping $mapping, array $usedFilters = []): array
    {
        $result = [];

        $entity = $this->dataPipelineService->apply($entity, $mapping->getTransformations());

        /** @var FieldMapping $field */
        foreach ($mapping->getFields() as $field) {
            $serializedName = $field->getSerializedName();

            $result[$serializedName] = $this->mapField($entity, $field, $usedFilters);
        }

        return $result;
    }

    /**
     * @param $entity
     * @param FieldMapping $field
     * @param array $usedFilters
     * @return array|mixed|null
     */
    private function mapField($entity, FieldMapping $field, array $usedFilters = []) {
        $name = $field->getName();
        $type = $field->getType();

        $hasCustomClosure = isset($field->getOptions()['value']);
        if (!$hasCustomClosure && !$this->propertyAccessor->isReadable($entity, $name)) {
            throw new \RuntimeException(sprintf('Field with name %s is not readable on entity %s', $name, get_class($entity)));
        }

        $transformations = $field->getTransformations();
        $output = null;

        $unprocessed = $hasCustomClosure ? $field->getOptions()['value']($entity, $usedFilters) : $this->propertyAccessor->getValue($entity, $name);
        $value = $this->dataPipelineService->apply($unprocessed, $transformations);

        if (in_array($type, [FieldMapping::TYPE_ENTITY, FieldMapping::TYPE_ARRAY])) {
            $arrayType = $field->getArrayType();

            if ($type === FieldMapping::TYPE_ARRAY && $arrayType !== null) {
                $output = $value ?? [];
                goto doOutput;
            }

            if ($value === null) {
                $output = $arrayType !== null ? [] : null;

                goto doOutput;
            }

            $mapping = $field->getEntryMapping();

            if (!$mapping instanceof EntityMapping && !$mapping instanceof FieldMapping) {
                throw new LogicException(sprintf('Invalid entity mapping for collection at field %s', $name));
            }

            if ($type === FieldMapping::TYPE_ARRAY) {
                $list = [];

                if (!is_iterable($value)) {
                    throw new LogicException(sprintf('Expected iterable for field %s', $name));
                }

                if ($arrayType !== null) {
                    $output = $value;

                    goto doOutput;
                }

                foreach ($value as $child) {
                    if ($mapping instanceof EntityMapping) {
                        $list[] = $this->mapEntity($child, $mapping, $usedFilters);
                    } else {
                        $list[] = $this->mapField($child, $mapping, $usedFilters);
                    }
                }

                $output =  $list;

                goto doOutput;
            } else {
                $output = $mapping instanceof EntityMapping ? $this->mapEntity($value, $mapping, $usedFilters) : $this->mapField($value, $mapping, $usedFilters);

                goto doOutput;
            }
        } else {
            $output = $value;

            goto doOutput;
        }

        doOutput:
        return $output;
    }

    /**
     * @param BaseOptions $options
     * @return QueryBuilder
     */
    private function createQueryBuilder(BaseOptions $options): QueryBuilder
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($this->getEntityType());
        $name = self::ENTITY_ALIAS;

        $queryBuilder = $repository->createQueryBuilder($name);

        $this->applyTenant($queryBuilder, $name, $options->getTenant());

        return $queryBuilder;
    }
}
