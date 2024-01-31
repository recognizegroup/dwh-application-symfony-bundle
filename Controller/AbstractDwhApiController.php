<?php

namespace Recognize\DwhApplication\Controller;

use Recognize\DwhApplication\Loader\EntityLoaderInterface;
use Recognize\DwhApplication\Model\DetailOptions;
use Recognize\DwhApplication\Model\DwhUser;
use Recognize\DwhApplication\Model\Filter;
use Recognize\DwhApplication\Model\ListOptions;
use Recognize\DwhApplication\Model\RequestFilter;
use Recognize\DwhApplication\Service\DocumentationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class AbstractDwhApiController
 * @package Recognize\DwhApplication\Controller
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
abstract class AbstractDwhApiController extends AbstractController
{
    private const PAGE_PARAMETER = 'page';
    private const PAGE_DEFAULT_VALUE = 1;
    private const LIMIT_PARAMETER = 'limit';
    private const LIMIT_DEFAULT_VALUE = 25;
    private const LIMIT_MAX_VALUE = 50;

    /** @var EntityLoaderInterface[]|array */
    private $entityTypes = [];

    /**
     * @param Request $request
     * @param string $type
     * @return Response
     */
    #[Route("/{type}")]
    public function listAction(Request $request, string $type) {
        $this->checkEntityType($type);

        $options = $this->buildListOptions($request);
        $loader = $this->entityTypes[$type];

        $result = $loader->fetchList($options);

        return $this->serialize($result);
    }

    /**
     * @Route("/{type}/{id}")
     *
     * @param Request $request
     * @param string $type
     * @return Response
     */
    public function detailAction(Request $request, string $type) {
        $this->checkEntityType($type);

        $options = $this->buildDetailOptions($request);
        $loader = $this->entityTypes[$type];

        $result = $loader->fetchDetail($options);

        return $this->serialize($result);
    }

    /**
     * @Route("", name="recognize_dwh_definition")
     *
     * @param Request $request
     * @param DocumentationService $documentationService
     * @return Response
     */
    public function definitionAction(Request $request, DocumentationService $documentationService) {
        $documentation = $documentationService->generate($this->entityTypes);

        return $this->serialize($documentation);
    }

    /**
     * @param string $slug
     * @param EntityLoaderInterface $class
     */
    protected function registerEntityType(string $slug, EntityLoaderInterface $class) {
        $this->entityTypes[$slug] = $class;
    }

    /**
     * @param string $type
     */
    private function checkEntityType(string $type): void {
        if (!isset($this->entityTypes[$type])) {
            throw new NotFoundHttpException(sprintf('Requested entity type %s not registered.', $type));
        }
    }

    /**
     * @param Request $request
     * @return ListOptions
     */
    private function buildListOptions(Request $request): ListOptions {
        $page = $request->query->getInt(self::PAGE_PARAMETER, self::PAGE_DEFAULT_VALUE);
        $limit = $request->query->getInt(self::LIMIT_PARAMETER, self::LIMIT_DEFAULT_VALUE);
        $filters = $this->getFiltersFromRequest($request);

        if ($page <= 0 || $limit > self::LIMIT_MAX_VALUE || $limit < 0) {
            throw new BadRequestHttpException();
        }

        $options = new ListOptions();
        $options->setPage($page);
        $options->setLimit($limit);
        $options->setFilters($filters);

        /** @var DwhUser $user */
        $user = $this->getUser();
        $options->setTenant($user->getUuid());

        return $options;
    }

    /**
     * @param Request $request
     * @return DetailOptions
     */
    private function buildDetailOptions(Request $request): DetailOptions {
        $filters = $this->getFiltersFromRequest($request);

        $options = new DetailOptions();
        $options->setIdentifier($request->attributes->getInt('id'));

        /** @var DwhUser $user */
        $user = $this->getUser();
        $options->setTenant($user->getUuid());
        $options->setFilters($filters);

        return $options;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFiltersFromRequest(Request $request): array {
        $parameters = array_filter($request->query->all(), 'is_array');
        $filters = [];

        foreach ($parameters as $field => $operators) {
            foreach ($operators as $operatorKey => $value) {
                if (in_array($operatorKey, Filter::OPERATORS_ALL)) {
                    $filters[] = new RequestFilter($field, $operatorKey, $value);
                }
            }
        }

        return $filters;
    }

    /**
     * @param $data
     * @return Response
     */
    private function serialize($data): Response {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response($serializer->serialize($data, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
