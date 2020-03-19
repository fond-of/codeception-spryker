<?php

namespace FondOfCodeception\Lib;

use Spryker\Service\Kernel\AbstractServiceFactory;
use Spryker\Service\UtilGlob\UtilGlobService;
use Spryker\Service\UtilGlob\UtilGlobServiceFactory;
use Spryker\Service\UtilGlob\UtilGlobServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Transfer\Business\TransferBusinessFactory;
use Spryker\Zed\Transfer\Business\TransferFacade;
use Spryker\Zed\Transfer\Business\TransferFacadeInterface;
use Spryker\Zed\Transfer\Dependency\Service\TransferToUtilGlobServiceBridge;
use Spryker\Zed\Transfer\Dependency\Service\TransferToUtilGlobServiceInterface;
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

        if (!method_exists($container, 'set')) {
            $container[TransferDependencyProvider::SYMFONY_FINDER] = new Finder();
            $container[TransferDependencyProvider::SYMFONY_FILE_SYSTEM] = new Filesystem();

            return $container;
        }

        $container->set(TransferDependencyProvider::SYMFONY_FINDER, new Finder());
        $container->set(TransferDependencyProvider::SYMFONY_FILE_SYSTEM, new Filesystem());

        if (!defined('\Spryker\Zed\Transfer\TransferDependencyProvider::SERVICE_UTIL_GLOB')) {
            return $container;
        }

        $container->set(TransferDependencyProvider::SERVICE_UTIL_GLOB, $this->createTransferToUtilGlobServiceBridge());

        return $container;
    }

    /**
     * @return \FondOfCodeception\Lib\TransferToUtilGlobServiceInterface
     */
    protected function createTransferToUtilGlobServiceBridge(): TransferToUtilGlobServiceInterface
    {
        return  new TransferToUtilGlobServiceBridge($this->createUtilGlobService());
    }

    /**
     * @return \Spryker\Service\UtilGlob\UtilGlobServiceInterface
     */
    protected function createUtilGlobService(): UtilGlobServiceInterface
    {
        return (new UtilGlobService())
            ->setFactory($this->createUtilGlobServiceFactory());
    }

    /**
     * @return \Spryker\Service\Kernel\AbstractServiceFactory
     */
    protected function createUtilGlobServiceFactory(): AbstractServiceFactory
    {
        return new UtilGlobServiceFactory();
    }

    /**
     * @return \Spryker\Zed\Transfer\TransferConfig
     */
    protected function createTransferConfig(): TransferConfig
    {
        return new TransferConfig();
    }
}
