<?php
namespace netresearch\DeployRst;

class Driver
{
    protected function loadSetting($name)
    {
        //Console_CommandLine problem, not - allowed in option names
        $optname = str_replace('-', '_', $name);
        if (isset($this->options[$optname])) {
            return $this->options[$optname];
        }

        if (isset($this->metas[$name])) {
            return $this->metas[$name];
        }

        throw new Exception(
            'Required setting "' . $name . '" not found.' . "\n"
            . 'Add it as meta variable in your rST file like' . "\n"
            . ".. meta::\n"
            . '    :' . $name . ": value-of-$name\n"
            . 'or pass it as command line option',
            100
        );
    }

}

?>
