<?php

namespace FondOfCodeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Exception;
use FondOfCodeception\Lib\NullLoggerFactory;
use FondOfCodeception\Lib\PropelFacadeFactory;
use FondOfCodeception\Lib\SearchFacadeFactory;
use FondOfCodeception\Lib\TransferFacadeFactory;
use Psr\Log\LoggerInterface;
use Spryker\Shared\Config\Environment;
use Symfony\Component\Process\Process;

class Spryker extends Module
{
    /**
     * @var array
     */
    protected $config = [
        SprykerConstants::CONFIG_GENERATE_TRANSFER => true,
        SprykerConstants::CONFIG_GENERATE_MAP_CLASSES => true,
        SprykerConstants::CONFIG_GENERATE_PROPEL_CLASSES => true,
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
     * @var \FondOfCodeception\Lib\PropelFacadeFactory
     */
    protected $propelFacadeFactory;

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
        $this->propelFacadeFactory = new PropelFacadeFactory();
        $this->transferFacadeFactory = new TransferFacadeFactory();
    }

    /**
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->initEnvironment();

        if ((bool)$this->config[SprykerConstants::CONFIG_GENERATE_PROPEL_CLASSES]) {
            $this->generatePropelClasses();
        }

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
    protected function generatePropelClasses(): void
    {
        $propelFacade = $this->propelFacadeFactory->create();

        $this->debug('Deleting existing schema files...');
        $propelFacade->cleanPropelSchemaDirectory();

        $this->debug('Merging and coping schema files...');
        $propelFacade->copySchemaFilesToTargetDirectory();

        $configFile = APPLICATION_VENDOR_DIR . '/fond-of-codeception/spryker/propel.yml';

        if (!file_exists($configFile)) {
            $configFile = APPLICATION_ROOT_DIR . '/propel.yml';
        }

        $modelBuildCommand = [APPLICATION_VENDOR_DIR . '/bin/propel', 'model:build', '--config-dir', $configFile];
        $loaderScriptDirParams = ['--loader-script-dir', SprykerConstants::PROPEL_LOADER_SCRIPT_DIRECTORY];

        $this->debug('Generating propel classes...');
        try {
            (new Process(array_merge($modelBuildCommand, $loaderScriptDirParams)))->mustRun();
        } catch (Exception $exception) {
            (new Process($modelBuildCommand))->mustRun();
        }

        if (!file_exists(SprykerConstants::PROPEL_LOADER_SCRIPT)) {
            return;
        }

        $this->debug('Load propel classes...');
        require_once SprykerConstants::PROPEL_LOADER_SCRIPT;
    }

    /**
     * @return void
     */
    protected function generateTransfer(): void
    {
        $transferFacade = $this->transferFacadeFactory->create();
        $nullLogger = $this->getNullLogger();

        if (method_exists($transferFacade, 'deleteGeneratedEntityTransferObjects')) {
            $this->debug('Deleting existing entity transfer classes...');
            $transferFacade->deleteGeneratedEntityTransferObjects();
        }

        $this->debug('Deleting existing transfer classes...');
        if (!method_exists($transferFacade, 'deleteGeneratedDataTransferObjects')) {
            $transferFacade->deleteGeneratedTransferObjects();
        } else {
            $transferFacade->deleteGeneratedDataTransferObjects();
        }

        if (method_exists($transferFacade, 'generateEntityTransferObjects')) {
            $this->debug('Generating entity transfer classes...');
            $transferFacade->generateEntityTransferObjects($nullLogger);
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
