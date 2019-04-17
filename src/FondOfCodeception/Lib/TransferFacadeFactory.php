<?php

namespace FondOfCodeception\Lib;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Transfer\Business\TransferBusinessFactory;
use Spryker\Zed\Transfer\Business\TransferFacade;
use Spryker\Zed\Transfer\Business\TransferFacadeInterface;
use Spryker\Zed\Transfer\TransferConfig;
use Spryker\Zed\Transfer\TransferDependencyProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class TransferFacadeFactory
{
    /**
     * @return \Spryker\Zed\Transfer\Business\TransferFacadeInterface
     */
    public function create(): TransferFacadeInterface
    {
        $transferFacade = new TransferFacade();

        $transferFacade->setFactory($this->createTransferBusinessFactory());

        return $transferFacade;
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractBusinessFactory
     */
    protected function createTransferBusinessFactory(): AbstractBusinessFactory
    {
        $transferBusinessFactory = new TransferBusinessFactory();

        $transferBusinessFactory->setContainer($this->createContainer());
        $transferBusinessFactory->setConfig($this->createTransferConfig());

        return $transferBusinessFactory;
    }

    /**
     * @throws
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createContainer(): Container
    {
        $container = new Container();

        $container->set(TransferDependencyProvider::SYMFONY_FINDER, new Finder());
        $container->set(TransferDependencyProvider::SYMFONY_FILE_SYSTEM, new Filesystem());

        return $container;
    }

    /**
     * @return \Spryker\Zed\Transfer\TransferConfig
     */
    protected function createTransferConfig(): TransferConfig
    {
        return new TransferConfig();
    }
}
