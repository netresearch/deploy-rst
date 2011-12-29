#!/usr/bin/env php
<?php
declare(encoding='utf-8');
/**
 * Shell script to deploy a reStructuredText file into a wiki.
 *
 * PHP Version 5
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://gitorious.nr/php/deploy-rst
 */

spl_autoload_register(
    function($class)
    {
        $file = str_replace(array('_', '\\'), '/', $class) . '.php';
        include_once $file;
    }
);

if (is_dir(__DIR__ . '/src/netresearch/DeployRst')) {
    set_include_path(
        get_include_path() . PATH_SEPARATOR . __DIR__ . '/src'
    );
}

require_once __DIR__ . '/config.php';
$cli = new netresearch\DeployRst\Cli($options);
$cli->run();
?>