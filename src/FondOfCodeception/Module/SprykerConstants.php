<?php

namespace FondOfCodeception\Module;

interface SprykerConstants
{
    /**
     * @var string
     */
    public const STORE = 'UNIT';

    /**
     * @var string
     */
    public const PROPEL_SCHEMA_DIRECTORY = APPLICATION_SOURCE_DIR . '/Orm/Propel/Schema';

    /**
     * @var string
     */
    public const PROPEL_LOADER_SCRIPT_DIRECTORY = APPLICATION_SOURCE_DIR . '/Orm/Propel/generated-conf';

    /**
     * @var string
     */
    public const PROPEL_LOADER_SCRIPT = self::PROPEL_LOADER_SCRIPT_DIRECTORY . '/loadDatabase.php';

    /**
     * @var string
     */
    public const CONFIG_GENERATE_TRANSFER = 'generate_transfer';

    /**
     * @var string
     */
    public const CONFIG_GENERATE_MAP_CLASSES = 'generate_map_classes';

    /**
     * @var string
     */
    public const CONFIG_GENERATE_PROPEL_CLASSES = 'generate_propel_classes';

    /**
     * @var string
     */
    public const CONFIG_SUPPORTED_SOURCE_IDENTIFIERS = 'supported_source_identifiers';

    /**
     * @var string
     */
    public const CONFIG_GENERATE_IDE_AUTO_COMPLETION = 'generate_ide_auto_completion';

    /**
     * @var string
     */
    public const CONFIG_IDE_AUTO_COMPLETION_SOURCE_DIRECTORIES = 'ide_auto_completion_source_directories';
}
