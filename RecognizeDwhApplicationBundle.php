<?php
namespace Recognize\DwhApplication;

use Recognize\DwhApplication\DependencyInjection\DwhApplicationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class RecognizeDwhApplicationBundle
 * @package Recognize\DwhApplication
 */
class RecognizeDwhApplicationBundle extends Bundle
{
    /**
     * @return DwhApplicationExtension|ExtensionInterface|null
     */
    public function getContainerExtension()
    {
        return new DwhApplicationExtension();
    }
}
