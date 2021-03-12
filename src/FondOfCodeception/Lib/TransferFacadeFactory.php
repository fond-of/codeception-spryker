<?php

namespace FondOfCodeception\Lib;

use FondOfCodeception\Module\SprykerConstants;
use Spryker\Service\UtilGlob\UtilGlobService;
use Spryker\Service\UtilGlob\UtilGlobServiceFactory;
use Spryker\Service\UtilGlob\UtilGlobServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Propel\Business\PropelFacade;
use Spryker\Zed\Propel\Business\PropelFacadeInterface;
use Spryker\Zed\Transfer\Business\TransferBusinessFactory;
use Spryker\Zed\Transfer\Business\TransferFacade;
use Spryker\Zed\Transfer\Business\TransferFacadeInterface;
use Spryker\Zed\Transfer\Dependency\Facade\TransferToPropelFacadeBridge;
use Spryker\Zed\Transfer\Dependency\Facade\TransferToPropelFacadeInterface;
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
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createContainer(): Container
    {
        $container = new Container();

        $container = $this->addSymfonyFinderToContainer($container);
        $container = $this->addSymfonyFilesystemToContainer($container);
        $container = $this->addPropelFacadeToContainer($container);
        $container = $this->addUtilGlobServiceToContainer($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSymfonyFinderToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Transfer\TransferDependencyProvider::SYMFONY_FINDER')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[TransferDependencyProvider::SYMFONY_FINDER] = new Finder();

            return $container;
        }

        $container->set(TransferDependencyProvider::SYMFONY_FINDER, new Finder());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSymfonyFilesystemToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Transfer\TransferDependencyProvider::SYMFONY_FILE_SYSTEM')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[TransferDependencyProvider::SYMFONY_FILE_SYSTEM] = new Filesystem();

            return $container;
        }

        $container->set(TransferDependencyProvider::SYMFONY_FILE_SYSTEM, new Filesystem());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilGlobServiceToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Transfer\TransferDependencyProvider::SERVICE_UTIL_GLOB')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[TransferDependencyProvider::SYMFONY_FINDER] = $this->createTransferToUtilGlobServiceBridge();

            return $container;
        }

        $container->set(TransferDependencyProvider::SERVICE_UTIL_GLOB, $this->createTransferToUtilGlobServiceBridge());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPropelFacadeToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Transfer\TransferDependencyProvider::FACADE_PROPEL')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[TransferDependencyProvider::FACADE_PROPEL] = $this->createTransferToPropelFacadeBridge();

            return $container;
        }

        $container->set(TransferDependencyProvider::FACADE_PROPEL, $this->createTransferToPropelFacadeBridge());

        return $container;
    }

    /**
     * @return \Spryker\Zed\Transfer\Dependency\Service\TransferToUtilGlobServiceInterface
     */
    protected function createTransferToUtilGlobServiceBridge(): TransferToUtilGlobServiceInterface
    {
        return new TransferToUtilGlobServiceBridge($this->createUtilGlobService());
    }

    /**
     * @return \Spryker\Service\UtilGlob\UtilGlobServiceInterface
     */
    protected function createUtilGlobService(): UtilGlobServiceInterface
    {
        return (new UtilGlobService())
            ->setFactory(new UtilGlobServiceFactory());
    }

    /**
     * @return \Spryker\Zed\Transfer\Dependency\Facade\TransferToPropelFacadeInterface
     */
    protected function createTransferToPropelFacadeBridge(): TransferToPropelFacadeInterface
    {
        return new TransferToPropelFacadeBridge($this->createPropelFacade());
    }

    /**
     * @return \Spryker\Zed\Propel\Business\PropelFacadeInterface
     */
    protected function createPropelFacade(): PropelFacadeInterface
    {
        return new class extends PropelFacade {
            /**
             * @return string
             */
            public function getSchemaDirectory(): string
            {
                return SprykerConstants::SCHEMA_DIRECTORY;
            }
        };
    }

    /**
     * @return \Spryker\Zed\Transfer\TransferConfig
     */
    protected function createTransferConfig(): TransferConfig
    {
        return new class extends TransferConfig {
            /**
             * @return string[]
             */
            public function getSourceDirectories(): array
            {
                $sourceDirectories = parent::getSourceDirectories();

                $sourceDirectories[] = rtrim(APPLICATION_ROOT_DIR, DIRECTORY_SEPARATOR) . '/*/src/*/Shared/*/Transfer/';
                $sourceDirectories[] = rtrim(APPLICATION_ROOT_DIR, DIRECTORY_SEPARATOR) . '/*/*/src/*/Shared/*/Transfer/';

                return $sourceDirectories;
            }
        };
    }
}
