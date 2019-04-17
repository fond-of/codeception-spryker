<?php

namespace FondOfCodeception\Lib;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NullLoggerFactory
{
    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function create(): LoggerInterface
    {
        return new NullLogger();
    }
}
