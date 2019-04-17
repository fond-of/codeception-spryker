<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use Psr\Log\NullLogger;

class NullLoggerFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\NullLoggerFactory
     */
    protected $nullLoggerFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->nullLoggerFactory = new NullLoggerFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $nullLogger = $this->nullLoggerFactory->create();

        $this->assertInstanceOf(NullLogger::class, $nullLogger);
    }
}
