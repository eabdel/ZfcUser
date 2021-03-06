<?php

namespace ZfcUserTest\Options;

use Zend\ServiceManager\ServiceManager;
use ZfcUser\ModuleOptionsFactory;

class ModuleOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover \ZfcUser\ModuleOptionsFactory::createService
     */
    public function testCreateService()
    {
        $factory = new ModuleOptionsFactory();
        $sm      = new ServiceManager();

        $sm->setService('Configuration', array());
        $sm->setAllowOverride(true);

        $this->assertInstanceOf('ZfcUser\ModuleOptions', $factory->createService($sm));

        $sm->setService('Configuration', array(
            'zfc_user' => array(
                'entityClass' => 'foo\bar'
            )
        ));

        $options = $factory->createService($sm);

        $this->assertInstanceOf('ZfcUser\ModuleOptions', $options);
        $this->assertEquals('foo\bar', $options->getEntityClass());
    }
}