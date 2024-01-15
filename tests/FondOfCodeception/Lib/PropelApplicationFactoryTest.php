<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use Propel\Generator\Command\ModelBuildCommand;

class PropelApplicationFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\PropelApplicationFactory
     */
    protected $propelApplicationFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->propelApplicationFactory = new PropelApplicationFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $propelApplication = $this->propelApplicationFactory->create();

        static::assertFalse($propelApplication->isAutoExitEnabled());
        static::assertInstanceOf(ModelBuildCommand::class, $propelApplication->get('model:build'));
    }
}
