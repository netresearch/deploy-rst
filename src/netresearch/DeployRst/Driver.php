<?php
namespace netresearch\DeployRst;

class Driver
{
    protected function loadSetting($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
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
