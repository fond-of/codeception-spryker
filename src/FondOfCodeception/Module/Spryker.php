<?php

namespace FondOfCodeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use FondOfCodeception\Lib\NullLoggerFactory;
use FondOfCodeception\Lib\TransferFacadeFactory;
use Spryker\Shared\Config\Environment;

class Spryker extends Module
{
    /**
     * @var array
     */
    protected $config = [
        'generate_transfer' => true
    ];

    /**
     * @var \FondOfCodeception\Lib\TransferFacadeFactory
     */
    protected $transferFacadeFactory;

    /**
     * @var \FondOfCodeception\Lib\NullLoggerFactory
     */
    protected $nullLoggerFactory;

    /**
     * @param \Codeception\Lib\ModuleContainer $moduleContainer
     * @param array|null $config
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);

        $this->transferFacadeFactory = new TransferFacadeFactory();
        $this->nullLoggerFactory = new NullLoggerFactory();
    }

    /**
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->initEnvironment();

        if ($this->config['generate_transfer']) {
            $this->generateTransfer();
        }
    }

    /**
     * @return void
     */
    protected function generateTransfer(): void
    {
        $transferFacade = $this->transferFacadeFactory->create();
        $nullLogger = $this->nullLoggerFactory->create();

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
    protected function initEnvironment(): void
    {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', Environment::TESTING);
        defined('APPLICATION_STORE') || define('APPLICATION_STORE', 'UNIT');
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
