<?php

namespace FondOfCodeception\Lib;

use FondOfCodeception\Module\SprykerConstants;
use Generated\Shared\Transfer\StoreTransfer;
use ReflectionClass;
use Spryker\Client\Store\StoreClient;
use Spryker\Client\Store\StoreFactory;
use Spryker\Service\UtilEncoding\UtilEncodingService;
use Spryker\Service\UtilEncoding\UtilEncodingServiceFactory;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\SearchElasticsearch\Dependency\Client\SearchElasticsearchToStoreClientBridge;
use Spryker\Shared\SearchElasticsearch\Dependency\Client\SearchElasticsearchToStoreClientInterface;
use Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceBridge as SharedSearchElasticsearchToUtilEncodingServiceBridge;
use Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface as SharedSearchElasticsearchToUtilEncodingServiceInterface;
use Spryker\Shared\SearchElasticsearch\SearchElasticsearchConfig as SharedSearchElasticsearchConfig;
use Spryker\Shared\Store\Reader\StoreReaderInterface;
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
use Spryker\Zed\SearchElasticsearch\Dependency\Facade\SearchElasticsearchToStoreFacadeBridge;
use Spryker\Zed\SearchElasticsearch\Dependency\Facade\SearchElasticsearchToStoreFacadeInterface;
use Spryker\Zed\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceBridge as ZedSearchElasticsearchToUtilEncodingServiceBridge;
use Spryker\Zed\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface as ZedSearchElasticsearchToUtilEncodingServiceInterface;
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
        $reflectionClass = new ReflectionClass(JsonIndexDefinitionLoader::class);

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
                            SprykerConstants::STORE,
                        ],
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

            if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::FACADE_STORE')) {
                $container[SearchElasticsearchDependencyProvider::FACADE_STORE] = $this->createSearchElasticsearchToStoreFacadeBridge();
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

        if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::FACADE_STORE')) {
            $container->set(SearchElasticsearchDependencyProvider::FACADE_STORE, $this->createSearchElasticsearchToStoreFacadeBridge());
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
             * @param bool $fallbackToDefault
             *
             * @return \Generated\Shared\Transfer\StoreTransfer
             */
            public function getCurrentStore(bool $fallbackToDefault = false)
            {
                return (new StoreTransfer())->setName(SprykerConstants::STORE);
            }

            /**
             * @return array<\Generated\Shared\Transfer\StoreTransfer>
             */
            public function getAllStores()
            {
                return [
                    $this->getCurrentStore(),
                ];
            }
        };

        return new SearchToStoreFacadeBridge($storeFacade);
    }

    /**
     * @return \Spryker\Zed\SearchElasticsearch\Dependency\Facade\SearchElasticsearchToStoreFacadeInterface
     */
    protected function createSearchElasticsearchToStoreFacadeBridge(): SearchElasticsearchToStoreFacadeInterface
    {
        $storeFacade = new class extends StoreFacade {
            /**
             * @param bool $fallbackToDefault
             *
             * @return \Generated\Shared\Transfer\StoreTransfer
             */
            public function getCurrentStore(bool $fallbackToDefault = false)
            {
                return (new StoreTransfer())->setName(SprykerConstants::STORE);
            }

            /**
             * @return array<\Generated\Shared\Transfer\StoreTransfer>
             */
            public function getAllStores()
            {
                return [
                    $this->getCurrentStore(),
                ];
            }

            /**
             * @return bool
             */
            public function isDynamicStoreEnabled(): bool
            {
                return true;
            }
        };

        return new SearchElasticsearchToStoreFacadeBridge($storeFacade);
    }

    /**
     * @return array<\Spryker\Zed\SearchExtension\Dependency\Plugin\InstallPluginInterface>
     */
    protected function createSearchMapInstallerPlugins(): array
    {
        $elasticsearchIndexMapInstallerPlugin = new ElasticsearchIndexMapInstallerPlugin();

        $elasticsearchIndexMapInstallerPlugin->setFacade($this->createElasticsearchFacade());

        return [
            $elasticsearchIndexMapInstallerPlugin,
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
            /** @var array */
            protected const POSSIBLE_DIRECTORY_PATTERNS = [
                '%s/vendor/*/*/src/*/Shared/*/Schema/',
                '%s/*/*/src/*/Shared/*/Schema/',
                '%s/src/*/Shared/*/Schema/',
            ];

            /**
             * @return array
             */
            public function getJsonSchemaDefinitionDirectories(): array
            {
                $directories = [];

                foreach (static::POSSIBLE_DIRECTORY_PATTERNS as $possibleDirectoryPattern) {
                    $directory = sprintf($possibleDirectoryPattern, APPLICATION_ROOT_DIR);
                    if (glob($directory, GLOB_NOSORT | GLOB_ONLYDIR)) {
                        $directories[] = $directory;
                    }
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

        return new class ($this->config[SprykerConstants::CONFIG_SUPPORTED_SOURCE_IDENTIFIERS]) extends SharedSearchElasticsearchConfig {
            /**
             * @var array<string>
             */
            protected $supportedSourceIdentifiers;

            /**
             * @param array<string> $supportedSourceIdentifiers
             */
            public function __construct(array $supportedSourceIdentifiers)
            {
                $this->supportedSourceIdentifiers = $supportedSourceIdentifiers;
            }

            /**
             * @return array<string>
             */
            public function getSupportedSourceIdentifiers(): array
            {
                return $this->supportedSourceIdentifiers;
            }
        };
    }

    /**
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function createSearchElasticsearchContainer(): Container
    {
        $container = new Container();

        $requiredClassName = '\Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceBridge';
        $createUtilEncodingMethod = 'createSharedSearchElasticsearchToUtilEncodingServiceBridge';

        if (!class_exists($requiredClassName)) {
            $createUtilEncodingMethod = 'createZedSearchElasticsearchToUtilEncodingServiceBridge';
        }

        if (!method_exists($container, 'set')) {
            if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::CLIENT_STORE')) {
                $container[SearchElasticsearchDependencyProvider::CLIENT_STORE] = $this->createSearchElasticsearchToStoreClientBridge();
            }

            if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::FACADE_STORE')) {
                $container[SearchElasticsearchDependencyProvider::FACADE_STORE] = $this->createSearchElasticsearchToStoreFacadeBridge();
            }

            $container[SearchElasticsearchDependencyProvider::SERVICE_UTIL_ENCODING] = $this->{$createUtilEncodingMethod}();

            return $container;
        }

        $container->set(
            SearchElasticsearchDependencyProvider::SERVICE_UTIL_ENCODING,
            $this->{$createUtilEncodingMethod}(),
        );

        if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::FACADE_STORE')) {
            $container->set(SearchElasticsearchDependencyProvider::FACADE_STORE, $this->createSearchElasticsearchToStoreFacadeBridge());
        }

        if (defined('\Spryker\Zed\SearchElasticsearch\SearchElasticsearchDependencyProvider::CLIENT_STORE')) {
            $container->set(
                SearchElasticsearchDependencyProvider::CLIENT_STORE,
                $this->createSearchElasticsearchToStoreClientBridge(),
            );
        }

        return $container;
    }

    /**
     * @return \Spryker\Shared\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface
     */
    protected function createSharedSearchElasticsearchToUtilEncodingServiceBridge(): SharedSearchElasticsearchToUtilEncodingServiceInterface
    {
        $utilEncodingServiceFactory = new UtilEncodingServiceFactory();

        return new SharedSearchElasticsearchToUtilEncodingServiceBridge(
            $this->createUtilEncodingService(),
        );
    }

    /**
     * @return \Spryker\Zed\SearchElasticsearch\Dependency\Service\SearchElasticsearchToUtilEncodingServiceInterface
     */
    protected function createZedSearchElasticsearchToUtilEncodingServiceBridge(): ZedSearchElasticsearchToUtilEncodingServiceInterface
    {
        $utilEncodingServiceFactory = new UtilEncodingServiceFactory();

        return new ZedSearchElasticsearchToUtilEncodingServiceBridge(
            $this->createUtilEncodingService(),
        );
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected function createUtilEncodingService(): UtilEncodingServiceInterface
    {
        $utilEncodingServiceFactory = new UtilEncodingServiceFactory();

        return (new UtilEncodingService())
            ->setFactory($utilEncodingServiceFactory);
    }

    /**
     * @return \Spryker\Shared\SearchElasticsearch\Dependency\Client\SearchElasticsearchToStoreClientInterface
     */
    protected function createSearchElasticsearchToStoreClientBridge(): SearchElasticsearchToStoreClientInterface
    {
        $storeClient = (new StoreClient())
            ->setFactory($this->createStoreFactory());

        return new SearchElasticsearchToStoreClientBridge(
            $storeClient,
        );
    }

    /**
     * @return \Spryker\Client\Store\StoreFactory
     */
    protected function createStoreFactory(): StoreFactory
    {
        $storeReader = new class implements StoreReaderInterface {
            /**
             * @return \Generated\Shared\Transfer\StoreTransfer
             */
            public function getCurrentStore()
            {
                return $this->getStoreByName(SprykerConstants::STORE);
            }

            /**
             * @param string $storeName
             *
             * @return \Generated\Shared\Transfer\StoreTransfer
             */
            public function getStoreByName($storeName)
            {
                return (new StoreTransfer())
                    ->setName($storeName);
            }
        };

        return new class ($storeReader) extends StoreFactory {
            /**
             * @var \Spryker\Shared\Store\Reader\StoreReaderInterface
             */
            protected $storeReader;

            /**
             * @param \Spryker\Shared\Store\Reader\StoreReaderInterface $storeReader
             */
            public function __construct(StoreReaderInterface $storeReader)
            {
                $this->storeReader = $storeReader;
            }

            /**
             * @return \Spryker\Shared\Store\Reader\StoreReaderInterface
             */
            public function createStoreReader()
            {
                return $this->storeReader;
            }
        };
    }
}
