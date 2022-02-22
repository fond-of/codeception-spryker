<?php

namespace Propel\Generator\Behavior\Synchronization;

use Codeception\Test\Unit;
use Spryker\Zed\SynchronizationBehavior\SynchronizationBehaviorConfig;

class SynchronizationBehaviorTest extends Unit
{
    /**
     * @var \Propel\Generator\Behavior\Synchronization\SynchronizationBehavior
     */
    protected $synchronizationBehavior;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->synchronizationBehavior = new SynchronizationBehavior();
    }

    /**
     * @return void
     */
    public function testGetConfig(): void
    {
        static::assertInstanceOf(
            SynchronizationBehaviorConfig::class,
            $this->synchronizationBehavior->getConfig(),
        );
    }
}
