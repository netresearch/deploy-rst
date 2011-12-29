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
 * @link     https://github.com/netresearch/deploy-rst
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

if ('@cfg_dir@' == '@' . 'cfg_dir@') {
    $cfgfile = __DIR__ . '/config.php';
} else {
    $cfgfile = '@cfg_dir@/config.php';
}

//old code in system_folders
error_reporting(error_reporting() & ~E_STRICT);
$sf = new System_Folders();
$homedir = $sf->getHome();
if (file_exists($homedir . '/.config/deploy-rst')) {
    $cfgfile = $homedir . '/.config/deploy-rst';
}

$options = array();
if (file_exists($cfgfile)) {
    include $cfgfile;
}

$cli = new netresearch\DeployRst\Cli($options);
$cli->run();
?>