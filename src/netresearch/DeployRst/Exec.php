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
 * @link     https://github.com/netresearch/deploy-rst
 */
namespace netresearch\DeployRst;

/**
 * Helper methods to execute external processes
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
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


    public static function runPipe($cmd, $stdin)
    {
        $pipeSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            //2 => array('pipe', 'w')
        );
        $proc = proc_open($cmd, $pipeSpec, $pipes);

        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $retval = proc_close($proc);

        return array($output, $retval);
    }

}

?>