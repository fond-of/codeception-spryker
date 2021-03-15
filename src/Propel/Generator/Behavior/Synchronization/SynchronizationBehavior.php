<?php

namespace Propel\Generator\Behavior\Synchronization;

use Spryker\Zed\SynchronizationBehavior\Persistence\Propel\Behavior\SynchronizationBehavior as SprykerSynchronizationBehavior;
use Spryker\Zed\SynchronizationBehavior\SynchronizationBehaviorConfig;

class SynchronizationBehavior extends SprykerSynchronizationBehavior
{
    /**
     * @return \Spryker\Zed\Kernel\AbstractBundleConfig
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = new SynchronizationBehaviorConfig();
        }

        return $this->config;
    }
}
