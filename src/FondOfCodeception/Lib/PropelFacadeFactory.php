<?php

namespace FondOfCodeception\Lib;

use FondOfCodeception\Module\SprykerConstants;
use Spryker\Service\UtilText\UtilTextService;
use Spryker\Service\UtilText\UtilTextServiceFactory;
use Spryker\Service\UtilText\UtilTextServiceInterface;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Propel\Business\PropelBusinessFactory;
use Spryker\Zed\Propel\Business\PropelFacade;
use Spryker\Zed\Propel\Business\PropelFacadeInterface;
use Spryker\Zed\Propel\Communication\Plugin\Propel\ForeignKeyIndexPropelSchemaElementFilterPlugin;
use Spryker\Zed\Propel\Dependency\Service\PropelToUtilTextServiceBridge;
use Spryker\Zed\Propel\Dependency\Service\PropelToUtilTextServiceInterface;
use Spryker\Zed\Propel\PropelConfig;
use Spryker\Zed\Propel\PropelDependencyProvider;

class PropelFacadeFactory
{
    /**
     * @return \Spryker\Zed\Propel\Business\PropelFacadeInterface
     */
    public function create(): PropelFacadeInterface
    {
        $propelFacade = new PropelFacade();

        $propelFacade->setFactory($this->createPropelBusinessFactory());

        return $propelFacade;
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractBusinessFactory
     */
    protected function createPropelBusinessFactory(): AbstractBusinessFactory
    {
        $propelBusinessFactory = new PropelBusinessFactory();

        $propelBusinessFactory->setContainer($this->createContainer());
        $propelBusinessFactory->setConfig($this->createPropelConfig());

        return $propelBusinessFactory;
    }

    /**
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createContainer(): Container
    {
        $container = new Container();

        $container = $this->addUtilTextServiceToContainer($container);
        $container = $this->addPropelSchemaElementFilterPluginsToContainer($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilTextServiceToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Propel\PropelDependencyProvider::UTIL_TEXT_SERVICE')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[PropelDependencyProvider::UTIL_TEXT_SERVICE] = $this->createPropelToUtilTextServiceBridge();

            return $container;
        }

        $container->set(PropelDependencyProvider::UTIL_TEXT_SERVICE, $this->createPropelToUtilTextServiceBridge());

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPropelSchemaElementFilterPluginsToContainer(Container $container): Container
    {
        if (!defined('\Spryker\Zed\Propel\PropelDependencyProvider::PLUGINS_PROPEL_SCHEMA_ELEMENT_FILTER')) {
            return $container;
        }

        if (!method_exists($container, 'set')) {
            $container[PropelDependencyProvider::PLUGINS_PROPEL_SCHEMA_ELEMENT_FILTER] = $this->createPropelSchemaElementFilterPlugins();

            return $container;
        }

        $container->set(
            PropelDependencyProvider::PLUGINS_PROPEL_SCHEMA_ELEMENT_FILTER,
            $this->createPropelSchemaElementFilterPlugins()
        );

        return $container;
    }

    /**
     * @return \Spryker\Zed\Kernel\AbstractBundleConfig
     */
    protected function createPropelConfig(): AbstractBundleConfig
    {
        return new class extends PropelConfig {
            /**
             * @return string[]
             */
            public function getCorePropelSchemaPathPatterns(): array
            {
                $corePropelSchemaPathPatterns = parent::getCorePropelSchemaPathPatterns();
                $additionalCorePropelSchemaPathPattern = APPLICATION_ROOT_DIR . '/bundles/*/src/*/Zed/*/Persistence/Propel/Schema/';

                if (!glob($additionalCorePropelSchemaPathPattern)) {
                    return $corePropelSchemaPathPatterns;
                }

                $corePropelSchemaPathPatterns[] = $additionalCorePropelSchemaPathPattern;

                return $corePropelSchemaPathPatterns;
            }

            /**
             * @return string[]
             */
            public function getProjectPropelSchemaPathPatterns()
            {
                return [];
            }

            /**
             * @return string
             */
            public function getSchemaDirectory()
            {
                return SprykerConstants::SCHEMA_DIRECTORY;
            }
        };
    }

    /**
     * @return \Spryker\Zed\Propel\Dependency\Service\PropelToUtilTextServiceInterface
     */
    protected function createPropelToUtilTextServiceBridge(): PropelToUtilTextServiceInterface
    {
        return new PropelToUtilTextServiceBridge(
            $this->createUtilTextService()
        );
    }

    /**
     * @return \Spryker\Service\UtilText\UtilTextServiceInterface
     */
    protected function createUtilTextService(): UtilTextServiceInterface
    {
        $utilTextServiceFactory = new UtilTextServiceFactory();

        return (new UtilTextService())
            ->setFactory($utilTextServiceFactory);
    }

    /**
     * @return \Spryker\Zed\Propel\Dependency\Plugin\PropelSchemaElementFilterPluginInterface[]
     */
    protected function createPropelSchemaElementFilterPlugins(): array
    {
        return [
            new ForeignKeyIndexPropelSchemaElementFilterPlugin(),
        ];
    }
}
