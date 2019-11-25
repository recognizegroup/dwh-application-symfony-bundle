<?php
namespace Recognize\DwhApplication\Model;

/**
 * Interface DataTransformationInterface
 * @package Recognize\DwhApplication\Model
 */
interface DataTransformationInterface
{
    /**
     * Method that applies data transformations, after which a new output is generated
     *
     * @param mixed $input
     * @return mixed
     */
    public function transform($input);
}
