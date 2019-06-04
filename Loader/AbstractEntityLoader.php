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
     * @param ListOptions $listOptions
     * @return ProtocolResponse
     * @throws NonUniqueResultException
     */
    public function fetchList(ListOptions $listOptions): ProtocolResponse
    {
        $queryBuilder = $this->createQueryBuilder($listOptions);

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
     * @param array $results
     * @return array
     */
    private function mapList(array $results): array {
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
    private function mapEntity($entity, EntityMapping $mapping): array {
        $result = [];

        /** @var FieldMapping $field */
        foreach ($mapping->getFields() as $field) {
            $serializedName = $field->getSerializedName();

            $result[$serializedName] = $this->mapField($entity, $field);
        }

        return $result;
    }

    private function mapField($entity, FieldMapping $field) {
        $name = $field->getName();
        $type = $field->getType();

        if (!$this->propertyAccessor->isReadable($entity, $name)) {
            throw new \RuntimeException(sprintf('Field with name %s is not readable on entity %s', $name, get_class($entity)));
        }

        $value = $this->propertyAccessor->getValue($entity, $name);

        if (in_array($type, [FieldMapping::TYPE_ENTITY, FieldMapping::TYPE_ARRAY])) {
            $arrayType = $field->getArrayType();

            if ($value === null) {
                return $arrayType !== null ? [] : null;
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
                    return $value ?? [];
                }

                foreach ($value as $child) {
                    if ($mapping instanceof EntityMapping) {
                        $list[] = $this->mapEntity($child, $mapping);
                    } else {
                        $list[] = $this->mapField($child, $mapping);
                    }
                }

                return $list;
            } else {
                return $mapping instanceof EntityMapping ? $this->mapEntity($value, $mapping) : $this->mapField($value, $mapping);
            }
        } else {
            return $value;
        }
    }

    /**
     * @param BaseOptions $options
     * @return QueryBuilder
     */
    private function createQueryBuilder(BaseOptions $options): QueryBuilder {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($this->getEntityType());
        $name = self::ENTITY_ALIAS;

        $queryBuilder = $repository->createQueryBuilder($name);

        $this->applyTenant($queryBuilder, $name, $options->getTenant());

        return $queryBuilder;
    }
}
