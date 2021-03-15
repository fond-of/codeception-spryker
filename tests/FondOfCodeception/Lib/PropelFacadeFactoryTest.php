<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use Spryker\Zed\Propel\Business\PropelFacade;

class PropelFacadeFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\PropelFacadeFactory
     */
    protected $propelFacadeFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->propelFacadeFactory = new PropelFacadeFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $propelFacade = $this->propelFacadeFactory->create();

        static::assertInstanceOf(PropelFacade::class, $propelFacade);
    }
}
