<?php
namespace netresearch\DeployRst;

class Exec
{
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