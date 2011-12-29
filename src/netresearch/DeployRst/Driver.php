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
 * Base driver class
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://gitorious.nr/php/deploy-rst
 */
class Driver
{
    /**
     * Command line options
     *
     * @var array
     */
    protected $options;

    /**
     * Path to the rST file
     *
     * @var string
     */
    protected $file;

    /**
     * rST meta settings
     *
     * @var array
     */
    protected $metas;

    /**
     * Create a new instance, set some variables
     *
     * @param string $file    Path to rST file
     * @param string $metas   rST meta settings
     * @param string $options CLI options
     */
    public function __construct($file, $metas, $options)
    {
        $this->options = $options;
        $this->file    = $file;
        $this->metas   = $metas;
    }

    /**
     * Read a single required setting and returns its value.
     * Reads it from the meta data as well as from the CLI options.
     *
     * @param string $name Name of the setting.
     *
     * @return string Setting value
     *
     * @throws Exception When the setting is missing
     */
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
