<?php

namespace FondOfCodeception\Lib;

use Codeception\Test\Unit;
use FondOfCodeception\Module\SprykerConstants;
use Spryker\Zed\Search\Business\SearchFacade;

class SearchFacadeFactoryTest extends Unit
{
    /**
     * @var \FondOfCodeception\Lib\SearchFacadeFactory
     */
    protected $searchFacadeFactory;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->searchFacadeFactory = new SearchFacadeFactory(
            [
                SprykerConstants::CONFIG_SUPPORTED_SOURCE_IDENTIFIERS => ['page'],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $searchFacade = $this->searchFacadeFactory->create();

        static::assertInstanceOf(SearchFacade::class, $searchFacade);
    }
}
