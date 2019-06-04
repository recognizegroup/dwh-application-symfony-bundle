<?php

namespace Recognize\DwhApplication\Loader;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Recognize\DwhApplication\Model\BaseOptions;
use Recognize\DwhApplication\Model\DetailOptions;
use Recognize\DwhApplication\Model\Filter;
use Recognize\DwhApplication\Model\ListOptions;
use Recognize\DwhApplication\Model\ProtocolResponse;
use Recognize\DwhApplication\Schema\EntityMapping;
use Recognize\DwhApplication\Schema\FieldMapping;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class AbstractEntityLoader
 * @package Recognize\DwhApplication\Loader
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
abstract class AbstractEntityLoader implements EntityLoaderInterface
{
    private const ENTITY_ALIAS = 'entity';

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var PropertyAccessor */
    private $propertyAccessor;

    /** @var string */
    private $protocolVersion;

    /**
     * AbstractEntityLoader constructor.
     * @param ManagerRegistry $managerRegistry
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ManagerRegistry $managerRegistry, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $managerRegistry->getManager();
        $this->propertyAccessor = new PropertyAccessor();
        $this->protocolVersion = $parameterBag->get('recognize.dwh_application.protocol_version');
    }

    /**
     * @return array|Filter[]
     */
    abstract function getFilters(): array;


    /**
     * @param ListOptions $listOptions
     * @return ProtocolResponse
     * @throws NonUniqueResultException
     */
    public function fetchList(ListOptions $listOptions): ProtocolResponse
    {
        $queryBuilder = $this->createQueryBuilder($listOptions);

        $this->applyFilters($queryBuilder, $listOptions->getFilers());
        $queryBuilder->setMaxResults($listOptions->getLimit());
        $queryBuilder->setFirstResult(($listOptions->getPage() - 1) * $listOptions->getLimit());

        $query = $queryBuilder->getQuery();
        $results = $query->getResult();

        $countQueryBuilder = clone $queryBuilder;
        $countQueryBuilder->select(sprintf('COUNT(%s)', self::ENTITY_ALIAS));

        $total = (int) $countQueryBuilder->getQuery()
                    ->getSingleScalarResult();

        $mapped = $this->mapList($results);

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
        $mapped = $this->mapEntity($result, $mapping);

        return new ProtocolResponse(['protocol' => $this->protocolVersion], $mapped);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Filter $filter
     */
    private function applyFilter(QueryBuilder $queryBuilder, Filter $filter)
    {
        $queryBuilder->andWhere($filter->getField().' '.$filter->getOperator().' '.$filter->getValue());
    }

    /**
     * @param QueryBuilder   $queryBuilder
     * @param array|Filter[] $filters
     */
    private function applyFilters(QueryBuilder $queryBuilder, array $filters)
    {
        $availableFilters = array_map(function(Filter $filter) {
            return $filter->getField();
        }, $this->getFilters());

        foreach ($filters as $filter) {
            if(\in_array($filter->getField(), $availableFilters, true)) {
                $this->applyFilter($queryBuilder, $filter);
            }
        }
    }

    /**
     * @param array $results
     * @return array
     */
    private function mapList(array $results): array
    {
        $mapping = $this->getEntityMapping();

        return array_map(function ($entity) use ($mapping) {
            return $this->mapEntity($entity, $mapping);
        }, $results);
    }

    /**
     * @param $entity
     * @param EntityMapping $mapping
     * @return array
     */
    private function mapEntity($entity, EntityMapping $mapping): array
    {
        $result = [];

        /** @var FieldMapping $field */
        foreach ($mapping->getFields() as $field) {
            $name = $field->getName();
            $serializedName = $field->getSerializedName();
            $type = $field->getType();

            if (!$this->propertyAccessor->isReadable($entity, $name)) {
                throw new \RuntimeException(sprintf('Field with name %s is not readable on entity %s', $name, get_class($entity)));
            }

            $value = $this->propertyAccessor->getValue($entity, $name);

            if (in_array($type, [FieldMapping::TYPE_ENTITY, FieldMapping::TYPE_ARRAY])) {
                $mapping = $field->getEntryMapping();

                if (!$mapping instanceof EntityMapping) {
                    throw new LogicException(sprintf('Invalid entity mapping for collection at field %s', $name));
                }

                if ($type === FieldMapping::TYPE_ARRAY) {
                    $result[$serializedName] = [];

                    if (!is_iterable($value)) {
                        throw new LogicException(sprintf('Expected iterable for field %s', $name));
                    }

                    foreach ($value as $child) {
                        $result[$serializedName][] = $this->mapEntity($child, $mapping);
                    }
                } else {
                    $result[$serializedName] = $this->mapEntity($value, $mapping);
                }
            } else {
                $result[$serializedName] = $value;
            }
        }

        return $result;
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
