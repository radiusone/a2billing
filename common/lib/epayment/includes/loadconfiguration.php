<?php

use A2billing\Table;

$instance_sub_table = new Table("cc_configuration", "configuration_key as cfgkey, configuration_value as cfgvalue");
$DBHandle  = DbConnect();
$configuration_query = $instance_sub_table -> get_list($DBHandle);

foreach ($configuration_query as $configuration) {
    define($configuration['cfgkey'], $configuration['cfgvalue']);
}
