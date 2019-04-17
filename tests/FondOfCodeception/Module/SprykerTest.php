<?php

namespace FondOfCodeception\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use Spryker\Shared\Config\Environment;

class SprykerTest extends Unit
{
    /**
     * @var \FondOfCodeception\Module\Spryker
     */
    protected $spryker;

    /**
     * @var \Codeception\Lib\ModuleContainer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleContainerMock;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->moduleContainerMock = $this->getMockBuilder(ModuleContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->spryker = new Spryker($this->moduleContainerMock);
    }

    /**
     * @return void
     */
    public function testInitialize(): void
    {
        $this->spryker->_initialize();

        $rootDirectory = rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR);

        $this->assertDirectoryExists(codecept_root_dir('src/Generated/Shared/Transfer'));
        $this->assertEquals('ZED', APPLICATION);
        $this->assertEquals(Environment::TESTING, APPLICATION_ENV);
        $this->assertEquals('UNIT', APPLICATION_STORE);
        $this->assertEquals($rootDirectory, APPLICATION_ROOT_DIR);
        $this->assertEquals($rootDirectory . DIRECTORY_SEPARATOR . 'src', APPLICATION_SOURCE_DIR);
        $this->assertEquals($rootDirectory . DIRECTORY_SEPARATOR . 'vendor', APPLICATION_VENDOR_DIR);
    }
}
