<?php

namespace FondOfCodeception\Lib;

use FondOfCodeception\Module\SprykerConstants;
use Spryker\Zed\Development\Business\DevelopmentBusinessFactory;
use Spryker\Zed\Development\Business\DevelopmentFacade;
use Spryker\Zed\Development\Business\DevelopmentFacadeInterface;
use Spryker\Zed\Development\DevelopmentConfig;
use Spryker\Zed\Development\DevelopmentDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Container;
use Symfony\Component\Finder\Finder;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DevelopmentFactory
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Spryker\Zed\Development\Business\DevelopmentFacadeInterface
     */
    public function create(): DevelopmentFacadeInterface
    {
        $developmentFacade = new DevelopmentFacade();

        $developmentFacade->setFactory($this->createDevelopmentBusinessFactory());

        return $developmentFacade;
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractBusinessFactory
     */
    protected function createDevelopmentBusinessFactory(): AbstractBusinessFactory
    {
        $developmentBusinessFactory = new DevelopmentBusinessFactory();

        $developmentBusinessFactory->setContainer($this->createContainer());
        $developmentBusinessFactory->setConfig($this->createDevelopmentConfig());

        return $developmentBusinessFactory;
    }

    /**
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createContainer(): Container
    {
        $container = new Container();

        $container = $this->addTwigEnvironmentToContainer($container);
        $container = $this->addTwigLoaderFilesystemToContainer($container);
        $container = $this->addFinderToContainer($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTwigEnvironmentToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Development\DevelopmentDependencyProvider::TWIG_ENVIRONMENT')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[DevelopmentDependencyProvider::TWIG_ENVIRONMENT] = new Environment(
                new FilesystemLoader(),
            );

            return $container;
        }

        $container->set(DevelopmentDependencyProvider::TWIG_ENVIRONMENT, new Environment(
            new FilesystemLoader(),
        ));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTwigLoaderFilesystemToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Development\DevelopmentDependencyProvider::TWIG_LOADER_FILESYSTEM')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[DevelopmentDependencyProvider::TWIG_LOADER_FILESYSTEM] = new FilesystemLoader();

            return $container;
        }

        $container->set(DevelopmentDependencyProvider::TWIG_LOADER_FILESYSTEM, new FilesystemLoader());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFinderToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Development\DevelopmentDependencyProvider::FINDER')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[DevelopmentDependencyProvider::FINDER] = new Finder();

            return $container;
        }

        $container->set(DevelopmentDependencyProvider::FINDER, new Finder());

        return $container;
    }

    /**
     * @return \Spryker\Zed\Development\DevelopmentConfig
     */
    protected function createDevelopmentConfig(): DevelopmentConfig
    {
        return new class ($this->config[SprykerConstants::CONFIG_IDE_AUTO_COMPLETION_SOURCE_DIRECTORIES]) extends DevelopmentConfig {
            /**
             * @var array<string>
             */
            protected array $ideAutoCompletionSourceDirectories;

            /**
             * @param array<string> $ideAutoCompletionSourceDirectories
             */
            public function __construct(array $ideAutoCompletionSourceDirectories)
            {
                $this->ideAutoCompletionSourceDirectories = $ideAutoCompletionSourceDirectories;
            }

            /**
             * @return array<string>
             */
            public function getIdeAutoCompletionSourceDirectoryGlobPatterns(): array
            {
                try {
                    $ideAutoCompletionSourceDirectories = parent::getIdeAutoCompletionSourceDirectoryGlobPatterns();
                } catch (Throwable $e) {
                    $ideAutoCompletionSourceDirectories = [APPLICATION_VENDOR_DIR . '/*/*/src/' => '*/*/'];
                }

                return array_merge(
                    $ideAutoCompletionSourceDirectories,
                    $this->ideAutoCompletionSourceDirectories,
                );
            }
        };
    }
}
