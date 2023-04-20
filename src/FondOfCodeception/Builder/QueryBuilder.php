<?php

namespace FondOfCodeception\Builder;

use Spryker\Zed\PropelOrm\Business\Builder\QueryBuilder as BaseQueryBuilder;
use Spryker\Zed\PropelOrm\Business\PropelOrmBusinessFactory;
use Spryker\Zed\PropelOrm\PropelOrmConfig;

class QueryBuilder extends BaseQueryBuilder
{
    public function getConfig() {
        if (
            !class_exists('Spryker\Zed\PropelOrm\PropelOrmConfig')
        ) {
            return parent::getConfig();
        }

        return new class extends PropelOrmConfig {
            /**
             * @return bool
             */
            public function isBooleanCastingEnabled(): bool
            {
                return false;
            }
        };
    }

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

            /**
             * @return array<\Spryker\Zed\PropelOrmExtension\Dependency\Plugin\PostUpdateExtensionPluginInterface>
             */
            public function getPostUpdateExtensionPlugins(): array
            {
                return [];
            }

            /**
             * @return array<\Spryker\Zed\PropelOrmExtension\Dependency\Plugin\PostDeleteExtensionPluginInterface>
             */
            public function getPostDeleteExtensionPlugins(): array
            {
                return [];
            }
        };
    }
}
