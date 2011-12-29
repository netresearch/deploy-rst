#!/usr/bin/env php
<?php
function __autoload($class)
{
    $file = str_replace(array('_', '\\'), '/', $class) . '.php';
    require_once $file;
}
if (is_dir(__DIR__ . '/src/netresearch/DeployRst')) {
    set_include_path(
        get_include_path() . PATH_SEPARATOR . __DIR__ . '/src'
    );
}

require_once __DIR__ . '/config.php';
$cli = new netresearch\DeployRst\Cli($options);
$cli->run();
?>