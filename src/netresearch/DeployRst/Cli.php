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
 * Command line interface
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
 */
class Cli
{
    public $file;
    public $metas;
    public $options;

    /**
     * Set the options from the config file
     *
     * @param array $options Array of config options, similar to the cli options
     */
    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * Run the whole deployment process
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->loadParams();
            $this->loadMeta();
            $this->runDriver();
        } catch (Exception $e) {
            file_put_contents('php://stderr', trim($e->getMessage()) . "\n");
            exit($e->getCode());
        }
    }

    /**
     * Loads and parses command line parameters.
     * Also takes care of the --help switch.
     *
     * @return void
     *
     * @throws Exception When the rST file does not exist
     */
    protected function loadParams()
    {
        $parser = new \Console_CommandLine();
        $parser->description = 'Deploy reStructuredText documents into a wiki';
        $parser->version     = '0.1.0';
        $parser->addArgument('file', array('description' => 'rST file path'));

        $parser->addOption(
            'driver',
            array(
                'long_name'   => '--driver',
                'optional'    => true,
                'action'      => 'StoreString',
                'description' => 'Wiki driver to use',
            )
        );

        //No -D options: https://pear.php.net/bugs/bug.php?id=19163
        //yep, that does not automatically work with new drivers
        Driver_Confluence::loadHelp($parser);

        try {
            $result = $parser->parse();

            foreach (array_keys($result->options) as $key) {
                if ($result->options[$key] === null) {
                    unset($result->options[$key]);
                }
            }
            $this->options = array_merge($this->options, $result->options);
        } catch (\Console_CommandLine_Exception $e) {
            $parser->displayError($e->getMessage());
        }

        $this->file = $result->args['file'];
        if (!file_exists($this->file)) {
            throw new Exception('File does not exist', 2);
        }
    }

    /**
     * Loads meta data from the rST document
     *
     * @return void
     */
    protected function loadMeta()
    {
        $this->metas = Rst::extractMeta($this->file);
    }

    /**
     * Runs the desired wiki driver, which in turn deploys the page.
     *
     * @return void
     *
     * @throws Exception When the driver cannot be found, or the driver
     *                   produces an error itself
     */
    protected function runDriver()
    {
        if (isset($this->metas['deploy-target'])) {
            $drname = $this->metas['deploy-target'];
        } else if (isset($this->options['driver'])) {
            $drname = $this->options['driver'];
        } else {
            throw new Exception(
                'No driver specified.' . "\n"
                . 'Use a deploy-target meta directive or --driver cli option',
                5
            );
        }

        $class = '\\netresearch\DeployRst\\Driver_' . ucfirst($drname);
        if (!class_exists($class)) {
            throw new Exception(
                'No wiki driver found: ' . $drname
            );
        }

        $driver = new $class($this->file, $this->metas, $this->options);
        $driver->run();
    }

}

?>