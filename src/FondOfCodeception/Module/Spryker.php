<?php

namespace FondOfCodeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use FondOfCodeception\Lib\NullLoggerFactory;
use FondOfCodeception\Lib\SearchFacadeFactory;
use FondOfCodeception\Lib\TransferFacadeFactory;
use Psr\Log\LoggerInterface;
use Spryker\Shared\Config\Environment;

class Spryker extends Module
{
    /**
     * @var array
     */
    protected $config = [
        SprykerConstants::CONFIG_GENERATE_TRANSFER => true,
        SprykerConstants::CONFIG_GENERATE_MAP_CLASSES => true,
        SprykerConstants::CONFIG_SUPPORTED_SOURCE_IDENTIFIERS => ['page'],
    ];

    /**
     * @var \FondOfCodeception\Lib\NullLoggerFactory
     */
    protected $nullLoggerFactory;

    /**
     * @var \FondOfCodeception\Lib\SearchFacadeFactory
     */
    protected $searchFacadeFactory;

    /**
     * @var \FondOfCodeception\Lib\TransferFacadeFactory
     */
    protected $transferFacadeFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $nullLogger;

    /**
     * @param \Codeception\Lib\ModuleContainer $moduleContainer
     * @param array|null $config
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);

        $this->nullLoggerFactory = new NullLoggerFactory();
        $this->searchFacadeFactory = new SearchFacadeFactory($this->config);
        $this->transferFacadeFactory = new TransferFacadeFactory();
    }

    /**
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->initEnvironment();

        if ((bool)$this->config[SprykerConstants::CONFIG_GENERATE_TRANSFER]) {
            $this->generateTransfer();
        }

        if ((bool)$this->config[SprykerConstants::CONFIG_GENERATE_MAP_CLASSES]) {
            $this->generateMapClasses();
        }
    }

    /**
     * @return void
     */
    protected function generateTransfer(): void
    {
        $transferFacade = $this->transferFacadeFactory->create();
        $nullLogger = $this->getNullLogger();

        $this->debug('Deleting existing transfer classes...');
        if (!method_exists($transferFacade, 'deleteGeneratedDataTransferObjects')) {
            $transferFacade->deleteGeneratedTransferObjects();
        } else {
            $transferFacade->deleteGeneratedDataTransferObjects();
        }

        $this->debug('Generating transfer classes...');
        $transferFacade->generateTransferObjects($nullLogger);
    }

    /**
     * @return void
     */
    protected function generateMapClasses(): void
    {
        $searchFacade = $this->searchFacadeFactory->create();
        $nullLogger = $this->getNullLogger();

        $this->debug('Generating map classes...');
        if (!method_exists($searchFacade, 'generateSourceMap')) {
            $searchFacade->generatePageIndexMap($nullLogger);
        } else {
            $searchFacade->generateSourceMap($nullLogger);
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getNullLogger(): LoggerInterface
    {
        if ($this->nullLogger === null) {
            $this->nullLogger = $this->nullLoggerFactory->create();
        }

        return $this->nullLogger;
    }

    /**
     * @return void
     */
    protected function initEnvironment(): void
    {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', Environment::TESTING);
        defined('APPLICATION_STORE') || define('APPLICATION_STORE', SprykerConstants::STORE);
        defined('APPLICATION') || define('APPLICATION', 'ZED');
        defined('APPLICATION_ROOT_DIR') || define('APPLICATION_ROOT_DIR', $this->getPathToRootDirectory());
        defined('APPLICATION_VENDOR_DIR') || define('APPLICATION_VENDOR_DIR', APPLICATION_ROOT_DIR . '/vendor');
        defined('APPLICATION_SOURCE_DIR') || define('APPLICATION_SOURCE_DIR', APPLICATION_ROOT_DIR . '/src');
    }

    /**
     * @return string
     */
    protected function getPathToRootDirectory(): string
    {
        return rtrim(Configuration::projectDir(), DIRECTORY_SEPARATOR);
    }
}
