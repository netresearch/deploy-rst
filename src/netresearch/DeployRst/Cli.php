<?php
namespace netresearch\DeployRst;

class Cli
{
    public $file;
    public $metas;
    public $options;

    public function __construct($options = array())
    {
        $this->options = $options;
    }

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

    protected function loadMeta()
    {
        $this->metas = Rst::extractMeta($this->file);
    }

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