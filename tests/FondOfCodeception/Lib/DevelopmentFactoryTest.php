<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use FondOfCodeception\Module\SprykerConstants;
use Spryker\Zed\Development\Business\DevelopmentFacade;

class DevelopmentFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\DevelopmentFactory
     */
    protected DevelopmentFactory $developmentFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        defined('APPLICATION_ROOT_DIR') || define('APPLICATION_ROOT_DIR', getcwd());

        $this->developmentFactory = new DevelopmentFactory([
            SprykerConstants::CONFIG_IDE_AUTO_COMPLETION_SOURCE_DIRECTORIES => [],
        ]);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $developmentFacade = $this->developmentFactory->create();

        static::assertInstanceOf(DevelopmentFacade::class, $developmentFacade);
    }
}
