<?php

namespace FondOfCodeception\Lib;

use Propel\Generator\Command\ModelBuildCommand;
use Symfony\Component\Console\Application;

class PropelApplicationFactory
{
    /**
     * @return \Symfony\Component\Console\Application
     */
    public function create(): Application
    {
        $application = new Application();

        $application->setAutoExit(false);
        $application->add(new ModelBuildCommand());

        return $application;
    }
}
