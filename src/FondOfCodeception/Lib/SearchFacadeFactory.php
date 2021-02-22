<?php

namespace FondOfCodeception\Lib;

use FondOfCodeception\Module\SprykerConstants;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Service\UtilEncoding\UtilEncodingService;
use Spryker\Service\UtilEncoding\UtilEncodingServiceFactory;
use Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceBridge;
use Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface;
use Spryker\Shared\SearchElasticsearch\SearchElasticsearchConfig as SharedSearchElasticsearchConfig;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Search\Business\Model\Elasticsearch\Definition\JsonIndexDefinitionLoader;
use Spryker\Zed\Search\Business\SearchBusinessFactory;
use Spryker\Zed\Search\Business\SearchFacade;
use Spryker\Zed\Search\Business\SearchFacadeInterface;
use Spryker\Zed\Search\Dependency\Facade\SearchToStoreFacadeBridge;
use Spryker\Zed\Search\Dependency\Facade\SearchToStoreFacadeInterface;
use Spryker\Zed\Search\Dependency\Service\SearchToUtilEncodingBridge;
use Spryker\Zed\Search\Dependency\Service\SearchToUtilEncodingInterface;
use Spryker\Zed\Search\SearchConfig;
use Spryker\Zed\Search\SearchDependencyProvider;
use Spryker\Zed\SearchElasticsearch\Business\SearchElasticsearchBusinessFactory;
use Spryker\Zed\SearchElasticsearch\Business\SearchElasticsearchFacade;
use Spryker\Zed\SearchElasticsearch\Communication\Plugin\Search\ElasticsearchIndexMapInstallerPlugin;
use Spryker\Zed\SearchElasticsearch\SearchElasticsearchConfig;
use Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider;
use Spryker\Zed\Store\Business\StoreFacade;

class SearchFacadeFactory
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Spryker\Zed\Search\Business\SearchFacadeInterface
     */
    public function create(): SearchFacadeInterface
    {
        $searchFacade = new SearchFacade();

        $searchFacade->setFactory($this->createSearchBusinessFactory());

        return $searchFacade;
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractBusinessFactory
     */
    protected function createSearchBusinessFactory(): AbstractBusinessFactory
    {
        $searchBusinessFactory = new SearchBusinessFactory();
        $reflectionClass = new \ReflectionClass(JsonIndexDefinitionLoader::class);

        if ($reflectionClass->getConstructor()->getNumberOfRequiredParameters() === 4) {
            $searchBusinessFactory = new class extends SearchBusinessFactory {
                /**
                 * @return \Spryker\Zed\Search\Business\Model\Elasticsearch\Definition\IndexDefinitionLoaderInterface
                 */
                protected function createJsonIndexDefinitionLoader()
                {
                    return new JsonIndexDefinitionLoader(
                        $this->getConfig()->getJsonIndexDefinitionDirectories(),
                        $this->createJsonIndexDefinitionMerger(),
                        $this->getUtilEncodingService(),
                        [
                            SprykerConstants::STORE
                        ]
                    );
                }

            };
        }

        $searchBusinessFactory->setConfig($this->createSearchConfig());
        $searchBusinessFactory->setContainer($this->createSearchContainer());

        return $searchBusinessFactory;
    }

    /**
     * @return \Spryker\Zed\Search\SearchConfig
     */
    protected function createSearchConfig(): SearchConfig
    {
        return new SearchConfig();
    }

    /**
     * @throws
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createSearchContainer(): Container
    {
        $container = new Container();

        if (!method_exists($container, 'set')) {
            $container[SearchDependencyProvider::SERVICE_UTIL_ENCODING] = $this->createSearchToUtilEncodingBridge();

            if (defined('\Spryker\Zed\Search\SearchDependencyProvider::FACADE_STORE')) {
                $container[SearchDependencyProvider::FACADE_STORE] = $this->createSearchToStoreFacadeBridge();
            }

            if (defined('\Spryker\Zed\Search\SearchDependencyProvider::PLUGINS_SEARCH_MAP_INSTALLER')) {
                $container[SearchDependencyProvider::PLUGINS_SEARCH_MAP_INSTALLER] = $this->createSearchMapInstallerPlugins();
            }

            return $container;
        }

        $container->set(SearchDependencyProvider::SERVICE_UTIL_ENCODING, $this->createSearchToUtilEncodingBridge());

        if (defined('\Spryker\Zed\Search\SearchDependencyProvider::FACADE_STORE')) {
            $container->set(SearchDependencyProvider::FACADE_STORE, $this->createSearchToStoreFacadeBridge());
        }

        if (defined('\Spryker\Zed\Search\SearchDependencyProvider::PLUGINS_SEARCH_MAP_INSTALLER')) {
            $container->set(SearchDependencyProvider::PLUGINS_SEARCH_MAP_INSTALLER, $this->createSearchMapInstallerPlugins());
        }

        return $container;
    }

    /**
     * @return \Spryker\Zed\Search\Dependency\Service\SearchToUtilEncodingInterface
     */
    protected function createSearchToUtilEncodingBridge(): SearchToUtilEncodingInterface
    {
        $utilEncodingServiceFactory = new UtilEncodingServiceFactory();
        $utilEncodingService = (new UtilEncodingService())
            ->setFactory($utilEncodingServiceFactory);

        return new SearchToUtilEncodingBridge($utilEncodingService);
    }

    /**
     * @return \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected function createSearchToStoreFacadeBridge(): SearchToStoreFacadeInterface
    {
        $storeFacade = new class extends StoreFacade {
            /**
             * @return \Generated\Shared\Transfer\StoreTransfer
             */
            public function getCurrentStore()
            {
                return (new StoreTransfer())->setName(SprykerConstants::STORE);
            }

            /**
             * @return \Generated\Shared\Transfer\StoreTransfer[]
             */
            public function getAllStores()
            {
                return [
                    $this->getCurrentStore()
                ];
            }
        };

        return new SearchToStoreFacadeBridge($storeFacade);
    }

    /**
     * @return \Spryker\Zed\SearchExtension\Dependency\Plugin\InstallPluginInterface[]
     */
    protected function createSearchMapInstallerPlugins(): array
    {
        $elasticsearchIndexMapInstallerPlugin = new ElasticsearchIndexMapInstallerPlugin();

        $elasticsearchIndexMapInstallerPlugin->setFacade($this->createElasticsearchFacade());

        return [
            $elasticsearchIndexMapInstallerPlugin
        ];
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function createElasticsearchFacade(): AbstractFacade
    {
        $searchElasticsearchFacade = new SearchElasticsearchFacade();

        $searchElasticsearchFacade->setFactory($this->createSearchElasticsearchBusinessFactory());

        return $searchElasticsearchFacade;
    }

    /**
     * @return \Spryker\Zed\Kernel\Business\AbstractBusinessFactory
     */
    protected function createSearchElasticsearchBusinessFactory(): AbstractBusinessFactory
    {
        $searchElasticsearchBusinessFactory = new SearchElasticsearchBusinessFactory();

        $searchElasticsearchBusinessFactory->setConfig($this->createSearchElasticsearchConfig());
        $searchElasticsearchBusinessFactory->setContainer($this->createSearchElasticsearchContainer());

        return $searchElasticsearchBusinessFactory;
    }

    /**
     * @return \Spryker\Zed\SearchElasticsearch\SearchElasticsearchConfig
     */
    protected function createSearchElasticsearchConfig(): SearchElasticsearchConfig
    {
        $searchElasticsearchConfig = new class extends SearchElasticsearchConfig {
            /**
             * @return array
             */
            public function getJsonSchemaDefinitionDirectories(): array
            {
                $directories = [];

                $directory = sprintf('%s/vendor/*/*/src/*/Shared/*/Schema/', APPLICATION_ROOT_DIR);
                if (glob($directory, GLOB_NOSORT | GLOB_ONLYDIR)) {
                    $directories[] = $directory;
                }

                $directory = sprintf('%s/*/*/src/*/Shared/*/Schema/', APPLICATION_ROOT_DIR);
                if (glob($directory, GLOB_NOSORT | GLOB_ONLYDIR)) {
                    $directories[] = $directory;
                }

                return $directories;
            }

        };

        $searchElasticsearchConfig->setSharedConfig($this->createSharedSearchElasticsearchConfig());

        return $searchElasticsearchConfig;
    }

    /**
     * @return \Spryker\Shared\SearchElasticsearch\SearchElasticsearchConfig
     */
    protected function createSharedSearchElasticsearchConfig(): SharedSearchElasticsearchConfig
    {
        if (!isset($this->config[SprykerConstants::CONFIG_SUPPORTED_SOURCE_IDENTIFIERS])) {
            return new SharedSearchElasticsearchConfig();
        }

        return new class($this->config[SprykerConstants::CONFIG_SUPPORTED_SOURCE_IDENTIFIERS]) extends SharedSearchElasticsearchConfig {
            /**
             * @var string[]
             */
            protected $supportedSourceIdentifiers;

            /**
             * @param string[] $supportedSourceIdentifiers
             */
            public function __construct(array $supportedSourceIdentifiers)
            {
                $this->supportedSourceIdentifiers = $supportedSourceIdentifiers;
            }

            /**
             * @return string[]
             */
            public function getSupportedSourceIdentifiers(): array
            {
                return $this->supportedSourceIdentifiers;
            }
        };
    }

    /**
     * @return \Spryker\Zed\Kernel\Container
     * @throws \Spryker\Service\Container\Exception\FrozenServiceException
     */
    protected function createSearchElasticsearchContainer(): Container
    {
        $container = new Container();

        if (!method_exists($container, 'set')) {
            $container[SearchElasticsearchDependencyProvider::SERVICE_UTIL_ENCODING] = $this->createSearchElasticsearchToUtilEncodingBridge();

            return $container;
        }

        $container->set(SearchElasticsearchDependencyProvider::SERVICE_UTIL_ENCODING, $this->createSearchElasticsearchToUtilEncodingBridge());

        return $container;
    }

    /**
     * @return \Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface
     */
    protected function createSearchElasticsearchToUtilEncodingBridge(): SearchElasticsearchToUtilEncodingServiceInterface
    {
        $utilEncodingServiceFactory = new UtilEncodingServiceFactory();

        return new SearchElasticsearchToUtilEncodingServiceBridge(
            (new UtilEncodingService())
                ->setFactory($utilEncodingServiceFactory)
        );
    }
}
