<?php

namespace FondOfCodeception\Module;

interface SprykerConstants
{
    public const STORE = 'UNIT';

    public const SCHEMA_DIRECTORY = APPLICATION_SOURCE_DIR . '/Orm/Propel/Schema';
    public const PROPEL_DATABSE_CONFIGURATION_FILE = APPLICATION_ROOT_DIR . '/generated-conf/loadDatabase.php';

    public const CONFIG_GENERATE_TRANSFER = 'generate_transfer';
    public const CONFIG_GENERATE_MAP_CLASSES = 'generate_map_classes';
    public const CONFIG_GENERATE_PROPEL_CLASSES = 'generate_propel_classes';
    public const CONFIG_SUPPORTED_SOURCE_IDENTIFIERS = 'supported_source_identifiers';
}
