<?php

/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 9/28/15
 * Time: 7:18 PM
 */
namespace Ellie\Service\Navigation;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
class ServiceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $navigation = new Service();
        return $navigation->createService($serviceLocator);
    }
}