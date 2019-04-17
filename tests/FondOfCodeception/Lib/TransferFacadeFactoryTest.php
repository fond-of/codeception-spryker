<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use Spryker\Zed\Transfer\Business\TransferFacade;

class TransferFacadeFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\TransferFacadeFactory
     */
    protected $transferFacadeFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->transferFacadeFactory = new TransferFacadeFactory();
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $transferFacade = $this->transferFacadeFactory->create();

        $this->assertInstanceOf(TransferFacade::class, $transferFacade);
    }
}
