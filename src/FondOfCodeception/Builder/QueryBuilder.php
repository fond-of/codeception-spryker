<?php

namespace FondOfCodeception\Builder;

use Spryker\Zed\PropelOrm\Business\Builder\QueryBuilder as BaseQueryBuilder;
use Spryker\Zed\PropelOrm\Business\PropelOrmBusinessFactory;

class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @return \Spryker\Zed\Kernel\Business\BusinessFactoryInterface
     */
    protected function getFactory()
    {
        if (
            !class_exists('Spryker\Zed\PropelOrm\Business\PropelOrmBusinessFactory')
            || !interface_exists('Spryker\Zed\PropelOrmExtension\Dependency\Plugin\FindExtensionPluginInterface')
            || !interface_exists('Spryker\Zed\PropelOrmExtension\Dependency\Plugin\PostSaveExtensionPluginInterface')
        ) {
            return parent::getFactory();
        }

        return new class extends PropelOrmBusinessFactory {
            /**
             * @return array<\Spryker\Zed\PropelOrmExtension\Dependency\Plugin\FindExtensionPluginInterface>
             */
            public function getFindExtensionPlugins(): array
            {
                return [];
            }

            /**
             * @return array<\Spryker\Zed\PropelOrmExtension\Dependency\Plugin\PostSaveExtensionPluginInterface>
             */
            public function getPostSaveExtensionPlugins(): array
            {
                return [];
            }
        };
    }
}
