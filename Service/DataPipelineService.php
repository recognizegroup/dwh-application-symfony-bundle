<?php


namespace Recognize\DwhApplication\Service;

use Recognize\DwhApplication\Model\DataTransformationInterface;

/**
 * Class DataPipelineService
 * @package Recognize\DwhApplication\Service
 */
class DataPipelineService
{
    /**
     * @param mixed $input
     * @param DataTransformationInterface[] $transformations
     * @return mixed
     */
    public function apply(mixed $input, array $transformations) {
        $output = $input;

        /** @var DataTransformationInterface $transformation */
        foreach ($transformations as $transformation) {
            $output = $transformation->transform($output);
        }

        return $output;
    }
}
