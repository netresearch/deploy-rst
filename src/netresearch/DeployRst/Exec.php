<?php
declare(encoding='utf-8');
/**
 * Part of DeployRst
 *
 * PHP Version 5
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://gitorious.nr/php/deploy-rst
 */
namespace netresearch\DeployRst;

/**
 * Helper methods to execute external processes
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://gitorious.nr/php/deploy-rst
 */
class Exec
{
    /**
     * Run a command and return the full output and the return value.
     *
     * @param string $cmd Command to execute
     *
     * @return array First value is the full output of the command, second value
     *               is the return value / exit code of the command
     */
    public static function run($cmd)
    {
        $handle = popen($cmd, 'r');
        if (!is_resource($handle)) {
            return array('', 1);
        }

        $output = stream_get_contents($handle);
        $retval = pclose($handle);

        return array($output, $retval);
    }

}

?>